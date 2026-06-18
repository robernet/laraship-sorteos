<?php

namespace Corals\Modules\ClubPago\Providers;

use Corals\Foundation\Providers\BaseInstallModuleServiceProvider;
use Corals\Modules\ClubPago\database\migrations\ClubPagoTables;
use Corals\Modules\ClubPago\database\seeds\ClubPagoDatabaseSeeder;

class InstallModuleServiceProvider extends BaseInstallModuleServiceProvider
{
    protected $migrations = [
        ClubPagoTables::class,
    ];

    protected function providerBooted()
    {
        $this->createSchema();

        $clubpagoDatabaseSeeder = new ClubPagoDatabaseSeeder();

        $clubpagoDatabaseSeeder->run();

    }
}
