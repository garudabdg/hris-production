<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreatIntelligenceReport extends Model
{
    use HasFactory;

    protected $table = 'threat_intelligence_reports';

    protected $fillable = [
        'tanggal',
        'jenis_ancaman',
        'sumber_ancaman',
        'deskripsi_insiden',
        'dampak',
        'tindakan_yang_diambil',
        'status',
    ];
}
