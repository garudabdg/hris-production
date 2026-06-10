<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiIndicatorDetail extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'kpi_indicator_details';
    protected $guarded = ['id'];

    public function indicator()
    {
        return $this->belongsTo(KpiIndicator::class, 'kpi_indicator_id');
    }
}
