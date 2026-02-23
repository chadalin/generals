<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Country;
use App\Models\General;
use App\Models\Territory;
use App\Models\Battle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index()
{
    $games = Game::whereHas('players', function($query) {
        $query->where('user_id', Auth::id());
    })->orWhere('status', 'waiting')->get();
    
    // Декодируем settings для каждой игры
    foreach ($games as $game) {
        $game->settings_array = json_decode($game->settings, true) ?? [];
    }
    
    return view('games.index', compact('games'));
}

    /**
     * Show the form for creating a new game.
     */
    public function create()
    {
        return view('games.create');
    }

    /**
     * Store a newly created game in storage.
     */
public function store(Request $request)
{
    \Log::info('Game store method called', ['data' => $request->all()]);
    
    try {
        // Преобразуем checkbox'ы в boolean перед валидацией
        $request->merge([
            'random_countries' => $request->has('random_countries'),
            'fog_of_war' => $request->has('fog_of_war'),
            'is_private' => $request->has('is_private'),
        ]);

        // Правила валидации
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'max_players' => 'required|integer|min:2|max:20',
            'map_size' => 'required|integer|min:50|max:200',
            'ai_difficulty' => 'required|integer|min:1|max:3',
            'start_year' => 'required|integer|min:1950|max:2000',
            'is_private' => 'boolean',
            'password' => 'required_if:is_private,true|nullable|string|min:4',
            'random_countries' => 'boolean',
            'fog_of_war' => 'boolean',
        ]);

        \Log::info('Validation passed', ['validated' => $validated]);

        DB::beginTransaction();

        // Сохраняем map_size как число
        $mapSizeValue = $validated['map_size'];

        // Создаем игру
        $game = Game::create([
            'name' => $validated['name'],
            'max_players' => $validated['max_players'],
            'current_players' => 1,
            'map_size' => $mapSizeValue,
            'year' => $validated['start_year'],
            'current_year' => $validated['start_year'],
            'turn_duration' => 1,
            'is_private' => $validated['is_private'],
            'password' => $validated['is_private'] && !empty($validated['password']) 
                ? bcrypt($validated['password']) 
                : null,
            'status' => 'waiting',
            'settings' => json_encode([
                'ai_difficulty' => $validated['ai_difficulty'],
                'random_countries' => $validated['random_countries'],
                'fog_of_war' => $validated['fog_of_war'],
                'map_size_label' => $this->convertMapSizeToString($mapSizeValue),
                'map_width' => $this->getMapWidth($mapSizeValue),
                'map_height' => $this->getMapHeight($mapSizeValue),
            ]),
        ]);

        \Log::info('Game created', ['game_id' => $game->id]);

        // Создаем игрока (хозяина игры) - добавляем ВСЕ поля
        $player = Player::create([
            'user_id' => Auth::id(),
            'game_id' => $game->id,
            'username' => Auth::user()->name, // Обязательное поле
            'money' => 10000,
            'grain' => 5000,
            'scientists' => 500,
            'soldiers' => 1500,
            'peasants' => 8000,
            'research_military' => 0,
            'research_economy' => 0,
            'research_science' => 0,
            'is_ready' => 0, // false
            'is_ai' => 0, // false
            'ai_difficulty' => $validated['ai_difficulty'],
        ]);

        \Log::info('Player created', ['player_id' => $player->id]);

        // Создаем страну для игрока
        $country = Country::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'name' => 'Страна ' . Auth::user()->name,
            'color' => $this->getRandomColor(),
        ]);

        \Log::info('Country created', ['country_id' => $country->id]);

        // Создаем стартового генерала
        $general = General::create([
            'player_id' => $player->id,
            'country_id' => $country->id,
            'name' => $this->generateGeneralName(),
            'level' => 1,
            'experience' => 0,
            'order' => 'defend',
            'soldiers_count' => 500,
            'speed' => 5,
            'attack' => 5,
            'defense' => 5,
            'cost' => 1000,
            'age' => 30,
            'is_alive' => true,
        ]);

        \Log::info('General created', ['general_id' => $general->id]);

        DB::commit();

        \Log::info('Game creation completed successfully');

        return redirect()->route('games.index')
            ->with('success', 'Игра успешно создана!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed', ['errors' => $e->errors()]);
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Game creation failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Ошибка при создании игры: ' . $e->getMessage())
            ->withInput();
    }
}
// Добавьте этот метод в контроллер
private function convertMapSizeToString($size)
{
    if ($size <= 70) {
        return 'small';
    } elseif ($size <= 130) {
        return 'medium';
    } else {
        return 'large';
    }
}



private function getMapHeight($size)
{
    if ($size <= 70) {
        return 15; // small
    } elseif ($size <= 130) {
        return 20; // medium
    } else {
        return 25; // large
    }
}





    /**
     * Display the specified game.
     */
    public function show(Game $game)
    {
        $game->load(['players.user', 'countries.territories']);
        
        $currentPlayer = Player::where('game_id', $game->id)
            ->where('user_id', Auth::id())
            ->first();

        return view('games.show', compact('game', 'currentPlayer'));
    }

    /**
     * Show the form for editing the specified game.
     */
    public function edit(Game $game)
    {
        // Проверяем, является ли пользователь владельцем игры
        $player = Player::where('game_id', $game->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$player || $game->status !== 'waiting') {
            return redirect()->route('games.index')
                ->with('error', 'Вы не можете редактировать эту игру');
        }

        return view('games.edit', compact('game'));
    }

    /**
     * Update the specified game in storage.
     */
    public function update(Request $request, Game $game)
    {
        // Проверяем права на редактирование
        $player = Player::where('game_id', $game->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$player || $game->status !== 'waiting') {
            return redirect()->route('games.index')
                ->with('error', 'Вы не можете редактировать эту игру');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'max_players' => 'required|integer|min:2|max:8',
            'turn_duration' => 'required|integer|min:1|max:7',
            'is_private' => 'boolean',
            'password' => 'nullable|string|min:4',
        ]);

        $game->update([
            'name' => $validated['name'],
            'max_players' => $validated['max_players'],
            'turn_duration' => $validated['turn_duration'],
            'is_private' => $request->has('is_private'),
            'password' => $request->is_private && $request->password 
                ? bcrypt($validated['password']) 
                : $game->password,
        ]);

        return redirect()->route('games.show', $game)
            ->with('success', 'Игра успешно обновлена!');
    }

    /**
     * Remove the specified game from storage.
     */
    public function destroy(Game $game)
    {
        // Проверяем права на удаление
        $player = Player::where('game_id', $game->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$player || $game->status !== 'waiting') {
            return redirect()->route('games.index')
                ->with('error', 'Вы не можете удалить эту игру');
        }

        DB::transaction(function () use ($game) {
            // Удаляем связанные данные
            $game->players()->delete();
            $game->countries()->delete();
            $game->battles()->delete();
            $game->delete();
        });

        return redirect()->route('games.index')
            ->with('success', 'Игра успешно удалена!');
    }

    /**
     * Start the game.
     */
    public function start(Game $game)
    {
        // Проверяем, является ли пользователь владельцем игры
        $player = Player::where('game_id', $game->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$player || $game->status !== 'waiting') {
            return redirect()->route('games.index')
                ->with('error', 'Вы не можете начать эту игру');
        }

        if ($game->current_players < 2) {
            return redirect()->back()
                ->with('error', 'Недостаточно игроков для начала игры');
        }

        $game->update(['status' => 'active']);

        return redirect()->route('games.play', $game)
            ->with('success', 'Игра началась!');
    }

    public function play(Game $game)
{
    $player = Player::where('game_id', $game->id)
        ->where('user_id', Auth::id())
        ->with(['country.territories', 'generals'])
        ->firstOrFail();

    // Проверяем, есть ли у игрока страна
    if (!$player->country) {
        // Если нет страны, создаем её
        $country = Country::firstOrCreate([
            'game_id' => $game->id,
            'player_id' => $player->id,
        ], [
            'name' => 'Страна ' . $player->username,
            'color' => $this->getRandomColor(),
        ]);
        
        $player->update(['country_id' => $country->id]);
        $player->load('country');
    }

    $game->load([
        'countries.territories',
        'players.country',
        'players.generals'
    ]);

    return view('games.play', compact('game', 'player'));
}

    public function processTurn(Request $request, Game $game)
{
    $player = Player::where('game_id', $game->id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    DB::transaction(function () use ($request, $game, $player) {
        // Получаем общее население
        $totalPopulation = $player->peasants + $player->soldiers + $player->scientists;
        
        // Проверяем, чтобы сумма не превышала общее население
        $newScientists = min($request->scientists_count ?? $player->scientists, $totalPopulation);
        $newSoldiers = min($request->soldiers_count ?? $player->soldiers, $totalPopulation - $newScientists);
        $newPeasants = $totalPopulation - $newScientists - $newSoldiers;

        // Обновляем распределение населения
        $player->update([
            'scientists' => $newScientists,
            'soldiers' => $newSoldiers,
            'peasants' => $newPeasants,
        ]);

        // Процесс хода
        $this->processEconomy($player);
        $this->processResearch($player);
        $this->processGenerals($game, $player);
        $this->processBattles($game);

        // Увеличиваем год
        $game->increment('current_year');
    });

    return redirect()->back()->with('success', 'Ход успешно обработан!');
}
    /**
     * Get game status for API.
     */
    public function status(Game $game)
    {
        return response()->json([
            'status' => $game->status,
            'current_year' => $game->current_year,
            'players_count' => $game->current_players,
            'max_players' => $game->max_players,
        ]);
    }

    /**
     * Get players for API.
     */
    public function players(Game $game)
    {
        $players = $game->players()->with('user')->get()->map(function($player) {
            return [
                'id' => $player->id,
                'name' => $player->user->name,
                'country_name' => $player->country_name,
                'color' => $player->color,
                'is_ready' => $player->is_ready,
            ];
        });

        return response()->json($players);
    }

    // Приватные вспомогательные методы
    private function getMapWidth($size)
    {
        return [
            'small' => 20,
            'medium' => 30,
            'large' => 40,
        ][$size] ?? 20;
    }

    

    private function getRandomColor()
    {
        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEEAD', '#D4A5A5', '#9B59B6', '#3498DB'];
        return $colors[array_rand($colors)];
    }

    private function generateGeneralName()
    {
        $firstNames = ['Александр', 'Дмитрий', 'Николай', 'Михаил', 'Петр', 'Иван', 'Сергей', 'Владимир'];
        $lastNames = ['Суворов', 'Кутузов', 'Жуков', 'Рокоссовский', 'Конев', 'Василевский', 'Багратион', 'Ермолов'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

   
    
    
    private function generateGameMap(Game $game)
{
    // Очищаем старые территории для этой игры
    Territory::where('game_id', $game->id)->delete();
    
    $mapWidth = $game->getSetting('map_width', 30);
    $mapHeight = $game->getSetting('map_height', 20);
    $territoriesPerCountry = 10; // Количество территорий на страну
    
    $allTerritories = [];
    $countries = $game->countries;

    foreach ($countries as $country) {
        for ($i = 0; $i < $territoriesPerCountry; $i++) {
            $placed = false;
            $attempts = 0;
            
            while (!$placed && $attempts < 100) {
                $x = rand(0, $mapWidth - 1);
                $y = rand(0, $mapHeight - 1);
                
                // Проверяем, свободна ли клетка
                $existing = Territory::where('game_id', $game->id)
                    ->where('x', $x)
                    ->where('y', $y)
                    ->first();
                
                if (!$existing) {
                    // Проверяем, не слишком ли далеко от других территорий этой страны
                    $nearby = Territory::where('game_id', $game->id)
                        ->where('country_id', $country->id)
                        ->whereBetween('x', [$x - 3, $x + 3])
                        ->whereBetween('y', [$y - 3, $y + 3])
                        ->count();
                    
                    // Первая территория (столица) может быть где угодно
                    if ($i == 0 || $nearby > 0) {
                        $territory = Territory::create([
                            'game_id' => $game->id,
                            'country_id' => $country->id,
                            'name' => $this->generateTerritoryName(),
                            'x' => $x,
                            'y' => $y,
                            'population' => rand(500, 5000),
                            'resource_value' => rand(50, 300),
                            'is_capital' => ($i == 0),
                            'type' => $this->getRandomTerritoryType(),
                        ]);
                        
                        $allTerritories[] = $territory;
                        $placed = true;
                    }
                }
                $attempts++;
            }
        }
    }
    
    // Заполняем оставшиеся клетки нейтральными территориями
    $this->generateNeutralTerritories($game, $mapWidth, $mapHeight);
    
    return $allTerritories;
}

private function generateTerritoryName()
{
    $prefixes = ['Северная', 'Южная', 'Восточная', 'Западная', 'Центральная', 'Новая', 'Старая', 'Великая'];
    $names = ['Земля', 'Область', 'Провинция', 'Регион', 'Край', 'Долина', 'Равнина', 'Горы', 'Лес', 'Поле'];
    $suffixes = ['ская', 'цкая', 'ская область', 'ский край', 'ская провинция'];
    
    return $prefixes[array_rand($prefixes)] . ' ' . 
           $names[array_rand($names)] . ' ' . 
           $suffixes[array_rand($suffixes)];
}

private function getRandomTerritoryType()
{
    $types = ['normal', 'forest', 'mountain', 'hill', 'plain', 'desert'];
    return $types[array_rand($types)];
}

private function generateNeutralTerritories(Game $game, $width, $height)
{
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $existing = Territory::where('game_id', $game->id)
                ->where('x', $x)
                ->where('y', $y)
                ->first();
            
            if (!$existing) {
                Territory::create([
                    'game_id' => $game->id,
                    'country_id' => null,
                    'name' => 'Ничейная земля',
                    'x' => $x,
                    'y' => $y,
                    'population' => rand(100, 500),
                    'resource_value' => rand(10, 50),
                    'is_capital' => false,
                    'type' => 'neutral',
                ]);
            }
        }
    }
}

    private function processEconomy(Player $player)
{
    // Проверяем, какие поля существуют
    $income = $player->peasants * 2;
    $grainProduction = $player->peasants * 3;

    $player->increment('money', $income);
    $player->increment('grain', $grainProduction);
}

    private function processResearch(Player $player)
{
    // Используем существующие поля для исследований
    $researchPoints = $player->scientists * 5;
    
    // Распределяем исследования поровну или добавляем в одно поле
    // Вариант 1: Распределяем поровну между тремя типами
    $pointsPerType = floor($researchPoints / 3);
    
    $player->increment('research_military', $pointsPerType);
    $player->increment('research_economy', $pointsPerType);
    $player->increment('research_science', $researchPoints - ($pointsPerType * 2));
    
    // Вариант 2: Добавляем все в research_military (выберите нужный)
    // $player->increment('research_military', $researchPoints);
}

    private function processGenerals(Game $game, Player $player)
    {
        foreach ($player->generals as $general) {
            $this->executeGeneralOrder($general, $game);
        }
    }

    private function executeGeneralOrder(General $general, Game $game)
    {
        $currentTerritory = $general->currentTerritory;
        if (!$currentTerritory) return;

        switch ($general->order) {
            case 'attack':
                $this->processAttackOrder($general, $currentTerritory, $game);
                break;
            case 'defend':
                $this->processDefendOrder($general, $currentTerritory);
                break;
            case 'expand':
                $this->processExpandOrder($general, $currentTerritory, $game);
                break;
        }

        $general->increment('experience', 10);
        if ($general->experience >= $general->level * 100) {
            $general->increment('level');
            $general->update(['experience' => 0]);
        }
    }

    private function processAttackOrder(General $general, Territory $territory, Game $game)
    {
        $neighbor = $this->findEnemyNeighbor($territory, $general->player->country_id, $game);
        
        if ($neighbor) {
            Battle::create([
                'game_id' => $game->id,
                'attacker_country_id' => $general->player->country_id,
                'defender_country_id' => $neighbor->country_id,
                'territory_id' => $neighbor->id,
                'result' => 'pending'
            ]);
        }
    }

    private function processDefendOrder(General $general, Territory $territory)
    {
        // Логика защиты
        $territory->increment('defense_bonus', 10);
    }

    private function processExpandOrder(General $general, Territory $territory, Game $game)
    {
        // Логика расширения
        $neighbors = $this->findNeutralNeighbors($territory, $game);
        if (count($neighbors) > 0) {
            // Захватываем первую нейтральную территорию
            $target = $neighbors[0];
            $target->update(['country_id' => $general->player->country_id]);
        }
    }

    private function findEnemyNeighbor(Territory $territory, $playerCountryId, Game $game)
    {
        $directions = [
            [-1, 0], [1, 0], [0, -1], [0, 1],
            [-1, -1], [-1, 1], [1, -1], [1, 1]
        ];

        foreach ($directions as $dir) {
            $newX = $territory->x + $dir[0];
            $newY = $territory->y + $dir[1];

            if ($newX >= 0 && $newX < $game->map_width && 
                $newY >= 0 && $newY < $game->map_height) {
                
                $neighbor = Territory::where('x', $newX)
                    ->where('y', $newY)
                    ->where('country_id', '!=', $playerCountryId)
                    ->first();

                if ($neighbor) return $neighbor;
            }
        }

        return null;
    }

    private function findNeutralNeighbors(Territory $territory, Game $game)
    {
        $neighbors = [];
        $directions = [
            [-1, 0], [1, 0], [0, -1], [0, 1]
        ];

        foreach ($directions as $dir) {
            $newX = $territory->x + $dir[0];
            $newY = $territory->y + $dir[1];

            if ($newX >= 0 && $newX < $game->map_width && 
                $newY >= 0 && $newY < $game->map_height) {
                
                $neighbor = Territory::where('x', $newX)
                    ->where('y', $newY)
                    ->whereNull('country_id')
                    ->first();

                if ($neighbor) $neighbors[] = $neighbor;
            }
        }

        return $neighbors;
    }

    private function processBattles(Game $game)
    {
        $battles = Battle::where('game_id', $game->id)
            ->where('result', 'pending')
            ->get();

        foreach ($battles as $battle) {
            $this->resolveBattle($battle);
        }
    }

    private function resolveBattle(Battle $battle)
    {
        // Простая логика разрешения битвы
        $attackerPower = rand(1, 100);
        $defenderPower = rand(1, 100);

        if ($attackerPower > $defenderPower) {
            $battle->update(['result' => 'attacker_wins']);
            // Передаем территорию атакующему
            $battle->territory->update(['country_id' => $battle->attacker_country_id]);
        } else {
            $battle->update(['result' => 'defender_wins']);
        }
    }
    /**
 * Join a game.
 */
public function join(Request $request, Game $game)
{
    // Проверяем, не в игре ли уже пользователь
    $existingPlayer = Player::where('game_id', $game->id)
        ->where('user_id', Auth::id())
        ->first();

    if ($existingPlayer) {
        return redirect()->route('games.show', $game)
            ->with('error', 'Вы уже в этой игре');
    }

    // Проверяем, не заполнена ли игра
    if ($game->current_players >= $game->max_players) {
        return redirect()->route('games.index')
            ->with('error', 'Игра уже заполнена');
    }

    // Проверяем статус игры
    if ($game->status !== 'waiting') {
        return redirect()->route('games.index')
            ->with('error', 'Игра уже началась');
    }

    // Проверяем пароль для приватной игры
    if ($game->is_private) {
        $request->validate([
            'password' => 'required|string'
        ]);

        if (!password_verify($request->password, $game->password)) {
            return redirect()->back()
                ->with('error', 'Неверный пароль');
        }
    }

    DB::transaction(function () use ($game) {
        // Создаем игрока
        $player = Player::create([
            'user_id' => Auth::id(),
            'game_id' => $game->id,
            'country_name' => 'Страна ' . Auth::user()->name,
            'color' => $this->getRandomColor(),
            'total_population' => 10000,
            'peasants_count' => 8000,
            'soldiers_count' => 1500,
            'scientists_count' => 500,
            'money' => 5000,
            'grain' => 3000,
            'research_points' => 0,
        ]);

        // Создаем страну
        $country = Country::create([
    'game_id' => $game->id,
    'player_id' => $player->id,
    'name' => 'Страна ' . Auth::user()->name,
    'color' => $this->getRandomColor(),
]);


         $player->update(['country_id' => $country->id]);

        // Создаем стартового генерала
        General::create([
            'player_id' => $player->id,
            'name' => $this->generateGeneralName(),
            'level' => 1,
            'experience' => 0,
            'order' => 'defend',
        ]);

        // Увеличиваем счетчик игроков
        $game->increment('current_players');
    });

    return redirect()->route('games.show', $game)
        ->with('success', 'Вы присоединились к игре!');
}
}