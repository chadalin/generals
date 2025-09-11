<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Research extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id', 'game_id', 'military_level', 'military_progress',
        'military_weapons', 'military_armor', 'military_tactics',
        'military_logistics', 'military_siege', 'economy_level',
        'economy_progress', 'economy_agriculture', 'economy_trade',
        'economy_taxation', 'economy_infrastructure', 'economy_industry',
        'science_level', 'science_progress', 'science_education',
        'science_medicine', 'science_engineering', 'science_mathematics',
        'science_philosophy', 'tech_cavalry', 'tech_navy', 'tech_artillery',
        'tech_espionage', 'tech_diplomacy', 'tech_medicine', 'attack_bonus',
        'defense_bonus', 'economy_bonus', 'research_bonus', 'population_growth_bonus',
        'last_research_at', 'research_speed'
    ];

    protected $casts = [
        'military_weapons' => 'boolean',
        'military_armor' => 'boolean',
        'military_tactics' => 'boolean',
        'military_logistics' => 'boolean',
        'military_siege' => 'boolean',
        'economy_agriculture' => 'boolean',
        'economy_trade' => 'boolean',
        'economy_taxation' => 'boolean',
        'economy_infrastructure' => 'boolean',
        'economy_industry' => 'boolean',
        'science_education' => 'boolean',
        'science_medicine' => 'boolean',
        'science_engineering' => 'boolean',
        'science_mathematics' => 'boolean',
        'science_philosophy' => 'boolean',
        'tech_cavalry' => 'boolean',
        'tech_navy' => 'boolean',
        'tech_artillery' => 'boolean',
        'tech_espionage' => 'boolean',
        'tech_diplomacy' => 'boolean',
        'tech_medicine' => 'boolean',
        'attack_bonus' => 'decimal:2',
        'defense_bonus' => 'decimal:2',
        'economy_bonus' => 'decimal:2',
        'research_bonus' => 'decimal:2',
        'population_growth_bonus' => 'decimal:2',
        'last_research_at' => 'datetime',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function getMilitaryTechnologiesAttribute()
    {
        return [
            'weapons' => $this->military_weapons,
            'armor' => $this->military_armor,
            'tactics' => $this->military_tactics,
            'logistics' => $this->military_logistics,
            'siege' => $this->military_siege,
        ];
    }

    public function getEconomyTechnologiesAttribute()
    {
        return [
            'agriculture' => $this->economy_agriculture,
            'trade' => $this->economy_trade,
            'taxation' => $this->economy_taxation,
            'infrastructure' => $this->economy_infrastructure,
            'industry' => $this->economy_industry,
        ];
    }

    public function getScienceTechnologiesAttribute()
    {
        return [
            'education' => $this->science_education,
            'medicine' => $this->science_medicine,
            'engineering' => $this->science_engineering,
            'mathematics' => $this->science_mathematics,
            'philosophy' => $this->science_philosophy,
        ];
    }

    public function getSpecialTechnologiesAttribute()
    {
        return [
            'cavalry' => $this->tech_cavalry,
            'navy' => $this->tech_navy,
            'artillery' => $this->tech_artillery,
            'espionage' => $this->tech_espionage,
            'diplomacy' => $this->tech_diplomacy,
            'medicine' => $this->tech_medicine,
        ];
    }

    public function calculateResearchPoints()
    {
        $basePoints = $this->player->scientists * 0.1;
        $bonusPoints = $basePoints * ($this->research_bonus - 1);
        return $basePoints + $bonusPoints;
    }
}