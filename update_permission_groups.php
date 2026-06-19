<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission_group;
use Spatie\Permission\Models\Permission;

// 1. Group for Izin Keluar
$gIzinKeluar = Permission_group::firstOrCreate(['name' => 'Izin Keluar']);
Permission::where('name', 'like', 'izinkeluar%')->update(['id_permission_group' => $gIzinKeluar->id]);

// 2. Group for Daily Report BU
$gDailyReport = Permission_group::firstOrCreate(['name' => 'Daily Report BU']);
Permission::where('name', 'like', 'dailyreportbu%')->update(['id_permission_group' => $gDailyReport->id]);

echo "Permission groups updated successfully.\n";
