<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssetPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissiongroup = Permission_group::firstOrCreate(['name' => 'Manajemen Aset']);

        Permission::firstOrCreate(['name' => 'asset.index'],            ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.create'],           ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.edit'],             ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.show'],             ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.delete'],           ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.kategori.index'],   ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.kategori.create'],  ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.kategori.edit'],    ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.kategori.delete'],  ['id_permission_group' => $permissiongroup->id]);

        // Berikan semua permission asset ke Super Admin
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $permissions = Permission::where('id_permission_group', $permissiongroup->id)->get();
            foreach ($permissions as $permission) {
                if (!$superAdmin->hasPermissionTo($permission)) {
                    $superAdmin->givePermissionTo($permission);
                }
            }
        }
    }
}
