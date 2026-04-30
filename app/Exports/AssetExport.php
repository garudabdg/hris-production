<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AssetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Asset::with(['category', 'cabang']);

        // Scoping per cabang jika bukan super admin
        if (!empty($this->filters['_scoped'])) {
            $cabangs = $this->filters['_cabangs'] ?? [];
            if (empty($cabangs)) {
                return collect(); // tidak ada akses
            }
            $query->whereIn('kode_cabang', $cabangs);
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('nama_asset', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('kode_asset', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('merk', 'like', '%' . $this->filters['search'] . '%');
            });
        }
        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (!empty($this->filters['kode_cabang'])) {
            $query->where('kode_cabang', $this->filters['kode_cabang']);
        }
        if (!empty($this->filters['kondisi'])) {
            $query->where('kondisi', $this->filters['kondisi']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('kode_asset')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Aset',
            'Nama Aset',
            'Kategori',
            'Cabang',
            'Merk',
            'No. Seri',
            'Kondisi',
            'Status',
            'Lokasi',
            'Tanggal Pembelian',
            'Harga Pembelian (Rp)',
            'Deskripsi',
            'Catatan',
        ];
    }

    public function map($asset): array
    {
        static $no = 0;
        $no++;

        $kondisiMap = [
            'baik'            => 'Baik',
            'rusak'           => 'Rusak',
            'dalam_perbaikan' => 'Dalam Perbaikan',
        ];
        $statusMap = [
            'tersedia'   => 'Tersedia',
            'dipinjam'   => 'Dipinjam',
            'tidak_aktif' => 'Tidak Aktif',
        ];

        return [
            $no,
            $asset->kode_asset,
            $asset->nama_asset,
            $asset->category->nama_kategori ?? '-',
            optional($asset->cabang)->nama_cabang ?? '-',
            $asset->merk ?? '-',
            $asset->no_seri ?? '-',
            $kondisiMap[$asset->kondisi] ?? $asset->kondisi,
            $statusMap[$asset->status] ?? $asset->status,
            $asset->lokasi ?? '-',
            $asset->tanggal_perolehan ? $asset->tanggal_perolehan->format('d/m/Y') : '-',
            $asset->nilai_perolehan ?? 0,
            $asset->deskripsi ?? '-',
            $asset->catatan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B4253']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 30,
            'D' => 20,
            'E' => 20,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 15,
            'J' => 20,
            'K' => 18,
            'L' => 22,
            'M' => 35,
            'N' => 35,
        ];
    }

    public function title(): string
    {
        return 'Daftar Aset';
    }
}
