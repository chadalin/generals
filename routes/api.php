<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// routes/api.php
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('games/{game}/map-data', function (Game $game) {
        return $game->countries->flatMap->territories->map(function($territory) {
            return [
                'id' => $territory->id,
                'country_id' => $territory->country_id,
                'name' => $territory->name,
                'x' => $territory->x,
                'y' => $territory->y,
                'population' => $territory->population,
                'resource_value' => $territory->resource_value,
                'is_capital' => $territory->is_capital
            ];
        });
    });
});