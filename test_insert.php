<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $grupDetail = \App\Models\GrupDetail::create([
        'kode_grup' => 'GR1', // Just a dummy
        'nik' => 'dummy123'
    ]);
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
