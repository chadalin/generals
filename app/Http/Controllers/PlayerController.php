<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Game;
use App\Models\Research;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{
    public function show(Player $player)
    {
        $this->authorize('view', $player);
        
        $player->load(['country', 'generals', 'research', 'game']);
        
        return view('players.show', compact('player'));
    }

    public function updateResources(Request $request, Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        
        $validated = $request->validate([
            'scientists' => 'required|integer|min:0|max:' . ($player->peasants + $player->scientists + $player->soldiers),
            'soldiers' => 'required|integer|min:0|max:' . ($player->peasants + $player->scientists + $player->soldiers),
        ]);

        $totalPopulation = $player->peasants + $player->scientists + $player->soldiers;
        $newPeasants = $totalPopulation - $validated['scientists'] - $validated['soldiers'];

        if ($newPeasants < 0) {
            return response()->json(['error' => 'Invalid population allocation'], 422);
        }

        $player->update([
            'scientists' => $validated['scientists'],
            'soldiers' => $validated['soldiers'],
            'peasants' => $newPeasants,
        ]);

        return response()->json([
            'success' => true,
            'resources' => [
                'scientists' => $player->scientists,
                'soldiers' => $player->soldiers,
                'peasants' => $player->peasants,
            ]
        ]);
    }

    public function allocateResearch(Request $request, Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        $research = $player->research ?? new Research(['player_id' => $player->id, 'game_id' => $game->id]);

        $validated = $request->validate([
            'research_area' => 'required|in:military,economy,science',
            'technology' => 'required|string',
        ]);

        $researchArea = $validated['research_area'];
        $technology = $validated['technology'];

        // Проверяем, доступна ли технология для исследования
        if (!$this->canResearchTechnology($research, $researchArea, $technology)) {
            return response()->json(['error' => 'Technology not available for research'], 422);
        }

        // Вычисляем стоимость исследования
        $cost = $this->calculateResearchCost($research, $researchArea, $technology);
        
        if ($player->money < $cost) {
            return response()->json(['error' => 'Not enough money for research'], 422);
        }

        // Выполняем исследование
        $research->{$technology} = true;
        $research->{$researchArea . '_progress'} += 100; // Завершаем текущий уровень
        $research->{$researchArea . '_level'} += 1;
        
        // Применяем бонусы от исследования
        $this->applyResearchBonuses($research, $researchArea, $technology);
        
        $research->save();
        $player->decrement('money', $cost);

        return response()->json([
            'success' => true,
            'research' => $research,
            'player_money' => $player->money
        ]);
    }

    public function recruitSoldiers(Request $request, Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:1000',
        ]);

        $cost = $validated['count'] * 10; // 10 денег за солдата

        if ($player->money < $cost) {
            return response()->json(['error' => 'Not enough money to recruit soldiers'], 422);
        }

        $player->increment('soldiers', $validated['count']);
        $player->decrement('money', $cost);
        $player->decrement('peasants', $validated['count']);

        return response()->json([
            'success' => true,
            'soldiers' => $player->soldiers,
            'peasants' => $player->peasants,
            'money' => $player->money
        ]);
    }

    public function trainScientists(Request $request, Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:500',
        ]);

        $cost = $validated['count'] * 20; // 20 денег за ученого

        if ($player->money < $cost) {
            return response()->json(['error' => 'Not enough money to train scientists'], 422);
        }

        $player->increment('scientists', $validated['count']);
        $player->decrement('money', $cost);
        $player->decrement('peasants', $validated['count']);

        return response()->json([
            'success' => true,
            'scientists' => $player->scientists,
            'peasants' => $player->peasants,
            'money' => $player->money
        ]);
    }

    public function getStatistics(Player $player)
    {
        $this->authorize('view', $player);

        $statistics = [
            'total_battles' => $player->allBattles->count(),
            'battles_won' => $player->battlesAsAttacker->where('result', 'attacker_win')->count() +
                            $player->battlesAsDefender->where('result', 'defender_win')->count(),
            'battles_lost' => $player->battlesAsAttacker->where('result', 'defender_win')->count() +
                             $player->battlesAsDefender->where('result', 'attacker_win')->count(),
            'territory_captured' => $player->battlesAsAttacker->sum('territory_captured'),
            'soldiers_lost' => $player->allBattles->sum(function($battle) use ($player) {
                return $battle->attacker_country_id === $player->country_id ? 
                    $battle->attacker_soldiers_lost : $battle->defender_soldiers_lost;
            }),
            'enemies_defeated' => $player->battlesAsAttacker->where('result', 'attacker_win')
                ->pluck('defender_country_id')->unique()->count(),
        ];

        $statistics['win_rate'] = $statistics['total_battles'] > 0 ? 
            round(($statistics['battles_won'] / $statistics['total_battles']) * 100, 2) : 0;

        return response()->json($statistics);
    }

    private function canResearchTechnology($research, $researchArea, $technology)
    {
        $availableTechnologies = [
            'military' => ['weapons', 'armor', 'tactics', 'logistics', 'siege'],
            'economy' => ['agriculture', 'trade', 'taxation', 'infrastructure', 'industry'],
            'science' => ['education', 'medicine', 'engineering', 'mathematics', 'philosophy'],
        ];

        return in_array($technology, $availableTechnologies[$researchArea]) &&
               !$research->{$technology};
    }

    private function calculateResearchCost($research, $researchArea, $technology)
    {
        $baseCosts = [
            'military' => 1000,
            'economy' => 800,
            'science' => 1200,
        ];

        $levelModifier = $research->{$researchArea . '_level'} * 200;
        return $baseCosts[$researchArea] + $levelModifier;
    }

    private function applyResearchBonuses($research, $researchArea, $technology)
    {
        $bonuses = [
            'military' => [
                'weapons' => ['attack_bonus' => 0.1],
                'armor' => ['defense_bonus' => 0.1],
                'tactics' => ['attack_bonus' => 0.05, 'defense_bonus' => 0.05],
                'logistics' => ['attack_bonus' => 0.08],
                'siege' => ['attack_bonus' => 0.12],
            ],
            'economy' => [
                'agriculture' => ['economy_bonus' => 0.15],
                'trade' => ['economy_bonus' => 0.12],
                'taxation' => ['economy_bonus' => 0.1],
                'infrastructure' => ['economy_bonus' => 0.08, 'research_bonus' => 0.05],
                'industry' => ['economy_bonus' => 0.2],
            ],
            'science' => [
                'education' => ['research_bonus' => 0.15],
                'medicine' => ['population_growth_bonus' => 0.1],
                'engineering' => ['research_bonus' => 0.1],
                'mathematics' => ['research_bonus' => 0.12],
                'philosophy' => ['research_bonus' => 0.08],
            ],
        ];

        if (isset($bonuses[$researchArea][$technology])) {
            foreach ($bonuses[$researchArea][$technology] as $bonusType => $bonusValue) {
                $research->{$bonusType} += $bonusValue;
            }
        }
    }
}