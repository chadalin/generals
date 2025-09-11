<?php

namespace App\Http\Controllers;

use App\Models\General;
use App\Models\Game;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneralController extends Controller
{
    public function show(General $general)
    {
        $this->authorize('view', $general);
        
        $general->load(['player', 'country', 'battles']);
        
        return view('generals.show', compact('general'));
    }

    public function hire(Request $request, Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $cost = 1000; // Базовая стоимость генерала

        if ($player->money < $cost) {
            return response()->json(['error' => 'Not enough money to hire general'], 422);
        }

        if ($player->generals()->count() >= 10) {
            return response()->json(['error' => 'Maximum number of generals reached'], 422);
        }

        // Генерируем характеристики генерала
        $stats = $this->generateGeneralStats();

        $general = General::create([
            'player_id' => $player->id,
            'country_id' => $player->country_id,
            'name' => $validated['name'],
            'speed' => $stats['speed'],
            'attack' => $stats['attack'],
            'defense' => $stats['defense'],
            'cost' => $cost,
            'order' => 'train',
            'soldiers_count' => 0,
        ]);

        $player->decrement('money', $cost);

        return response()->json([
            'success' => true,
            'general' => $general,
            'remaining_money' => $player->money,
        ]);
    }

    public function updateOrder(Request $request, General $general)
    {
        $this->authorize('update', $general);
        
        $validated = $request->validate([
            'order' => 'required|in:rest,train,attack,defend',
            'target_country_id' => 'nullable|exists:countries,id',
            'soldiers_count' => 'required|integer|min:0|max:' . $general->player->soldiers,
        ]);

        if ($validated['order'] === 'attack' && empty($validated['target_country_id'])) {
            return response()->json(['error' => 'Target country is required for attack order'], 422);
        }

        // Проверяем, что целевая страна является соседом
        if ($validated['order'] === 'attack') {
            $targetCountry = Country::find($validated['target_country_id']);
            if (!$this->areCountriesNeighbors($general->country, $targetCountry)) {
                return response()->json(['error' => 'Target country is not a neighbor'], 422);
            }
        }

        $general->update([
            'order' => $validated['order'],
            'target_country_id' => $validated['target_country_id'],
            'soldiers_count' => $validated['soldiers_count'],
        ]);

        return response()->json([
            'success' => true,
            'general' => $general->fresh(),
        ]);
    }

    public function train(General $general)
    {
        $this->authorize('update', $general);
        
        $trainingCost = 100; // Стоимость тренировки

        if ($general->player->money < $trainingCost) {
            return response()->json(['error' => 'Not enough money for training'], 422);
        }

        // Увеличиваем характеристики в зависимости от текущего порядка
        $improvements = [];
        
        switch ($general->order) {
            case 'train':
                $improvements = [
                    'attack' => rand(1, 3),
                    'defense' => rand(1, 2),
                ];
                break;
            case 'rest':
                $improvements = [
                    'speed' => rand(1, 2),
                    'experience' => rand(5, 10),
                ];
                break;
        }

        $general->increment('experience', $improvements['experience'] ?? 0);
        
        foreach ($improvements as $stat => $value) {
            if ($stat !== 'experience') {
                $general->increment($stat, $value);
            }
        }

        $general->player->decrement('money', $trainingCost);

        return response()->json([
            'success' => true,
            'improvements' => $improvements,
            'general' => $general->fresh(),
            'remaining_money' => $general->player->money,
        ]);
    }

    public dismiss(General $general)
    {
        $this->authorize('delete', $general);
        
        $refund = $general->cost * 0.5; // 50% возврат стоимости

        $general->player->increment('money', $refund);
        $general->delete();

        return response()->json([
            'success' => true,
            'refund' => $refund,
            'remaining_money' => $general->player->money,
        ]);
    }

    public function getBattleHistory(General $general)
    {
        $this->authorize('view', $general);
        
        $battles = $general->battles()
            ->with(['attackerCountry', 'defenderCountry'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function($battle) use ($general) {
                $wasAttacker = $battle->attacker_general_id === $general->id;
                $result = $this->getBattleResultForGeneral($battle, $general);
                
                return [
                    'battle' => $battle,
                    'role' => $wasAttacker ? 'attacker' : 'defender',
                    'result' => $result,
                    'opponent' => $wasAttacker ? $battle->defenderCountry : $battle->attackerCountry,
                    'soldiers_led' => $wasAttacker ? $battle->attacker_soldiers : $battle->defender_soldiers,
                    'soldiers_lost' => $wasAttacker ? $battle->attacker_soldiers_lost : $battle->defender_soldiers_lost,
                    'experience_gained' => $wasAttacker ? $battle->attacker_experience_gain : $battle->defender_experience_gain,
                ];
            });

        return response()->json([
            'battles' => $battles,
            'total_battles' => $battles->count(),
            'battles_won' => $battles->where('result', 'win')->count(),
            'battles_lost' => $battles->where('result', 'loss')->count(),
            'total_experience' => $battles->sum('experience_gained'),
        ]);
    }

    public function promote(General $general)
    {
        $this->authorize('update', $general);
        
        $promotionCost = $general->experience * 10;

        if ($general->player->money < $promotionCost) {
            return response()->json(['error' => 'Not enough money for promotion'], 422);
        }

        // Увеличиваем все характеристики при продвижении
        $improvements = [
            'attack' => rand(5, 10),
            'defense' => rand(5, 10),
            'speed' => rand(3, 7),
        ];

        foreach ($improvements as $stat => $value) {
            $general->increment($stat, $value);
        }

        $general->player->decrement('money', $promotionCost);
        $general->increment('experience', 50); // Бонус опыта за продвижение

        return response()->json([
            'success' => true,
            'improvements' => $improvements,
            'general' => $general->fresh(),
            'remaining_money' => $general->player->money,
        ]);
    }

    private function generateGeneralStats()
    {
        return [
            'speed' => rand(40, 80),
            'attack' => rand(40, 80),
            'defense' => rand(40, 80),
        ];
    }

    private function areCountriesNeighbors(Country $country1, Country $country2)
    {
        return $country1->territories()
            ->whereHas('neighbors', function($query) use ($country2) {
                $query->where('country_id', $country2->id);
            })
            ->exists();
    }

    private function getBattleResultForGeneral($battle, $general)
    {
        if ($battle->result === 'ongoing') {
            return 'ongoing';
        }

        $wasAttacker = $battle->attacker_general_id === $general->id;
        
        if (($wasAttacker && $battle->result === 'attacker_win') ||
            (!$wasAttacker && $battle->result === 'defender_win')) {
            return 'win';
        }

        return 'loss';
    }
}