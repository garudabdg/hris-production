<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    use KaryawanApiHelper;

    public function profil(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan     = Karyawan::where('karyawan.nik', $userkaryawan->nik)
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        return response()->json([
            'success' => true,
            'data' => array_merge($this->formatKaryawan($karyawan), [
                'no_ktp'         => $karyawan->no_ktp,
                'no_hp'          => $karyawan->no_hp,
                'alamat'         => $karyawan->alamat,
                'tanggal_lahir'  => $karyawan->tanggal_lahir,
                'tempat_lahir'   => $karyawan->tempat_lahir,
                'jenis_kelamin'  => $karyawan->jenis_kelamin,
                'tanggal_masuk'  => $karyawan->tanggal_masuk,
                'status_karyawan'=> $karyawan->status_karyawan,
                'email'          => $user->email,
                'username'       => $user->username,
                'foto_url'       => !empty($karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $karyawan->foto)
                    ? url(Storage::url('karyawan/' . $karyawan->foto))
                    : null,
            ]),
        ]);
    }

    public function updateProfil(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan     = Karyawan::where('nik', $userkaryawan->nik)->first();

        $request->validate([
            'nama_karyawan' => 'required|string|max:100',
            'no_ktp'        => 'nullable|string|max:20',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:500',
            'tanggal_lahir' => 'nullable|date',
            'foto'          => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'email'         => 'nullable|email|max:100',
        ]);

        try {
            $data_foto = [];
            if ($request->hasFile('foto')) {
                $foto_name            = $karyawan->nik . '.' . $request->file('foto')->getClientOriginalExtension();
                $destination_foto_path = '/public/karyawan';
                $data_foto             = ['foto' => $foto_name];

                if (!Storage::exists($destination_foto_path)) {
                    Storage::makeDirectory($destination_foto_path, 0775, true);
                }
                Storage::delete($destination_foto_path . '/' . $karyawan->foto);
                $request->file('foto')->storeAs($destination_foto_path, $foto_name);
            }

            Karyawan::where('nik', $karyawan->nik)->update(array_merge([
                'nama_karyawan' => $request->nama_karyawan,
                'no_ktp'        => $request->no_ktp,
                'no_hp'         => $request->no_hp,
                'alamat'        => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
            ], $data_foto));

            User::where('id', $user->id)->update([
                'name'  => $request->nama_karyawan,
                'email' => $request->email ?? $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data.',
            ], 500);
        }
    }
}
