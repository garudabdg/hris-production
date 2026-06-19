<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Daily Report BU Nasabah
 * 
 * Menyimpan pengolahan data calon nasabah.
 * Jumlah baris dinamis — bisa ditambah/hapus oleh user.
 * Status lead: cold, warm, hot.
 */
class DailyReportBuNasabah extends Model
{
    use HasFactory;

    protected $table = 'daily_report_bu_nasabah';

    protected $fillable = [
        'daily_report_bu_id',
        'nama',
        'akun_sosial_media',
        'no_whatsapp',
        'status_lead',
        'keterangan',
    ];

    /**
     * Relasi ke header report
     */
    public function dailyReport()
    {
        return $this->belongsTo(DailyReportBu::class, 'daily_report_bu_id');
    }
}
