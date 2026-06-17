<?php

namespace Corals\Modules\Sorteos\database\seeds;

use Corals\Menu\Models\Menu;
use Corals\Settings\Models\Setting;
use Corals\User\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SorteosDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SorteosPermissionsDatabaseSeeder::class);
        $this->call(SorteosRolesDatabaseSeeder::class);
        $this->call(SorteosMenuDatabaseSeeder::class);
        $this->call(SorteosSettingsDatabaseSeeder::class);
    }

    public function rollback()
    {
        Permission::where('name', 'like', 'Sorteos::%')->delete();

        \Spatie\Permission\Models\Role::whereIn('name', ['sorteos_admin', 'sorteos_operator', 'sorteos_support'])->delete();

        Menu::where('key', 'sorteos')
            ->orWhere('active_menu_url', 'like', 'sorteos%')
            ->orWhere('url', 'like', 'sorteos%')
            ->delete();

        Setting::where('category', 'Sorteos')
            ->orWhere('category', 'ClubPago')
            ->orWhere('category', 'Brevo')
            ->delete();

        Media::whereIn('collection_name', ['sorteos-media-collection'])->delete();
    }
}
