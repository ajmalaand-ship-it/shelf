<?php

use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\OwnerPreview\AppConfigController as OwnerPreviewAppConfigController;
use App\Http\Controllers\Api\OwnerPreview\CollectionController as OwnerPreviewCollectionController;
use App\Http\Controllers\Api\OwnerPreview\PoemController as OwnerPreviewPoemController;
use App\Http\Controllers\Api\PoemController;
use App\Http\Middleware\RequireOwnerPreviewToken;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/app-config', AppConfigController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/authors', [AuthorController::class, 'index']);
    Route::get('/authors/{author:slug}', [AuthorController::class, 'show']);
    Route::get('/authors/{author:slug}/image', [AuthorController::class, 'image'])->name('authors.image');
    Route::get('/collections', [CollectionController::class, 'index']);
    Route::get('/collections/{collection:slug}', [CollectionController::class, 'show']);
    Route::get('/collections/{collection:slug}/poems', [CollectionController::class, 'poems']);
    Route::get('/poems/{poem}', [PoemController::class, 'show']);
    Route::get('/poems/{poem}/audio', [PoemController::class, 'audio'])->name('poems.audio');
});

Route::prefix('owner-preview')
    ->middleware([RequireOwnerPreviewToken::class, 'throttle:30,1'])
    ->group(function (): void {
        Route::get('/app-config', OwnerPreviewAppConfigController::class);
        Route::get('/categories', [CategoryController::class, 'preview']);
        Route::get('/authors', [App\Http\Controllers\Api\OwnerPreview\AuthorController::class, 'index']);
        Route::get('/authors/{author:slug}', [App\Http\Controllers\Api\OwnerPreview\AuthorController::class, 'show']);
        Route::get('/collections', [OwnerPreviewCollectionController::class, 'index']);
        Route::get('/collections/{collection:slug}', [OwnerPreviewCollectionController::class, 'show']);
        Route::get('/collections/{collection:slug}/poems', [OwnerPreviewCollectionController::class, 'poems']);
        Route::get('/poems/{poem}', [OwnerPreviewPoemController::class, 'show']);
        Route::get('/poems/{poem}/audio', [OwnerPreviewPoemController::class, 'audio'])
            ->name('owner-preview.poems.audio');
    });
