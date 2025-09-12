<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('countries', function (Blueprint $table) {
        $table->foreignId('player_id')->nullable()->after('game_id')
              ->constrained()->onDelete('set null');
    });
}

public function down()
{
    Schema::table('countries', function (Blueprint $table) {
        $table->dropForeign(['player_id']);
        $table->dropColumn('player_id');
    });
}
};
