<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResearchTable extends Migration
{
    public function up()
    {
        Schema::create('research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            
            // Военные исследования
            $table->integer('military_level')->default(0);
            $table->integer('military_progress')->default(0);
            $table->boolean('military_weapons')->default(false);
            $table->boolean('military_armor')->default(false);
            $table->boolean('military_tactics')->default(false);
            $table->boolean('military_logistics')->default(false);
            $table->boolean('military_siege')->default(false);
            
            // Экономические исследования
            $table->integer('economy_level')->default(0);
            $table->integer('economy_progress')->default(0);
            $table->boolean('economy_agriculture')->default(false);
            $table->boolean('economy_trade')->default(false);
            $table->boolean('economy_taxation')->default(false);
            $table->boolean('economy_infrastructure')->default(false);
            $table->boolean('economy_industry')->default(false);
            
            // Научные исследования
            $table->integer('science_level')->default(0);
            $table->integer('science_progress')->default(0);
            $table->boolean('science_education')->default(false);
            $table->boolean('science_medicine')->default(false);
            $table->boolean('science_engineering')->default(false);
            $table->boolean('science_mathematics')->default(false);
            $table->boolean('science_philosophy')->default(false);
            
            // Специальные технологии
            $table->boolean('tech_cavalry')->default(false);
            $table->boolean('tech_navy')->default(false);
            $table->boolean('tech_artillery')->default(false);
            $table->boolean('tech_espionage')->default(false);
            $table->boolean('tech_diplomacy')->default(false);
            $table->boolean('tech_medicine')->default(false);
            
            // Бонусы от исследований
            $table->decimal('attack_bonus', 5, 2)->default(1.0);
            $table->decimal('defense_bonus', 5, 2)->default(1.0);
            $table->decimal('economy_bonus', 5, 2)->default(1.0);
            $table->decimal('research_bonus', 5, 2)->default(1.0);
            $table->decimal('population_growth_bonus', 5, 2)->default(1.0);
            
            // Временные метки исследований
            $table->timestamp('last_research_at')->nullable();
            $table->integer('research_speed')->default(1);
            
            $table->timestamps();
            
            // Уникальный индекс для предотвращения дублирования
            $table->unique(['player_id', 'game_id']);
            
            // Индексы для быстрого поиска
            $table->index(['game_id', 'military_level']);
            $table->index(['game_id', 'economy_level']);
            $table->index(['game_id', 'science_level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('research');
    }
}