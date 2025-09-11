<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Auth::routes();

// Или явно указать все маршруты если Auth::routes() не работает
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Redirect root to games index
Route::get('/', function () {
    return redirect()->route('games.index');
});

// Game Routes
Route::middleware('auth')->group(function () {
    Route::resource('games', GameController::class);
    Route::post('games/{game}/start', [GameController::class, 'start'])->name('games.start');
    Route::get('games/{game}/play', [GameController::class, 'play'])->name('games.play');
    Route::post('games/{game}/process-turn', [GameController::class, 'processTurn'])->name('games.process-turn');
    
    // General Routes
    Route::post('games/{game}/generals/hire', [GeneralController::class, 'hire'])->name('generals.hire');
    Route::post('generals/{general}/order', [GeneralController::class, 'updateOrder'])->name('generals.order');
});

// API Routes for real-time updates
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('games/{game}/status', [GameController::class, 'status']);
    Route::get('games/{game}/players', [GameController::class, 'players']);
});


// Map Routes
Route::middleware('auth')->prefix('maps')->group(function () {
    Route::get('game/{game}', [MapController::class, 'show'])->name('maps.show');
    Route::get('game/{game}/territory/{x}/{y}', [MapController::class, 'territoryInfo'])->name('maps.territory.info');
    Route::post('game/{game}/update', [MapController::class, 'updateMap'])->name('maps.update');
    Route::post('game/{game}/generate', [MapController::class, 'generateMap'])->name('maps.generate');
});


// Player Routes
Route::middleware('auth')->prefix('players')->group(function () {
    Route::get('{player}', [PlayerController::class, 'show'])->name('players.show');
    Route::post('{player}/resources', [PlayerController::class, 'updateResources'])->name('players.update-resources');
    Route::post('{player}/research', [PlayerController::class, 'allocateResearch'])->name('players.allocate-research');
    Route::post('{player}/recruit-soldiers', [PlayerController::class, 'recruitSoldiers'])->name('players.recruit-soldiers');
    Route::post('{player}/train-scientists', [PlayerController::class, 'trainScientists'])->name('players.train-scientists');
    Route::get('{player}/statistics', [PlayerController::class, 'getStatistics'])->name('players.statistics');
});

// Country Routes
Route::middleware('auth')->prefix('countries')->group(function () {
    Route::get('{country}', [CountryController::class, 'show'])->name('countries.show');
    Route::get('{country}/territories', [CountryController::class, 'getTerritories'])->name('countries.territories');
    Route::get('{country}/neighbors', [CountryController::class, 'getNeighbors'])->name('countries.neighbors');
    Route::get('{country}/relations', [CountryController::class, 'getRelations'])->name('countries.relations');
    Route::post('{country}/relations/{targetCountry}', [CountryController::class, 'updateRelation'])->name('countries.update-relation');
    Route::get('{country}/statistics', [CountryController::class, 'getStatistics'])->name('countries.statistics');
    Route::post('{country}/expand', [CountryController::class, 'expandTerritory'])->name('countries.expand');
    Route::post('{country}/prepare-defense', [CountryController::class, 'prepareDefense'])->name('countries.prepare-defense');
});

// General Routes
Route::middleware('auth')->prefix('generals')->group(function () {
    Route::get('{general}', [GeneralController::class, 'show'])->name('generals.show');
    Route::post('hire/{game}', [GeneralController::class, 'hire'])->name('generals.hire');
    Route::post('{general}/order', [GeneralController::class, 'updateOrder'])->name('generals.update-order');
    Route::post('{general}/train', [GeneralController::class, 'train'])->name('generals.train');
    Route::delete('{general}/dismiss', [GeneralController::class, 'dismiss'])->name('generals.dismiss');
    Route::get('{general}/battle-history', [GeneralController::class, 'getBattleHistory'])->name('generals.battle-history');
    Route::post('{general}/promote', [GeneralController::class, 'promote'])->name('generals.promote');
});

// Battle Routes
Route::middleware('auth')->prefix('battles')->group(function () {
    Route::get('/', [BattleController::class, 'index'])->name('battles.index');
    Route::get('{battle}', [BattleController::class, 'show'])->name('battles.show');
    Route::post('initiate/{game}', [BattleController::class, 'initiateBattle'])->name('battles.initiate');
    Route::post('{battle}/resolve', [BattleController::class, 'resolveBattle'])->name('battles.resolve');
    Route::get('{battle}/log', [BattleController::class, 'getBattleLog'])->name('battles.log');
    Route::get('game/{game}', [BattleController::class, 'getGameBattles'])->name('battles.game-battles');
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
