<?php

namespace Corals\Modules\Sorteos\Providers;

use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Observers\CarteraObserver;
use Corals\Modules\Sorteos\Observers\SorteoObserver;
use Illuminate\Support\ServiceProvider;

class SorteosObserverServiceProvider extends ServiceProvider
{
    /**
     * Register Observers
     */
    public function boot()
    {
        Sorteo::observe(SorteoObserver::class);
        Cartera::observe(CarteraObserver::class);
    }
}
