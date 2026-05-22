<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LaporanLemburPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissiongroup = Permission_group::firstOrCreate(['name' => 'Laporan']);

        $permission = Permission::firstOrCreate(
            ['name' => 'laporan.lembur'], 
            ['id_permission_group' => $permissiongroup->id]
        );

        $roleID = 1;
        $role = Role::findById($roleID);

        if ($role) {
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
                $this->command->info('Permission laporan.lembur successfully added to role ID 1.');
            }
        }
    }
}
