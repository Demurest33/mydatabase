<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnilistController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/anilist', [AnilistController::class, 'index'])->name('anilist.index');
