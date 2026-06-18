<?php

namespace Corals\Modules\ClubPago\Providers;

use Corals\Foundation\Providers\BaseUninstallModuleServiceProvider;
use Corals\Settings\Models\Setting;

class UninstallModuleServiceProvider extends BaseUninstallModuleServiceProvider
{
    protected function providerBooted()
    {
        Setting::where('code', 'like', 'payment_clubpago%')->delete();
    }
}
