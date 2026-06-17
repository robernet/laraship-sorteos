<?php

namespace Corals\Modules\Sorteos\database\seeds;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SorteosRolesDatabaseSeeder extends Seeder
{
    /**
     * Roles and their permission sets for the Sorteos module.
     *
     *  sorteos_admin    — full access to everything in the module
     *  sorteos_operator — create/view/update; no delete or hard-delete
     *  sorteos_support  — view-only + resend emails (order.update)
     */
    public function run(): void
    {
        $guard = config('auth.defaults.guard');

        $allModels   = ['sorteo', 'cartera', 'boleto', 'order'];
        $allLevels   = ['view', 'create', 'update', 'delete', 'restore', 'hardDelete'];
        $opLevels    = ['view', 'create', 'update'];
        $suppLevels  = ['view'];

        // ── Administrador Sorteos ────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'sorteos_admin', 'guard_name' => $guard]);
        $adminPerms = ['Administrations::admin.sorteos'];
        foreach ($allModels as $model) {
            foreach ($allLevels as $level) {
                $adminPerms[] = "Sorteos::{$model}.{$level}";
            }
        }
        $admin->syncPermissions($adminPerms);

        // ── Operador Sorteos ─────────────────────────────────────────────────
        $operator = Role::firstOrCreate(['name' => 'sorteos_operator', 'guard_name' => $guard]);
        $opPerms  = ['Administrations::admin.sorteos'];
        foreach ($allModels as $model) {
            foreach ($opLevels as $level) {
                $opPerms[] = "Sorteos::{$model}.{$level}";
            }
        }
        $operator->syncPermissions($opPerms);

        // ── Soporte Sorteos ──────────────────────────────────────────────────
        $support     = Role::firstOrCreate(['name' => 'sorteos_support', 'guard_name' => $guard]);
        $suppPerms   = ['Administrations::admin.sorteos'];
        foreach ($allModels as $model) {
            foreach ($suppLevels as $level) {
                $suppPerms[] = "Sorteos::{$model}.{$level}";
            }
        }
        // Support can also update orders (to resend confirmation emails)
        $suppPerms[] = 'Sorteos::order.update';
        $support->syncPermissions($suppPerms);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Sorteos roles seeded: sorteos_admin, sorteos_operator, sorteos_support');
    }
}
