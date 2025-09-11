<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattlesTable extends Migration
{
    public function up()
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('attacker_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('defender_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('attacker_general_id')->nullable()->constrained('generals')->onDelete('set null');
            $table->foreignId('defender_general_id')->nullable()->constrained('generals')->onDelete('set null');
            
            // Силы сторон
            $table->integer('attacker_soldiers')->default(0);
            $table->integer('defender_soldiers')->default(0);
            $table->integer('attacker_soldiers_lost')->default(0);
            $table->integer('defender_soldiers_lost')->default(0);
            
            // Исход битвы
            $table->integer('territory_captured')->default(0);
            $table->integer('duration_hours')->default(0); // Длительность в часах (ходах)
            $table->string('result')->default('ongoing'); // ongoing, attacker_win, defender_win, draw
            $table->decimal('damage_modifier', 5, 2)->default(1.0); // Модификатор урона
            
            // Координаты битвы
            $table->integer('battle_x')->nullable();
            $table->integer('battle_y')->nullable();
            
            // Статистика
            $table->integer('attacker_experience_gain')->default(0);
            $table->integer('defender_experience_gain')->default(0);
            $table->boolean('is_surprise_attack')->default(false);
            $table->boolean('is_defense_prepared')->default(false);
            
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['game_id', 'result']);
            $table->index(['attacker_country_id', 'defender_country_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('battles');
    }
}