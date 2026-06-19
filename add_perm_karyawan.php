<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;

$role = Role::where('name', 'karyawan')->first();
if ($role) {
    $role->givePermissionTo(['izinkeluar.index', 'izinkeluar.create', 'izinkeluar.delete']);
    echo "Permissions given to karyawan.\n";
} else {
    echo "Role karyawan not found.\n";
}
