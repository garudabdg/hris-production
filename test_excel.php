<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = storage_path('app/test.xlsx');
// Create a fake excel file with 2 columns and 2 rows
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'No WA');
$sheet->setCellValue('B1', 'Nama');
$sheet->setCellValue('A2', '081234567890');
$sheet->setCellValue('B2', 'Budi');
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($filePath);

$collections = \Maatwebsite\Excel\Facades\Excel::toCollection(null, $filePath);
echo json_encode($collections->first()) . "\n";
