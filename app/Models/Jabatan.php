<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = "jabatan";
    protected $primaryKey = "kode_jabatan";
    public $incrementing = false;
    protected $guarded = [];

    public function kpi_indicator()
    {
        return $this->hasOne(KpiIndicator::class, 'kode_jabatan', 'kode_jabatan');
    }
}
