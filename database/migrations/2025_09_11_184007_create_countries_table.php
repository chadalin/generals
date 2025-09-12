<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#cccccc');
            $table->integer('territory')->default(100);
            $table->integer('population')->default(1000);
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->json('neighbors')->nullable();
            $table->json('relations')->nullable();
            $table->boolean('is_alive')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
}