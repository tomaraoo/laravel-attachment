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
    Route::get('/{file_url}', [AttachmentController::class, 'show'])
        ->where('file_url', '[A-Za-z0-9\-_]+')
        ->name('show');
});
