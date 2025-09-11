@extends('layouts.app')

@section('styles')
<style>
    .game-creation {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    .creation-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    .setting-slider {
        width: 100%;
        margin: 1rem 0;
    }
    .slider-value {
        font-weight: bold;
        color: #667eea;
    }
</style>
@endsection

@section('content')
<div class="game-creation">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card creation-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Create New Game</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('games.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Game Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ old('name') }}" required placeholder="Enter game name">
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="max_players" class="form-label">Maximum Players</label>
                                <input type="number" class="form-control" id="max_players" name="max_players" 
                                       value="{{ old('max_players', 6) }}" min="2" max="20" required>
                                <div class="form-text">Number of players (including AI)</div>
                            </div>

                            <div class="mb-3">
                                <label for="map_size" class="form-label">Map Size</label>
                                <input type="range" class="form-range setting-slider" id="map_size" name="map_size" 
                                       value="{{ old('map_size', 100) }}" min="50" max="200" step="10"
                                       oninput="updateSliderValue('map_size', 'map_size_value')">
                                <div class="d-flex justify-content-between">
                                    <span>Small</span>
                                    <span class="slider-value" id="map_size_value">{{ old('map_size', 100) }}</span>
                                    <span>Large</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="ai_difficulty" class="form-label">AI Difficulty</label>
                                <select class="form-select" id="ai_difficulty" name="ai_difficulty">
                                    <option value="1" {{ old('ai_difficulty') == 1 ? 'selected' : '' }}>Easy</option>
                                    <option value="2" {{ old('ai_difficulty', 2) ? 'selected' : '' }}>Medium</option>
                                    <option value="3" {{ old('ai_difficulty') == 3 ? 'selected' : '' }}>Hard</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="start_year" class="form-label">Start Year</label>
                                <input type="number" class="form-control" id="start_year" name="start_year" 
                                       value="{{ old('start_year', 1950) }}" min="1950" max="2000">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_private" name="is_private" 
                                       {{ old('is_private') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_private">Private Game</label>
                            </div>

                            <div class="mb-3" id="password_field" style="display: none;">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter game password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Additional Settings</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="random_countries" 
                                           name="random_countries" checked>
                                    <label class="form-check-label" for="random_countries">
                                        Random Country Positions
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="fog_of_war" 
                                           name="fog_of_war" checked>
                                    <label class="form-check-label" for="fog_of_war">
                                        Fog of War
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus-circle"></i> Create Game
                                </button>
                                <a href="{{ route('games.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Games List
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateSliderValue(sliderId, valueId) {
        const slider = document.getElementById(sliderId);
        const value = document.getElementById(valueId);
        value.textContent = slider.value;
    }

    // Toggle password field based on private game checkbox
    document.getElementById('is_private').addEventListener('change', function() {
        const passwordField = document.getElementById('password_field');
        passwordField.style.display = this.checked ? 'block' : 'none';
    });

    // Initialize slider values
    document.addEventListener('DOMContentLoaded', function() {
        updateSliderValue('map_size', 'map_size_value');
    });
</script>
@endsection