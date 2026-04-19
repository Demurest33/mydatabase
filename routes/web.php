<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnilistController;
use App\Http\Controllers\Neo4jController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\AssetController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/anilist', [AnilistController::class, 'index'])->name('anilist.index');

Route::get('/neo4j', [Neo4jController::class, 'index'])->name('neo4j.index');
Route::get('/neo4j/data', [Neo4jController::class, 'graphData'])->name('neo4j.data');

Route::get('/characters', [CharacterController::class, 'index'])->name('characters.index');
Route::get('/characters/{id}', [CharacterController::class, 'show'])->name('characters.show');
Route::post('/characters/{id}/assets', [CharacterController::class, 'storeAsset'])->name('characters.assets.store');

Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');