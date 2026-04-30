<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ItTicketPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = Permission_group::firstOrCreate(['name' => 'IT Ticket']);

        Permission::firstOrCreate(['name' => 'it-ticket.index'],         ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'it-ticket.create'],        ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'it-ticket.show'],          ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'it-ticket.respond'],       ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'it-ticket.update-status'], ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'it-ticket.assign'],        ['id_permission_group' => $group->id]);
        Permission::firstOrCreate(['name' => 'it-ticket.delete'],        ['id_permission_group' => $group->id]);

        // Super Admin: semua permission
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $permissions = Permission::where('id_permission_group', $group->id)->get();
            foreach ($permissions as $perm) {
                if (!$superAdmin->hasPermissionTo($perm)) {
                    $superAdmin->givePermissionTo($perm);
                }
            }
        }

        // IT Staff: semua kecuali delete
        $itStaff = Role::where('name', 'it staff')->first();
        if ($itStaff) {
            $itPerms = Permission::where('id_permission_group', $group->id)
                                 ->where('name', '!=', 'it-ticket.delete')
                                 ->get();
            foreach ($itPerms as $perm) {
                if (!$itStaff->hasPermissionTo($perm)) {
                    $itStaff->givePermissionTo($perm);
                }
            }
        }

        // Semua role karyawan biasa: bisa index, create, show, respond
        $karyawanRoles = ['karyawan', 'hrd', 'hr staff', 'head business', 'head cso', 'direktur utama'];
        $basicPerms    = Permission::where('id_permission_group', $group->id)
                                   ->whereIn('name', ['it-ticket.index', 'it-ticket.create', 'it-ticket.show', 'it-ticket.respond'])
                                   ->get();
        foreach ($karyawanRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($basicPerms as $perm) {
                    if (!$role->hasPermissionTo($perm)) {
                        $role->givePermissionTo($perm);
                    }
                }
            }
        }
    }
}
