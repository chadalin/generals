@extends('layouts.app')

@section('styles')
<style>
    .game-map {
        position: relative;
        background: #e0e0e0;
        border: 2px solid #333;
        margin: 0 auto;
    }

    .territory {
        position: absolute;
        width: 10px;
        height: 10px;
        border: 1px solid rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .territory:hover {
        transform: scale(1.5);
        z-index: 100;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    .territory.land { background-color: #8BC34A; }
    .territory.water { background-color: #2196F3; }
    .territory.mountain { background-color: #795548; }
    .territory.coast { background-color: #FFC107; }

    .country-capital {
        position: absolute;
        width: 15px;
        height: 15px;
        border: 2px solid #000;
        border-radius: 50%;
        z-index: 200;
    }

    .map-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .territory-info {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 1000;
        min-width: 300px;
    }

    .zoom-controls {
        position: absolute;
        bottom: 10px;
        right: 10px;
    }

    .minimap {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 150px;
        height: 150px;
        border: 2px solid #333;
        background: #ccc;
        z-index: 100;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>World Map - {{ $game->name }} (Year: {{ $game->year }})</h4>
                    <div>
                        <span class="badge bg-primary">Scale: <span id="map-scale">1x</span></span>
                        <span class="badge bg-info ms-2">Territories: {{ $territories->count() }}</span>
                    </div>
                </div>
                <div class="card-body position-relative">
                    <!-- Main Map Container -->
                    <div id="map-container" style="position: relative; overflow: hidden;">
                        <div id="game-map" class="game-map" 
                             style="width: {{ $game->map_size * 10 }}px; 
                                    height: {{ $game->map_size * 10 }}px;">
                            <!-- Territories will be rendered by JavaScript -->
                        </div>

                        <!-- Map Controls -->
                        <div class="map-controls">
                            <div class="btn-group-vertical">
                                <button class="btn btn-sm btn-primary" onclick="zoomIn()">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="zoomOut()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="centerMap()">
                                    <i class="fas fa-crosshairs"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="toggleGrid()">
                                    <i class="fas fa-border-all"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Mini Map -->
                        <div class="minimap" id="mini-map">
                            <!-- Mini map will be rendered here -->
                        </div>
                    </div>

                    <!-- Territory Info Panel -->
                    <div class="territory-info" id="territory-info" style="display: none;">
                        <h5 id="info-title">Territory Information</h5>
                        <div id="info-content"></div>
                        <button class="btn btn-sm btn-close" onclick="hideInfo()" style="position: absolute; top: 5px; right: 5px;"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Map Legend</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="territory land me-2"></div>
                                <span>Land</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="territory water me-2"></div>
                                <span>Water</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="territory mountain me-2"></div>
                                <span>Mountain</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="territory coast me-2"></div>
                                <span>Coast</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6>Countries:</h6>
                            @foreach($countries as $country)
                            <div class="d-flex align-items-center mb-1">
                                <div style="width: 15px; height: 15px; background: {{ $country->color }}; border: 1px solid #000; margin-right: 5px;"></div>
                                <span>{{ $country->name }}</span>
                                <span class="badge bg-secondary ms-2">{{ $country->territories()->count() }} territories</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Map Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <strong>Total Territories:</strong> {{ $territories->count() }}<br>
                            <strong>Map Size:</strong> {{ $game->map_size }}x{{ $game->map_size }}<br>
                            <strong>Water Territories:</strong> {{ $territories->where('type', 'water')->count() }}
                        </div>
                        <div class="col-6">
                            <strong>Land Territories:</strong> {{ $territories->where('type', 'land')->count() }}<br>
                            <strong>Neutral Territories:</strong> {{ $territories->whereNull('country_id')->count() }}<br>
                            <strong>Average Resources:</strong> {{ number_format($territories->avg('resources'), 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let mapData = {
        territories: @json($territories->map(function($t) {
            return [
                'x' => $t->x,
                'y' => $t->y,
                'type' => $t->type,
                'color' => $t->country ? $t->country->color : '#CCCCCC',
                'resources' => $t->resources,
                'country_id' => $t->country_id,
                'country_name' => $t->country ? $t->country->name : 'Neutral'
            ];
        })),
        countries: @json($countries->map(function($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'color' => $c->color,
                'capital_x' => $c->x,
                'capital_y' => $c->y
            ];
        })),
        mapSize: {{ $game->map_size }},
        scale: 1,
        offsetX: 0,
        offsetY: 0
    };

    function renderMap() {
        const mapElement = document.getElementById('game-map');
        mapElement.innerHTML = '';

        // Render territories
        mapData.territories.forEach(territory => {
            const div = document.createElement('div');
            div.className = `territory ${territory.type}`;
            div.style.left = `${territory.x * 10 * mapData.scale + mapData.offsetX}px`;
            div.style.top = `${territory.y * 10 * mapData.scale + mapData.offsetY}px`;
            div.style.width = `${10 * mapData.scale}px`;
            div.style.height = `${10 * mapData.scale}px`;
            div.style.backgroundColor = territory.color;
            div.setAttribute('data-x', territory.x);
            div.setAttribute('data-y', territory.y);
            div.setAttribute('data-type', territory.type);
            div.setAttribute('data-resources', territory.resources);
            div.setAttribute('data-country', territory.country_name);
            
            div.addEventListener('click', function() {
                showTerritoryInfo(territory);
            });

            div.addEventListener('mouseenter', function() {
                this.style.zIndex = '50';
                this.style.boxShadow = '0 0 5px rgba(0,0,0,0.3)';
            });

            div.addEventListener('mouseleave', function() {
                this.style.zIndex = '';
                this.style.boxShadow = '';
            });

            mapElement.appendChild(div);
        });

        // Render country capitals
        mapData.countries.forEach(country => {
            const capital = document.createElement('div');
            capital.className = 'country-capital';
            capital.style.left = `${country.capital_x * 10 * mapData.scale + mapData.offsetX}px`;
            capital.style.top = `${country.capital_y * 10 * mapData.scale + mapData.offsetY}px`;
            capital.style.backgroundColor = country.color;
            capital.style.borderColor = '#000';
            capital.title = `${country.name} Capital`;
            
            mapElement.appendChild(capital);
        });

        // Update scale indicator
        document.getElementById('map-scale').textContent = `${mapData.scale.toFixed(1)}x`;
    }

    function showTerritoryInfo(territory) {
        const infoPanel = document.getElementById('territory-info');
        const title = document.getElementById('info-title');
        const content = document.getElementById('info-content');

        title.textContent = `Territory (${territory.x}, ${territory.y})`;
        content.innerHTML = `
            <div class="row">
                <div class="col-6">
                    <strong>Type:</strong> ${territory.type}<br>
                    <strong>Resources:</strong> ${territory.resources}<br>
                    <strong>Owner:</strong> ${territory.country_name}
                </div>
                <div class="col-6">
                    <div style="width: 20px; height: 20px; background: ${territory.color}; border: 1px solid #000;"></div>
                    ${territory.country_id ? '<span class="badge bg-success">Occupied</span>' : '<span class="badge bg-warning">Neutral</span>'}
                </div>
            </div>
        `;

        infoPanel.style.display = 'block';
    }

    function hideInfo() {
        document.getElementById('territory-info').style.display = 'none';
    }

    function zoomIn() {
        if (mapData.scale < 3) {
            mapData.scale += 0.1;
            renderMap();
        }
    }

    function zoomOut() {
        if (mapData.scale > 0.5) {
            mapData.scale -= 0.1;
            renderMap();
        }
    }

    function centerMap() {
        mapData.offsetX = 0;
        mapData.offsetY = 0;
        renderMap();
    }

    function toggleGrid() {
        const territories = document.querySelectorAll('.territory');
        territories.forEach(t => {
            if (t.style.borderWidth === '0px') {
                t.style.border = '1px solid rgba(0,0,0,0.1)';
            } else {
                t.style.border = '0px';
            }
        });
    }

    // Drag to move map
    let isDragging = false;
    let startX, startY, startOffsetX, startOffsetY;

    document.getElementById('game-map').addEventListener('mousedown', function(e) {
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        startOffsetX = mapData.offsetX;
        startOffsetY = mapData.offsetY;
        this.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', function(e) {
        if (isDragging) {
            mapData.offsetX = startOffsetX + (e.clientX - startX);
            mapData.offsetY = startOffsetY + (e.clientY - startY);
            renderMap();
        }
    });

    document.addEventListener('mouseup', function() {
        isDragging = false;
        document.getElementById('game-map').style.cursor = 'grab';
    });

    // Initialize map
    document.addEventListener('DOMContentLoaded', function() {
        renderMap();
        
        // Auto-update map every 30 seconds
        setInterval(updateMap, 30000);
    });

    function updateMap() {
        fetch('{{ route("maps.update", $game->id) }}')
            .then(response => response.json())
            .then(data => {
                mapData.territories = data.territories;
                renderMap();
            });
    }

    // Keyboard controls
    document.addEventListener('keydown', function(e) {
        switch(e.key) {
            case '+':
            case '=':
                zoomIn();
                break;
            case '-':
                zoomOut();
                break;
            case 'c':
                centerMap();
                break;
            case 'g':
                toggleGrid();
                break;
        }
    });
</script>
@endsection