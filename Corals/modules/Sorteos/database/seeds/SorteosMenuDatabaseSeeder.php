<?php

namespace Corals\Modules\Sorteos\database\seeds;

use Illuminate\Database\Seeder;

class SorteosMenuDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sorteos_menu_id = \DB::table('menus')->insertGetId([
            'parent_id' => 1,// admin
            'key' => 'sorteos',
            'url' => null,
            'active_menu_url' => 'sorteos*',
            'name' => 'Sorteos',
            'description' => 'Sorteos Menu Item',
            'icon' => 'fa fa-globe',
            'target' => null, 'roles' => '["1","2"]',
            'order' => 0,
        ]);

        // seed children menu
        \DB::table('menus')->insert(
            [
                [
                    'parent_id' => $sorteos_menu_id,
                    'key' => null,
                    'url' => config('sorteos.models.sorteo.resource_url'),
                    'active_menu_url' => config('sorteos.models.sorteo.resource_url') . '*',
                    'name' => 'Sorteos',
                    'description' => 'Sorteos List Menu Item',
                    'icon' => 'fa fa-cube',
                    'target' => null, 'roles' => '["1"]',
                    'order' => 0,
                ],
            ]
        );
    }
}
