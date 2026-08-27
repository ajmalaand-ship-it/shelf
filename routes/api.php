<?php

use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\PoemController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/app-config', AppConfigController::class);
    Route::get('/collections', [CollectionController::class, 'index']);
    Route::get('/collections/{collection:slug}', [CollectionController::class, 'show']);
    Route::get('/collections/{collection:slug}/poems', [CollectionController::class, 'poems']);
    Route::get('/poems/{poem}', [PoemController::class, 'show']);
    Route::get('/poems/{poem}/audio', [PoemController::class, 'audio'])->name('poems.audio');
});
