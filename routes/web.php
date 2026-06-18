<?php

use Corals\Modules\Sorteos\Http\Controllers\PublicSorteoController;
use Corals\Settings\Facades\Modules;
use Illuminate\Support\Facades\Route;

// Root: public sorteo listing, bypassing the CMS dashboard fallback.
Route::get('/', [PublicSorteoController::class, 'index'])->name('home');

Route::namespace('\Corals\Foundation\Http\Controllers')
    ->controller('PublicBaseController')->group(function () {
        if (!Modules::isModuleActive('corals-cms')) {
            Route::get('/welcome', 'welcome');
        }
    });
