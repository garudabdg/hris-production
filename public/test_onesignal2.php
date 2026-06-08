<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$title = "Test Judul Dari Controller Logic";
$message = "<p>Test pesan dari controller logic</p>";

$response = Illuminate\Support\Facades\Http::withHeaders([
    'Authorization' => 'Basic ' . config('services.onesignal.rest_api_key'),
    'Content-Type' => 'application/json',
])->post('https://onesignal.com/api/v1/notifications', [
    'app_id' => config('services.onesignal.app_id'),
    'included_segments' => ['All'], // Send to all subscribed users
    'isAnyWeb' => true,
    'headings' => ['en' => $title],
    'contents' => ['en' => \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($message)), 100)],
    'url' => 'https://hris.didimax.online/'
]);

echo "Status: " . $response->status() . "<br>";
echo "Response: " . $response->body() . "<br>";
