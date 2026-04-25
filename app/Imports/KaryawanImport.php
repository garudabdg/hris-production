<?php

namespace App\Imports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class KaryawanImport implements ToModel, WithHeadingRow, WithValidation
{
    private static $nikCounter = null;

    public function model(array $row)
    {
        if ($this->isRowEmpty($row)) {
            return null;
        }

        $nik = $this->generateNik();

        return new Karyawan([
            'nik' => $nik,
            'nik_show' => $row['nik'],
            'no_ktp' => $row['no_ktp'],
            'nama_karyawan' => $row['nama_karyawan'],
            'tempat_lahir' => $row['tempat_lahir'],
            'tanggal_lahir' => $this->convertDate($row['tanggal_lahir']),
            'alamat' => $row['alamat'],
            'no_hp' => $row['no_hp'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'kode_status_kawin' => $row['kode_status_kawin'],
            'pendidikan_terakhir' => $row['pendidikan_terakhir'],
            'kode_cabang' => $row['kode_cabang'],
            'kode_dept' => $row['kode_dept'],
            'kode_jabatan' => $row['kode_jabatan'],
            'tanggal_masuk' => $this->convertDate($row['tanggal_masuk']),
            'status_karyawan' => $row['status_karyawan'],
            'kode_jadwal' => null,
            'pin' => null,
            'tanggal_nonaktif' => null,
            'tanggal_off_gaji' => null,
            'lock_location' => 1,
            'lock_jam_kerja' => 1,
            'status_aktif_karyawan' => $row['status_aktif_karyawan'] ?? 1,
            'password' => bcrypt('12345')
        ]);
    }

    private function convertDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // Handle Excel serial number
        if (is_numeric($dateValue)) {
            try {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                    ->addDays($dateValue - 2)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error('Failed to convert Excel serial date: ' . $dateValue);
                return null;
            }
        }

        // Format sudah Y-m-d dari export
        try {
            return Carbon::createFromFormat('Y-m-d', $dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error('Failed to convert date: ' . $dateValue . ' - ' . $e->getMessage());
            return null;
        }
    }

    private function isRowEmpty(array $row)
    {
        $requiredFields = [
            'nik',
            'nama_karyawan',
            'no_ktp',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'no_hp',
            'jenis_kelamin',
            'kode_status_kawin',
            'pendidikan_terakhir',
            'kode_cabang',
            'kode_dept',
            'kode_jabatan',
            'tanggal_masuk',
            'status_karyawan'
        ];

        foreach ($requiredFields as $field) {
            if (!empty($row[$field]) && trim($row[$field]) !== '') {
                return false;
            }
        }

        return true;
    }

    private function generateNik()
    {
        $tahun = date('y');
        $bulan = date('m');
        $prefix = $tahun . $bulan;

        if (self::$nikCounter === null) {
            $last = Karyawan::where('nik', 'like', $prefix . '%')
                ->orderBy('nik', 'desc')
                ->first();

            self::$nikCounter = 0;
            if ($last) {
                self::$nikCounter = (int)substr($last->nik, 4, 5);
            }
        }

        self::$nikCounter++;
        $nikAuto = $prefix . str_pad((string)self::$nikCounter, 5, '0', STR_PAD_LEFT);

        return $nikAuto;
    }

    public function rules(): array
    {
        return [
            'nik' => ['required', 'unique:karyawan,nik_show'],
            'no_ktp' => 'required',
            'nama_karyawan' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
            'kode_status_kawin' => 'required|exists:status_kawin,kode_status_kawin',
            'pendidikan_terakhir' => 'required',
            'kode_cabang' => 'required|exists:cabang,kode_cabang',
            'kode_dept' => 'required|exists:departemen,kode_dept',
            'kode_jabatan' => 'required|exists:jabatan,kode_jabatan',
            'tanggal_masuk' => 'required',
            'status_karyawan' => 'required|in:K,T,M,O',
            'status_aktif_karyawan' => 'nullable|in:0,1',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nik.required' => 'NIK harus diisi',
            'nik.unique' => 'NIK sudah terdaftar di sistem',
            'no_ktp.required' => 'No KTP harus diisi',
            'nama_karyawan.required' => 'Nama karyawan harus diisi',
            'tempat_lahir.required' => 'Tempat lahir harus diisi',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'alamat.required' => 'Alamat harus diisi',
            'no_hp.required' => 'No HP harus diisi',
            'jenis_kelamin.required' => 'Jenis kelamin harus diisi',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
            'kode_status_kawin.required' => 'Kode status kawin harus diisi',
            'kode_status_kawin.exists' => 'Kode status kawin tidak valid',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir harus diisi',
            'kode_cabang.required' => 'Kode cabang harus diisi',
            'kode_cabang.exists' => 'Kode cabang tidak valid',
            'kode_dept.required' => 'Kode departemen harus diisi',
            'kode_dept.exists' => 'Kode departemen tidak valid',
            'kode_jabatan.required' => 'Kode jabatan harus diisi',
            'kode_jabatan.exists' => 'Kode jabatan tidak valid',
            'tanggal_masuk.required' => 'Tanggal masuk harus diisi',
            'status_karyawan.required' => 'Status karyawan harus diisi',
            'status_karyawan.in' => 'Status karyawan harus K, T, M, atau O',
            'status_aktif_karyawan.in' => 'Status aktif harus 0 atau 1',
        ];
    }
}