<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersAdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari permission group Users
        $permissiongroup = Permission_group::where('name', 'Users')->first();
        
        if (!$permissiongroup) {
            $permissiongroup = Permission_group::create(['name' => 'Users']);
        }

        // Buat permission baru untuk manage admin users
        $permission = Permission::firstOrCreate([
            'name' => 'users.admin',
        ], [
            'id_permission_group' => $permissiongroup->id
        ]);

        $this->command->info('Permission users.admin created/ensured.');

        // Berikan permission ke role super admin (ID 1)
        $roleSuperAdmin = Role::find(1);
        if ($roleSuperAdmin && !$roleSuperAdmin->hasPermissionTo($permission)) {
            $roleSuperAdmin->givePermissionTo($permission);
            $this->command->info('Permission users.admin given to super admin role.');
        }

        // Berikan juga ke role head (ID 2) jika ada
        $roleHead = Role::find(2);
        if ($roleHead && !$roleHead->hasPermissionTo($permission)) {
            $roleHead->givePermissionTo($permission);
            $this->command->info('Permission users.admin given to head role.');
        }
        
        // JANGAN berikan ke HRD (ID 5) secara default
        // Biarkan admin yang mengatur melalui role permission page
    }
}
