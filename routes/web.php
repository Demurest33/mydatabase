<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnilistController;
use App\Http\Controllers\Neo4jController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/anilist', [AnilistController::class, 'index'])->name('anilist.index');

Route::get('/neo4j', [Neo4jController::class, 'index'])->name('neo4j.index');
Route::get('/neo4j/data', [Neo4jController::class, 'graphData'])->name('neo4j.data');
