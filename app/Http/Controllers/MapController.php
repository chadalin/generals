<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Territory;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    public function show(Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        $countries = $game->countries()->with('player')->get();
        $territories = $game->territories()->with('country')->get();
        
        return view('maps.show', compact('game', 'player', 'countries', 'territories'));
    }

    public function territoryInfo(Game $game, $x, $y)
    {
        $territory = $game->territories()
            ->where('x', $x)
            ->where('y', $y)
            ->with('country')
            ->first();

        if (!$territory) {
            return response()->json(['error' => 'Territory not found'], 404);
        }

        return response()->json([
            'territory' => $territory,
            'owner' => $territory->country ? $territory->country->name : 'Neutral',
            'resources' => $territory->resources,
            'type' => $territory->type
        ]);
    }

    public function updateMap(Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        
        // Get visible territories (own and adjacent)
        $visibleTerritories = $this->getVisibleTerritories($game, $player);
        
        return response()->json([
            'territories' => $visibleTerritories,
            'countries' => $game->countries->map(function($country) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'color' => $country->color,
                    'territory_count' => $country->territories()->count()
                ];
            })
        ]);
    }

    private function getVisibleTerritories(Game $game, $player)
    {
        $playerCountry = $player->country;
        
        if (!$playerCountry) {
            return collect();
        }

        // Get player's territories and adjacent ones
        $playerTerritories = $playerCountry->territories()->pluck('id');
        
        $visible = Territory::where('game_id', $game->id)
            ->where(function($query) use ($playerTerritories) {
                $query->whereIn('country_id', $playerTerritories)
                      ->orWhereIn('id', function($q) use ($playerTerritories) {
                          $q->select('id')
                            ->from('territories')
                            ->whereIn('id', $playerTerritories)
                            ->orWhereJsonContains('neighbors', $playerTerritories->toArray());
                      });
            })
            ->get()
            ->map(function($territory) {
                return [
                    'x' => $territory->x,
                    'y' => $territory->y,
                    'color' => $territory->country ? $territory->country->color : '#CCCCCC',
                    'type' => $territory->type,
                    'resources' => $territory->resources
                ];
            });

        return $visible;
    }

    public function generateMap(Game $game)
    {
        if ($game->players()->where('user_id', Auth::id())->exists()) {
            $game->generateMap();
            return redirect()->back()->with('success', 'Map generated successfully');
        }

        return redirect()->back()->with('error', 'Not authorized');
    }
}