<?php

namespace Corals\Modules\ClubPago\database\seeds;

use Illuminate\Database\Seeder;

class ClubPagoMenuDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::table('menus')->where('key', 'clubpago-references')->exists()) {
            return;
        }

        $sorteoParent = \DB::table('menus')->where('key', 'sorteos')->first();
        $parentId = $sorteoParent ? $sorteoParent->id : 1;

        \DB::table('menus')->insert([
            'parent_id'       => $parentId,
            'key'             => 'clubpago-references',
            'url'             => config('clubpago.models.clubpago_reference.resource_url'),
            'active_menu_url' => config('clubpago.models.clubpago_reference.resource_url') . '*',
            'name'            => 'Referencias ClubPago',
            'description'     => 'Referencias de pago en efectivo generadas',
            'icon'            => 'fa fa-barcode',
            'target'          => null,
            'roles'           => '["1","2"]',
            'order'           => 15,
        ]);
    }
}
