<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

$izin = \App\Models\Izinsakit::where('kode_izin_sakit', 'IS26040001')->first();
if ($izin) {
    echo "doc_sid: " . $izin->doc_sid . "\n";
    echo "File exists: " . (\Illuminate\Support\Facades\Storage::disk('public')->exists('uploads/sid/' . $izin->doc_sid) ? 'YES' : 'NO') . "\n";
    echo "Storage URL: " . \Illuminate\Support\Facades\Storage::url('uploads/sid/' . $izin->doc_sid) . "\n";
} else {
    echo "Record not found\n";
}
?>
