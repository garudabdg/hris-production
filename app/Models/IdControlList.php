<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdControlList extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'nama_aplikasi',
        'role',
        'nama_pengguna',
        'division',
        'location',
        'type_id',
        'remarks',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nama_pengguna', 'nik');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'location', 'kode_cabang');
    }
}
