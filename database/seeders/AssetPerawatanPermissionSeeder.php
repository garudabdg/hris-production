<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssetPerawatanPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissiongroup = Permission_group::firstOrCreate(['name' => 'Manajemen Aset']);

        Permission::firstOrCreate(['name' => 'asset.perawatan.index'],  ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.perawatan.create'], ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'asset.perawatan.delete'], ['id_permission_group' => $permissiongroup->id]);

        // Berikan semua permission perawatan ke Super Admin
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $permissions = Permission::whereIn('name', [
                'asset.perawatan.index',
                'asset.perawatan.create',
                'asset.perawatan.delete',
            ])->get();
            foreach ($permissions as $permission) {
                if (!$superAdmin->hasPermissionTo($permission)) {
                    $superAdmin->givePermissionTo($permission);
                }
            }
        }
    }
}
