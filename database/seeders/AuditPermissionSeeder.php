<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuditPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissiongroup = Permission_group::firstOrCreate(['name' => 'Audit']);

        Permission::firstOrCreate(['name' => 'audit.index'],   ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'audit.show'],    ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'audit.export'],  ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'audit.cleanup'], ['id_permission_group' => $permissiongroup->id]);

        // Berikan semua permission audit ke Super Admin
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
