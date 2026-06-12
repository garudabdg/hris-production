<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gajipokok extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = "karyawan_gaji_pokok";
    protected $primaryKey = "kode_gaji";
    protected $guarded = [];
    public $incrementing = false;
}
