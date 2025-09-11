<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'name', 'color', 'territory', 'population', 
        'x', 'y', 'neighbors', 'relations', 'is_alive'
    ];

    protected $casts = [
        'neighbors' => 'array',
        'relations' => 'array',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function player()
    {
        return $this->hasOne(Player::class);
    }

    public function generals()
    {
        return $this->hasMany(General::class);
    }

     public function battlesAsAttacker()
    {
        return $this->hasMany(Battle::class, 'attacker_country_id');
    }

    public function battlesAsDefender()
    {
        return $this->hasMany(Battle::class, 'defender_country_id');
    }

    public function getAllBattlesAttribute()
    {
        return $this->battlesAsAttacker->merge($this->battlesAsDefender);
    }

    public function getWinRateAttribute()
    {
        $totalBattles = $this->getAllBattlesAttribute()->count();
        if ($totalBattles === 0) {
            return 0;
        }

        $wins = $this->battlesAsAttacker->where('result', 'attacker_win')->count() +
                $this->battlesAsDefender->where('result', 'defender_win')->count();

        return ($wins / $totalBattles) * 100;
    }
}