<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Стратегическая игра для военных Генералы</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AlpineJS CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        .game-interface {
            background: #1a202c;
            min-height: 100vh;
            color: white;
        }
        .game-panel {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            margin-bottom: 1rem;
        }
        .resource-bar {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 5px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .tab-content {
            min-height: 400px;
        }
        .general-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .general-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        .general-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.2);
        }
        .event-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .map-cell {
            width: 10px;
            height: 10px;
            display: inline-block;
            margin: 1px;
        }
        .player-territory {
            background-color: #e53e3e;
        }
        .neutral-territory {
            background-color: #a0aec0;
        }
        .enemy-territory {
            background-color: #805ad5;
        }
        .slider-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .slider-value {
            min-width: 40px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 2px 5px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="game-interface">
    <!-- Top Resource Bar -->
    <div class="container-fluid py-2 bg-dark">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex gap-3">
                    <span class="text-warning">
                        <i class="fas fa-coins"></i> Money: <span id="money-value">{{ number_format($player->money) }}</span>
                    </span>
                    <span class="text-success">
                        <i class="fas fa-wheat"></i> Grain: <span id="grain-value">{{ number_format($player->grain) }}</span>
                    </span>
                    <span class="text-info">
                        <i class="fas fa-users"></i> Population: <span id="population-value">{{ number_format($player->peasants + $player->scientists + $player->soldiers) }}</span>
                    </span>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-light me-3">
                    Year: {{ $game->year }}
                </span>
                <span class="badge bg-{{ $game->status === 'in_progress' ? 'success' : 'warning' }}">
                    {{ ucfirst($game->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-lg-3">
                <!-- Country Info -->
                <div class="game-panel p-3 mb-3">
                    <h5 class="text-center mb-3">
                        <span class="country-color" style="background: {{ $country->color }}; width: 20px; height: 20px; display: inline-block; border-radius: 50%; margin-right: 10px;"></span>
                        {{ $country->name }}
                    </h5>
                    
                    <div class="resource-bar">
                        <small class="text-muted">Territory</small>
                        <div class="d-flex justify-content-between">
                            <span>{{ number_format($country->territory) }} km²</span>
                            <span class="text-success">+0%</span>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <small class="text-muted">Population</small>
                        <div class="progress mb-1" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: {{ ($player->peasants / ($player->peasants + $player->scientists + $player->soldiers)) * 100 }}%"></div>
                            <div class="progress-bar bg-info" style="width: {{ ($player->scientists / ($player->peasants + $player->scientists + $player->soldiers)) * 100 }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ ($player->soldiers / ($player->peasants + $player->scientists + $player->soldiers)) * 100 }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-success">Peasants: <span id="peasants-value">{{ $player->peasants }}</span></span>
                            <span class="text-info">Scientists: <span id="scientists-value">{{ $player->scientists }}</span></span>
                            <span class="text-danger">Soldiers: <span id="soldiers-value">{{ $player->soldiers }}</span></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="game-panel p-3 mb-3">
                    <h6 class="text-center mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recruitModal">
                            <i class="fas fa-shield-alt"></i> Recruit Soldiers
                        </button>
                        <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#scientistsModal">
                            <i class="fas fa-flask"></i> Train Scientists
                        </button>
                        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#researchModal">
                            <i class="fas fa-research"></i> Research
                        </button>
                    </div>
                </div>

                <!-- Generals List -->
                <div class="game-panel p-3">
                    <h6 class="text-center mb-3">Generals</h6>
                    <div id="generals-list">
                        @foreach($generals as $general)
                        <div class="general-card" data-general-id="{{ $general->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>{{ $general->name }}</strong>
                                <span class="badge bg-{{ $general->order === 'attack' ? 'danger' : ($general->order === 'defend' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($general->order) }}
                                </span>
                            </div>
                            <small class="text-muted">
                                A:{{ $general->attack }} D:{{ $general->defense }} S:{{ $general->speed }}
                            </small>
                            <div class="progress mt-1" style="height: 3px;">
                                <div class="progress-bar" style="width: {{ $general->experience % 100 }}%;"></div>
                            </div>
                        </div>
                        @endforeach
                        
                        <button class="btn btn-outline-success btn-sm w-100 mt-2" data-bs-toggle="modal" data-bs-target="#hireGeneralModal">
                            <i class="fas fa-plus"></i> Hire General
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-6">
                <!-- Game Tabs -->
                <ul class="nav nav-tabs" id="gameTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="map-tab" data-bs-toggle="tab" data-bs-target="#map" type="button" role="tab">
                            <i class="fas fa-map"></i> Map
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="military-tab" data-bs-toggle="tab" data-bs-target="#military" type="button" role="tab">
                            <i class="fas fa-shield-alt"></i> Military
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="economy-tab" data-bs-toggle="tab" data-bs-target="#economy" type="button" role="tab">
                            <i class="fas fa-coins"></i> Economy
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="research-tab" data-bs-toggle="tab" data-bs-target="#research" type="button" role="tab">
                            <i class="fas fa-flask"></i> Research
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="diplomacy-tab" data-bs-toggle="tab" data-bs-target="#diplomacy" type="button" role="tab">
                            <i class="fas fa-handshake"></i> Diplomacy
                        </button>
                    </li>
                </ul>

                <div class="tab-content game-panel p-3" id="gameTabsContent">
                    <!-- Map Tab -->
                    <div class="tab-pane fade show active" id="map" role="tabpanel">
                        <div class="text-center">
                            <h4>World Map</h4>
                            <p class="text-muted">Interactive map showing territories and movements</p>
                            <div class="bg-dark rounded p-4">
                                <div id="game-map" style="width: 500px; height: 500px; margin: 0 auto; border: 1px solid #333;">
                                    <!-- Map will be generated here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Military Tab -->
                    <div class="tab-pane fade" id="military" role="tabpanel">
                        <h4>Military Overview</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Army Strength</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <img src="https://cdn-icons-png.flaticon.com/512/1695/1695218.png" alt="Army" width="80" class="mb-2">
                                            <h3>{{ number_format($player->soldiers) }}</h3>
                                            <p class="text-muted">Total Soldiers</p>
                                        </div>
                                        <p>⚔️ Attack Power: {{ number_format($player->soldiers * 1.5) }}</p>
                                        <p>🛡️ Defense Power: {{ number_format($player->soldiers * 1.2) }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Generals</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <img src="https://cdn-icons-png.flaticon.com/512/3474/3474365.png" alt="Generals" width="80" class="mb-2">
                                            <h3>{{ number_format($generals->count()) }}</h3>
                                            <p class="text-muted">Total Generals</p>
                                        </div>
                                        <p>⭐ Experience: {{ number_format($generals->sum('experience')) }}</p>
                                        <p>⚡ Average Speed: {{ number_format($generals->avg('speed'), 1) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">Recent Battles</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <img src="https://cdn-icons-png.flaticon.com/512/185/185932.png" alt="Battles" width="80" class="mb-2">
                                </div>
                                @if($player->allBattles->count() > 0)
                                    @foreach($player->allBattles->take(3) as $battle)
                                    <div class="battle-item mb-2 p-2 border rounded">
                                        <strong>{{ $battle->attackerCountry->name }} vs {{ $battle->defenderCountry->name }}</strong>
                                        <br>
                                        <small class="text-muted">Result: {{ ucfirst($battle->result) }} | Losses: {{ $battle->attacker_soldiers_lost + $battle->defender_soldiers_lost }}</small>
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-muted text-center">No battles yet</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Economy Tab -->
                    <div class="tab-pane fade" id="economy" role="tabpanel">
                        <h4>Economic Overview</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Resource Production</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>💰 Money Production: {{ number_format($player->territory * 0.1) }} per turn</p>
                                        <p>🌾 Grain Production: {{ number_format($player->peasants * 2) }} per turn</p>
                                        <p>👨‍🌾 Peasants: {{ number_format($player->peasants) }}</p>
                                        <p>📊 Efficiency: {{ number_format(($player->peasants / ($player->peasants + $player->scientists + $player->soldiers)) * 100, 1) }}%</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">Buildings & Infrastructure</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>🏠 Farms: {{ number_format($player->peasants / 100) }}</p>
                                        <p>🏭 Factories: 0</p>
                                        <p>🏛️ Universities: {{ number_format($player->scientists / 50) }}</p>
                                        <p>🛡️ Barracks: {{ number_format($player->soldiers / 100) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Economic Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-warning w-100 mb-2" onclick="buildFarm()">
                                            🏠 Build Farm ($500)
                                        </button>
                                        <small class="text-muted">+10 grain production</small>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-info w-100 mb-2" onclick="buildFactory()">
                                            🏭 Build Factory ($1000)
                                        </button>
                                        <small class="text-muted">+20 money production</small>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-success w-100 mb-2" onclick="buildMarket()">
                                            🏪 Build Market ($800)
                                        </button>
                                        <small class="text-muted">+15% trade efficiency</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Research Tab -->
                    <div class="tab-pane fade" id="research" role="tabpanel">
                        <h4>Research & Development</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0">Military Research</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Level: {{ $player->research->military_level ?? 0 }}</p>
                                        <p>Progress: {{ $player->research->military_progress ?? 0 }}%</p>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-danger" style="width: {{ $player->research->military_progress ?? 0 }}%"></div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger w-100" onclick="researchMilitary()">
                                            🔫 Research Military
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">Economic Research</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Level: {{ $player->research->economy_level ?? 0 }}</p>
                                        <p>Progress: {{ $player->research->economy_progress ?? 0 }}%</p>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-warning" style="width: {{ $player->research->economy_progress ?? 0 }}%"></div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-warning w-100" onclick="researchEconomy()">
                                            💰 Research Economy
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Science Research</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Level: {{ $player->research->science_level ?? 0 }}</p>
                                        <p>Progress: {{ $player->research->science_progress ?? 0 }}%</p>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-info" style="width: {{ $player->research->science_progress ?? 0 }}%"></div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-info w-100" onclick="researchScience()">
                                            🔬 Research Science
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Available Technologies</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="technology-item mb-3">
                                            <h6>🛡️ Improved Armor</h6>
                                            <p class="text-muted small">+10% defense for all units</p>
                                            <span class="badge bg-secondary">Military Lvl 2</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="technology-item mb-3">
                                            <h6>🌾 Advanced Farming</h6>
                                            <p class="text-muted small">+25% grain production</p>
                                            <span class="badge bg-secondary">Economy Lvl 1</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="technology-item mb-3">
                                            <h6>🔭 Scientific Method</h6>
                                            <p class="text-muted small">+20% research speed</p>
                                            <span class="badge bg-secondary">Science Lvl 3</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Diplomacy Tab -->
                    <div class="tab-pane fade" id="diplomacy" role="tabpanel">
                        <h4>Diplomatic Relations</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Diplomatic Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <img src="https://cdn-icons-png.flaticon.com/512/1006/1006581.png" alt="Diplomacy" width="80">
                                        </div>
                                        <p>🤝 Allies: 0</p>
                                        <p>⚖️ Neutral: {{ $game->countries->count() - 1 }}</p>
                                        <p>⚔️ Enemies: 0</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">Diplomatic Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-outline-primary w-100 mb-2" onclick="proposeAlliance()">
                                            🤝 Propose Alliance
                                        </button>
                                        <button class="btn btn-outline-secondary w-100 mb-2" onclick="declareWar()">
                                            ⚔️ Declare War
                                        </button>
                                        <button class="btn btn-outline-success w-100 mb-2" onclick="offerTrade()">
                                            💰 Offer Trade Deal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-3">
                <!-- Turn Actions -->
                <div class="game-panel p-3 mb-3">
                    <h6 class="text-center mb-3">Turn Actions</h6>
                    <form id="turn-form" action="{{ route('games.process-turn', $game->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Scientists: {{ $player->scientists }}</label>
                            <div class="slider-container">
                                <input type="range" name="scientists" class="form-range" id="scientists-slider"
                                       min="0" max="{{ $player->peasants + $player->scientists + $player->soldiers }}"
                                       value="{{ $player->scientists }}">
                                <span class="slider-value" id="scientists-slider-value">{{ $player->scientists }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Soldiers: {{ $player->soldiers }}</label>
                            <div class="slider-container">
                                <input type="range" name="soldiers" class="form-range" id="soldiers-slider"
                                       min="0" max="{{ $player->peasants + $player->scientists + $player->soldiers }}"
                                       value="{{ $player->soldiers }}">
                                <span class="slider-value" id="soldiers-slider-value">{{ $player->soldiers }}</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check"></i> End Turn
                        </button>
                    </form>
                </div>

                <!-- Events Log -->
                <div class="game-panel p-3">
                    <h6 class="text-center mb-3">Events Log</h6>
                    <div id="events-log" style="height: 300px; overflow-y: auto;">
                        <div class="event-item border-success">
                            <div class="d-flex justify-content-between">
                                <span>Game started successfully</span>
                                <small class="text-muted">{{ now()->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="event-item border-info">
                            <div class="d-flex justify-content-between">
                                <span>Research completed: Improved Farming</span>
                                <small class="text-muted">{{ now()->subMinutes(5)->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="event-item border-danger">
                            <div class="d-flex justify-content-between">
                                <span>Border skirmish with Red Kingdom - 50 soldiers lost</span>
                                <small class="text-muted">{{ now()->subMinutes(15)->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="event-item border-success">
                            <div class="d-flex justify-content-between">
                                <span>Harvest completed: +1250 grain</span>
                                <small class="text-muted">{{ now()->subMinutes(30)->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="event-item border-info">
                            <div class="d-flex justify-content-between">
                                <span>New technology available: Military Tactics</span>
                                <small class="text-muted">{{ now()->subMinutes(45)->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="event-item border-success">
                            <div class="d-flex justify-content-between">
                                <span>Trade agreement with Blue Alliance: +500 money</span>
                                <small class="text-muted">{{ now()->subHours(1)->format('H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="recruitModal" tabindex="-1" aria-labelledby="recruitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="recruitModalLabel">Recruit Soldiers</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="soldiersCount" class="form-label">Number of Soldiers</label>
                    <input type="number" class="form-control" id="soldiersCount" min="1" max="1000" value="10">
                    <div class="form-text">Cost: 10 grain per soldier</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="recruitSoldiers()">Recruit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="scientistsModal" tabindex="-1" aria-labelledby="scientistsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="scientistsModalLabel">Train Scientists</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="scientistsCount" class="form-label">Number of Scientists</label>
                    <input type="number" class="form-control" id="scientistsCount" min="1" max="1000" value="5">
                    <div class="form-text">Cost: 15 grain per scientist</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="trainScientists()">Train</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="researchModal" tabindex="-1" aria-labelledby="researchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="researchModalLabel">Research</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Research Focus</label>
                    <select class="form-select" id="researchFocus">
                        <option value="military">Military Technology</option>
                        <option value="economy">Economic Development</option>
                        <option value="science">Scientific Advancement</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="researchPoints" class="form-label">Research Points Allocation</label>
                    <input type="range" class="form-range" id="researchPoints" min="0" max="100" value="50">
                    <div class="form-text" id="researchPointsText">Allocating 50% of scientists to research</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyResearch()">Apply</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hireGeneralModal" tabindex="-1" aria-labelledby="hireGeneralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="hireGeneralModalLabel">Hire General</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="generalName" class="form-label">General Name</label>
                    <input type="text" class="form-control" id="generalName" placeholder="Enter general's name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Specialization</label>
                    <select class="form-select" id="generalSpecialization">
                        <option value="attack">Attack</option>
                        <option value="defense">Defense</option>
                        <option value="speed">Mobility</option>
                    </select>
                </div>
                <div class="form-text">Cost: 1000 money</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="hireGeneral()">Hire</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Initialize game when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeGame();
    });

    function initializeGame() {
        // Initialize map
        generateMap();
        
        // Initialize event listeners
        initializeEventListeners();
        
        // Initialize sliders
        initializeSliders();
        
        // Start game clock
        startGameClock();
    }

    function generateMap() {
        const mapContainer = document.getElementById('game-map');
        if (!mapContainer) return;
        
        // Clear previous map
        mapContainer.innerHTML = '';
        
        // Generate a simple grid map
        const mapSize = 50;
        for (let y = 0; y < mapSize; y++) {
            for (let x = 0; x < mapSize; x++) {
                const cell = document.createElement('div');
                cell.className = 'map-cell';
                
                // Random territory type for demonstration
                const rand = Math.random();
                if (rand < 0.1) {
                    cell.classList.add('player-territory');
                } else if (rand < 0.3) {
                    cell.classList.add('enemy-territory');
                } else {
                    cell.classList.add('neutral-territory');
                }
                
                mapContainer.appendChild(cell);
            }
            mapContainer.appendChild(document.createElement('br'));
        }
    }

    function initializeEventListeners() {
        // General selection
        const generalCards = document.querySelectorAll('.general-card');
        generalCards.forEach(card => {
            card.addEventListener('click', function() {
                generalCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                const generalId = this.dataset.generalId;
                loadGeneralDetails(generalId);
            });
        });

        // Research points slider
        const researchSlider = document.getElementById('researchPoints');
        if (researchSlider) {
            researchSlider.addEventListener('input', function() {
                const researchText = document.getElementById('researchPointsText');
                researchText.textContent = `Allocating ${this.value}% of scientists to research`;
            });
        }

        // Form submission
        const turnForm = document.getElementById('turn-form');
        if (turnForm) {
            turnForm.addEventListener('submit', function(e) {
                e.preventDefault();
                processTurn();
            });
        }
    }

    function initializeSliders() {
        // Scientists slider
        const scientistsSlider = document.getElementById('scientists-slider');
        const scientistsValue = document.getElementById('scientists-slider-value');
        
        if (scientistsSlider && scientistsValue) {
            scientistsSlider.addEventListener('input', function() {
                scientistsValue.textContent = this.value;
                updatePopulationSummary();
            });
        }

        // Soldiers slider
        const soldiersSlider = document.getElementById('soldiers-slider');
        const soldiersValue = document.getElementById('soldiers-slider-value');
        
        if (soldiersSlider && soldiersValue) {
            soldiersSlider.addEventListener('input', function() {
                soldiersValue.textContent = this.value;
                updatePopulationSummary();
            });
        }
    }

    function updatePopulationSummary() {
        const scientistsSlider = document.getElementById('scientists-slider');
        const soldiersSlider = document.getElementById('soldiers-slider');
        
        if (scientistsSlider && soldiersSlider) {
            const scientists = parseInt(scientistsSlider.value);
            const soldiers = parseInt(soldiersSlider.value);
            const totalPopulation = {{ $player->peasants + $player->scientists + $player->soldiers }};
            const peasants = totalPopulation - scientists - soldiers;
            
            document.getElementById('peasants-value').textContent = peasants;
            document.getElementById('scientists-value').textContent = scientists;
            document.getElementById('soldiers-value').textContent = soldiers;
        }
    }

    function startGameClock() {
        // Update game clock every minute
        setInterval(() => {
            // This would typically update game time from server
            console.log('Game clock tick');
        }, 60000);
    }

    function loadGeneralDetails(generalId) {
        // In a real game, this would fetch general details from the server
        console.log('Loading details for general:', generalId);
        
        // For demonstration, show an alert
        alert(`Details for general ${generalId} would be loaded here`);
    }

    function processTurn() {
        // Collect form data
        const formData = new FormData(document.getElementById('turn-form'));
        
        // Add additional data
        formData.append('research_focus', document.getElementById('researchFocus').value);
        formData.append('research_points', document.getElementById('researchPoints').value);
        
        // Submit turn to server
        fetch('{{ route("games.process-turn", $game->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update game state with response data
                updateGameState(data);
                addEvent('Turn processed successfully', 'success');
            } else {
                addEvent('Error processing turn: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            addEvent('Network error occurred while processing turn', 'error');
        });
    }

    function updateGameState(gameData) {
        // Update resources
        if (gameData.money !== undefined) {
            document.getElementById('money-value').textContent = gameData.money.toLocaleString();
        }
        if (gameData.grain !== undefined) {
            document.getElementById('grain-value').textContent = gameData.grain.toLocaleString();
        }
        if (gameData.population !== undefined) {
            document.getElementById('population-value').textContent = gameData.population.toLocaleString();
        }
        
        // Update population counts
        if (gameData.peasants !== undefined) {
            document.getElementById('peasants-value').textContent = gameData.peasants;
        }
        if (gameData.scientists !== undefined) {
            document.getElementById('scientists-value').textContent = gameData.scientists;
            document.getElementById('scientists-slider').value = gameData.scientists;
            document.getElementById('scientists-slider-value').textContent = gameData.scientists;
        }
        if (gameData.soldiers !== undefined) {
            document.getElementById('soldiers-value').textContent = gameData.soldiers;
            document.getElementById('soldiers-slider').value = gameData.soldiers;
            document.getElementById('soldiers-slider-value').textContent = gameData.soldiers;
        }
        
        // Update research progress
        if (gameData.research) {
            // Update research progress bars here
        }
        
        // Add game events to log
        if (gameData.events && Array.isArray(gameData.events)) {
            gameData.events.forEach(event => {
                addEvent(event.message, event.type);
            });
        }
    }

    function addEvent(message, type) {
        const eventsLog = document.getElementById('events-log');
        const eventItem = document.createElement('div');
        
        eventItem.className = 'event-item mb-2 p-2 border rounded';
        if (type === 'success') eventItem.classList.add('border-success');
        else if (type === 'error') eventItem.classList.add('border-danger');
        else if (type === 'info') eventItem.classList.add('border-info');
        else if (type === 'warning') eventItem.classList.add('border-warning');
        
        eventItem.innerHTML = `
            <div class="d-flex justify-content-between">
                <span>${message}</span>
                <small class="text-muted">${new Date().toLocaleTimeString()}</small>
            </div>
        `;
        
        eventsLog.prepend(eventItem);
        
        // Keep only the last 20 events
        while (eventsLog.children.length > 20) {
            eventsLog.removeChild(eventsLog.lastChild);
        }
        
        // Auto-scroll to top
        eventsLog.scrollTop = 0;
    }

    // Modal actions
    function recruitSoldiers() {
        const count = parseInt(document.getElementById('soldiersCount').value);
        if (isNaN(count) || count <= 0) {
            addEvent('Invalid number of soldiers', 'error');
            return;
        }
        
        const cost = count * 10;
        const currentGrain = parseInt(document.getElementById('grain-value').textContent.replace(/,/g, ''));
        
        if (cost > currentGrain) {
            addEvent('Not enough grain to recruit soldiers', 'error');
            return;
        }
        
        // In a real game, this would send a request to the server
        addEvent(`Recruited ${count} soldiers for ${cost} grain`, 'success');
        
        // Update resources locally for demonstration
        document.getElementById('grain-value').textContent = (currentGrain - cost).toLocaleString();
        document.getElementById('soldiers-value').textContent = parseInt(document.getElementById('soldiers-value').textContent) + count;
        
        // Close modal
        $('#recruitModal').modal('hide');
    }

    function trainScientists() {
        const count = parseInt(document.getElementById('scientistsCount').value);
        if (isNaN(count) || count <= 0) {
            addEvent('Invalid number of scientists', 'error');
            return;
        }
        
        const cost = count * 15;
        const currentGrain = parseInt(document.getElementById('grain-value').textContent.replace(/,/g, ''));
        
        if (cost > currentGrain) {
            addEvent('Not enough grain to train scientists', 'error');
            return;
        }
        
        // In a real game, this would send a request to the server
        addEvent(`Trained ${count} scientists for ${cost} grain`, 'success');
        
        // Update resources locally for demonstration
        document.getElementById('grain-value').textContent = (currentGrain - cost).toLocaleString();
        document.getElementById('scientists-value').textContent = parseInt(document.getElementById('scientists-value').textContent) + count;
        
        // Close modal
        $('#scientistsModal').modal('hide');
    }

    function applyResearch() {
        const focus = document.getElementById('researchFocus').value;
        const points = document.getElementById('researchPoints').value;
        
        // In a real game, this would send a request to the server
        addEvent(`Set research focus to ${focus} with ${points}% allocation`, 'success');
        
        // Close modal
        $('#researchModal').modal('hide');
    }

    function hireGeneral() {
        const name = document.getElementById('generalName').value.trim();
        if (!name) {
            addEvent('Please enter a name for the general', 'error');
            return;
        }
        
        const specialization = document.getElementById('generalSpecialization').value;
        const cost = 1000;
        const currentMoney = parseInt(document.getElementById('money-value').textContent.replace(/,/g, ''));
        
        if (cost > currentMoney) {
            addEvent('Not enough money to hire a general', 'error');
            return;
        }
        
        // In a real game, this would send a request to the server
        addEvent(`Hired General ${name} (${specialization}) for ${cost} money`, 'success');
        
        // Update resources locally for demonstration
        document.getElementById('money-value').textContent = (currentMoney - cost).toLocaleString();
        
        // Close modal
        $('#hireGeneralModal').modal('hide');
    }

    // Simple action functions
    function buildFarm() {
        const cost = 500;
        const currentMoney = parseInt(document.getElementById('money-value').textContent.replace(/,/g, ''));
        
        if (cost > currentMoney) {
            addEvent('Not enough money to build a farm', 'error');
            return;
        }
        
        addEvent('Built a farm for 500 money. Grain production increased.', 'success');
        document.getElementById('money-value').textContent = (currentMoney - cost).toLocaleString();
    }

    function buildFactory() {
        const cost = 1000;
        const currentMoney = parseInt(document.getElementById('money-value').textContent.replace(/,/g, ''));
        
        if (cost > currentMoney) {
            addEvent('Not enough money to build a factory', 'error');
            return;
        }
        
        addEvent('Built a factory for 1000 money. Money production increased.', 'success');
        document.getElementById('money-value').textContent = (currentMoney - cost).toLocaleString();
    }

    function buildMarket() {
        const cost = 800;
        const currentMoney = parseInt(document.getElementById('money-value').textContent.replace(/,/g, ''));
        
        if (cost > currentMoney) {
            addEvent('Not enough money to build a market', 'error');
            return;
        }
        
        addEvent('Built a market for 800 money. Trade efficiency increased.', 'success');
        document.getElementById('money-value').textContent = (currentMoney - cost).toLocaleString();
    }

    function researchMilitary() {
        addEvent('Started military technology research', 'info');
    }

    function researchEconomy() {
        addEvent('Started economic development research', 'info');
    }

    function researchScience() {
        addEvent('Started scientific advancement research', 'info');
    }

    function proposeAlliance() {
        addEvent('Alliance proposed to neighboring kingdom', 'info');
    }

    function declareWar() {
        if (confirm('Are you sure you want to declare war? This will have serious consequences!')) {
            addEvent('War declared! Military mobilization initiated.', 'warning');
        }
    }

    function offerTrade() {
        addEvent('Trade agreement offered to nearby territories', 'info');
    }
</script>
</body>
</html>