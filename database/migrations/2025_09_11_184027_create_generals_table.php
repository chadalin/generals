<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralsTable extends Migration
{
    public function up()
    {
        Schema::create('generals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('speed')->default(50);
            $table->integer('attack')->default(50);
            $table->integer('defense')->default(50);
            $table->integer('experience')->default(0);
            $table->integer('soldiers_count')->default(0);
            $table->string('order')->default('rest');
            $table->integer('target_country_id')->nullable();
            $table->integer('cost')->default(1000);
            $table->integer('age')->default(30);
            $table->boolean('is_alive')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('generals');
    }
}