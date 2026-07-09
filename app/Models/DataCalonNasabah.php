<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCalonNasabah extends Model
{
    use HasFactory;

    protected $table = 'data_calon_nasabah';

    protected $fillable = [
        'nik',
        'tanggal',
        'nama',
        'akun_sosial_media',
        'no_whatsapp',
        'status_lead',
        'keterangan',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
