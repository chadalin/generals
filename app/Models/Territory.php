<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Territory extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'country_id', 'x', 'y', 'type', 'resources', 'is_border', 'neighbors'
    ];

    protected $casts = [
        'neighbors' => 'array',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function getColorAttribute()
    {
        if (!$this->country_id) {
            return '#CCCCCC'; // Neutral territory
        }
        return $this->country->color;
    }
}