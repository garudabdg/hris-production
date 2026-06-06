<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$encrypted = "eyJpdiI6ImxCSGM3YmFmUkFnK2NSSmNMck1LN0E9PSIsInZhbHVlIjoiaDI1NzV4WGJLWXc4ZXA0VlZiamRsUT09IiwibWFjIjoiNjFmZmQ3NGY1MGQ3MjBhMjNkOTJmZjRlZDk0MTY1NDIyOGQxODk0MDM1OTlkZTYxNzNjMGRkNmFhY2E1YWM1OCIsInRhZyI6IiJ9";

try {
    $decrypted = \Illuminate\Support\Facades\Crypt::decrypt($encrypted);
    echo "DECRYPTED KODE_GRUP: " . $decrypted . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
