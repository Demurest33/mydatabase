<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnilistController;
use App\Http\Controllers\Neo4jController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\FranchiseController;
use App\Http\Controllers\WouldYouRatherController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', [AssetController::class, 'index'])->name('home');

// Auth Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/anilist', [AnilistController::class, 'index'])->name('anilist.index');
Route::get('/would-you-rather', [WouldYouRatherController::class, 'index'])->name('wyr.index');
Route::post('/would-you-rather/fetch', [WouldYouRatherController::class, 'fetch'])->name('wyr.fetch');
Route::get('/would-you-rather/game', [WouldYouRatherController::class, 'game'])->name('wyr.game');
Route::get('/would-you-rather/progress/{batchId}', [WouldYouRatherController::class, 'progress'])->name('wyr.progress');

Route::get('/api/media/search', [Neo4jController::class, 'searchMediaJson'])->name('api.media.search');

Route::get('/media/{id}', [\App\Http\Controllers\MediaController::class, 'show'])->name('media.show');

Route::get('/characters', [CharacterController::class, 'index'])->name('characters.index');
Route::get('/api/characters/search', [CharacterController::class, 'searchJson'])->name('api.characters.search');
Route::get('/characters/{id}', [CharacterController::class, 'show'])->name('characters.show');
Route::post('/characters/{id}/assets', [CharacterController::class, 'storeAsset'])->name('characters.assets.store');

Route::get('/franchises', [FranchiseController::class, 'index'])->name('franchises.index');
Route::get('/franchises/{name}', [FranchiseController::class, 'show'])->name('franchises.show')
    ->where('name', '.+'); // allow spaces and special chars

// Backoffice
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/assets', [\App\Http\Controllers\Admin\AssetCrudController::class, 'index'])->name('admin.assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{id}/edit', [\App\Http\Controllers\Admin\AssetCrudController::class, 'edit'])->name('admin.assets.edit');
    Route::put('/assets/{id}', [\App\Http\Controllers\Admin\AssetCrudController::class, 'update'])->name('admin.assets.update');
    Route::delete('/assets/{id}', [\App\Http\Controllers\Admin\AssetCrudController::class, 'destroy'])->name('admin.assets.destroy');

    Route::resource('franchises', \App\Http\Controllers\Admin\FranchiseCrudController::class)->names('admin.franchises');
    
    Route::resource('media', \App\Http\Controllers\Admin\MediaCrudController::class)->names('admin.media');

    Route::resource('characters', \App\Http\Controllers\Admin\CharacterCrudController::class)->names('admin.characters');
});