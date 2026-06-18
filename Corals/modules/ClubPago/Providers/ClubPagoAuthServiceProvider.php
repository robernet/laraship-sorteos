<?php

namespace Corals\Modules\ClubPago\Providers;

use Corals\Modules\ClubPago\Models\ClubPagoReference;
use Corals\Modules\ClubPago\Policies\ClubPagoReferencePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class ClubPagoAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        ClubPagoReference::class => ClubPagoReferencePolicy::class,
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
