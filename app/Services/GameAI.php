<?php

namespace App\Services;

use App\Models\Player;
use App\Models\General;

class GameAI
{
    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function executeTurn()
    {
        $this->allocateResources();
        $this->manageGenerals();
        $this->conductResearch();
        $this->handleDiplomacy();
    }

    private function allocateResources()
    {
        // Simple resource allocation logic
        $totalPopulation = $this->player->peasants + $this->player->scientists + $this->player->soldiers;
        
        $scientists = min($totalPopulation * 0.2, 100);
        $soldiers = min($totalPopulation * 0.3, 200);
        $peasants = $totalPopulation - $scientists - $soldiers;

        $this->player->update([
            'scientists' => $scientists,
            'soldiers' => $soldiers,
            'peasants' => $peasants,
        ]);
    }

    private function manageGenerals()
    {
        $generals = $this->player->generals;

        if ($generals->isEmpty() && $this->player->money >= 1000) {
            // Hire a general if none exist
            $this->hireGeneral();
        }

        foreach ($generals as $general) {
            $this->assignGeneralOrder($general);
        }
    }

    private function hireGeneral()
    {
        $names = ['Alexander', 'Caesar', 'Napoleon', 'Patton', 'Zhukov', 'Rommel'];
        
        General::create([
            'player_id' => $this->player->id,
            'country_id' => $this->player->country_id,
            'name' => $names[array_rand($names)],
            'speed' => rand(40, 80),
            'attack' => rand(40, 80),
            'defense' => rand(40, 80),
            'cost' => 1000,
        ]);

        $this->player->decrement('money', 1000);
    }

    private function assignGeneralOrder(General $general)
    {
        // Simple AI logic for general orders
        $neighbors = $this->player->country->neighbors ?? [];

        if (!empty($neighbors) && $this->player->soldiers > 50) {
            // Attack a random neighbor
            $target = $neighbors[array_rand($neighbors)];
            
            $general->update([
                'order' => 'attack',
                'target_country_id' => $target,
                'soldiers_count' => min($this->player->soldiers, 50),
            ]);
        } else {
            // Train or rest
            $general->update([
                'order' => rand(0, 1) ? 'train' : 'rest',
                'soldiers_count' => 0,
            ]);
        }
    }

    private function conductResearch()
    {
        // Focus on military research
        $this->player->increment('research_military', $this->player->scientists * 0.1);
    }

    private function handleDiplomacy()
    {
        // Simple diplomacy logic
        // AI players are generally aggressive
    }
}