<?php

namespace Corals\Modules\ClubPago;

use Corals\Foundation\Providers\BasePackageServiceProvider;
use Corals\Modules\ClubPago\Models\ClubPagoReference;
use Corals\Modules\ClubPago\Notifications\ClubPagoReferenceNotification;
use Corals\Modules\ClubPago\Providers\ClubPagoRouteServiceProvider;
use Corals\Settings\Facades\Modules;
use Corals\Settings\Facades\Settings;
use Corals\User\Communication\Facades\CoralsNotification;

class ClubPagoServiceProvider extends BasePackageServiceProvider
{
    protected $packageCode = 'corals-clubpago';

    public function bootPackage()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'ClubPago');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'ClubPago');

        $this->registerCustomFieldsModels();
        $this->addEvents();
    }

    public function registerPackage()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/clubpago.php', 'clubpago');

        $this->app->register(ClubPagoRouteServiceProvider::class);
    }

    public function registerModulesPackages()
    {
        Modules::addModulesPackages('corals/clubpago');
    }

    protected function registerCustomFieldsModels()
    {
        Settings::addCustomFieldModel(ClubPagoReference::class);
    }

    protected function addEvents()
    {
        CoralsNotification::addEvent('notifications.clubpago.send_reference', 'Reference', ClubPagoReferenceNotification::class);
    }
}
