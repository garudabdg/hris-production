<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$departemens = App\Models\Departemen::all();
foreach ($departemens as $dept) {
    if ($dept->kode_dept == 'BU') {
        var_dump($dept->sub_departemen);
        echo "<br>Is empty: " . (empty($dept->sub_departemen) ? 'Yes' : 'No');
        echo "<br>Is array: " . (is_array($dept->sub_departemen) ? 'Yes' : 'No');
    }
}
