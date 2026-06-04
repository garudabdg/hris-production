<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaryawanPelatihan extends Model
{
    use HasFactory;

    protected $table = 'karyawan_pelatihan';

    protected $fillable = [
        'nik',
        'nama_pelatihan',
        'tanggal_pelatihan',
        'tanggal_expired',
        'file_sertifikat',
    ];

    protected $casts = [
        'tanggal_pelatihan' => 'date',
        'tanggal_expired' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
