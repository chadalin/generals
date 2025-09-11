<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class General extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id', 'country_id', 'name', 'speed', 'attack', 
        'defense', 'experience', 'soldiers_count', 'order', 
        'target_country_id', 'cost', 'age', 'is_alive'
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function targetCountry()
    {
        return $this->belongsTo(Country::class, 'target_country_id');
    }
}