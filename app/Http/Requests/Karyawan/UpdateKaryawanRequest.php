<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKaryawanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nik_show' => 'required',
            'no_ktp' => 'required|string|max:20',
            'nama_karyawan' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'jenis_kelamin' => 'required',
            'no_hp' => 'required|string|regex:/^0[0-9]{9,12}$/',
            'kode_status_kawin' => 'required',
            'pendidikan_terakhir' => 'required',
            'kode_cabang' => 'required',
            'kode_dept' => 'required',
            'kode_jabatan' => 'required',
            'tanggal_masuk' => 'required',
            'status_karyawan' => 'required',
            'sub_departemen' => 'nullable|string',
            'tanggal_nonaktif' => 'nullable|date',
            'foto' => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'lock_location' => 'nullable',
            'lock_jam_kerja' => 'nullable',
            'status_aktif_karyawan' => 'nullable',
            'rfid_uid' => 'nullable',
            'pin' => 'nullable'
        ];
    }
}
