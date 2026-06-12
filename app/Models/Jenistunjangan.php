<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jenistunjangan extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = "jenis_tunjangan";
    protected $primaryKey = "kode_jenis_tunjangan";
    protected $guarded = [];
    public $incrementing = false;
}
