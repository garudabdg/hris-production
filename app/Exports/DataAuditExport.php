<?php

namespace App\Exports;

use App\Models\DataAuditLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataAuditExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $rowNumber = 0;

    public function collection()
    {
        return DataAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Waktu',
            'Aktor (User)',
            'Aktivitas',
            'Tabel/Model',
            'ID Data',
            'IP Address',
            'User Agent',
            'Data Lama (Old Values)',
            'Data Baru (New Values)',
        ];
    }

    public function map($log): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y H:i:s'),
            $log->user ? $log->user->name : 'Sistem',
            ucfirst($log->action),
            class_basename($log->model_type),
            $log->model_id,
            $log->ip_address,
            $log->user_agent,
            json_encode($log->old_values, JSON_UNESCAPED_SLASHES),
            json_encode($log->new_values, JSON_UNESCAPED_SLASHES),
        ];
    }

    public function title(): string
    {
        return 'Data Audit Log';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:J' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF024A75'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,    // No
            'B' => 20,   // Waktu
            'C' => 25,   // Aktor
            'D' => 15,   // Aktivitas
            'E' => 25,   // Tabel/Model
            'F' => 10,   // ID Data
            'G' => 15,   // IP Address
            'H' => 40,   // User Agent
            'I' => 50,   // Data Lama
            'J' => 50,   // Data Baru
        ];
    }
}
