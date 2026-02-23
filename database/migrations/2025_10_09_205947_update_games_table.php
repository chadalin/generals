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
    Schema::table('games', function (Blueprint $table) {
        $table->integer('current_year')->default(1);
       // $table->string('status')->default('active');
        $table->integer('map_width')->default(20);
        $table->integer('map_height')->default(20);
        $table->json('map_data')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
