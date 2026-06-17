<?php

namespace Corals\Modules\Sorteos\Providers;

use Corals\Foundation\Providers\BaseUninstallModuleServiceProvider;
use Corals\Modules\Sorteos\database\migrations\SorteosTables;
use Corals\Modules\Sorteos\database\seeds\SorteosDatabaseSeeder;

class UninstallModuleServiceProvider extends BaseUninstallModuleServiceProvider
{
    protected $migrations = [
        SorteosTables::class,
    ];

    protected function providerBooted()
    {
        $this->dropSchema();

        $sorteosDatabaseSeeder = new SorteosDatabaseSeeder();

        $sorteosDatabaseSeeder->rollback();
    }
}
