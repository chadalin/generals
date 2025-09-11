@extends('layouts.app')

@section('styles')
<style>
    .country-profile {
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
    .country-header {
        border-bottom: 3px solid {{ $country->color }};
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    .relation-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    .diplomacy-actions {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 1rem;
    }
</style>
@endsection

@section('content')
<div class="country-profile">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Country Header -->
                <div class="profile-card p-4 mb-4">
                    <div class="country-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="country-flag me-3" 
                                         style="background: {{ $country->color }}; width: 60px; height: 60px; 
                                                border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.2);"></div>
                                    <div>
                                        <h2 class="mb-1">{{ $country->name }}</h2>
                                        <p class="text-muted mb-0">
                                            {{ $country->player ? 'Ruled by ' . $country->player->username : 'AI Controlled' }} |
                                            Game: {{ $country->game->name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-{{ $country->is_alive ? 'success' : 'danger' }} fs-6">
                                    {{ $country->is_alive ? 'Active' : 'Defeated' }}
                                </span>
                                <span class="badge bg-info fs-6 ms-2">
                                    Year: {{ $country->game->year }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mt-3">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary">{{ number_format($country->territory) }}</h4>
                            <p class="text-muted mb-0">Territory (km²)</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">{{ number_format($country->player ? ($country->player->peasants + $country->player->scientists + $country->player->soldiers) : 0) }}</h4>
                            <p class="text-muted mb-0">Population</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-danger">{{ number_format($country->player ? $country->player->soldiers : 0) }}</h4>
                            <p class="text-muted mb-0">Military Power</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ number_format($country->player ? $country->player->money : 0) }}</h4>
                            <p class="text-muted mb-0">Wealth</p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-map-marked-alt fa-2x mb-2"></i>
                            <h3>{{ number_format($country->territories()->count()) }}</h3>
                            <p class="mb-0">Territories</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-gem fa-2x mb-2"></i>
                            <h3>{{ number_format($country->territories()->sum('resources')) }}</h3>
                            <p class="mb-0">Total Resources</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-crown fa-2x mb-2"></i>
                            <h3>{{ number_format($country->player ? $country->player->generals()->count() : 0) }}</h3>
                            <p class="mb-0">Generals</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-trophy fa-2x mb-2"></i>
                            <h3>{{ number_format($country->win_rate) }}%</h3>
                            <p class="mb-0">Win Rate</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Territory Info -->
                        <div class="profile-card p-4 mb-4">
                            <h4 class="mb-3">Territory Information</h4>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Total Area:</strong><br>
                                       {{ number_format($country->territory) }} km²</p>
                                    <p><strong>Average Resources:</strong><br>
                                       {{ number_format($country->territories()->avg('resources'), 1) }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Border Territories:</strong><br>
                                       {{ number_format($country->territories()->where('is_border', true)->count()) }}</p>
                                    <p><strong>Resource Richness:</strong><br>
                                       {{ number_format(($country->territories()->avg('resources') / 100) * 100) }}%</p>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 10px;">
                                <div class="progress-bar" style="width: {{ ($country->territory / ($country->game->map_size * $country->game->map_size)) * 100 }}%;
                                            background: {{ $country->color }};"></div>
                            </div>
                            <small class="text-muted">Percentage of world territory controlled</small>
                        </div>

                        <!-- Military Strength -->
                        <div class="profile-card p-4 mb-4">
                            <h4 class="mb-3">Military Strength</h4>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Total Soldiers:</strong><br>
                                       {{ number_format($country->player ? $country->player->soldiers : 0) }}</p>
                                    <p><strong>Generals:</strong><br>
                                       {{ number_format($country->player ? $country->player->generals()->count() : 0) }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Military Level:</strong><br>
                                       {{ $country->player && $country->player->research ? $country->player->research->military_level : 0 }}</p>
                                    <p><strong>Attack Bonus:</strong><br>
                                       x{{ number_format($country->player && $country->player->research ? $country->player->research->attack_bonus : 1.0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Diplomacy & Relations -->
                        <div class="profile-card p-4 mb-4">
                            <h4 class="mb-3">Diplomatic Relations</h4>
                            <div class="diplomacy-actions mb-3">
                                <h6 class="text-center mb-3">Current Relations</h6>
                                <div id="relations-list">
                                    @php
                                        $relations = $country->relations ?? [];
                                        $neighbors = App\Models\Country::where('game_id', $country->game_id)
                                            ->where('id', '!=', $country->id)
                                            ->get();
                                    @endphp
                                    
                                    @foreach($neighbors as $neighbor)
                                    <div class="d-flex align-items-center justify-content-between mb-2 p-2" 
                                         style="background: rgba(0,0,0,0.05); border-radius: 5px;">
                                        <div class="d-flex align-items-center">
                                            <div class="country-color me-2" 
                                                 style="background: {{ $neighbor->color }}; width: 16px; height: 16px; border-radius: 3px;"></div>
                                            <span>{{ $neighbor->name }}</span>
                                        </div>
                                        <span class="relation-badge bg-{{ $relations[$neighbor->id] ?? 'neutral' === 'ally' ? 'success' : ($relations[$neighbor->id] ?? 'neutral' === 'enemy' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($relations[$neighbor->id] ?? 'neutral') }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            @if($country->player && $country->player->user_id === Auth::id())
                            <div class="text-center">
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#diplomacyModal">
                                    <i class="fas fa-handshake"></i> Manage Diplomacy
                                </button>
                            </div>
                            @endif
                        </div>

                        <!-- Recent Activity -->
                        <div class="profile-card p-4">
                            <h4 class="mb-3">Recent Activity</h4>
                            <div class="activity-feed">
                                @php
                                    $recentBattles = $country->allBattles->sortByDesc('started_at')->take(5);
                                @endphp
                                
                                @forelse($recentBattles as $battle)
                                <div class="activity-item mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">
                                            {{ $battle->attacker_country_id === $country->id ? 'Attacked ' : 'Defended against ' }}
                                            {{ $battle->attacker_country_id === $country->id ? $battle->defenderCountry->name : $battle->attackerCountry->name }}
                                        </span>
                                        <span class="badge bg-{{ $battle->result === ($battle->attacker_country_id === $country->id ? 'attacker_win' : 'defender_win') ? 'success' : 'danger' }}">
                                            {{ $battle->result === ($battle->attacker_country_id === $country->id ? 'attacker_win' : 'defender_win') ? 'Victory' : 'Defeat' }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $battle->started_at->diffForHumans() }}</small>
                                </div>
                                @empty
                                <p class="text-muted">No recent activity</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="{{ route('games.play', $country->game_id) }}" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-play"></i> Continue Game
                    </a>
                    <a href="{{ route('maps.show', $country->game_id) }}" class="btn btn-info btn-lg me-3">
                        <i class="fas fa-map"></i> View on Map
                    </a>
                    <a href="{{ route('games.show', $country->game_id) }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Back to Game
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Diplomacy Modal -->
@if($country->player && $country->player->user_id === Auth::id())
<div class="modal fade" id="diplomacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Diplomatic Relations Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Current Relation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($neighbors as $neighbor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="country-color me-2" 
                                             style="background: {{ $neighbor->color }}; width: 16px; height: 16px; border-radius: 3px;"></div>
                                        {{ $neighbor->name }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $relations[$neighbor->id] ?? 'neutral' === 'ally' ? 'success' : ($relations[$neighbor->id] ?? 'neutral' === 'enemy' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($relations[$neighbor->id] ?? 'neutral') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" onclick="updateRelation({{ $country->id }}, {{ $neighbor->id }}, 'ally')">
                                            Ally
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="updateRelation({{ $country->id }}, {{ $neighbor->id }}, 'neutral')">
                                            Neutral
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="updateRelation({{ $country->id }}, {{ $neighbor->id }}, 'enemy')">
                                            Enemy
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    function updateRelation(countryId, targetCountryId, relation) {
        fetch(`/countries/${countryId}/relations/${targetCountryId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ relation: relation })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Relation updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
</script>
@endsection