<?php

namespace Corals\Modules\Sorteos\database\seeds;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SorteosMenuDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::table('menus')->where('key', 'sorteos')->exists()) {
            return;
        }

        $roleIds = $this->resolveRoleIds([
            'superuser',
            'sorteos_admin', 'sorteos_operator', 'sorteos_support',
        ]);

        $adminRoles    = $this->json($roleIds, ['superuser', 'sorteos_admin']);
        $operatorRoles = $this->json($roleIds, ['superuser', 'sorteos_admin', 'sorteos_operator']);
        $allRoles      = $this->json($roleIds, ['superuser', 'sorteos_admin', 'sorteos_operator', 'sorteos_support']);

        $parentId = \DB::table('menus')->insertGetId([
            'parent_id'       => 1,
            'key'             => 'sorteos',
            'url'             => null,
            'active_menu_url' => 'sorteos*',
            'name'            => 'Sorteos ITSON',
            'description'     => 'Módulo de sorteos y venta de boletos',
            'icon'            => 'fa fa-ticket',
            'target'          => null,
            'roles'           => $allRoles,
            'order'           => 50,
        ]);

        $children = [
            [
                'url'             => config('sorteos.models.sorteo.resource_url'),
                'active_menu_url' => config('sorteos.models.sorteo.resource_url') . '*',
                'name'            => 'Sorteos',
                'description'     => 'Gestión de sorteos',
                'icon'            => 'fa fa-star',
                'roles'           => $operatorRoles,
                'order'           => 1,
            ],
            [
                'url'             => config('sorteos.models.cartera.resource_url'),
                'active_menu_url' => config('sorteos.models.cartera.resource_url') . '*',
                'name'            => 'Carteras',
                'description'     => 'Gestión de carteras de boletos',
                'icon'            => 'fa fa-folder-open',
                'roles'           => $operatorRoles,
                'order'           => 2,
            ],
            [
                'url'             => config('sorteos.models.boleto.resource_url'),
                'active_menu_url' => config('sorteos.models.boleto.resource_url') . '*',
                'name'            => 'Boletos',
                'description'     => 'Consulta y validación de boletos',
                'icon'            => 'fa fa-barcode',
                'roles'           => $allRoles,
                'order'           => 3,
            ],
            [
                'url'             => config('sorteos.models.order.resource_url'),
                'active_menu_url' => config('sorteos.models.order.resource_url') . '*',
                'name'            => 'Órdenes',
                'description'     => 'Historial de órdenes y pagos',
                'icon'            => 'fa fa-shopping-cart',
                'roles'           => $allRoles,
                'order'           => 4,
            ],
            [
                'url'             => 'sorteos/reports',
                'active_menu_url' => 'sorteos/reports*',
                'name'            => 'Reportes',
                'description'     => 'Reportes y estadísticas',
                'icon'            => 'fa fa-bar-chart',
                'roles'           => $adminRoles,
                'order'           => 5,
            ],
            [
                'url'             => 'sorteos/reports/sales',
                'active_menu_url' => 'sorteos/reports/sales*',
                'name'            => 'Ventas',
                'description'     => 'Reporte de ventas por período',
                'icon'            => 'fa fa-line-chart',
                'roles'           => $adminRoles,
                'order'           => 6,
            ],
            [
                'url'             => 'sorteos/reports/buyers',
                'active_menu_url' => 'sorteos/reports/buyers*',
                'name'            => 'Compradores',
                'description'     => 'Reporte por comprador',
                'icon'            => 'fa fa-users',
                'roles'           => $adminRoles,
                'order'           => 7,
            ],
            [
                'url'             => 'sorteos/reports/geographic',
                'active_menu_url' => 'sorteos/reports/geographic*',
                'name'            => 'Geográfico',
                'description'     => 'Reporte de ventas por ubicación',
                'icon'            => 'fa fa-map-marker',
                'roles'           => $adminRoles,
                'order'           => 8,
            ],
            [
                'url'             => 'sorteos/reports/payment-methods',
                'active_menu_url' => 'sorteos/reports/payment-methods*',
                'name'            => 'Métodos de Pago',
                'description'     => 'Reporte por método de pago',
                'icon'            => 'fa fa-credit-card',
                'roles'           => $adminRoles,
                'order'           => 9,
            ],
        ];

        foreach ($children as $child) {
            \DB::table('menus')->insert(array_merge([
                'parent_id' => $parentId,
                'key'       => null,
                'target'    => null,
            ], $child));
        }
    }

    private function resolveRoleIds(array $names): array
    {
        return Role::whereIn('name', $names)->pluck('id', 'name')->all();
    }

    private function json(array $ids, array $names): string
    {
        $resolved = array_values(array_filter(array_map(
            fn($name) => isset($ids[$name]) ? (string) $ids[$name] : null,
            $names
        )));

        return json_encode($resolved);
    }
}
