<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UtilitiesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = Permission_group::firstOrCreate(['name' => 'Utilities']);

        // Roles & Permissions management
        Permission::firstOrCreate(['name' => 'roles.index'],            ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'roles.create'],           ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'roles.edit'],             ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'roles.delete'],           ['id_permission_group' => $group->id]);

        Permission::firstOrCreate(['name' => 'permissions.index'],      ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'permissions.create'],     ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'permissions.edit'],       ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'permissions.delete'],     ['id_permission_group' => $group->id]);

        Permission::firstOrCreate(['name' => 'permissiongroups.index'], ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'permissiongroups.create'],['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'permissiongroups.edit'],  ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'permissiongroups.delete'],['id_permission_group' => $group->id]);

        // Reset Data
        Permission::firstOrCreate(['name' => 'resetdata.index'],        ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'resetdata.execute'],      ['id_permission_group' => $group->id]);

        // Berikan semua ke super admin saja
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $permissions = Permission::where('id_permission_group', $group->id)->get();
            foreach ($permissions as $permission) {
                if (!$superAdmin->hasPermissionTo($permission)) {
                    $superAdmin->givePermissionTo($permission);
                }
            }
        }
    }
}
