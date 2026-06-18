<?php

use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Settings\Facades\Modules;
use Illuminate\Support\Facades\Route;

// Send root URL to the active sorteo's public page, bypassing the CMS.
Route::get('/', function () {
    $sorteo = Sorteo::where('status', 'active')->first();
    if ($sorteo) {
        return redirect()->route('sorteos.public.show', $sorteo->slug);
    }
    return redirect()->route('login');
})->name('home');

Route::namespace('\Corals\Foundation\Http\Controllers')
    ->controller('PublicBaseController')->group(function () {
        if (!Modules::isModuleActive('corals-cms')) {
            Route::get('/welcome', 'welcome');
        }
    });
