<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

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

// Assign to superadmin
$roleSuperAdmin = Role::where('name', 'superadmin')->first();
if ($roleSuperAdmin) {
    $roleSuperAdmin->givePermissionTo($permissions);
}

// Assign to karyawan
$roleKaryawan = Role::where('name', 'karyawan')->first();
if ($roleKaryawan) {
    $roleKaryawan->givePermissionTo($permissions);
}

// Assign to admin
$roleAdmin = Role::where('name', 'admin')->first();
if ($roleAdmin) {
    $roleAdmin->givePermissionTo($permissions);
}

echo "Permissions created and assigned!\n";
