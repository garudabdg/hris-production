<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Cabang;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class AssetImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public int $imported   = 0;
    public int $skipped    = 0;
    public array $errors   = [];

    /**
     * Akses cabang yang diperbolehkan (kosong = semua / super admin).
     */
    protected array $allowedCabangs;

    public function __construct(array $allowedCabangs = [])
    {
        $this->allowedCabangs = $allowedCabangs;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Skip baris contoh (nilai default dari template)
            if ($this->isRowEmpty($row->toArray())) {
                continue;
            }

            $rowNum = $index + 2; // +2 karena baris 1 = header

            // Resolve category_id dari nama kategori
            $categoryId = null;
            if (!empty($row['nama_kategori'])) {
                $cat = AssetCategory::where('nama_kategori', trim($row['nama_kategori']))->first();
                if ($cat) {
                    $categoryId = $cat->id;
                } else {
                    // Auto-create kategori baru
                    $cat = AssetCategory::create(['nama_kategori' => trim($row['nama_kategori'])]);
                    $categoryId = $cat->id;
                }
            }

            // Validasi kode cabang
            $kodeCabang = !empty($row['kode_cabang']) ? strtoupper(trim($row['kode_cabang'])) : null;
            if ($kodeCabang) {
                $cabangExists = Cabang::where('kode_cabang', $kodeCabang)->exists();
                if (!$cabangExists) {
                    $this->errors[] = "Baris {$rowNum}: Kode cabang '{$kodeCabang}' tidak ditemukan.";
                    $this->skipped++;
                    continue;
                }

                // Validasi akses cabang (non-super-admin)
                if (!empty($this->allowedCabangs) && !in_array($kodeCabang, $this->allowedCabangs)) {
                    $this->errors[] = "Baris {$rowNum}: Anda tidak memiliki akses ke cabang '{$kodeCabang}'.";
                    $this->skipped++;
                    continue;
                }
            }

            // Validasi duplikasi kode aset
            $kodeAsset = trim($row['kode_asset']);
            if (Asset::where('kode_asset', $kodeAsset)->exists()) {
                $this->errors[] = "Baris {$rowNum}: Kode aset '{$kodeAsset}' sudah ada, dilewati.";
                $this->skipped++;
                continue;
            }

            // Normalisasi kondisi
            $kondisiMap = [
                'baik'            => 'baik',
                'rusak'           => 'rusak',
                'dalam perbaikan' => 'dalam_perbaikan',
                'dalam_perbaikan' => 'dalam_perbaikan',
            ];
            $kondisi = $kondisiMap[strtolower(trim($row['kondisi'] ?? ''))] ?? 'baik';

            // Normalisasi status
            $statusMap = [
                'tersedia'    => 'tersedia',
                'dipinjam'    => 'dipinjam',
                'tidak aktif' => 'tidak_aktif',
                'tidak_aktif' => 'tidak_aktif',
            ];
            $status = $statusMap[strtolower(trim($row['status'] ?? ''))] ?? 'tersedia';

            // Konversi tanggal
            $tanggalPerolehan = null;
            if (!empty($row['tanggal_perolehan'])) {
                $tanggalPerolehan = $this->convertDate($row['tanggal_perolehan']);
            }

            // Bersihkan nilai numerik (hapus titik/koma pemisah ribuan)
            $nilaiPerolehan = null;
            if (!empty($row['nilai_perolehan'])) {
                $nilaiPerolehan = (float) preg_replace('/[^\d.]/', '', str_replace(',', '.', $row['nilai_perolehan']));
            }

            Asset::create([
                'kode_asset'        => $kodeAsset,
                'nama_asset'        => trim($row['nama_asset']),
                'category_id'       => $categoryId,
                'kode_cabang'       => $kodeCabang,
                'merk'              => !empty($row['merk']) ? trim($row['merk']) : null,
                'no_seri'           => !empty($row['no_seri']) ? trim($row['no_seri']) : null,
                'kondisi'           => $kondisi,
                'status'            => $status,
                'tanggal_perolehan' => $tanggalPerolehan,
                'nilai_perolehan'   => $nilaiPerolehan,
                'lokasi'            => !empty($row['lokasi']) ? trim($row['lokasi']) : null,
                'deskripsi'         => !empty($row['deskripsi']) ? trim($row['deskripsi']) : null,
                'catatan'           => !empty($row['catatan']) ? trim($row['catatan']) : null,
            ]);

            $this->imported++;
        }
    }

    public function rules(): array
    {
        return [
            'kode_asset' => 'required',
            'nama_asset' => 'required',
            'kondisi'    => 'required',
            'status'     => 'required',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'kode_asset.required' => 'Kode aset wajib diisi.',
            'nama_asset.required' => 'Nama aset wajib diisi.',
            'kondisi.required'    => 'Kondisi wajib diisi.',
            'status.required'     => 'Status wajib diisi.',
        ];
    }

    private function convertDate($value): ?string
    {
        if (empty($value)) return null;

        // Excel serial date
        if (is_numeric($value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                    ->addDays((int)$value - 2)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Format dd/mm/yyyy
        try {
            return Carbon::createFromFormat('d/m/Y', trim($value))->format('Y-m-d');
        } catch (\Exception) {}

        // Format yyyy-mm-dd
        try {
            return Carbon::createFromFormat('Y-m-d', trim($value))->format('Y-m-d');
        } catch (\Exception) {}

        return null;
    }

    private function isRowEmpty(array $row): bool
    {
        $values = array_values($row);
        return empty(array_filter($values, fn($v) => $v !== null && $v !== ''));
    }
}
