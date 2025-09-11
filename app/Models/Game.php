<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'max_players', 'current_players', 'map_size', 
        'year', 'turn_duration', 'is_private', 'password', 'status', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function countries()
    {
        return $this->hasMany(Country::class);
    }

    public function humanPlayers()
    {
        return $this->players()->where('is_ai', false);
    }

    public function aiPlayers()
    {
        return $this->players()->where('is_ai', true);
    }


    public function territories()
    {
        return $this->hasMany(Territory::class);
    }

    public function generateMap()
    {
        $mapSize = $this->map_size;
        $countries = $this->countries;
        
        // Clear existing territories
        $this->territories()->delete();

        // Generate base map
        $territories = [];
        for ($x = 0; $x < $mapSize; $x++) {
            for ($y = 0; $y < $mapSize; $y++) {
                $territories[] = [
                    'game_id' => $this->id,
                    'x' => $x,
                    'y' => $y,
                    'type' => $this->getTerritoryType($x, $y, $mapSize),
                    'resources' => rand(0, 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Bulk insert
        Territory::insert($territories);

        // Assign territories to countries
        $this->assignTerritoriesToCountries();
    }

    private function getTerritoryType($x, $y, $size)
    {
        $distanceFromCenter = sqrt(pow($x - $size/2, 2) + pow($y - $size/2, 2));
        
        if ($distanceFromCenter < $size * 0.4) {
            return 'land';
        } elseif ($distanceFromCenter < $size * 0.45) {
            return 'coast';
        } else {
            return 'water';
        }
    }

    private function assignTerritoriesToCountries()
    {
        $countries = $this->countries;
        $territories = $this->territories()->where('type', 'land')->get();
        
        foreach ($countries as $country) {
            $centerX = $country->x;
            $centerY = $country->y;
            $radius = 5; // Initial territory radius
            
            // Get territories within radius
            $nearbyTerritories = $territories->filter(function($territory) use ($centerX, $centerY, $radius) {
                $distance = sqrt(pow($territory->x - $centerX, 2) + pow($territory->y - $centerY, 2));
                return $distance <= $radius;
            });

            // Assign to country
            Territory::whereIn('id', $nearbyTerritories->pluck('id'))
                    ->update(['country_id' => $country->id]);
        }
    }
}