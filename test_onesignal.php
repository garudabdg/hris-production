<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$appId = config('services.onesignal.app_id');
$restKey = config('services.onesignal.rest_api_key');

$payload = [
    'app_id' => $appId,
    'included_segments' => ['All'],
    'isAnyWeb' => true,
    'headings' => ['en' => 'Test Direct'],
    'contents' => ['en' => 'This is a test from scratch script'],
    'url' => 'https://hris.didimax.online'
];

echo "Sending to App ID: " . $appId . "\n";
echo "Payload: " . json_encode($payload) . "\n";

$response = Illuminate\Support\Facades\Http::withHeaders([
    'Authorization' => 'Basic ' . $restKey,
    'Content-Type' => 'application/json',
])->post('https://onesignal.com/api/v1/notifications', $payload);

echo "Status: " . $response->status() . "\n";
echo "Response: " . $response->body() . "\n";
