<?php

namespace App\Exports;

use App\Models\AssetCategory;
use App\Models\Cabang;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AssetTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    public function array(): array
    {
        // Baris contoh
        return [
            [
                'AST-001',
                'Laptop Dell Inspiron',
                'Elektronik',
                'HO',
                'Dell',
                'SN123456789',
                'Baik',
                'Tersedia',
                '01/01/2024',
                '15000000',
                'Ruang IT',
                'Laptop untuk operasional',
                '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'kode_asset *',
            'nama_asset *',
            'nama_kategori',
            'kode_cabang',
            'merk',
            'no_seri',
            'kondisi *',
            'status *',
            'tanggal_perolehan',
            'nilai_perolehan',
            'lokasi',
            'deskripsi',
            'catatan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2D3748']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Baris contoh — warna abu terang
        $sheet->getStyle('A2:M2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F4F8']],
            'font' => ['italic' => true, 'color' => ['argb' => 'FF718096']],
        ]);

        // Border seluruh area
        $sheet->getStyle('A1:M100')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ]);

        // Tinggi header row
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 30,
            'C' => 22,
            'D' => 14,
            'E' => 18,
            'F' => 20,
            'G' => 18,
            'H' => 16,
            'I' => 20,
            'J' => 20,
            'K' => 22,
            'L' => 35,
            'M' => 30,
        ];
    }

    public function title(): string
    {
        return 'Template Import Aset';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Sheet kedua: Referensi ───────────────────────────────────
                $wb        = $event->sheet->getParent();
                $refSheet  = $wb->createSheet();
                $refSheet->setTitle('Referensi');

                // Kolom kondisi valid
                $refSheet->setCellValue('A1', 'Kondisi (isi salah satu)');
                $refSheet->setCellValue('A2', 'Baik');
                $refSheet->setCellValue('A3', 'Rusak');
                $refSheet->setCellValue('A4', 'Dalam Perbaikan');

                // Kolom status valid
                $refSheet->setCellValue('C1', 'Status (isi salah satu)');
                $refSheet->setCellValue('C2', 'Tersedia');
                $refSheet->setCellValue('C3', 'Dipinjam');
                $refSheet->setCellValue('C4', 'Tidak Aktif');

                // Daftar cabang
                $refSheet->setCellValue('E1', 'Kode Cabang Tersedia');
                $row = 2;
                foreach (Cabang::orderBy('nama_cabang')->get() as $c) {
                    $refSheet->setCellValue("E{$row}", $c->kode_cabang);
                    $refSheet->setCellValue("F{$row}", $c->nama_cabang);
                    $row++;
                }

                // Daftar kategori
                $refSheet->setCellValue('H1', 'Kategori Tersedia');
                $row = 2;
                foreach (AssetCategory::orderBy('nama_kategori')->get() as $cat) {
                    $refSheet->setCellValue("H{$row}", $cat->nama_kategori);
                    $row++;
                }

                // Style header referensi
                foreach (['A1', 'C1', 'E1', 'H1'] as $cell) {
                    $refSheet->getStyle($cell)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4A5568']],
                    ]);
                }
                $refSheet->getColumnDimension('A')->setWidth(26);
                $refSheet->getColumnDimension('C')->setWidth(26);
                $refSheet->getColumnDimension('E')->setWidth(18);
                $refSheet->getColumnDimension('F')->setWidth(30);
                $refSheet->getColumnDimension('H')->setWidth(30);

                // ── Catatan di bawah template ────────────────────────────────
                $mainSheet = $event->sheet->getDelegate();
                $mainSheet->setCellValue('A105', '* = Kolom wajib diisi');
                $mainSheet->setCellValue('A106', 'Kondisi: Baik / Rusak / Dalam Perbaikan');
                $mainSheet->setCellValue('A107', 'Status: Tersedia / Dipinjam / Tidak Aktif');
                $mainSheet->setCellValue('A108', 'Tanggal format: DD/MM/YYYY (contoh: 25/12/2024)');
                $mainSheet->setCellValue('A109', 'Lihat sheet "Referensi" untuk daftar kode cabang dan kategori yang tersedia.');

                $mainSheet->getStyle('A105:M109')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF718096'], 'size' => 9],
                ]);

                // Kembali ke sheet utama
                $wb->setActiveSheetIndex(0);
            },
        ];
    }
}
