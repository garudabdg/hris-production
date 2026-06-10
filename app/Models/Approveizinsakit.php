<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approveizinsakit extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = 'presensi_izinsakit_approve';
    protected $guarded = [];
}
