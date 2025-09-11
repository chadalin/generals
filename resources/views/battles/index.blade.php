@extends('layouts.app')

@section('styles')
<style>
    .battles-list {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    .battle-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
    }
    .battle-card:hover {
        transform: translateY(-2px);
    }
    .battle-result-win {
        border-left: 4px solid #28a745;
    }
    .battle-result-loss {
        border-left: 4px solid #dc3545;
    }
    .battle-result-draw {
        border-left: 4px solid #ffc107;
    }
    .battle-result-ongoing {
        border-left: 4px solid #17a2b8;
    }
</style>
@endsection

@section('content')
<div class="battles-list">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Battles History - {{ $game->name }}</h4>
                        <span class="badge bg-light text-dark">
                            Total: {{ $battles->total() }} battles
                        </span>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <select class="form-select" id="result-filter">
                                    <option value="">All Results</option>
                                    <option value="attacker_win">Attacker Wins</option>
                                    <option value="defender_win">Defender Wins</option>
                                    <option value="draw">Draws</option>
                                    <option value="ongoing">Ongoing</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control" id="date-filter" placeholder="Filter by date">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="search-filter" placeholder="Search battles...">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100" onclick="applyFilters()">Apply Filters</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Battles List -->
                @forelse($battles as $battle)
                <div class="card battle-card battle-result-{{ $battle->result === 'ongoing' ? 'ongoing' : ($battle->result === 'attacker_win' ? 'win' : ($battle->result === 'defender_win' ? 'loss' : 'draw')) }}">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title">
                                    {{ $battle->attackerCountry->name }} 
                                    <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                    {{ $battle->defenderCountry->name }}
                                </h5>
                                <div class="d-flex flex-wrap gap-3 mb-2">
                                    <span class="badge bg-{{ $battle->result === 'attacker_win' ? 'success' : ($battle->result === 'defender_win' ? 'danger' : ($battle->result === 'draw' ? 'warning' : 'info')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $battle->result)) }}
                                    </span>
                                    <span class="text-muted">
                                        <i class="fas fa-calendar"></i> {{ $battle->started_at->format('M d, Y H:i') }}
                                    </span>
                                    <span class="text-muted">
                                        <i class="fas fa-clock"></i> {{ $battle->duration_hours }} hours
                                    </span>
                                </div>
                                <div class="battle-stats">
                                    <small class="text-muted">
                                        Forces: {{ number_format($battle->attacker_soldiers) }} vs {{ number_format($battle->defender_soldiers) }} |
                                        Losses: {{ number_format($battle->attacker_soldiers_lost) }} / {{ number_format($battle->defender_soldiers_lost) }} |
                                        Territory: +{{ number_format($battle->territory_captured) }} km²
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ route('battles.show', $battle->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-info-circle"></i> Details
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="showBattleLog({{ $battle->id }})">
                                        <i class="fas fa-scroll"></i> Log
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-peace fa-3x text-muted mb-3"></i>
                        <h5>No battles yet</h5>
                        <p class="text-muted">The world is at peace... for now.</p>
                    </div>
                </div>
                @endforelse

                <!-- Pagination -->
                @if($battles->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $battles->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function applyFilters() {
        const result = document.getElementById('result-filter').value;
        const date = document.getElementById('date-filter').value;
        const search = document.getElementById('search-filter').value;
        
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        
        if (result) params.set('result', result);
        if (date) params.set('date', date);
        if (search) params.set('search', search);
        
        window.location.href = url.pathname + '?' + params.toString();
    }

    function showBattleLog(battleId) {
        // AJAX call to get battle log
        fetch(`/battles/${battleId}/log`)
            .then(response => response.json())
            .then(data => {
                // Show log in modal
                console.log('Battle log:', data);
            });
    }

    // Initialize filters from URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        document.getElementById('result-filter').value = urlParams.get('result') || '';
        document.getElementById('date-filter').value = urlParams.get('date') || '';
        document.getElementById('search-filter').value = urlParams.get('search') || '';
    });
</script>
@endsection