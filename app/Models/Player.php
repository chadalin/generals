<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'user_id', 'country_id', 'username', 'money', 'grain',
        'scientists', 'soldiers', 'peasants', 'research_military', 
        'research_economy', 'research_science', 'is_ready', 'is_ai', 'ai_difficulty'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function generals()
    {
        return $this->hasMany(General::class);
    }

     public function research()
    {
        return $this->hasOne(Research::class);
    }

    public function battlesAsAttacker()
    {
        return $this->hasManyThrough(
            Battle::class,
            Country::class,
            'player_id', // Foreign key on countries table
            'attacker_country_id', // Foreign key on battles table
            'id', // Local key on players table
            'id' // Local key on countries table
        );
    }

    public function battlesAsDefender()
    {
        return $this->hasManyThrough(
            Battle::class,
            Country::class,
            'player_id', // Foreign key on countries table
            'defender_country_id', // Foreign key on battles table
            'id', // Local key on players table
            'id' // Local key on countries table
        );
    }

    public function getAllBattlesAttribute()
    {
        return $this->battlesAsAttacker->merge($this->battlesAsDefender);
    }
}