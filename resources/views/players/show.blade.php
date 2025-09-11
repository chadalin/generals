@extends('layouts.app')

@section('styles')
<style>
    .player-profile {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    .profile-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    .progress-lg {
        height: 20px;
    }
    .technology-badge {
        background: rgba(102, 126, 234, 0.2);
        border: 1px solid #667eea;
        border-radius: 20px;
        padding: 0.5rem 1rem;
        margin: 0.25rem;
        display: inline-block;
    }
    .technology-badge.researched {
        background: rgba(40, 167, 69, 0.2);
        border-color: #28a745;
    }
</style>
@endsection

@section('content')
<div class="player-profile">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Profile Header -->
                <div class="profile-card p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="country-color me-3" 
                                     style="background: {{ $player->country->color }}; width: 40px; height: 40px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.2);"></div>
                                <div>
                                    <h2 class="mb-1">{{ $player->username }}</h2>
                                    <p class="text-muted mb-0">
                                        Ruler of {{ $player->country->name }} | 
                                        Game: {{ $player->game->name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-{{ $player->is_ai ? 'secondary' : 'primary' }} fs-6">
                                {{ $player->is_ai ? 'AI Player' : 'Human Player' }}
                            </span>
                            <span class="badge bg-{{ $player->is_ready ? 'success' : 'warning' }} fs-6 ms-2">
                                {{ $player->is_ready ? 'Ready' : 'Not Ready' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-coins fa-2x mb-2"></i>
                            <h3>{{ number_format($player->money) }}</h3>
                            <p class="mb-0">Wealth</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-wheat-alt fa-2x mb-2"></i>
                            <h3>{{ number_format($player->grain) }}</h3>
                            <p class="mb-0">Food Supply</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h3>{{ number_format($player->peasants + $player->scientists + $player->soldiers) }}</h3>
                            <p class="mb-0">Population</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-shield-alt fa-2x mb-2"></i>
                            <h3>{{ number_format($player->soldiers) }}</h3>
                            <p class="mb-0">Military Power</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Resources Card -->
                        <div class="profile-card p-4 mb-4">
                            <h4 class="mb-3">Resources & Population</h4>
                            <div class="mb-3">
                                <label class="form-label">Peasants: {{ $player->peasants }}</label>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-success" style="width: {{ ($player->peasants / ($player->peasants + $player->scientists + $player->soldiers)) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Scientists: {{ $player->scientists }}</label>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-info" style="width: {{ ($player->scientists / ($player->peasants + $player->scientists + $player->soldiers)) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Soldiers: {{ $player->soldiers }}</label>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-danger" style="width: {{ ($player->soldiers / ($player->peasants + $player->scientists + $player->soldiers)) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Military Card -->
                        <div class="profile-card p-4 mb-4">
                            <h4 class="mb-3">Military Strength</h4>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Total Soldiers:</strong><br>{{ number_format($player->soldiers) }}</p>
                                    <p><strong>Generals:</strong><br>{{ $player->generals->count() }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Attack Bonus:</strong><br>x{{ number_format($player->research->attack_bonus ?? 1.0, 2) }}</p>
                                    <p><strong>Defense Bonus:</strong><br>x{{ number_format($player->research->defense_bonus ?? 1.0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Research Progress -->
                        <div class="profile-card p-4 mb-4">
                            <h4 class="mb-3">Research Progress</h4>
                            <div class="mb-3">
                                <label class="form-label">Military Research (Level {{ $player->research->military_level ?? 0 }})</label>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-danger" style="width: {{ $player->research->military_progress ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Economic Research (Level {{ $player->research->economy_level ?? 0 }})</label>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-warning" style="width: {{ $player->research->economy_progress ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Science Research (Level {{ $player->research->science_level ?? 0 }})</label>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-info" style="width: {{ $player->research->science_progress ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Technologies -->
                        <div class="profile-card p-4">
                            <h4 class="mb-3">Researched Technologies</h4>
                            <div class="technologies-grid">
                                @if($player->research)
                                    @foreach($player->research->military_technologies as $tech => $researched)
                                        @if($researched)
                                            <span class="technology-badge researched">
                                                <i class="fas fa-check-circle me-1"></i> {{ ucfirst($tech) }}
                                            </span>
                                        @else
                                            <span class="technology-badge">
                                                <i class="fas fa-lock me-1"></i> {{ ucfirst($tech) }}
                                            </span>
                                        @endif
                                    @endforeach
                                @else
                                    <p class="text-muted">No research data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Generals Section -->
                <div class="profile-card p-4 mt-4">
                    <h4 class="mb-3">Military Generals</h4>
                    <div class="row">
                        @forelse($player->generals as $general)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $general->name }}</h5>
                                    <p class="card-text">
                                        <strong>Stats:</strong><br>
                                        Attack: {{ $general->attack }}<br>
                                        Defense: {{ $general->defense }}<br>
                                        Speed: {{ $general->speed }}<br>
                                        Experience: {{ $general->experience }}
                                    </p>
                                    <span class="badge bg-{{ $general->order === 'attack' ? 'danger' : ($general->order === 'defend' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($general->order) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-muted text-center">No generals hired yet</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="{{ route('games.play', $player->game_id) }}" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-play"></i> Continue Game
                    </a>
                    <a href="{{ route('games.show', $player->game_id) }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Back to Game
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection