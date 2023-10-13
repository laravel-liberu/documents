<?php

use Illuminate\Support\Facades\Route;
use LaravelLiberu\Documents\Http\Controllers\Destroy;
use LaravelLiberu\Documents\Http\Controllers\Index;
use LaravelLiberu\Documents\Http\Controllers\Store;

Route::middleware(['api', 'auth', 'core'])
    ->prefix('api/core/documents')
    ->as('core.documents.')
    ->group(function () {
        Route::get('', Index::class)->name('index');
        Route::post('', Store::class)->name('store');
        Route::delete('{document}', Destroy::class)->name('destroy');
    });
