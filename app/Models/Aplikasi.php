<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aplikasi extends Model
{
    protected $table = 'aplikasis';
    protected $primaryKey = 'kode_aplikasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_aplikasi',
        'nama_aplikasi',
    ];
}
