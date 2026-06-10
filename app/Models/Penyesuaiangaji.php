<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyesuaiangaji extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = "karyawan_penyesuaian_gaji";
    protected $guarded = [];
    protected $primaryKey = "kode_penyesuaian_gaji";
    public $incrementing = false;
}
