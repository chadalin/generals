<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'attacker_country_id', 'defender_country_id',
        'attacker_general_id', 'defender_general_id', 'attacker_soldiers',
        'defender_soldiers', 'attacker_soldiers_lost', 'defender_soldiers_lost',
        'territory_captured', 'duration_hours', 'result', 'damage_modifier',
        'battle_x', 'battle_y', 'attacker_experience_gain', 'defender_experience_gain',
        'is_surprise_attack', 'is_defense_prepared', 'started_at', 'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'damage_modifier' => 'decimal:2',
        'is_surprise_attack' => 'boolean',
        'is_defense_prepared' => 'boolean',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function attackerCountry()
    {
        return $this->belongsTo(Country::class, 'attacker_country_id');
    }

    public function defenderCountry()
    {
        return $this->belongsTo(Country::class, 'defender_country_id');
    }

    public function attackerGeneral()
    {
        return $this->belongsTo(General::class, 'attacker_general_id');
    }

    public function defenderGeneral()
    {
        return $this->belongsTo(General::class, 'defender_general_id');
    }

    public function getTotalCasualtiesAttribute()
    {
        return $this->attacker_soldiers_lost + $this->defender_soldiers_lost;
    }

    public function isFinished()
    {
        return $this->result !== 'ongoing';
    }

    public function getVictorAttribute()
    {
        if ($this->result === 'attacker_win') {
            return $this->attackerCountry;
        } elseif ($this->result === 'defender_win') {
            return $this->defenderCountry;
        }
        return null;
    }
}