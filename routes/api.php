<?php

use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\OwnerPreview\AppConfigController as OwnerPreviewAppConfigController;
use App\Http\Controllers\Api\OwnerPreview\CollectionController as OwnerPreviewCollectionController;
use App\Http\Controllers\Api\OwnerPreview\PoemController as OwnerPreviewPoemController;
use App\Http\Controllers\Api\PoemController;
use App\Http\Middleware\AccountsEnabled;
use App\Http\Middleware\PrivateAccountResponse;
use App\Http\Middleware\ReaderAccountAccess;
use App\Http\Middleware\RequireOwnerPreviewToken;
use Illuminate\Support\Facades\Route;

Route::post('purchases/webhook', App\Http\Controllers\PurchaseWebhookController::class)->middleware('throttle:120,1');
Route::get('purchases/config', [App\Http\Controllers\LibraryController::class, 'config'])->middleware([PrivateAccountResponse::class, 'throttle:60,1']);
Route::prefix('library')->middleware([PrivateAccountResponse::class, AccountsEnabled::class, 'auth:reader', ReaderAccountAccess::class, 'throttle:120,1'])->group(function (): void {
    Route::get('/', [App\Http\Controllers\LibraryController::class, 'index']);
    Route::post('confirm', [App\Http\Controllers\LibraryController::class, 'index']);
    Route::post('restore', [App\Http\Controllers\LibraryController::class, 'index']);
    Route::post('books/{collection}/consent', [App\Http\Controllers\LibraryController::class, 'consent']);
    Route::get('books/{collection:slug}', [App\Http\Controllers\LibraryController::class, 'book']);
    Route::get('books/{collection:slug}/content', [App\Http\Controllers\LibraryController::class, 'content']);
    Route::get('books/{collection}/cover', [App\Http\Controllers\LibraryController::class, 'cover']);
    Route::get('poems/{poem}', [App\Http\Controllers\LibraryController::class, 'poem']);
    Route::get('poems/{poem}/audio', [App\Http\Controllers\LibraryController::class, 'audio']);
    Route::get('poems/{poem}/media/{kind}', [App\Http\Controllers\LibraryController::class, 'media'])->name('library.media');
});

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

Route::prefix('auth')->middleware([PrivateAccountResponse::class])->group(function (): void {
    Route::get('config', [AuthController::class, 'config'])->middleware('throttle:60,1');
    Route::middleware([AccountsEnabled::class, 'throttle:reader-auth'])->group(function (): void {
        foreach (['register', 'login', 'google', 'forgot' => 'forgot-password'] as $method => $path) {
            $method = is_int($method) ? $path : $method;
            Route::post($path, [AuthController::class, $method]);
        }
        Route::middleware(['auth:reader', ReaderAccountAccess::class])->group(function (): void {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('verify/resend', [AuthController::class, 'resend']);
            Route::post('password', [AuthController::class, 'changePassword']);
            Route::post('delete-request', [AuthController::class, 'deleteRequest']);
        });
    });
});
