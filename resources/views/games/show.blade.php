@extends('layouts.app')

@section('styles')
<style>
    .game-lobby {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    .lobby-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    .player-list {
        max-height: 300px;
        overflow-y: auto;
    }
    .player-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    .player-item.ready {
        border-left-color: #28a745;
    }
    .player-item.ai {
        border-left-color: #6c757d;
    }
    .country-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 10px;
    }
</style>
@endsection

@section('content')
<div class="game-lobby">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card lobby-card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $game->name }} - Lobby</h4>
                        <span class="badge bg-light text-dark">
                            Players: {{ $game->current_players }}/{{ $game->max_players }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Game Settings</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Map Size:</span>
                                        <span class="fw-bold">{{ $game->map_size }}x{{ $game->map_size }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Status:</span>
                                        <span class="badge bg-{{ $game->status === 'waiting' ? 'warning' : 'success' }}">
                                            {{ ucfirst($game->status) }}
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Year:</span>
                                        <span class="fw-bold">{{ $game->year }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Game Actions</h5>
                                <div class="d-grid gap-2">
                                    @if($game->status === 'waiting' && $player)
                                        @if($player->user_id === Auth::id() || Auth::user()->is_admin)
                                            <form action="{{ route('games.start', $game->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-lg w-100">
                                                    <i class="fas fa-play"></i> Start Game
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{ route('games.play', $game->id) }}" class="btn btn-primary w-100">
                                            <i class="fas fa-door-open"></i> Enter Game
                                        </a>
                                    @elseif($game->status === 'in_progress')
                                        <a href="{{ route('games.play', $game->id) }}" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-fight"></i> Continue Game
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('games.index') }}" class="btn btn-secondary w-100">
                                        <i class="fas fa-arrow-left"></i> Back to Games
                                    </a>
                                </div>
                            </div>
                        </div>

                        <h5>Players</h5>
                        <div class="player-list">
                            @foreach($game->players as $gamePlayer)
                            <div class="card player-item mb-2 {{ $gamePlayer->is_ready ? 'ready' : '' }} {{ $gamePlayer->is_ai ? 'ai' : '' }}">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            @if($gamePlayer->country)
                                                <span class="country-color" style="background: {{ $gamePlayer->country->color }};"></span>
                                            @endif
                                            <span class="fw-bold">{{ $gamePlayer->username }}</span>
                                            @if($gamePlayer->is_ai)
                                                <span class="badge bg-secondary ms-2">AI</span>
                                            @endif
                                        </div>
                                        <div>
                                            @if($gamePlayer->is_ready)
                                                <span class="badge bg-success">Ready</span>
                                            @else
                                                <span class="badge bg-warning">Not Ready</span>
                                            @endif
                                            @if($gamePlayer->user_id === Auth::id())
                                                <span class="badge bg-info">You</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card lobby-card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Chat</h5>
                    </div>
                    <div class="card-body">
                        <div id="game-chat" style="height: 200px; overflow-y: auto; margin-bottom: 1rem;">
                            <div class="text-center text-muted">No messages yet</div>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Type your message...">
                            <button class="btn btn-primary" type="button">Send</button>
                        </div>
                    </div>
                </div>

                <div class="card lobby-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Game Information</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Game ID:</strong> {{ $game->id }}<br>
                            <strong>Created:</strong> {{ $game->created_at->diffForHumans() }}<br>
                            <strong>Turn Duration:</strong> {{ $game->turn_duration }} minutes<br>
                            <strong>Privacy:</strong> {{ $game->is_private ? 'Private' : 'Public' }}
                        </p>
                        <hr>
                        <h6>How to Play:</h6>
                        <ul class="small">
                            <li>Manage your resources and population</li>
                            <li>Research technologies to gain advantages</li>
                            <li>Hire and train generals for battles</li>
                            <li>Expand your territory and conquer opponents</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-refresh the page every 30 seconds to update player status
    setInterval(function() {
        window.location.reload();
    }, 30000);
</script>
@endsection