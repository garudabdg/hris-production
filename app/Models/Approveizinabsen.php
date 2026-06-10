<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approveizinabsen extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = 'presensi_izinabsen_approve';
    protected $primaryKey = 'id_presensi';
    public $incrementing = false;
    protected $guarded = [];
}
