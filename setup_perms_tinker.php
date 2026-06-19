<?php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permissions = [
    'dailyreportbu.index',
    'dailyreportbu.create',
    'dailyreportbu.edit',
    'dailyreportbu.delete'
];

foreach ($permissions as $perm) {
    Permission::firstOrCreate(['name' => $perm], ['guard_name' => 'web', 'id_permission_group' => 1]);
}

$roleSuperAdmin = Role::where('name', 'superadmin')->first();
if ($roleSuperAdmin) {
    $roleSuperAdmin->givePermissionTo($permissions);
}

$roleKaryawan = Role::where('name', 'karyawan')->first();
if ($roleKaryawan) {
    $roleKaryawan->givePermissionTo($permissions);
}

$roleAdmin = Role::where('name', 'admin')->first();
if ($roleAdmin) {
    $roleAdmin->givePermissionTo($permissions);
}

echo "Permissions created and assigned!\n";
