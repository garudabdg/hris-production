<?php

namespace App\Http\Controllers\Api\Karyawan;

use Illuminate\Support\Facades\Storage;

trait KaryawanApiHelper
{
    protected function formatKaryawan($karyawan): array
    {
        if (!$karyawan) return [];

        return [
            'nik'          => $karyawan->nik,
            'nik_show'     => $karyawan->nik_show ?? $karyawan->nik,
            'nama'         => $karyawan->nama_karyawan,
            'jabatan'      => $karyawan->nama_jabatan ?? null,
            'departemen'   => $karyawan->nama_dept    ?? null,
            'cabang'       => $karyawan->nama_cabang  ?? null,
            'kode_dept'    => $karyawan->kode_dept    ?? null,
            'tanggal_lahir'=> $karyawan->tanggal_lahir ?? null,
            'foto_url'     => !empty($karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $karyawan->foto)
                ? url(Storage::url('karyawan/' . $karyawan->foto))
                : null,
        ];
    }
}
