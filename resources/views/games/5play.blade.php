<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Стратегическая игра "Генералы"</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #fff;
        }
        .game-interface {
            padding: 20px;
        }
        .game-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .resource-bar {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }
        .map-container {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 15px;
            overflow-x: auto;
        }
        .map-grid {
            display: grid;
            gap: 2px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
            border-radius: 5px;
            min-width: min-content;
        }
        .map-cell {
            width: 40px;
            height: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            color: white;
            text-shadow: 1px 1px 2px black;
        }
        .map-cell:hover {
            transform: scale(1.1);
            z-index: 10;
            border-color: #fff;
        }
        .map-cell.capital {
            background-color: gold !important;
            color: black;
            font-weight: bold;
        }
        .map-cell.capital::after {
            content: "👑";
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 12px;
        }
        .general-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #4a90e2;
            transition: all 0.2s;
        }
        .general-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(5px);
        }
        .slider-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .slider-value {
            min-width: 50px;
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 5px;
            border-radius: 3px;
        }
        .nav-tabs .nav-link {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            margin-right: 5px;
        }
        .nav-tabs .nav-link.active {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            border-bottom-color: transparent;
        }
        .modal-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .modal-header {
            border-bottom-color: rgba(255, 255, 255, 0.2);
        }
        .modal-footer {
            border-top-color: rgba(255, 255, 255, 0.2);
        }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            border-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .btn-close-white {
            filter: invert(1);
        }
        .progress {
            background-color: rgba(0, 0, 0, 0.3);
        }
        .badge {
            font-size: 0.8rem;
            padding: 5px 8px;
        }
        .territory-info {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            padding: 20px;
            border-radius: 10px;
            z-index: 1000;
            display: none;
            max-width: 300px;
        }
    </style>
</head>
<body>
    <div class="game-interface">
        <!-- Top Resource Bar -->
        <div class="container-fluid py-2 bg-dark bg-opacity-75 rounded-3 mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex gap-3 flex-wrap">
                        <span class="text-warning">
                            <i class="fas fa-coins"></i> Деньги: 
                            <span id="money-value">{{ number_format($player->money) }}</span>
                        </span>
                        <span class="text-success">
                            <i class="fas fa-wheat"></i> Зерно: 
                            <span id="grain-value">{{ number_format($player->grain) }}</span>
                        </span>
                        <span class="text-info">
                            <i class="fas fa-users"></i> Население: 
                            <span id="population-value">{{ number_format($player->peasants + $player->soldiers + $player->scientists) }}</span>
                        </span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-light me-3">
                        Год: <span id="year-value">{{ $game->current_year }}</span>
                    </span>
                    <span class="badge bg-{{ $game->status === 'active' ? 'success' : 'warning' }}">{{ $game->status }}</span>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-3">
            <div class="row">
                <!-- Left Sidebar -->
                <div class="col-lg-3">
                    <!-- Country Info -->
                    <div class="game-panel p-3 mb-3">
                        @php
                            $countryColor = $player->country->color ?? '#6c757d';
                            $countryName = $player->country->name ?? 'Страна ' . $player->username;
                            $territoryCount = $player->country->territories->count() ?? 0;
                        @endphp
                        
                        <h5 class="text-center mb-3">
                            <span class="country-color" style="background: {{ $countryColor }}; width: 20px; height: 20px; display: inline-block; border-radius: 50%; margin-right: 10px;"></span>
                            {{ $countryName }}
                        </h5>
                        
                        <div class="resource-bar">
                            <small class="text-muted">Территория</small>
                            <div class="d-flex justify-content-between">
                                <span>{{ $territoryCount }} тер.</span>
                            </div>
                        </div>

                        <div class="resource-bar">
                            <small class="text-muted">Население</small>
                            @php
                                $totalPopulation = $player->peasants + $player->soldiers + $player->scientists;
                                $peasantsPercent = $totalPopulation > 0 ? ($player->peasants / $totalPopulation) * 100 : 0;
                                $scientistsPercent = $totalPopulation > 0 ? ($player->scientists / $totalPopulation) * 100 : 0;
                                $soldiersPercent = $totalPopulation > 0 ? ($player->soldiers / $totalPopulation) * 100 : 0;
                            @endphp
                            <div class="progress mb-1" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: {{ $peasantsPercent }}%"></div>
                                <div class="progress-bar bg-info" style="width: {{ $scientistsPercent }}%"></div>
                                <div class="progress-bar bg-danger" style="width: {{ $soldiersPercent }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between flex-wrap">
                                <span class="text-success">Крестьяне: {{ number_format($player->peasants) }}</span>
                                <span class="text-info">Ученые: {{ number_format($player->scientists) }}</span>
                                <span class="text-danger">Солдаты: {{ number_format($player->soldiers) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Generals List -->
                    <div class="game-panel p-3">
                        <h6 class="text-center mb-3">Генералы</h6>
                        <div id="generals-list">
                            @forelse($player->generals as $general)
                            <div class="general-card" data-general-id="{{ $general->id }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $general->name }}</strong>
                                    <span class="badge bg-{{ $general->order === 'attack' ? 'danger' : ($general->order === 'defend' ? 'warning' : 'success') }}">
                                        {{ $general->order === 'attack' ? 'Атака' : ($general->order === 'defend' ? 'Оборона' : 'Расширение') }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    Ур.{{ $general->level ?? 1 }} | Опыт: {{ $general->experience ?? 0 }}
                                </small>
                                @php
                                    $expNeeded = ($general->level ?? 1) * 100;
                                    $expProgress = $expNeeded > 0 ? (($general->experience ?? 0) / $expNeeded) * 100 : 0;
                                @endphp
                                <div class="progress mt-1" style="height: 3px;">
                                    <div class="progress-bar" style="width: {{ $expProgress }}%;"></div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100" 
                                        onclick="changeGeneralOrder({{ $general->id }})">
                                    Сменить приказ
                                </button>
                            </div>
                            @empty
                            <p class="text-center text-muted">У вас нет генералов</p>
                            @endforelse
                            
                            <button class="btn btn-outline-success btn-sm w-100 mt-2" data-bs-toggle="modal" data-bs-target="#hireGeneralModal">
                                <i class="fas fa-plus"></i> Нанять генерала (1000💰)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-6">
                    <ul class="nav nav-tabs" id="gameTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="map-tab" data-bs-toggle="tab" data-bs-target="#map" type="button" role="tab">
                                <i class="fas fa-map"></i> Карта
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab">
                                <i class="fas fa-chart-line"></i> Статистика
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="research-tab" data-bs-toggle="tab" data-bs-target="#research" type="button" role="tab">
                                <i class="fas fa-flask"></i> Исследования
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content game-panel p-3" id="gameTabsContent">
                        <!-- Map Tab -->
                        <div class="tab-pane fade show active" id="map" role="tabpanel">
                            <h4 class="mb-3">Карта мира</h4>
                            <div class="map-container">
                                <div class="map-grid" id="game-map" 
                                     data-map-width="{{ $game->map_width ?? 20 }}"
                                     data-map-height="{{ $game->map_height ?? 15 }}">
                                    <!-- Карта генерируется JavaScript -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats Tab -->
                        <div class="tab-pane fade" id="stats" role="tabpanel">
                            <h4 class="mb-3">Статистика</h4>
                            <div class="row">
                                <div class="col-6">
                                    <div class="resource-bar">
                                        <small>Военная мощь</small>
                                        <h5>{{ $player->soldiers * 10 }}</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="resource-bar">
                                        <small>Научный потенциал</small>
                                        <h5>{{ $player->scientists * 5 }}</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="resource-bar">
                                        <small>Экономика</small>
                                        <h5>{{ $player->peasants * 2 }}</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="resource-bar">
                                        <small>Исследования</small>
                                        <h5>{{ $player->research_military + $player->research_economy + $player->research_science }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Research Tab -->
                        <div class="tab-pane fade" id="research" role="tabpanel">
                            <h4 class="mb-3">Исследования</h4>
                            <div class="list-group">
                                <div class="list-group-item bg-transparent text-light border-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Военные технологии</span>
                                        <span class="badge bg-primary">{{ $player->research_military }}</span>
                                    </div>
                                </div>
                                <div class="list-group-item bg-transparent text-light border-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Экономика</span>
                                        <span class="badge bg-primary">{{ $player->research_economy }}</span>
                                    </div>
                                </div>
                                <div class="list-group-item bg-transparent text-light border-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Наука</span>
                                        <span class="badge bg-primary">{{ $player->research_science }}</span>
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
                        <h6 class="text-center mb-3">Действия хода</h6>
                        <form action="{{ route('games.process-turn', $game) }}" method="POST" id="turn-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Ученые: <span id="scientists-display">{{ $player->scientists }}</span></label>
                                <div class="slider-container">
                                    <input type="range" class="form-range" id="scientists-slider"
                                           name="scientists_count"
                                           min="0" max="{{ $player->peasants + $player->soldiers + $player->scientists }}"
                                           value="{{ $player->scientists }}">
                                    <span class="slider-value" id="scientists-slider-value">{{ $player->scientists }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Солдаты: <span id="soldiers-display">{{ $player->soldiers }}</span></label>
                                <div class="slider-container">
                                    <input type="range" class="form-range" id="soldiers-slider"
                                           name="soldiers_count"
                                           min="0" max="{{ $player->peasants + $player->soldiers + $player->scientists }}"
                                           value="{{ $player->soldiers }}">
                                    <span class="slider-value" id="soldiers-slider-value">{{ $player->soldiers }}</span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Завершить ход
                            </button>
                        </form>
                    </div>
                    
                    <!-- Players List -->
                    <div class="game-panel p-3">
                        <h6 class="text-center mb-3">Игроки</h6>
                        <div class="list-group">
                            @foreach($game->players as $gamePlayer)
                            <div class="list-group-item bg-transparent text-light border-secondary">
                                <div class="d-flex align-items-center">
                                    <span class="me-2" style="width: 12px; height: 12px; border-radius: 50%; background: {{ $gamePlayer->country->color ?? '#6c757d' }};"></span>
                                    <span>{{ $gamePlayer->username }}</span>
                                    @if($gamePlayer->id === $player->id)
                                        <span class="badge bg-success ms-2">Вы</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Territory Info Modal -->
    <div class="modal fade" id="territoryInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title">Информация о территории</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="territory-info-content">
                    <!-- Заполняется JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Hire General Modal -->
    <div class="modal fade" id="hireGeneralModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title">Нанять генерала</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('generals.hire', $game) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="generalName" class="form-label">Имя генерала</label>
                            <input type="text" class="form-control" id="generalName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Начальный приказ</label>
                            <select class="form-select" name="order">
                                <option value="attack">Атака</option>
                                <option value="defend" selected>Оборона</option>
                                <option value="expand">Расширение</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary" {{ $player->money < 1000 ? 'disabled' : '' }}>
                            Нанять за 1000💰
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Инициализация слайдеров
        document.addEventListener('DOMContentLoaded', function() {
            const scientistsSlider = document.getElementById('scientists-slider');
            const soldiersSlider = document.getElementById('soldiers-slider');
            const scientistsDisplay = document.getElementById('scientists-display');
            const soldiersDisplay = document.getElementById('soldiers-display');
            const scientistsValue = document.getElementById('scientists-slider-value');
            const soldiersValue = document.getElementById('soldiers-slider-value');
            
            function updateScientists() {
                scientistsDisplay.textContent = scientistsSlider.value;
                scientistsValue.textContent = scientistsSlider.value;
                
                // Корректируем солдат, чтобы сумма не превышала общее население
                const total = {{ $player->peasants + $player->soldiers + $player->scientists }};
                const maxSoldiers = total - parseInt(scientistsSlider.value);
                soldiersSlider.max = maxSoldiers;
                
                if (parseInt(soldiersSlider.value) > maxSoldiers) {
                    soldiersSlider.value = maxSoldiers;
                    soldiersDisplay.textContent = maxSoldiers;
                    soldiersValue.textContent = maxSoldiers;
                }
            }
            
            function updateSoldiers() {
                soldiersDisplay.textContent = soldiersSlider.value;
                soldiersValue.textContent = soldiersSlider.value;
                
                // Корректируем ученых
                const total = {{ $player->peasants + $player->soldiers + $player->scientists }};
                const maxScientists = total - parseInt(soldiersSlider.value);
                scientistsSlider.max = maxScientists;
                
                if (parseInt(scientistsSlider.value) > maxScientists) {
                    scientistsSlider.value = maxScientists;
                    scientistsDisplay.textContent = maxScientists;
                    scientistsValue.textContent = maxScientists;
                }
            }
            
            scientistsSlider.addEventListener('input', updateScientists);
            soldiersSlider.addEventListener('input', updateSoldiers);
            
            // Инициализация карты
            initializeMap();
        });

        // Инициализация карты
        function initializeMap() {
            const mapContainer = document.getElementById('game-map');
            if (!mapContainer) return;
            
            const width = parseInt(mapContainer.dataset.mapWidth) || 20;
            const height = parseInt(mapContainer.dataset.mapHeight) || 15;

            mapContainer.style.gridTemplateColumns = `repeat(${width}, 1fr)`;
            mapContainer.style.gridTemplateRows = `repeat(${height}, 1fr)`;

            // Загружаем данные о территориях
            fetch(`/api/games/{{ $game->id }}/map-data`)
                .then(response => response.json())
                .then(territories => {
                    renderMap(territories, width, height);
                })
                .catch(error => {
                    console.error('Error loading map data:', error);
                    // Если API недоступно, создаем пустую карту
                    renderEmptyMap(width, height);
                });
        }

        function renderMap(territories, width, height) {
            const mapContainer = document.getElementById('game-map');
            if (!mapContainer) return;
            
            mapContainer.innerHTML = '';

            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const cell = document.createElement('div');
                    cell.className = 'map-cell';
                    cell.dataset.x = x;
                    cell.dataset.y = y;

                    // Находим территорию для этой клетки
                    const territory = territories.find(t => t.x == x && t.y == y);
                    
                    if (territory) {
                        cell.classList.add(`territory-${territory.country_id}`);
                        if (territory.is_capital) {
                            cell.classList.add('capital');
                        }
                        
                        cell.title = `${territory.name}\nНаселение: ${territory.population || 0}\nРесурсы: ${territory.resource_value || 0}`;
                        
                        cell.addEventListener('click', () => showTerritoryInfo(territory));
                    }

                    mapContainer.appendChild(cell);
                }
            }
        }
        
        function renderEmptyMap(width, height) {
            const mapContainer = document.getElementById('game-map');
            if (!mapContainer) return;
            
            mapContainer.innerHTML = '';
            
            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const cell = document.createElement('div');
                    cell.className = 'map-cell';
                    cell.dataset.x = x;
                    cell.dataset.y = y;
                    cell.style.backgroundColor = '#2d3748';
                    mapContainer.appendChild(cell);
                }
            }
        }
        
        function showTerritoryInfo(territory) {
            const modal = new bootstrap.Modal(document.getElementById('territoryInfoModal'));
            const content = document.getElementById('territory-info-content');
            
            content.innerHTML = `
                <h6>${territory.name}</h6>
                <p>Население: ${territory.population || 0}</p>
                <p>Ресурсы: ${territory.resource_value || 0}</p>
                <p>Тип: ${territory.is_capital ? 'Столица' : 'Обычная'}</p>
            `;
            
            modal.show();
        }

        // Смена приказа генерала
        function changeGeneralOrder(generalId) {
            const newOrder = prompt('Введите новый приказ (attack/defend/expand):');
            if (newOrder && ['attack', 'defend', 'expand'].includes(newOrder)) {
                fetch(`/generals/${generalId}/order`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: newOrder })
                }).then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Ошибка при смене приказа');
                    }
                });
            }
        }

        // Обновление ресурсов в реальном времени
        function refreshResources() {
            fetch(`/api/games/{{ $game->id }}/status`)
                .then(response => response.json())
                .then(data => {
                    if (data.year) {
                        document.getElementById('year-value').textContent = data.year;
                    }
                });
        }

        // Обновляем каждые 30 секунд
        setInterval(refreshResources, 30000);
    </script>
</body>
</html>