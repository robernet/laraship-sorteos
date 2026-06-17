<?php

namespace Corals\Modules\Sorteos\Providers;

use Corals\Foundation\Providers\BaseInstallModuleServiceProvider;
use Corals\Modules\Sorteos\database\migrations\SorteosTables;
use Corals\Modules\Sorteos\database\seeds\SorteosDatabaseSeeder;

class InstallModuleServiceProvider extends BaseInstallModuleServiceProvider
{
    protected $module_public_path = __DIR__ . '/../public';

    protected $migrations = [
        SorteosTables::class,
    ];

    protected function providerBooted()
    {
        $this->createSchema();

        $sorteosDatabaseSeeder = new SorteosDatabaseSeeder();

        $sorteosDatabaseSeeder->run();
    }
}
