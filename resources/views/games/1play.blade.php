@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/game.css') }}" rel="stylesheet">
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
</style>
@endsection

@section('content')
<div class="game-interface">
    <!-- Top Resource Bar -->
    <div class="container-fluid py-2 bg-dark">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex gap-3">
                    <span class="text-warning">
                        <i class="fas fa-coins"></i> Money: {{ number_format($player->money) }}
                    </span>
                    <span class="text-success">
                        <i class="fas fa-wheat"></i> Grain: {{ number_format($player->grain) }}
                    </span>
                    <span class="text-info">
                        <i class="fas fa-users"></i> Population: {{ number_format($player->peasants + $player->scientists + $player->soldiers) }}
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
                            <span class="text-success">Peasants: {{ $player->peasants }}</span>
                            <span class="text-info">Scientists: {{ $player->scientists }}</span>
                            <span class="text-danger">Soldiers: {{ $player->soldiers }}</span>
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
                                <canvas id="gameMapCanvas" width="500" height="500" style="border: 1px solid #333;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Military Tab -->
                  <!-- Military Tab -->
<div class="tab-pane fade" id="military" role="tabpanel">
    <h4>Military Overview</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3"> <!-- Убрали bg-dark -->
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

                    <!-- Other tabs would be implemented similarly -->
                    <!-- Economy Tab -->
<div class="tab-pane fade" id="economy" role="tabpanel">
    <h4>Economic Overview</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="card bg-dark mb-3">
                <div class="card-header">
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
            <div class="card bg-dark mb-3">
                <div class="card-header">
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

    <div class="card bg-dark">
        <div class="card-header">
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
            <div class="card bg-dark mb-3">
                <div class="card-header bg-danger">
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
            <div class="card bg-dark mb-3">
                <div class="card-header bg-warning">
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
            <div class="card bg-dark mb-3">
                <div class="card-header bg-info">
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

    <div class="card bg-dark">
        <div class="card-header">
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

            <!-- Right Sidebar -->
            <div class="col-lg-3">
                <!-- Turn Actions -->
                <div class="game-panel p-3 mb-3">
                    <h6 class="text-center mb-3">Turn Actions</h6>
                    <form id="turn-form" action="{{ route('games.process-turn', $game->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Scientists: {{ $player->scientists }}</label>
                            <input type="range" name="scientists" class="form-range" 
                                   min="0" max="{{ $player->peasants + $player->scientists + $player->soldiers }}"
                                   value="{{ $player->scientists }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Soldiers: {{ $player->soldiers }}</label>
                            <input type="range" name="soldiers" class="form-range"
                                   min="0" max="{{ $player->peasants + $player->scientists + $player->soldiers }}"
                                   value="{{ $player->soldiers }}">
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
        @if(session('game_events'))
            @foreach(session('game_events') as $event)
            <div class="event-item mb-2 p-2 border rounded 
                {{ $event['type'] == 'battle' ? 'border-danger' : '' }}
                {{ $event['type'] == 'research' ? 'border-info' : '' }}
                {{ $event['type'] == 'resource' ? 'border-success' : '' }}">
                <div class="d-flex justify-content-between">
                    <span>{{ $event['message'] }}</span>
                    <small class="text-muted">{{ $event['timestamp']->format('H:i') }}</small>
                </div>
            </div>
            @endforeach
        @else
            <div class="event-item text-muted small mb-2">
                <div>Game started</div>
                <small>Year {{ $game->year }}</small>
            </div>
        @endif
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('modals.recruit-soldiers')
@include('modals.train-scientists')
@include('modals.research')
@include('modals.hire-general')
@endsection

@section('scripts')
@section('scripts')
<script>
    // Game interface functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializeGameInterface();
        initializeMap();
        initializeEventListeners();
    });

    function initializeGameInterface() {
        // General selection
        const generalCards = document.querySelectorAll('.general-card');
        generalCards.forEach(card => {
            card.addEventListener('click', function() {
                generalCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                loadGeneralDetails(this.dataset.generalId);
            });
        });

        // Resource allocation sliders
        initializeSliders();
    }

    function initializeMap() {
        const canvas = document.getElementById('gameMapCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        // Draw basic map grid
        for (let x = 0; x < 50; x++) {
            for (let y = 0; y < 50; y++) {
                ctx.fillStyle = (x + y) % 2 === 0 ? '#2d3748' : '#4a5568';
                ctx.fillRect(x * 10, y * 10, 10, 10);
            }
        }

        // Draw player territory
        drawPlayerTerritory(ctx);
    }

    function initializeEventListeners() {
        // Turn form submission
        const turnForm = document.getElementById('turn-form');
        if (turnForm) {
            turnForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitTurn();
            });
        }

        // Tab switching
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const targetTab = e.target.getAttribute('data-bs-target');
                onTabChange(targetTab);
            });
        });

        // Real-time updates
        startGameUpdates();
    }

    function initializeSliders() {
        const scientistSlider = document.querySelector('input[name="scientists"]');
        const soldierSlider = document.querySelector('input[name="soldiers"]');

        if (scientistSlider && soldierSlider) {
            scientistSlider.addEventListener('input', updatePopulationAllocation);
            soldierSlider.addEventListener('input', updatePopulationAllocation);
        }
    }

    function updatePopulationAllocation() {
        const scientistSlider = document.querySelector('input[name="scientists"]');
        const soldierSlider = document.querySelector('input[name="soldiers"]');
        const scientistValue = document.querySelector('label[for="scientists"]');
        const soldierValue = document.querySelector('label[for="soldiers"]');

        if (scientistSlider && soldierSlider && scientistValue && soldierValue) {
            const scientists = parseInt(scientistSlider.value);
            const soldiers = parseInt(soldierSlider.value);
            const total = scientists + soldiers;
            const peasants = {{ $player->peasants + $player->scientists + $player->soldiers }} - total;

            scientistValue.textContent = `Scientists: ${scientists}`;
            soldierValue.textContent = `Soldiers: ${soldiers}`;

            // Update peasants count
            const peasantElement = document.querySelector('span.text-success');
            if (peasantElement) {
                peasantElement.textContent = `Peasants: ${peasants}`;
            }
        }
    }

    function loadGeneralDetails(generalId) {
        // AJAX запрос для загрузки деталей генерала
        fetch(`/generals/${generalId}/details`)
            .then(response => response.json())
            .then(data => {
                updateGeneralDetailsPanel(data);
            })
            .catch(error => {
                console.error('Error loading general details:', error);
            });
    }

    function updateGeneralDetailsPanel(generalData) {
        const detailsPanel = document.getElementById('general-details-panel');
        if (!detailsPanel) return;

        detailsPanel.innerHTML = `
            <h5>${generalData.name}</h5>
            <p>Attack: ${generalData.attack}</p>
            <p>Defense: ${generalData.defense}</p>
            <p>Speed: ${generalData.speed}</p>
            <p>Experience: ${generalData.experience}</p>
            <p>Soldiers: ${generalData.soldiers_count}</p>
            <p>Order: ${generalData.order}</p>
        `;
    }

    function submitTurn() {
        const formData = new FormData(document.getElementById('turn-form'));
        const generalsData = {};

        // Собираем данные по генералам
        document.querySelectorAll('.general-card').forEach(card => {
            const generalId = card.dataset.generalId;
            const orderSelect = document.querySelector(`select[name="generals[${generalId}][type]"]`);
            const soldiersInput = document.querySelector(`input[name="generals[${generalId}][soldiers]"]`);

            if (orderSelect && soldiersInput) {
                generalsData[generalId] = {
                    type: orderSelect.value,
                    soldiers: soldiersInput.value
                };
            }
        });

        // Добавляем данные генералов в FormData
        formData.append('generals', JSON.stringify(generalsData));

        // AJAX отправка хода
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
                showSuccessMessage('Turn processed successfully!');
                // Обновляем интерфейс
                updateGameInterface(data.game_data);
            } else {
                showErrorMessage(data.error || 'Error processing turn');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('Network error occurred');
        });
    }

    function drawPlayerTerritory(ctx) {
        // Здесь будет логика отрисовки территории игрока
        // Временно рисуем случайные территории
        for (let i = 0; i < 10; i++) {
            const x = Math.floor(Math.random() * 50);
            const y = Math.floor(Math.random() * 50);
            ctx.fillStyle = '{{ $country->color }}';
            ctx.fillRect(x * 10, y * 10, 10, 10);
        }
    }

    function onTabChange(tabId) {
        switch (tabId) {
            case '#map':
                refreshMap();
                break;
            case '#military':
                loadMilitaryData();
                break;
            case '#economy':
                loadEconomyData();
                break;
            case '#research':
                loadResearchData();
                break;
            case '#diplomacy':
                loadDiplomacyData();
                break;
        }
    }

    function refreshMap() {
        // Логика обновления карты
        console.log('Refreshing map...');
    }

    function loadMilitaryData() {
        fetch('/game/{{ $game->id }}/military-data')
            .then(response => response.json())
            .then(data => {
                updateMilitaryTab(data);
            });
    }

    function updateMilitaryTab(data) {
        const militaryTab = document.querySelector('#military .card-body');
        if (militaryTab) {
            militaryTab.innerHTML = `
                <h5>Military Strength</h5>
                <p>Total Soldiers: ${data.total_soldiers || {{ $player->soldiers }}}</p>
                <p>Generals: ${data.total_generals || {{ $generals->count() }}}</p>
                <p>Military Power: ${data.military_power || 0}</p>
            `;
        }
    }

    function startGameUpdates() {
        // Запускаем периодическое обновление игры
        setInterval(() => {
            checkGameUpdates();
        }, 30000); // Каждые 30 секунд
    }

    function checkGameUpdates() {
        fetch('/game/{{ $game->id }}/updates')
            .then(response => response.json())
            .then(data => {
                if (data.updated) {
                    updateGameState(data);
                }
            });
    }

    function updateGameState(gameData) {
        // Обновляем ресурсы
        updateResources(gameData.resources);
        
        // Обновляем события
        addGameEvents(gameData.events);
        
        // Обновляем статус игры
        if (gameData.game_status) {
            updateGameStatus(gameData.game_status);
        }
    }

    function updateResources(resources) {
        if (resources.money) {
            const moneyElement = document.querySelector('.text-warning');
            if (moneyElement) moneyElement.textContent = `Money: ${resources.money}`;
        }
        if (resources.grain) {
            const grainElement = document.querySelector('.text-success');
            if (grainElement) grainElement.textContent = `Grain: ${resources.grain}`;
        }
    }

    function addGameEvents(events) {
        const eventsLog = document.getElementById('events-log');
        if (eventsLog && events) {
            events.forEach(event => {
                const eventElement = document.createElement('div');
                eventElement.className = 'event-item text-muted small mb-2';
                eventElement.innerHTML = `
                    <div>${event.message}</div>
                    <small>${event.timestamp}</small>
                `;
                eventsLog.prepend(eventElement);
            });
        }
    }

    function showSuccessMessage(message) {
        // Показываем успешное сообщение
        alert('Success: ' + message); // Можно заменить на toast уведомление
    }

    function showErrorMessage(message) {
        // Показываем сообщение об ошибке
        alert('Error: ' + message); // Можно заменить на toast уведомление
    }

    // Функции для модальных окон
    function recruitSoldiers() {
        const count = document.getElementById('soldiersCount').value;
        const cost = count * 10;

        fetch('/players/{{ $player->id }}/recruit-soldiers', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ count: count })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#recruitModal').modal('hide');
                updateResources(data.resources);
                showSuccessMessage(`Recruited ${count} soldiers!`);
            } else {
                showErrorMessage(data.error);
            }
        });
    }

    function trainScientists() {
        const count = document.getElementById('scientistsCount').value;
        
        fetch('/players/{{ $player->id }}/train-scientists', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ count: count })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#scientistsModal').modal('hide');
                updateResources(data.resources);
                showSuccessMessage(`Trained ${count} scientists!`);
            } else {
                showErrorMessage(data.error);
            }
        });
    }

    function hireGeneral() {
        const name = document.getElementById('generalName').value;
        
        fetch('/games/{{ $game->id }}/generals/hire', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#hireGeneralModal').modal('hide');
                updateResources({ money: data.remaining_money });
                showSuccessMessage(`Hired general ${name}!`);
                // Перезагружаем страницу для обновления списка генералов
                location.reload();
            } else {
                showErrorMessage(data.error);
            }
        });
    }

    // Hotkeys
    document.addEventListener('keydown', function(e) {
        switch(e.key) {
            case 'Enter':
                if (e.ctrlKey) {
                    e.preventDefault();
                    submitTurn();
                }
                break;
            case 'Escape':
                // Close modals
                $('.modal').modal('hide');
                break;
        }
    });

    // Drag and drop для генералов
    let draggedGeneral = null;

    document.querySelectorAll('.general-card').forEach(card => {
        card.setAttribute('draggable', 'true');
        
        card.addEventListener('dragstart', function(e) {
            draggedGeneral = this.dataset.generalId;
            e.dataTransfer.setData('text/plain', this.dataset.generalId);
        });
    });

    // Зоны для drop (например, для назначения приказов)
    document.querySelectorAll('.drop-zone').forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drop-over');
        });

        zone.addEventListener('dragleave', function() {
            this.classList.remove('drop-over');
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drop-over');
            const generalId = e.dataTransfer.getData('text/plain');
            assignGeneralToZone(generalId, this.dataset.zoneType);
        });
    });

    function assignGeneralToZone(generalId, zoneType) {
        // Логика назначения генерала в зону
        console.log(`Assigning general ${generalId} to ${zoneType}`);
    }

    // Простые функции для кнопок
function buildFarm() {
    alert('Building farm...');
}

function buildFactory() {
    alert('Building factory...');
}

function buildMarket() {
    alert('Building market...');
}

function researchMilitary() {
    alert('Researching military technology...');
}

function researchEconomy() {
    alert('Researching economy technology...');
}

function researchScience() {
    alert('Researching science technology...');
}

// Функции для дипломатии
function proposeAlliance() {
    alert('Proposing alliance...');
}

function declareWar() {
    if (confirm('Are you sure you want to declare war? This will have serious consequences!')) {
        alert('War declared!');
    }
}

function offerTrade() {
    alert('Offering trade deal...');
}

// Инициализация всех кнопок
function initializeAllButtons() {
    console.log('Initializing all buttons...');
    
    // Обработчики для всех кнопок
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Button clicked:', this.textContent);
        });
    });

    // Обработчики для модальных окон
    const recruitBtn = document.querySelector('[onclick="recruitSoldiers()"]');
    const trainBtn = document.querySelector('[onclick="trainScientists()"]');
    const hireBtn = document.querySelector('[onclick="hireGeneral()"]');
    
    if (recruitBtn) recruitBtn.onclick = recruitSoldiers;
    if (trainBtn) trainBtn.onclick = trainScientists;
    if (hireBtn) hireBtn.onclick = hireGeneral;
}

// Проверяем загрузку Bootstrap
function checkBootstrap() {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap not loaded!');
        // Загружаем Bootstrap динамически
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js';
        document.head.appendChild(script);
    } else {
        console.log('Bootstrap loaded successfully');
    }
}

// Обновляем инициализацию
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    checkBootstrap();
    initializeGameInterface();
    initializeMap();
    initializeEventListeners();
    initializeAllButtons();
});
</script>
@endsection
