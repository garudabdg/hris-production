<?php

$bladePath = __DIR__ . '/../resources/views/facerecognition-presensi/scan_any.blade.php';
$cssPath = __DIR__ . '/assets/css/facerecognition_presensi_scan_any.css';
$jsPath = __DIR__ . '/assets/js/facerecognition_presensi_scan_any.js';

$content = file_get_contents($bladePath);

// Extract CSS
preg_match('/<style>(.*?)<\/style>/s', $content, $cssMatches);
$cssContent = $cssMatches[1] ?? '';
$cssContent = trim($cssContent);

// Extract JS
preg_match('/<script>\s*let stream = null;(.*?)<\/script>\s*$/s', $content, $jsMatches);
$jsContent = "let stream = null;\n" . ($jsMatches[1] ?? '');
$jsContent = trim($jsContent);

// Modify JS to use window.ScanAnyConfig
$replacements = [
    "{{ route('facerecognition-presensi.index') }}" => "window.ScanAnyConfig.routes.index",
    "{{ asset('models') }}" => "window.ScanAnyConfig.assets.models",
    "`{{ route('facerecognition.getallwajah') }}?t=\${timestamp}`" => "`\${window.ScanAnyConfig.routes.getAllWajah}?t=\${timestamp}`",
    "`{{ route('facerecognition-presensi.generate', ['nik' => ':nik']) }}`.replace(':nik', nik)" => "window.ScanAnyConfig.routes.generate.replace(':nik', nik)",
    "'{{ route('facerecognition-presensi.store') }}'" => "window.ScanAnyConfig.routes.store",
    "'{{ csrf_token() }}'" => "window.ScanAnyConfig.csrfToken",
    "'{{ asset('assets/sound/absenmasuk.wav') }}'" => "window.ScanAnyConfig.assets.soundAbsenMasuk",
    "'{{ asset('assets/sound/mulaiabsen.wav') }}'" => "window.ScanAnyConfig.assets.soundMulaiAbsen",
    "'{{ asset('assets/sound/akhirabsen.wav') }}'" => "window.ScanAnyConfig.assets.soundAkhirAbsen",
    "'{{ asset('assets/sound/sudahabsen.wav') }}'" => "window.ScanAnyConfig.assets.soundSudahAbsen",
    "'{{ asset('assets/sound/sudahabsenpulang.wav') }}'" => "window.ScanAnyConfig.assets.soundSudahAbsenPulang",
];

foreach ($replacements as $search => $replace) {
    $jsContent = str_replace($search, $replace, $jsContent);
}

file_put_contents($cssPath, $cssContent);
file_put_contents($jsPath, $jsContent);

$configScript = "
    <script>
        window.ScanAnyConfig = {
            csrfToken: '{{ csrf_token() }}',
            routes: {
                index: '{{ route(\"facerecognition-presensi.index\") }}',
                getAllWajah: '{{ route(\"facerecognition.getallwajah\") }}',
                generate: '{{ route(\"facerecognition-presensi.generate\", [\"nik\" => \":nik\"]) }}',
                store: '{{ route(\"facerecognition-presensi.store\") }}'
            },
            assets: {
                models: '{{ asset(\"models\") }}',
                soundAbsenMasuk: '{{ asset(\"assets/sound/absenmasuk.wav\") }}',
                soundMulaiAbsen: '{{ asset(\"assets/sound/mulaiabsen.wav\") }}',
                soundAkhirAbsen: '{{ asset(\"assets/sound/akhirabsen.wav\") }}',
                soundSudahAbsen: '{{ asset(\"assets/sound/sudahabsen.wav\") }}',
                soundSudahAbsenPulang: '{{ asset(\"assets/sound/sudahabsenpulang.wav\") }}'
            }
        };
    </script>
    <script src=\"{{ asset('assets/js/facerecognition_presensi_scan_any.js') }}\"></script>";

$content = preg_replace('/<style>.*?<\/style>/s', "<link rel=\"stylesheet\" href=\"{{ asset('assets/css/facerecognition_presensi_scan_any.css') }}\">\n", $content);
$content = preg_replace('/<script>\s*let stream = null;.*?<\/script>/s', $configScript, $content);

file_put_contents($bladePath, $content);

echo "Done extracting and refactoring scan_any.blade.php\n";
