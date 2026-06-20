<?php

namespace Corals\Modules\Sorteos\Providers;

use Corals\Modules\Sorteos\Models\Colaborador;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Policies\ColaboradorPolicy;
use Corals\Modules\Sorteos\Policies\BoletoPolicy;
use Corals\Modules\Sorteos\Policies\CarteraPolicy;
use Corals\Modules\Sorteos\Policies\OrderPolicy;
use Corals\Modules\Sorteos\Policies\SorteoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class SorteosAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Colaborador::class => ColaboradorPolicy::class,
        Sorteo::class   => SorteoPolicy::class,
        Cartera::class  => CarteraPolicy::class,
        Boleto::class   => BoletoPolicy::class,
        Order::class    => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
