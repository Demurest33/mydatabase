<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnilistController;
use App\Http\Controllers\Neo4jController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\FranchiseController;
use App\Http\Controllers\WouldYouRatherController;

Route::get('/', [AssetController::class, 'index'])->name('home');

Route::get('/anilist', [AnilistController::class, 'index'])->name('anilist.index');
Route::get('/would-you-rather', [WouldYouRatherController::class, 'index'])->name('wyr.index');
Route::post('/would-you-rather/fetch', [WouldYouRatherController::class, 'fetch'])->name('wyr.fetch');
Route::get('/would-you-rather/game', [WouldYouRatherController::class, 'game'])->name('wyr.game');
Route::get('/would-you-rather/progress/{batchId}', [WouldYouRatherController::class, 'progress'])->name('wyr.progress');

Route::get('/neo4j', [Neo4jController::class, 'index'])->name('neo4j.index');
Route::get('/neo4j/data', [Neo4jController::class, 'graphData'])->name('neo4j.data');

Route::get('/characters', [CharacterController::class, 'index'])->name('characters.index');
Route::get('/api/characters/search', [CharacterController::class, 'searchJson'])->name('api.characters.search');
Route::get('/characters/{id}', [CharacterController::class, 'show'])->name('characters.show');
Route::post('/characters/{id}/assets', [CharacterController::class, 'storeAsset'])->name('characters.assets.store');

Route::get('/franchises', [FranchiseController::class, 'index'])->name('franchises.index');

Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');