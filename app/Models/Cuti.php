<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = "cuti";
    protected $primaryKey = "kode_cuti";
    public $incrementing = false;
    protected $guarded = [];
}
