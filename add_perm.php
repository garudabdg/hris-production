<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$perms = ['izinkeluar.index', 'izinkeluar.create', 'izinkeluar.edit', 'izinkeluar.delete', 'izinkeluar.approve'];
foreach ($perms as $p) {
    Permission::firstOrCreate(['name' => $p], ['id_permission_group' => 12]);
}
$role = Role::where('name', 'super admin')->first();
if ($role) {
    $role->givePermissionTo($perms);
}
echo "Done\n";
