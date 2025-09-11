@extends('layouts.app')

@section('styles')
<style>
    .general-profile {
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
    .stat-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0 auto;
    }
    .attack-stat { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
    .defense-stat { background: linear-gradient(135deg, #28a745, #1e7e34); color: white; }
    .speed-stat { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }
    .experience-bar {
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }
    .battle-history {
        max-height: 400px;
        overflow-y: auto;
    }
    .battle-item {
        border-left: 4px solid transparent;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
    .battle-win { border-left-color: #28a745; }
    .battle-loss { border-left-color: #dc3545; }
    .battle-draw { border-left-color: #ffc107; }
</style>
@endsection

@section('content')
<div class="general-profile">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- General Header -->
                <div class="profile-card p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="general-avatar me-3" 
                                     style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea, #764ba2); 
                                            border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                                            color: white; font-size: 1.5rem; font-weight: bold;">
                                    {{ substr($general->name, 0, 1) }}
                                </div>
                                <div>
                                    <h2 class="mb-1">{{ $general->name }}</h2>
                                    <p class="text-muted mb-0">
                                        General of {{ $general->country->name }} | 
                                        Age: {{ $general->age }} | 
                                        Service: {{ now()->diffInYears($general->created_at) }} years
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-{{ $general->is_alive ? 'success' : 'danger' }} fs-6">
                                {{ $general->is_alive ? 'Active' : 'Deceased' }}
                            </span>
                            <span class="badge bg-{{ $general->order === 'attack' ? 'danger' : ($general->order === 'defend' ? 'warning' : 'secondary') }} fs-6 ms-2">
                                {{ ucfirst($general->order) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="profile-card p-3 text-center">
                            <div class="stat-circle attack-stat mb-2">
                                {{ $general->attack }}
                            </div>
                            <h5>Attack</h5>
                            <p class="text-muted mb-0">Combat effectiveness</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="profile-card p-3 text-center">
                            <div class="stat-circle defense-stat mb-2">
                                {{ $general->defense }}
                            </div>
                            <h5>Defense</h5>
                            <p class="text-muted mb-0">Damage reduction</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="profile-card p-3 text-center">
                            <div class="stat-circle speed-stat mb-2">
                                {{ $general->speed }}
                            </div>
                            <h5>Speed</h5>
                            <p class="text-muted mb-0">Movement and initiative</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Current Status -->
                        <div class="profile-card p-4 mb-4">
                            <h4 class="mb-3">Current Status</h4>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <p><strong>Current Order:</strong><br>
                                       <span class="badge bg-{{ $general->order === 'attack' ? 'danger' : ($general->order === 'defend' ? 'warning' : 'secondary') }}">
                                           {{ ucfirst($general->order) }}
                                       </span>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Soldiers Under Command:</strong><br>
                                       {{ number_format($general->soldiers_count) }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Experience:</strong><br>
                                       {{ number_format($general->experience) }} XP</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Recruitment Cost:</strong><br>
                                       ${{ number_format($general->cost) }}</p>
                                </div>
                            </div>
                            <div class="experience-bar mt-3">
                                <div class="progress-bar bg-primary" style="width: {{ $general->experience % 100 }}%;">
                                    Level {{ floor($general->experience / 100) + 1 }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="profile-card p-4">
                            <h4 class="mb-3">General Actions</h4>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal">
                                    <i class="fas fa-scroll"></i> Change Orders
                                </button>
                                <button class="btn btn-outline-warning" onclick="trainGeneral({{ $general->id }})">
                                    <i class="fas fa-dumbbell"></i> Train General ($100)
                                </button>
                                <button class="btn btn-outline-info" onclick="promoteGeneral({{ $general->id }})">
                                    <i class="fas fa-star"></i> Promote General
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#dismissModal">
                                    <i class="fas fa-times"></i> Dismiss General
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Battle History -->
                        <div class="profile-card p-4">
                            <h4 class="mb-3">Battle History</h4>
                            <div class="battle-history">
                                @forelse($general->battles->take(10) as $battle)
                                @php
                                    $wasAttacker = $battle->attacker_general_id === $general->id;
                                    $result = $wasAttacker ? 
                                        ($battle->result === 'attacker_win' ? 'win' : ($battle->result === 'defender_win' ? 'loss' : 'draw')) :
                                        ($battle->result === 'defender_win' ? 'win' : ($battle->result === 'attacker_win' ? 'loss' : 'draw'));
                                @endphp
                                <div class="battle-item battle-{{ $result }}">
                                    <h6 class="mb-1">
                                        {{ $wasAttacker ? 'Attack on ' : 'Defense against ' }}
                                        {{ $wasAttacker ? $battle->defenderCountry->name : $battle->attackerCountry->name }}
                                    </h6>
                                    <small class="text-muted">{{ $battle->started_at->format('M d, Y') }}</small>
                                    <p class="mb-1">
                                        Forces: {{ number_format($wasAttacker ? $battle->attacker_soldiers : $battle->defender_soldiers) }} |
                                        Losses: {{ number_format($wasAttacker ? $battle->attacker_soldiers_lost : $battle->defender_soldiers_lost) }}
                                    </p>
                                    <span class="badge bg-{{ $result === 'win' ? 'success' : ($result === 'loss' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($result) }}
                                    </span>
                                </div>
                                @empty
                                <p class="text-muted">No battles yet</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="profile-card p-4 mt-4">
                            <h4 class="mb-3">Combat Statistics</h4>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Total Battles:</strong><br>
                                       {{ $general->battles->count() }}</p>
                                    <p><strong>Battles Won:</strong><br>
                                       {{ $general->battles->filter(function($battle) use ($general) {
                                           return ($battle->attacker_general_id === $general->id && $battle->result === 'attacker_win') ||
                                                  ($battle->defender_general_id === $general->id && $battle->result === 'defender_win');
                                       })->count() }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Win Rate:</strong><br>
                                       @php
                                           $total = $general->battles->count();
                                           $wins = $general->battles->filter(function($battle) use ($general) {
                                               return ($battle->attacker_general_id === $general->id && $battle->result === 'attacker_win') ||
                                                      ($battle->defender_general_id === $general->id && $battle->result === 'defender_win');
                                           })->count();
                                           $winRate = $total > 0 ? round(($wins / $total) * 100) : 0;
                                       @endphp
                                       {{ $winRate }}%
                                    </p>
                                    <p><strong>Total Experience:</strong><br>
                                       {{ number_format($general->battles->sum(function($battle) use ($general) {
                                           return $battle->attacker_general_id === $general->id ? 
                                               $battle->attacker_experience_gain : $battle->defender_experience_gain;
                                       })) }} XP</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="{{ route('games.play', $general->player->game_id) }}" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-arrow-left"></i> Back to Game
                    </a>
                    <a href="{{ route('players.show', $general->player_id) }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-user"></i> View Player Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Orders for {{ $general->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="orderForm">
                    <div class="mb-3">
                        <label class="form-label">New Order</label>
                        <select class="form-select" name="order">
                            <option value="rest" {{ $general->order === 'rest' ? 'selected' : '' }}>Rest</option>
                            <option value="train" {{ $general->order === 'train' ? 'selected' : '' }}>Train</option>
                            <option value="attack" {{ $general->order === 'attack' ? 'selected' : '' }}>Attack</option>
                            <option value="defend" {{ $general->order === 'defend' ? 'selected' : '' }}>Defend</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Soldiers to Assign</label>
                        <input type="number" class="form-control" name="soldiers_count" 
                               value="{{ $general->soldiers_count }}" min="0" max="{{ $general->player->soldiers }}">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateOrders({{ $general->id }})">Confirm Orders</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dismissModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dismiss {{ $general->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to dismiss {{ $general->name }}? You will receive a 50% refund of the recruitment cost.</p>
                <p>Refund: ${{ number_format($general->cost * 0.5) }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="dismissGeneral({{ $general->id }})">Dismiss General</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function trainGeneral(generalId) {
        fetch(`/generals/${generalId}/train`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('General trained successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }

    function updateOrders(generalId) {
        const formData = new FormData(document.getElementById('orderForm'));
        
        fetch(`/generals/${generalId}/order`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#orderModal').modal('hide');
                alert('Orders updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }

    function dismissGeneral(generalId) {
        fetch(`/generals/${generalId}/dismiss`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#dismissModal').modal('hide');
                alert('General dismissed successfully!');
                window.location.href = "{{ route('games.play', $general->player->game_id) }}";
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
</script>
@endsection