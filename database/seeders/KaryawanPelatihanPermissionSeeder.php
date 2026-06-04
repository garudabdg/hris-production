<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission_group;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class KaryawanPelatihanPermissionSeeder extends Seeder
{
    public function run()
    {
        // Buat atau ambil grup permission
        $permissiongroup = Permission_group::firstOrCreate(['name' => 'Pelatihan']);

        // Daftar permission untuk modul pelatihan
        $permissions = [
            'pelatihan.index',
            'pelatihan.create',
            'pelatihan.edit',
            'pelatihan.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['id_permission_group' => $permissiongroup->id]
            );
        }

        // Assign ke role super-admin (Biasanya ID 1)
        $role = Role::findById(1);
        $allPermissions = Permission::where('id_permission_group', $permissiongroup->id)->get();
        
        foreach ($allPermissions as $p) {
             if ($role && !$role->hasPermissionTo($p)) {
                 $role->givePermissionTo($p);
             }
        }
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
