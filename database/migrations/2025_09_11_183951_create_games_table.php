<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('max_players')->default(20);
            $table->integer('current_players')->default(0);
            $table->integer('map_size')->default(100);
            $table->integer('year')->default(1950);
            $table->integer('turn_duration')->default(3);
            $table->boolean('is_private')->default(false);
            $table->string('password')->nullable();
            $table->string('status')->default('waiting');
            $table->text('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('games');
    }
}