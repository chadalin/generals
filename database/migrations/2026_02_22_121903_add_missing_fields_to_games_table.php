<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // Добавляем недостающие поля
            if (!Schema::hasColumn('games', 'map_width')) {
                $table->integer('map_width')->nullable()->after('map_size');
            }
            if (!Schema::hasColumn('games', 'map_height')) {
                $table->integer('map_height')->nullable()->after('map_width');
            }
            if (!Schema::hasColumn('games', 'ai_difficulty')) {
                $table->integer('ai_difficulty')->default(2)->after('turn_duration');
            }
            if (!Schema::hasColumn('games', 'start_year')) {
                $table->integer('start_year')->default(1950)->after('ai_difficulty');
            }
            if (!Schema::hasColumn('games', 'random_countries')) {
                $table->boolean('random_countries')->default(true)->after('fog_of_war');
            }
            if (!Schema::hasColumn('games', 'fog_of_war')) {
                $table->boolean('fog_of_war')->default(true)->after('random_countries');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'map_width',
                'map_height',
                'ai_difficulty',
                'start_year',
                'random_countries',
                'fog_of_war'
            ]);
        });
    }
};