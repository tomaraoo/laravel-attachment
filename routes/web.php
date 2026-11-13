<?php

use Illuminate\Support\Facades\Route;
use Rft\LaravelAttachment\Http\Controllers\AttachmentController;

$prefix = config('laravel-attachment.routes.prefix', 'attachments');
$middleware = config('laravel-attachment.routes.middleware', ['web']);

Route::group([
    'prefix' => $prefix,
    'middleware' => $middleware,
    'as' => 'rft.attachments.',
], function () {
    Route::get('/demo', [AttachmentController::class, 'demo'])->name('demo');
    Route::post('/demo/test-update/{file_url}', [AttachmentController::class, 'testImmutability'])
        ->where('file_url', '[A-Za-z0-9\-_]+')
        ->name('test-update');

    Route::post('/upload', [AttachmentController::class, 'store'])->name('store');
    Route::get('/{file_url}', [AttachmentController::class, 'show'])
        ->where('file_url', '[A-Za-z0-9\-_]+')
        ->name('show');
    Route::delete('/{file_url}', [AttachmentController::class, 'destroy'])
        ->where('file_url', '[A-Za-z0-9\-_]+')
        ->name('destroy');
});
