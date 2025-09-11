<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class GenerateGameMap extends Command
{
    protected $signature = 'game:generate-map {gameId}';
    protected $description = 'Generate map for a specific game';

    public function handle()
    {
        $gameId = $this->argument('gameId');
        $game = Game::find($gameId);

        if (!$game) {
            $this->error('Game not found');
            return;
        }

        $game->generateMap();
        $this->info("Map generated for game: {$game->name}");
    }
}