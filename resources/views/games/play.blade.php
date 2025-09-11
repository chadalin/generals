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
                    <div class="tab-pane fade" id="military" role="tabpanel">
                        <h4>Military Overview</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-dark mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Army Strength</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Total Soldiers: {{ $player->soldiers }}</p>
                                        <p>Generals: {{ $generals->count() }}</p>
                                        <p>Military Power: {{ $player->soldiers + ($generals->sum('attack') + $generals->sum('defense')) * 10 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-dark mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Recent Battles</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">No recent battles</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other tabs would be implemented similarly -->
                    <div class="tab-pane fade" id="economy" role="tabpanel">Economy content...</div>
                    <div class="tab-pane fade" id="research" role="tabpanel">Research content...</div>
                    <div class="tab-pane fade" id="diplomacy" role="tabpanel">Diplomacy content...</div>
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
                        <div class="event-item text-muted small mb-2">
                            <div>Game started</div>
                            <small>Year {{ $game->year }}</small>
                        </div>
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
<script>
    // Game interface functionality
    document.addEventListener('DOMContentLoaded', function() {
        // General selection
        const generalCards = document.querySelectorAll('.general-card');
        generalCards.forEach(card => {
            card.addEventListener('click', function() {
                generalCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                // Load general details
            });
        });

        // Turn form submission
        document.getElementById('turn-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Submit turn data via AJAX
        });

        // Simple map rendering
        const canvas = document.getElementById('gameMapCanvas');
        const ctx = canvas.getContext('2d');
        
        // Draw basic map grid
        for (let x = 0; x < 50; x++) {
            for (let y = 0; y < 50; y++) {
                ctx.fillStyle = (x + y) % 2 === 0 ? '#2d3748' : '#4a5568';
                ctx.fillRect(x * 10, y * 10, 10, 10);
            }
        }
    });
</script>
@endsection