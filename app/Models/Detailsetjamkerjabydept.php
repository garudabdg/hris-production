<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detailsetjamkerjabydept extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = 'presensi_jamkerja_bydept_detail';
    protected $guarded = [];
}
