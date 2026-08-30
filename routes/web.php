<?php

use App\Http\Controllers\Api\PoemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'Pashto Poetry API', 'status' => 'ok']);
});

Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/media/audio/{poem}', [PoemController::class, 'stream'])
    ->middleware('signed')
    ->name('poems.audio.stream');

Route::get('/media/owner-preview/audio/{poem}', [PoemController::class, 'streamOwnerPreview'])
    ->middleware('signed')
    ->name('owner-preview.poems.audio.stream');
