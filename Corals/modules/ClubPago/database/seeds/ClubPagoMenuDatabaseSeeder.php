<?php

namespace Corals\Modules\ClubPago\database\seeds;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ClubPagoMenuDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::table('menus')->where('key', 'clubpago-references')->exists()) {
            return;
        }

        $sorteoParent = \DB::table('menus')->where('key', 'sorteos')->first();
        $parentId = $sorteoParent ? $sorteoParent->id : 1;

        $roles = Role::whereIn('name', ['superuser', 'sorteos_admin'])->pluck('id')
            ->map(fn($id) => (string) $id)->values()->toJson();

        \DB::table('menus')->insert([
            'parent_id'       => $parentId,
            'key'             => 'clubpago-references',
            'url'             => config('clubpago.models.clubpago_reference.resource_url'),
            'active_menu_url' => config('clubpago.models.clubpago_reference.resource_url') . '*',
            'name'            => 'Referencias ClubPago',
            'description'     => 'Referencias de pago en efectivo generadas',
            'icon'            => 'fa fa-barcode',
            'target'          => null,
            'roles'           => $roles,
            'order'           => 15,
        ]);
    }
}
