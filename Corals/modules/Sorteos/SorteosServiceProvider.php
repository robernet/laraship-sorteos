<?php

namespace Corals\Modules\Sorteos;

use Corals\Foundation\Providers\BasePackageServiceProvider;
use Corals\Modules\Sorteos\Facades\Sorteos;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Providers\SorteosAuthServiceProvider;
use Corals\Modules\Sorteos\Providers\SorteosObserverServiceProvider;
use Corals\Modules\Sorteos\Providers\SorteosRouteServiceProvider;
use Corals\Settings\Facades\Modules;
use Corals\Settings\Facades\Settings;
use Illuminate\Foundation\AliasLoader;

class SorteosServiceProvider extends BasePackageServiceProvider
{
    protected $packageCode = 'corals-sorteos';

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function bootPackage()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'Sorteos');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'Sorteos');

        $this->registerCustomFieldsModels();
        $this->registerHooks();
    }

    protected function registerHooks(): void
    {
        \Actions::add_action('order.confirmed', function ($order) {
            if (!$order) {
                return;
            }

            $boletoDigital = app(\Corals\Modules\Sorteos\Services\BoletoDigitalService::class);

            $order->loadMissing(['items.boleto']);

            foreach ($order->items as $item) {
                if ($item->boleto) {
                    $boletoDigital->getOrCreateToken($item->boleto);
                }
            }
        }, 10);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function registerPackage()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/sorteos.php', 'sorteos');

        $this->app->register(SorteosRouteServiceProvider::class);
        $this->app->register(SorteosAuthServiceProvider::class);
        $this->app->register(SorteosObserverServiceProvider::class);

        $this->app->booted(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Sorteos', Sorteos::class);
        });
    }

    protected function registerCustomFieldsModels()
    {
        Settings::addCustomFieldModel(Sorteo::class);
    }

    public function registerModulesPackages()
    {
        Modules::addModulesPackages('corals/sorteos');
    }
}
