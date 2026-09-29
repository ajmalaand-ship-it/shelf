<?php

use App\Http\Controllers\Account\EmailActionController;
use App\Http\Controllers\Api\PoemController;
use App\Http\Controllers\CoverController;
use App\Http\Middleware\AccountsEnabled;
use App\Http\Middleware\PrivateAccountResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'Shelf API', 'status' => 'ok']);
});

Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/media/audio/{poem}', [PoemController::class, 'stream'])
    ->middleware('signed')
    ->name('poems.audio.stream');

Route::get('/media/owner-preview/audio/{poem}', [PoemController::class, 'streamOwnerPreview'])
    ->middleware('signed')
    ->name('owner-preview.poems.audio.stream');

Route::get('/media/artwork/{poem}', [PoemController::class, 'streamArtwork'])
    ->middleware('signed')
    ->name('poems.artwork.stream');

Route::get('/media/owner-preview/artwork/{poem}', [PoemController::class, 'streamOwnerPreviewArtwork'])
    ->middleware('signed')
    ->name('owner-preview.poems.artwork.stream');

Route::get('/media/covers/{collection}', [CoverController::class, 'show'])->name('books.cover');
Route::get('/media/owner-preview/covers/{collection}', [CoverController::class, 'preview'])->middleware('signed')->name('owner-preview.books.cover');

Route::prefix('account')->middleware([PrivateAccountResponse::class])->group(function (): void {
    Route::view('delete', 'account.delete')->name('account.delete');
    Route::post('delete/request', [EmailActionController::class, 'deletionRequest'])
        ->middleware([AccountsEnabled::class, 'throttle:reader-auth']);
    Route::get('delete/confirm', [EmailActionController::class, 'page'])->defaults('purpose', 'delete');
    Route::get('{purpose}', [EmailActionController::class, 'page'])->whereIn('purpose', ['verify', 'reset', 'google']);
    Route::post('{purpose}', [EmailActionController::class, 'consume'])->whereIn('purpose', ['verify', 'reset', 'google', 'delete'])
        ->middleware([AccountsEnabled::class, 'throttle:reader-auth']);
});
