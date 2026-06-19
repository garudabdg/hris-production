<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Daily Report BU Offline
 * 
 * Menyimpan aktivitas offline (appointment, CTO, canvasing).
 * Jumlah baris dinamis — bisa ditambah/hapus oleh user.
 */
class DailyReportBuOffline extends Model
{
    use HasFactory;

    protected $table = 'daily_report_bu_offline';

    protected $fillable = [
        'daily_report_bu_id',
        'tipe',
        'nama_prospek',
        'whatsapp',
        'alamat',
    ];

    /**
     * Relasi ke header report
     */
    public function dailyReport()
    {
        return $this->belongsTo(DailyReportBu::class, 'daily_report_bu_id');
    }
}
