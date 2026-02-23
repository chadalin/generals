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
            background: #1a202c;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .game-interface {
            min-height: 100vh;
            padding: 20px 0;
        }
        .game-panel {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            margin-bottom: 1rem;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.15);
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
        .map-container {
            width: 100%;
            height: 500px;
            overflow: hidden;
            position: relative;
            border: 2px solid #4a5568;
            border-radius: 8px;
            background: #2d3748;
        }
        .map-grid {
            display: grid;
            grid-template-columns: repeat(20, 1fr);
            grid-template-rows: repeat(20, 1fr);
            width: 100%;
            height: 100%;
        }
        .map-cell {
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            transition: all 0.2s ease;
        }
        .map-cell:hover {
            transform: scale(1.1);
            z-index: 2;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
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
        .capital {
            position: relative;
            border: 2px solid gold !important;
        }
        .capital::after {
            content: '★';
            position: absolute;
            top: 2px;
            right: 2px;
            color: gold;
            font-size: 10px;
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
        .country-label {
            position: absolute;
            background: rgba(0, 0, 0, 0.7);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            z-index: 3;
            pointer-events: none;
        }
        .nav-tabs .nav-link {
            color: rgba(255, 255, 255, 0.7);
            border: none;
        }
        .nav-tabs .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border: none;
        }
        .btn {
            border-radius: 6px;
        }
        .modal-content {
            background: #2d3748;
            color: white;
        }
        .btn-close {
            filter: invert(1);
        }
    </style>
</head>
<body>
    <div class="game-interface">
        <!-- Top Resource Bar -->
        <div class="container-fluid py-2 bg-dark">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex gap-3 flex-wrap">
                        <span class="text-warning">
                            <i class="fas fa-coins"></i> Деньги: <span id="money-value">10,000</span>
                        </span>
                        <span class="text-success">
                            <i class="fas fa-wheat"></i> Зерно: <span id="grain-value">5,000</span>
                        </span>
                        <span class="text-info">
                            <i class="fas fa-users"></i> Население: <span id="population-value">1,250</span>
                        </span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-light me-3">
                        Год: <span id="year-value">1</span>
                    </span>
                    <span class="badge bg-success">
                        В процессе
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
                            <span class="country-color" style="background: #e53e3e; width: 20px; height: 20px; display: inline-block; border-radius: 50%; margin-right: 10px;"></span>
                            Красная Империя
                        </h5>
                        
                        <div class="resource-bar">
                            <small class="text-muted">Территория</small>
                            <div class="d-flex justify-content-between">
                                <span>1,250 км²</span>
                                <span class="text-success">+5%</span>
                            </div>
                        </div>

                        <div class="resource-bar">
                            <small class="text-muted">Население</small>
                            <div class="progress mb-1" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: 60%"></div>
                                <div class="progress-bar bg-info" style="width: 20%"></div>
                                <div class="progress-bar bg-danger" style="width: 20%"></div>
                            </div>
                            <div class="d-flex justify-content-between flex-wrap">
                                <span class="text-success">Крестьяне: <span id="peasants-value">750</span></span>
                                <span class="text-info">Ученые: <span id="scientists-value">250</span></span>
                                <span class="text-danger">Солдаты: <span id="soldiers-value">250</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="game-panel p-3 mb-3">
                        <h6 class="text-center mb-3">Быстрые действия</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recruitModal">
                                <i class="fas fa-shield-alt"></i> Нанять солдат
                            </button>
                            <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#scientistsModal">
                                <i class="fas fa-flask"></i> Обучить ученых
                            </button>
                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#researchModal">
                                <i class="fas fa-research"></i> Исследования
                            </button>
                        </div>
                    </div>

                    <!-- Generals List -->
                    <div class="game-panel p-3">
                        <h6 class="text-center mb-3">Генералы</h6>
                        <div id="generals-list">
                            <div class="general-card" data-general-id="1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Александр Невский</strong>
                                    <span class="badge bg-danger">
                                        Атака
                                    </span>
                                </div>
                                <small class="text-muted">
                                    А:85 З:75 С:60
                                </small>
                                <div class="progress mt-1" style="height: 3px;">
                                    <div class="progress-bar" style="width: 65%;"></div>
                                </div>
                            </div>
                            <div class="general-card" data-general-id="2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Дмитрий Донской</strong>
                                    <span class="badge bg-warning">
                                        Оборона
                                    </span>
                                </div>
                                <small class="text-muted">
                                    А:70 З:90 С:50
                                </small>
                                <div class="progress mt-1" style="height: 3px;">
                                    <div class="progress-bar" style="width: 45%;"></div>
                                </div>
                            </div>
                            
                            <button class="btn btn-outline-success btn-sm w-100 mt-2" data-bs-toggle="modal" data-bs-target="#hireGeneralModal">
                                <i class="fas fa-plus"></i> Нанять генерала
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
                                <i class="fas fa-map"></i> Карта
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="military-tab" data-bs-toggle="tab" data-bs-target="#military" type="button" role="tab">
                                <i class="fas fa-shield-alt"></i> Армия
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="economy-tab" data-bs-toggle="tab" data-bs-target="#economy" type="button" role="tab">
                                <i class="fas fa-coins"></i> Экономика
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="research-tab" data-bs-toggle="tab" data-bs-target="#research" type="button" role="tab">
                                <i class="fas fa-flask"></i> Исследования
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="diplomacy-tab" data-bs-toggle="tab" data-bs-target="#diplomacy" type="button" role="tab">
                                <i class="fas fa-handshake"></i> Дипломатия
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content game-panel p-3" id="gameTabsContent">
                        <!-- Map Tab -->
                        <div class="tab-pane fade show active" id="map" role="tabpanel">
                            <h4>Карта мира</h4>
                            <p class="text-muted">Интерактивная карта, отображающая территории и перемещения войск</p>
                            <div class="map-container">
                                <div class="map-grid" id="game-map">
                                    <!-- Map will be generated here -->
                                </div>
                                <div class="country-label" style="top: 15%; left: 25%;">Красная Империя</div>
                                <div class="country-label" style="top: 60%; left: 70%;">Синее Королевство</div>
                                <div class="country-label" style="top: 75%; left: 30%;">Зелёная Республика</div>
                                <div class="country-label" style="top: 30%; left: 75%;">Фиолетовый Султанат</div>
                            </div>
                            <div class="mt-3 d-flex justify-content-around">
                                <div><span class="player-territory map-cell me-2"></span> Ваша территория</div>
                                <div><span class="neutral-territory map-cell me-2"></span> Нейтральная территория</div>
                                <div><span class="enemy-territory map-cell me-2"></span> Вражеская территория</div>
                                <div><span class="capital map-cell me-2"></span> Столица</div>
                            </div>
                        </div>

                        <!-- Military Tab -->
                        <div class="tab-pane fade" id="military" role="tabpanel">
                            <h4>Военная мощь</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3 bg-dark">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">Сила армии</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <img src="https://cdn-icons-png.flaticon.com/512/1695/1695218.png" alt="Army" width="80" class="mb-2">
                                                <h3>250</h3>
                                                <p class="text-muted">Всего солдат</p>
                                            </div>
                                            <p>⚔️ Сила атаки: 375</p>
                                            <p>🛡️ Сила защиты: 300</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card mb-3 bg-dark">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Генералы</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <img src="https://cdn-icons-png.flaticon.com/512/3474/3474365.png" alt="Generals" width="80" class="mb-2">
                                                <h3>2</h3>
                                                <p class="text-muted">Всего генералов</p>
                                            </div>
                                            <p>⭐ Опыт: 110</p>
                                            <p>⚡ Средняя скорость: 55</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-dark">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0">Последние битвы</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <img src="https://cdn-icons-png.flaticon.com/512/185/185932.png" alt="Battles" width="80" class="mb-2">
                                    </div>
                                    <div class="battle-item mb-2 p-2 border rounded">
                                        <strong>Красная Империя vs Синее Королевство</strong>
                                        <br>
                                        <small class="text-muted">Результат: Победа | Потери: 50</small>
                                    </div>
                                    <div class="battle-item mb-2 p-2 border rounded">
                                        <strong>Красная Империя vs Зелёная Республика</strong>
                                        <br>
                                        <small class="text-muted">Результат: Ничья | Потери: 120</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other tabs would be implemented similarly -->
                        <div class="tab-pane fade" id="economy" role="tabpanel">
                            <h4>Экономика</h4>
                            <p class="text-muted">Управление ресурсами и развитием экономики</p>
                            <!-- Content for economy tab -->
                        </div>

                        <div class="tab-pane fade" id="research" role="tabpanel">
                            <h4>Исследования</h4>
                            <p class="text-muted">Развитие технологий и научные исследования</p>
                            <!-- Content for research tab -->
                        </div>

                        <div class="tab-pane fade" id="diplomacy" role="tabpanel">
                            <h4>Дипломатия</h4>
                            <p class="text-muted">Отношения с другими государствами</p>
                            <!-- Content for diplomacy tab -->
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-3">
                    <!-- Turn Actions -->
                    <div class="game-panel p-3 mb-3">
                        <h6 class="text-center mb-3">Действия хода</h6>
                        <form id="turn-form">
                            <div class="mb-3">
                                <label class="form-label">Ученые: <span id="scientists-value">250</span></label>
                                <div class="slider-container">
                                    <input type="range" class="form-range" id="scientists-slider"
                                           min="0" max="1250"
                                           value="250">
                                    <span class="slider-value" id="scientists-slider-value">250</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Солдаты: <span id="soldiers-value">250</span></label>
                                <div class="slider-container">
                                    <input type="range" class="form-range" id="soldiers-slider"
                                           min="0" max="1250"
                                           value="250">
                                    <span class="slider-value" id="soldiers-slider-value">250</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success w-100" onclick="processTurn()">
                                <i class="fas fa-check"></i> Завершить ход
                            </button>
                        </form>
                    </div>

                    <!-- Events Log -->
                    <div class="game-panel p-3">
                        <h6 class="text-center mb-3">Журнал событий</h6>
                        <div id="events-log" style="height: 300px; overflow-y: auto;">
                            <div class="event-item border-success">
                                <div class="d-flex justify-content-between">
                                    <span>Игра началась успешно</span>
                                    <small class="text-muted">10:00</small>
                                </div>
                            </div>
                            <div class="event-item border-info">
                                <div class="d-flex justify-content-between">
                                    <span>Исследование завершено: Улучшенное земледелие</span>
                                    <small class="text-muted">09:55</small>
                                </div>
                            </div>
                            <div class="event-item border-danger">
                                <div class="d-flex justify-content-between">
                                    <span>Пограничный конфликт с Синим Королевством - потеряно 50 солдат</span>
                                    <small class="text-muted">09:45</small>
                                </div>
                            </div>
                            <div class="event-item border-success">
                                <div class="d-flex justify-content-between">
                                    <span>Урожай собран: +1250 зерна</span>
                                    <small class="text-muted">09:30</small>
                                </div>
                            </div>
                            <div class="event-item border-info">
                                <div class="d-flex justify-content-between">
                                    <span>Доступна новая технология: Военная тактика</span>
                                    <small class="text-muted">09:15</small>
                                </div>
                            </div>
                            <div class="event-item border-success">
                                <div class="d-flex justify-content-between">
                                    <span>Торговое соглашение с Зелёной Республикой: +500 денег</span>
                                    <small class="text-muted">09:00</small>
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
                    <h5 class="modal-title" id="recruitModalLabel">Нанять солдат</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="soldiersCount" class="form-label">Количество солдат</label>
                        <input type="number" class="form-control" id="soldiersCount" min="1" max="1000" value="10">
                        <div class="form-text">Стоимость: 10 зерна за солдата</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="recruitSoldiers()">Нанять</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="scientistsModal" tabindex="-1" aria-labelledby="scientistsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="scientistsModalLabel">Обучить ученых</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="scientistsCount" class="form-label">Количество ученых</label>
                        <input type="number" class="form-control" id="scientistsCount" min="1" max="1000" value="5">
                        <div class="form-text">Стоимость: 15 зерна за ученого</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="trainScientists()">Обучить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="researchModal" tabindex="-1" aria-labelledby="researchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="researchModalLabel">Исследования</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Направление исследований</label>
                        <select class="form-select" id="researchFocus">
                            <option value="military">Военные технологии</option>
                            <option value="economy">Экономическое развитие</option>
                            <option value="science">Научные достижения</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="researchPoints" class="form-label">Распределение научных очков</label>
                        <input type="range" class="form-range" id="researchPoints" min="0" max="100" value="50">
                        <div class="form-text" id="researchPointsText">Назначено 50% ученых на исследования</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="applyResearch()">Применить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hireGeneralModal" tabindex="-1" aria-labelledby="hireGeneralModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="hireGeneralModalLabel">Нанять генерала</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="generalName" class="form-label">Имя генерала</label>
                        <input type="text" class="form-control" id="generalName" placeholder="Введите имя генерала">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Специализация</label>
                        <select class="form-select" id="generalSpecialization">
                            <option value="attack">Атака</option>
                            <option value="defense">Оборона</option>
                            <option value="speed">Мобильность</option>
                        </select>
                    </div>
                    <div class="form-text">Стоимость: 1000 денег</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="hireGeneral()">Нанять</button>
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
        }

        function generateMap() {
            const mapContainer = document.getElementById('game-map');
            if (!mapContainer) return;
            
            // Clear previous map
            mapContainer.innerHTML = '';
            
            // Generate a simple grid map
            const mapSize = 20;
            for (let y = 0; y < mapSize; y++) {
                for (let x = 0; x < mapSize; x++) {
                    const cell = document.createElement('div');
                    cell.className = 'map-cell';
                    
                    // Determine territory type
                    let cellType = 'neutral-territory';
                    let text = '';
                    
                    // Player territory in the top-left quadrant
                    if (x < 8 && y < 8) {
                        cellType = 'player-territory';
                        text = 'Красная';
                    }
                    // Enemy territories in other quadrants
                    else if (x > 12 && y > 12) {
                        cellType = 'enemy-territory';
                        text = 'Синее';
                    }
                    else if (x < 8 && y > 12) {
                        cellType = 'enemy-territory';
                        text = 'Зелёная';
                    }
                    else if (x > 12 && y < 8) {
                        cellType = 'enemy-territory';
                        text = 'Фиол';
                    }
                    
                    cell.classList.add(cellType);
                    
                    // Add capital markers
                    if ((x === 4 && y === 4) || (x === 15 && y === 15) || (x === 4 && y === 15) || (x === 15 && y === 4)) {
                        cell.classList.add('capital');
                    }
                    
                    // Add text for larger territories
                    if (text && x % 4 === 0 && y % 4 === 0) {
                        cell.innerText = text;
                    }
                    
                    mapContainer.appendChild(cell);
                }
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
                    researchText.textContent = `Назначено ${this.value}% ученых на исследования`;
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
                    document.getElementById('scientists-value').textContent = this.value;
                    updatePopulationSummary();
                });
            }

            // Soldiers slider
            const soldiersSlider = document.getElementById('soldiers-slider');
            const soldiersValue = document.getElementById('soldiers-slider-value');
            
            if (soldiersSlider && soldiersValue) {
                soldiersSlider.addEventListener('input', function() {
                    soldiersValue.textContent = this.value;
                    document.getElementById('soldiers-value').textContent = this.value;
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
                const totalPopulation = 1250;
                const peasants = totalPopulation - scientists - soldiers;
                
                document.getElementById('peasants-value').textContent = peasants;
                document.getElementById('population-value').textContent = totalPopulation;
            }
        }

        function loadGeneralDetails(generalId) {
            // In a real game, this would fetch general details from the server
            console.log('Loading details for general:', generalId);
            
            // For demonstration, show an alert
            alert(`Детали генерала ${generalId} будут загружены здесь`);
        }

        function processTurn() {
            // Collect form data
            const scientists = document.getElementById('scientists-slider').value;
            const soldiers = document.getElementById('soldiers-slider').value;
            
            // Simulate server processing
            setTimeout(() => {
                // Update year
                const yearElement = document.getElementById('year-value');
                yearElement.textContent = parseInt(yearElement.textContent) + 1;
                
                // Update resources
                const moneyElement = document.getElementById('money-value');
                const grainElement = document.getElementById('grain-value');
                
                moneyElement.textContent = (parseInt(moneyElement.textContent.replace(/,/g, '')) + 250).toLocaleString();
                grainElement.textContent = (parseInt(grainElement.textContent.replace(/,/g, '')) + 500).toLocaleString();
                
                // Add event
                addEvent('Ход завершен. Ресурсы обновлены.', 'success');
                
                // Show success message
                alert('Ход успешно обработан!');
            }, 500);
        }

        function addEvent(message, type) {
            const eventsLog = document.getElementById('events-log');
            const eventItem = document.createElement('div');
            
            eventItem.className = 'event-item mb-2 p-2 border rounded';
            if (type === 'success') eventItem.classList.add('border-success');
            else if (type === 'error') eventItem.classList.add('border-danger');
            else if (type === 'info') eventItem.classList.add('border-info');
            else if (type === 'warning') eventItem.classList.add('border-warning');
            
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                              now.getMinutes().toString().padStart(2, '0');
            
            eventItem.innerHTML = `
                <div class="d-flex justify-content-between">
                    <span>${message}</span>
                    <small class="text-muted">${timeString}</small>
                </div>
            `;
            
            eventsLog.prepend(eventItem);
            
            // Keep only the last 10 events
            while (eventsLog.children.length > 10) {
                eventsLog.removeChild(eventsLog.lastChild);
            }
            
            // Auto-scroll to top
            eventsLog.scrollTop = 0;
        }

        // Modal actions
        function recruitSoldiers() {
            const count = parseInt(document.getElementById('soldiersCount').value);
            if (isNaN(count) || count <= 0) {
                addEvent('Неверное количество солдат', 'error');
                return;
            }
            
            const cost = count * 10;
            const currentGrain = parseInt(document.getElementById('grain-value').textContent.replace(/,/g, ''));
            
            if (cost > currentGrain) {
                addEvent('Недостаточно зерна для найма солдат', 'error');
                return;
            }
            
            // Update resources
            document.getElementById('grain-value').textContent = (currentGrain - cost).toLocaleString();
            const currentSoldiers = parseInt(document.getElementById('soldiers-value').textContent);
            document.getElementById('soldiers-value').textContent = currentSoldiers + count;
            
            // Update slider
            document.getElementById('soldiers-slider').value = currentSoldiers + count;
            document.getElementById('soldiers-slider-value').textContent = currentSoldiers + count;
            
            addEvent(`Нанято ${count} солдат за ${cost} зерна`, 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('recruitModal'));
            modal.hide();
        }

        function trainScientists() {
            const count = parseInt(document.getElementById('scientistsCount').value);
            if (isNaN(count) || count <= 0) {
                addEvent('Неверное количество ученых', 'error');
                return;
            }
            
            const cost = count * 15;
            const currentGrain = parseInt(document.getElementById('grain-value').textContent.replace(/,/g, ''));
            
            if (cost > currentGrain) {
                addEvent('Недостаточно зерна для обучения ученых', 'error');
                return;
            }
            
            // Update resources
            document.getElementById('grain-value').textContent = (currentGrain - cost).toLocaleString();
            const currentScientists = parseInt(document.getElementById('scientists-value').textContent);
            document.getElementById('scientists-value').textContent = currentScientists + count;
            
            // Update slider
            document.getElementById('scientists-slider').value = currentScientists + count;
            document.getElementById('scientists-slider-value').textContent = currentScientists + count;
            
            addEvent(`Обучено ${count} ученых за ${cost} зерна`, 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('scientistsModal'));
            modal.hide();
        }

        function applyResearch() {
            const focus = document.getElementById('researchFocus').value;
            const points = document.getElementById('researchPoints').value;
            
            addEvent(`Установлен фокус исследований: ${focus} с распределением ${points}%`, 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('researchModal'));
            modal.hide();
        }

        function hireGeneral() {
            const name = document.getElementById('generalName').value.trim();
            if (!name) {
                addEvent('Пожалуйста, введите имя генерала', 'error');
                return;
            }
            
            const specialization = document.getElementById('generalSpecialization').value;
            const cost = 1000;
            const currentMoney = parseInt(document.getElementById('money-value').textContent.replace(/,/g, ''));
            
            if (cost > currentMoney) {
                addEvent('Недостаточно денег для найма генерала', 'error');
                return;
            }
            
            // Update resources
            document.getElementById('money-value').textContent = (currentMoney - cost).toLocaleString();
            
            addEvent(`Нанят генерал ${name} (${specialization}) за ${cost} денег`, 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('hireGeneralModal'));
            modal.hide();
        }
    </script>
</body>
</html>