<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->string('username');
            $table->integer('money')->default(10000);
            $table->integer('grain')->default(5000);
            $table->integer('scientists')->default(0);
            $table->integer('soldiers')->default(0);
            $table->integer('peasants')->default(1000);
            $table->integer('research_military')->default(0);
            $table->integer('research_economy')->default(0);
            $table->integer('research_science')->default(0);
            $table->boolean('is_ready')->default(false);
            $table->boolean('is_ai')->default(false);
            $table->integer('ai_difficulty')->default(2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('players');
    }
}