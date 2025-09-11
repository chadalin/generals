@extends('layouts.app')

@section('styles')
<style>
    .battle-detail {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    .battle-header {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    .force-card {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
    }
    .attacker-force {
        border: 3px solid #dc3545;
    }
    .defender-force {
        border: 3px solid #28a745;
    }
    .casualties-chart {
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 1rem;
    }
    .timeline {
        position: relative;
        padding-left: 3rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #667eea;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -2.2rem;
        top: 0.5rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background: #667eea;
        border: 3px solid white;
    }
</style>
@endsection

@section('content')
<div class="battle-detail">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Battle Header -->
                <div class="battle-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-3">Battle of {{ $battle->battle_x }},{{ $battle->battle_y }}</h2>
                            <div class="d-flex flex-wrap gap-3 mb-2">
                                <span class="badge bg-{{ $battle->result === 'attacker_win' ? 'success' : ($battle->result === 'defender_win' ? 'danger' : ($battle->result === 'draw' ? 'warning' : 'info')) }} fs-6">
                                    {{ ucfirst(str_replace('_', ' ', $battle->result)) }}
                                </span>
                                <span class="text-muted fs-6">
                                    <i class="fas fa-calendar"></i> {{ $battle->started_at->format('F j, Y') }}
                                </span>
                                <span class="text-muted fs-6">
                                    <i class="fas fa-clock"></i> {{ $battle->duration_hours }} hours
                                </span>
                                <span class="text-muted fs-6">
                                    <i class="fas fa-map-marker"></i> Coordinates: {{ $battle->battle_x }}, {{ $battle->battle_y }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('battles.index', ['game' => $battle->game_id]) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Battles
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Forces Comparison -->
                <div class="row mb-4">
                    <div class="col-md-5">
                        <div class="force-card attacker-force">
                            <h4 class="text-danger">Attacker</h4>
                            <h5>{{ $battle->attackerCountry->name }}</h5>
                            @if($battle->attackerGeneral)
                            <p class="mb-1">
                                <strong>General:</strong> {{ $battle->attackerGeneral->name }}
                            </p>
                            <p class="mb-1">
                                <strong>Stats:</strong> A{{ $battle->attackerGeneral->attack }} D{{ $battle->attackerGeneral->defense }} S{{ $battle->attackerGeneral->speed }}
                            </p>
                            @endif
                            <h3 class="text-danger mt-3">{{ number_format($battle->attacker_soldiers) }}</h3>
                            <small class="text-muted">Initial Forces</small>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-danger" style="width: {{ (($battle->attacker_soldiers - $battle->attacker_soldiers_lost) / $battle->attacker_soldiers) * 100 }}%"></div>
                            </div>
                            <p class="mt-2 text-danger">
                                Lost: {{ number_format($battle->attacker_soldiers_lost) }} ({{ round(($battle->attacker_soldiers_lost / $battle->attacker_soldiers) * 100) }}%)
                            </p>
                        </div>
                    </div>

                    <div class="col-md-2 text-center align-self-center">
                        <div class="vs-circle bg-dark text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <strong>VS</strong>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="force-card defender-force">
                            <h4 class="text-success">Defender</h4>
                            <h5>{{ $battle->defenderCountry->name }}</h5>
                            @if($battle->defenderGeneral)
                            <p class="mb-1">
                                <strong>General:</strong> {{ $battle->defenderGeneral->name }}
                            </p>
                            <p class="mb-1">
                                <strong>Stats:</strong> A{{ $battle->defenderGeneral->attack }} D{{ $battle->defenderGeneral->defense }} S{{ $battle->defenderGeneral->speed }}
                            </p>
                            @endif
                            <h3 class="text-success mt-3">{{ number_format($battle->defender_soldiers) }}</h3>
                            <small class="text-muted">Initial Forces</small>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ (($battle->defender_soldiers - $battle->defender_soldiers_lost) / $battle->defender_soldiers) * 100 }}%"></div>
                            </div>
                            <p class="mt-2 text-success">
                                Lost: {{ number_format($battle->defender_soldiers_lost) }} ({{ round(($battle->defender_soldiers_lost / $battle->defender_soldiers) * 100) }}%)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Battle Details -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Battle Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <p><strong>Total Casualties:</strong><br>
                                           {{ number_format($battle->attacker_soldiers_lost + $battle->defender_soldiers_lost) }}</p>
                                        <p><strong>Territory Captured:</strong><br>
                                           {{ number_format($battle->territory_captured) }} km²</p>
                                    </div>
                                    <div class="col-6">
                                        <p><strong>Damage Modifier:</strong><br>
                                           x{{ number_format($battle->damage_modifier, 2) }}</p>
                                        <p><strong>Experience Gained:</strong><br>
                                           Attacker: +{{ $battle->attacker_experience_gain }}<br>
                                           Defender: +{{ $battle->defender_experience_gain }}</p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <p><strong>Special Conditions:</strong></p>
                                        <div class="d-flex gap-2">
                                            @if($battle->is_surprise_attack)
                                                <span class="badge bg-warning">Surprise Attack</span>
                                            @endif
                                            @if($battle->is_defense_prepared)
                                                <span class="badge bg-info">Prepared Defense</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Battle Timeline</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <h6>Battle Started</h6>
                                        <small class="text-muted">{{ $battle->started_at->format('H:i:s') }}</small>
                                        <p>Forces engaged at coordinates {{ $battle->battle_x }}, {{ $battle->battle_y }}</p>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <h6>Initial Clash</h6>
                                        <small class="text-muted">{{ $battle->started_at->addHours(1)->format('H:i') }}</small>
                                        <p>First casualties reported</p>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <h6>Battle Conclusion</h6>
                                        <small class="text-muted">{{ $battle->ended_at->format('H:i:s') }}</small>
                                        <p>{{ ucfirst($battle->result) }} declared after {{ $battle->duration_hours }} hours</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Visualization -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Battlefield Map</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="battlefield-map bg-light rounded p-3">
                                <canvas id="battlefieldCanvas" width="600" height="400" style="border: 1px solid #ddd;"></canvas>
                            </div>
                            <p class="text-muted mt-2">Strategic overview of the battle location</p>
                        </div>
                    </div>
                </div>

                <!-- Aftermath -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Aftermath</h5>
                    </div>
                    <div class="card-body">
                        @if($battle->result === 'attacker_win')
                        <div class="alert alert-success">
                            <h6><i class="fas fa-trophy"></i> Victory for {{ $battle->attackerCountry->name }}</h6>
                            <p class="mb-0">
                                The attacker successfully captured {{ number_format($battle->territory_captured) }} km² of territory 
                                from {{ $battle->defenderCountry->name }}.
                            </p>
                        </div>
                        @elseif($battle->result === 'defender_win')
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-shield-alt"></i> Successful Defense by {{ $battle->defenderCountry->name }}</h6>
                            <p class="mb-0">
                                The defender repelled the attack from {{ $battle->attackerCountry->name }} 
                                and maintained control of their territory.
                            </p>
                        </div>
                        @elseif($battle->result === 'draw')
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-handshake"></i> Stalemate</h6>
                            <p class="mb-0">
                                Both sides sustained heavy losses with no clear victor. The battle ended in a draw.
                            </p>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <h6><i class="fas fa-spinner"></i> Battle Ongoing</h6>
                            <p class="mb-0">
                                The battle is still in progress. Check back later for results.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('battlefieldCanvas');
        const ctx = canvas.getContext('2d');
        
        // Draw simple battlefield
        ctx.fillStyle = '#8B4513'; // Ground color
        ctx.fillRect(0, 0, 600, 400);
        
        // Draw attacker forces (red)
        ctx.fillStyle = 'rgba(220, 53, 69, 0.7)';
        for (let i = 0; i < 50; i++) {
            ctx.fillRect(Math.random() * 200 + 50, Math.random() * 300 + 50, 4, 4);
        }
        
        // Draw defender forces (green)
        ctx.fillStyle = 'rgba(40, 167, 69, 0.7)';
        for (let i = 0; i < 50; i++) {
            ctx.fillRect(Math.random() * 200 + 350, Math.random() * 300 + 50, 4, 4);
        }
        
        // Draw battle lines
        ctx.strokeStyle = '#000';
        ctx.setLineDash([5, 5]);
        ctx.beginPath();
        ctx.moveTo(300, 0);
        ctx.lineTo(300, 400);
        ctx.stroke();
    });
</script>
@endsection