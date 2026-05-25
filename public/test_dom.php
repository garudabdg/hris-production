<?php
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$file = '/var/www/hris.didimax.online/storage/framework/cache/laravel-excel/laravel-excel-1yntgQQV3nrEp92XfhlZRXoiFZFoRMg3.html';
$html = file_get_contents($file);
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
$loaded = $dom->loadHTML($html);

if ($loaded === false) {
    echo "FAILED!\n";
} else {
    echo "SUCCESS!\n";
}

$errors = libxml_get_errors();
foreach ($errors as $error) {
    echo $error->message . "\n";
}
libxml_clear_errors();
