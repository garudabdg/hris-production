<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class KaryawanService
{
    public function generateNik()
    {
        $tahun = date('y');
        $bulan = date('m');
        $prefix = $tahun . $bulan;

        $last = Karyawan::where('nik', 'like', $prefix . '%')
            ->orderBy('nik', 'desc')
            ->first();

        $lastNumber = 0;
        if ($last) {
            $lastNumber = (int)substr($last->nik, 4, 5);
        }
        $nextNumber = $lastNumber + 1;
        return $prefix . str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function handleFotoUpload($file, $nik, $oldFoto = null)
    {
        $foto_name = $nik . "." . $file->getClientOriginalExtension();
        $destination_foto_path = "/public/karyawan";

        if (!Storage::exists($destination_foto_path)) {
            Storage::makeDirectory($destination_foto_path, 0775, true);
            $path = Storage::path($destination_foto_path);
            chmod($path, 0775);
        }

        if ($oldFoto && Storage::exists($destination_foto_path . "/" . $oldFoto)) {
            Storage::delete($destination_foto_path . "/" . $oldFoto);
        }

        $file->storeAs($destination_foto_path, $foto_name);

        return $foto_name;
    }

    public function storeKaryawan(array $data, $file = null)
    {
        $nik = $this->generateNik();
        $data['nik'] = $nik;
        
        // Default values for new employee
        $data['status_aktif_karyawan'] = 1;
        $data['password'] = Hash::make('12345');

        if ($file) {
            $data['foto'] = $this->handleFotoUpload($file, $nik);
        }

        return Karyawan::create($data);
    }

    public function updateKaryawan($nik, array $data, $file = null)
    {
        $karyawan = Karyawan::where('nik', $nik)->firstOrFail();

        if (isset($data['status_aktif_karyawan']) && $data['status_aktif_karyawan'] === '1') {
            $data['tanggal_nonaktif'] = null;
        }

        if ($file) {
            $data['foto'] = $this->handleFotoUpload($file, $nik, $karyawan->foto);
        }

        $karyawan->fill($data);
        $updated = $karyawan->save();

        if ($updated) {
            $user_karyawan = Userkaryawan::where('nik', $nik)->first();
            if ($user_karyawan && isset($data['nama_karyawan'])) {
                User::where('id', $user_karyawan->id_user)->update([
                    'name' => $data['nama_karyawan']
                ]);
            }
        }

        return $updated;
    }
}
