<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataCalonNasabahExport implements FromView, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    protected $nasabahs;

    public function __construct($nasabahs)
    {
        $this->nasabahs = $nasabahs;
    }

    public function view(): View
    {
        return view('datanasabah.export_excel', [
            'nasabahs' => $this->nasabahs,
        ]);
    }

    public function title(): string
    {
        return 'Data Calon Nasabah';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Add borders to the table (headers and data)
        $sheet->getStyle('A1:G' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        return [
            // Bold header row for table
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF024A75']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 30,
            'D' => 30,
            'E' => 15,
            'F' => 20,
            'G' => 20,
        ];
    }
}
