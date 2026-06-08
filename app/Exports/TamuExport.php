<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TamuExport implements FromView, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    protected $tamus;
    protected $tanggal;
    protected $generalsetting;
    protected $cabang;

    public function __construct($tamus, $tanggal, $generalsetting, $cabang)
    {
        $this->tamus = $tamus;
        $this->tanggal = $tanggal;
        $this->generalsetting = $generalsetting;
        $this->cabang = $cabang;
    }

    public function view(): View
    {
        return view('tamu.export_excel', [
            'tamus' => $this->tamus,
            'tanggal' => $this->tanggal,
            'generalsetting' => $this->generalsetting,
            'cabang' => $this->cabang
        ]);
    }

    public function title(): string
    {
        return 'Data Tamu ' . $this->tanggal;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Add borders to the table (headers and data)
        $sheet->getStyle('A6:H' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        return [
            // Bold header row for table
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF024A75']
                ],
            ],
            // Bold title
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 20,
            'F' => 30,
            'G' => 12,
            'H' => 12,
        ];
    }
}
