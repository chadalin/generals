<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTerritoriesTable extends Migration
{
    public function up()
    {
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->string('type')->default('land'); // land, water, mountain, etc.
            $table->integer('resources')->default(0);
            $table->boolean('is_border')->default(false);
            $table->json('neighbors')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('territories');
    }
}