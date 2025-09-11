<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Game;
use App\Models\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CountryController extends Controller
{
    public function show(Country $country)
    {
        $country->load(['player', 'game', 'territories', 'generals']);
        
        return view('countries.show', compact('country'));
    }

    public function getTerritories(Country $country)
    {
        $this->authorize('view', $country);
        
        $territories = $country->territories()
            ->with(['neighbors' => function($query) {
                $query->with('country');
            }])
            ->get();

        return response()->json([
            'territories' => $territories,
            'total' => $territories->count(),
            'resources' => $territories->sum('resources'),
        ]);
    }

    public function getNeighbors(Country $country)
    {
        $this->authorize('view', $country);
        
        $neighborCountries = Country::where('game_id', $country->game_id)
            ->where('id', '!=', $country->id)
            ->whereHas('territories', function($query) use ($country) {
                $query->whereIn('id', function($q) use ($country) {
                    $q->select('neighbor_id')
                      ->from('territory_neighbors')
                      ->whereIn('territory_id', $country->territories()->pluck('id'));
                });
            })
            ->with(['player', 'territories'])
            ->get();

        return response()->json([
            'neighbors' => $neighborCountries,
            'count' => $neighborCountries->count(),
        ]);
    }

    public function getRelations(Country $country)
    {
        $this->authorize('view', $country);
        
        $relations = $country->relations ?? [];
        $allCountries = Country::where('game_id', $country->game_id)
            ->where('id', '!=', $country->id)
            ->get()
            ->mapWithKeys(function($neighbor) use ($relations) {
                return [
                    $neighbor->id => [
                        'country' => $neighbor,
                        'relation' => $relations[$neighbor->id] ?? 'neutral',
                        'is_neighbor' => true, // Здесь нужно добавить логику проверки соседства
                    ]
                ];
            });

        return response()->json([
            'relations' => $allCountries,
        ]);
    }

    public function updateRelation(Request $request, Country $country, Country $targetCountry)
    {
        $this->authorize('update', $country);
        
        $validated = $request->validate([
            'relation' => 'required|in:ally,neutral,enemy',
        ]);

        $relations = $country->relations ?? [];
        $relations[$targetCountry->id] = $validated['relation'];
        
        $country->update(['relations' => $relations]);

        // Логируем изменение отношений
        event(new CountryRelationChanged($country, $targetCountry, $validated['relation']));

        return response()->json([
            'success' => true,
            'relation' => $validated['relation'],
        ]);
    }

    public function getStatistics(Country $country)
    {
        $this->authorize('view', $country);
        
        $statistics = [
            'total_territories' => $country->territories->count(),
            'total_resources' => $country->territories->sum('resources'),
            'average_resources' => $country->territories->avg('resources'),
            'border_territories' => $country->territories->where('is_border', true)->count(),
            
            'military_power' => $country->player->soldiers + 
                              ($country->generals->sum('attack') + $country->generals->sum('defense')) * 10,
            
            'economic_power' => $country->player->money + 
                              $country->player->grain + 
                              ($country->territories->sum('resources') * 0.1),
            
            'scientific_power' => $country->player->scientists * 
                                 ($country->player->research->research_bonus ?? 1.0),
        ];

        // Добавляем статистику битв
        $battleStats = [
            'total_battles' => $country->allBattles->count(),
            'battles_won' => $country->battlesAsAttacker->where('result', 'attacker_win')->count() +
                           $country->battlesAsDefender->where('result', 'defender_win')->count(),
            'battles_lost' => $country->battlesAsAttacker->where('result', 'defender_win')->count() +
                            $country->battlesAsDefender->where('result', 'attacker_win')->count(),
            'territory_captured' => $country->battlesAsAttacker->sum('territory_captured'),
            'territory_lost' => $country->battlesAsDefender->where('result', 'attacker_win')
                ->sum('territory_captured'),
        ];

        $battleStats['win_rate'] = $battleStats['total_battles'] > 0 ? 
            round(($battleStats['battles_won'] / $battleStats['total_battles']) * 100, 2) : 0;

        return response()->json(array_merge($statistics, $battleStats));
    }

    public function expandTerritory(Request $request, Country $country)
    {
        $this->authorize('update', $country);
        
        $validated = $request->validate([
            'direction' => 'required|in:north,south,east,west',
            'territories_count' => 'required|integer|min:1|max:10',
        ]);

        $cost = $validated['territories_count'] * 50; // 50 денег за территорию

        if ($country->player->money < $cost) {
            return response()->json(['error' => 'Not enough money for expansion'], 422);
        }

        $newTerritories = $this->acquireNewTerritories($country, $validated['direction'], $validated['territories_count']);
        
        $country->player->decrement('money', $cost);
        $country->increment('territory', $newTerritories->count());

        return response()->json([
            'success' => true,
            'new_territories' => $newTerritories->count(),
            'total_territory' => $country->territory,
            'remaining_money' => $country->player->money,
        ]);
    }

    private function acquireNewTerritories(Country $country, $direction, $count)
    {
        $borderTerritories = $country->territories()->where('is_border', true)->get();
        $newTerritories = collect();

        foreach ($borderTerritories as $territory) {
            if ($newTerritories->count() >= $count) break;

            $neighborCoords = $this->getNeighborCoordinates($territory, $direction);
            $neighborTerritory = Territory::where('game_id', $country->game_id)
                ->where('x', $neighborCoords['x'])
                ->where('y', $neighborCoords['y'])
                ->whereNull('country_id')
                ->first();

            if ($neighborTerritory) {
                $neighborTerritory->update(['country_id' => $country->id]);
                $newTerritories->push($neighborTerritory);
            }
        }

        return $newTerritories;
    }

    private function getNeighborCoordinates(Territory $territory, $direction)
    {
        $coordinates = ['x' => $territory->x, 'y' => $territory->y];

        switch ($direction) {
            case 'north': $coordinates['y']--; break;
            case 'south': $coordinates['y']++; break;
            case 'east': $coordinates['x']++; break;
            case 'west': $coordinates['x']--; break;
        }

        return $coordinates;
    }
}