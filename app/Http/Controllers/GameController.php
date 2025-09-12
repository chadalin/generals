<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::where('status', 'waiting')
                    ->orWhere('status', 'in_progress')
                    ->paginate(10);

        return view('games.index', compact('games'));
    }

    public function create()
    {
        return view('games.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'max_players' => 'required|integer|min:2|max:20',
            'map_size' => 'required|integer|min:50|max:200',
            'is_private' => 'boolean',
            'password' => 'nullable|string|min:3',
        ]);

        $game = Game::create([
            'name' => $validated['name'],
            'max_players' => $validated['max_players'],
            'map_size' => $validated['map_size'],
            'is_private' => $validated['is_private'] ?? false,
            'password' => $validated['password'] ?? null,
            'status' => 'waiting',
            'settings' => json_encode($request->only(['ai_difficulty', 'start_year'])),
        ]);

        // Create player for the host
        Player::create([
            'game_id' => $game->id,
            'user_id' => Auth::id(),
            'username' => Auth::user()->name,
            'is_ai' => false,
        ]);

        return redirect()->route('games.show', $game->id);
    }

    public function show(Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->first();
        
        if (!$player && $game->status === 'waiting') {
            if ($game->current_players < $game->max_players) {
                $player = Player::create([
                    'game_id' => $game->id,
                    'user_id' => Auth::id(),
                    'username' => Auth::user()->name,
                    'is_ai' => false,
                ]);
                $game->increment('current_players');
            }
        }

        return view('games.show', compact('game', 'player'));
    }

    public function play(Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        $country = $player->country;
        $generals = $player->generals;

        return view('games.play', compact('game', 'player', 'country', 'generals'));
    }

    public function start(Game $game)
    {
        if ($game->players()->where('user_id', Auth::id())->exists() && $game->status === 'waiting') {
            $game->update(['status' => 'in_progress']);
            $this->initializeGame($game);
        }

        return redirect()->route('games.play', $game->id);
    }

    private function initializeGame(Game $game)
    {
        // Generate countries
        $countries = [];
        $colors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF'];
        
        for ($i = 0; $i < $game->max_players; $i++) {
            $countries[] = Country::create([
                'game_id' => $game->id,
                'name' => 'Country ' . ($i + 1),
                'color' => $colors[$i % count($colors)],
                'territory' => 100,
                'population' => 1000,
                'x' => rand(10, $game->map_size - 10),
                'y' => rand(10, $game->map_size - 10),
                'is_alive' => true,
            ]);
        }

        // Assign countries to players
        $players = $game->players()->get();
        foreach ($players as $index => $player) {
            if (isset($countries[$index])) {
                $player->update(['country_id' => $countries[$index]->id]);
            }
        }

        // Create AI players if needed
        $aiCount = $game->max_players - $game->current_players;
        for ($i = 0; $i < $aiCount; $i++) {
            $aiPlayer = Player::create([
                'game_id' => $game->id,
                'user_id' => null,
                'username' => 'AI Player ' . ($i + 1),
                'is_ai' => true,
                'country_id' => $countries[$game->current_players + $i]->id,
            ]);
        }
    }

    public function processTurn(Request $request, Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->firstOrFail();
        
        // Process player actions
        $this->processPlayerActions($player, $request->all());

        // Mark player as ready
        $player->update(['is_ready' => true]);

        // Check if all players are ready
        if ($game->players()->where('is_ready', false)->count() === 0) {
            $this->processGameTurn($game);
        }

        return response()->json(['status' => 'success']);
    }

    private function processPlayerActions(Player $player, array $actions)
    {
        // Process resource allocation
        if (isset($actions['scientists'])) {
            $player->update(['scientists' => $actions['scientists']]);
        }

        if (isset($actions['soldiers'])) {
            $player->update(['soldiers' => $actions['soldiers']]);
        }

        // Process general orders
        if (isset($actions['generals'])) {
            foreach ($actions['generals'] as $generalId => $order) {
                $general = $player->generals()->find($generalId);
                if ($general) {
                    $general->update([
                        'order' => $order['type'],
                        'target_country_id' => $order['target'] ?? null,
                        'soldiers_count' => $order['soldiers'] ?? 0,
                    ]);
                }
            }
        }
    }

    private function processGameTurn(Game $game)
    {
        // Process all player actions
        foreach ($game->players as $player) {
            $this->executePlayerTurn($player);
        }

        // Process battles
        $this->processBattles($game);

        // Update game year
        $game->increment('year');

        // Reset player readiness
        $game->players()->update(['is_ready' => false]);

        // Check win condition
        $this->checkWinCondition($game);
    }

    private function executePlayerTurn(Player $player)
    {
        // Calculate resource production
        $grainProduction = $player->peasants * 2;
        $moneyProduction = $player->territory * 0.1;

        $player->update([
            'grain' => $player->grain + $grainProduction,
            'money' => $player->money + $moneyProduction,
        ]);

        // Process research
        $this->processResearch($player);

        // Process AI actions if AI player
        if ($player->is_ai) {
            $this->processAITurn($player);
        }
    }

    private function processResearch(Player $player)
    {
        // Process military research
        $militaryProgress = $player->scientists * 0.1;
        $player->increment('research_military', $militaryProgress);
    }

    private function processAITurn(Player $player)
{
    if (!$player->is_ai) return;

    $ai = new class($player) {
        private $player;

        public function __construct($player)
        {
            $this->player = $player;
        }

        public function executeTurn()
        {
            $this->allocateResources();
            $this->manageGenerals();
            $this->conductResearch();
        }

        private function allocateResources()
        {
            $totalPopulation = $this->player->peasants + $this->player->scientists + $this->player->soldiers;
            
            // AI логика распределения населения
            $scientists = min($totalPopulation * 0.2, 100);
            $soldiers = min($totalPopulation * 0.3, 200);
            $peasants = $totalPopulation - $scientists - $soldiers;

            $this->player->update([
                'scientists' => $scientists,
                'soldiers' => $soldiers,
                'peasants' => $peasants,
            ]);
        }

        private function manageGenerals()
        {
            $generals = $this->player->generals;

            if ($generals->isEmpty() && $this->player->money >= 1000) {
                $this->hireGeneral();
            }

            foreach ($generals as $general) {
                $this->assignGeneralOrder($general);
            }
        }

        private function hireGeneral()
        {
            $names = ['Александр', 'Цезарь', 'Наполеон', 'Жуков', 'Роммель'];
            
            \App\Models\General::create([
                'player_id' => $this->player->id,
                'country_id' => $this->player->country_id,
                'name' => $names[array_rand($names)],
                'speed' => rand(40, 80),
                'attack' => rand(40, 80),
                'defense' => rand(40, 80),
                'cost' => 1000,
                'order' => 'train',
            ]);

            $this->player->decrement('money', 1000);
        }

        private function assignGeneralOrder($general)
        {
            $neighbors = $this->player->country->neighbors ?? [];

            if (!empty($neighbors) && $this->player->soldiers > 50) {
                // Атака случайного соседа
                $target = $neighbors[array_rand($neighbors)];
                
                $general->update([
                    'order' => 'attack',
                    'target_country_id' => $target,
                    'soldiers_count' => min($this->player->soldiers, 50),
                ]);
            } else {
                // Тренировка или отдых
                $general->update([
                    'order' => rand(0, 1) ? 'train' : 'rest',
                    'soldiers_count' => 0,
                ]);
            }
        }

        private function conductResearch()
        {
            // Фокус на военные исследования
            $this->player->increment('research_military', $this->player->scientists * 0.1);
        }
    };

    $ai->executeTurn();
}

private function processBattles(Game $game)
{
    $battles = \App\Models\Battle::where('game_id', $game->id)
        ->where('result', 'ongoing')
        ->get();

    foreach ($battles as $battle) {
        $this->resolveBattle($battle);
    }

    // Автоматические атаки AI
    $aiPlayers = $game->players()->where('is_ai', true)->get();
    
    foreach ($aiPlayers as $aiPlayer) {
        $this->processAIAttacks($aiPlayer);
    }
}

private function resolveBattle(Battle $battle)
{
    $attackerPower = $battle->attacker_soldiers * $battle->damage_modifier;
    $defenderPower = $battle->defender_soldiers;

    $totalPower = $attackerPower + $defenderPower;
    $attackerWinChance = $attackerPower / $totalPower;

    if (rand(1, 100) <= $attackerWinChance * 100) {
        // Победа атакующего
        $battle->update([
            'result' => 'attacker_win',
            'attacker_soldiers_lost' => round($battle->attacker_soldiers * 0.3),
            'defender_soldiers_lost' => $battle->defender_soldiers,
            'territory_captured' => rand(10, 50),
            'ended_at' => now(),
        ]);
    } else {
        // Победа защитника
        $battle->update([
            'result' => 'defender_win',
            'attacker_soldiers_lost' => $battle->attacker_soldiers,
            'defender_soldiers_lost' => round($battle->defender_soldiers * 0.2),
            'ended_at' => now(),
        ]);
    }
}

private function processAIAttacks(Player $aiPlayer)
{
    $neighbors = $aiPlayer->country->neighbors ?? [];

    if (!empty($neighbors) && $aiPlayer->soldiers > 20) {
        $targetCountryId = $neighbors[array_rand($neighbors)];
        $soldiersCount = min($aiPlayer->soldiers, rand(20, 100));

        \App\Models\Battle::create([
            'game_id' => $aiPlayer->game_id,
            'attacker_country_id' => $aiPlayer->country_id,
            'defender_country_id' => $targetCountryId,
            'attacker_soldiers' => $soldiersCount,
            'defender_soldiers' => \App\Models\Country::find($targetCountryId)->player->soldiers ?? 0,
            'damage_modifier' => 1.0,
            'started_at' => now(),
            'result' => 'ongoing',
        ]);

        $aiPlayer->decrement('soldiers', $soldiersCount);
    }
}

    private function checkWinCondition(Game $game)
    {
        $aliveCountries = $game->countries()->where('is_alive', true)->count();
        
        if ($aliveCountries === 1) {
            $winner = $game->countries()->where('is_alive', true)->first();
            $game->update(['status' => 'finished']);
        }
    }
}