<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/grup/add-karyawan', 'POST', [
    'kode_grup' => 'GR1',
    'nik' => '99999999' // Some dummy nik
]);

// Since the route is protected by auth, we might need to bypass auth or just test the controller directly
$controller = new \App\Http\Controllers\GrupController();
$response = $controller->addKaryawan($request);

echo get_class($response) . "\n";
if ($response instanceof \Illuminate\Http\JsonResponse) {
    echo json_encode($response->getData()) . "\n";
} elseif ($response instanceof \Illuminate\Http\RedirectResponse) {
    echo "Redirect: " . json_encode(session()->all()) . "\n";
} else {
    echo $response->getContent();
}
