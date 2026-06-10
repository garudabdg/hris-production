<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detailpenyesuaiangaji extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = "karyawan_penyesuaian_gaji_detail";
    protected $guarded = [];
}
