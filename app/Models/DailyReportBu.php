<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Daily Report BU (Header)
 * 
 * Menyimpan header daily report karyawan divisi Business (BU).
 * Setiap karyawan hanya boleh 1 report per hari (unique nik+tanggal).
 */
class DailyReportBu extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'daily_report_bu';

    protected $fillable = [
        'nik',
        'tanggal',
        'sub_departemen',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke Karyawan (pemilik report)
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    /**
     * Relasi ke aktivitas online (4 platform: FB, IG, WA, TikTok)
     */
    public function onlineActivities()
    {
        return $this->hasMany(DailyReportBuOnline::class, 'daily_report_bu_id');
    }

    /**
     * Relasi ke aktivitas offline (appointment, CTO, canvasing)
     */
    public function offlineActivities()
    {
        return $this->hasMany(DailyReportBuOffline::class, 'daily_report_bu_id');
    }

    /**
     * Data calon nasabah yang terhubung dengan report ini (berdasarkan NIK dan Tanggal)
     */
    public function getNasabahDataAttribute()
    {
        return \App\Models\DataCalonNasabah::where('nik', $this->nik)
            ->where('tanggal', $this->tanggal)
            ->get();
    }

    /**
     * Hitung total aktivitas online (sum semua kolom angka)
     */
    public function getTotalOnlineAttribute()
    {
        return $this->onlineActivities->sum(function ($item) {
            return $item->posting + $item->share_group + $item->add_group
                + $item->add_friend + $item->inbox + $item->story
                + $item->broadcast + $item->fanspage;
        });
    }
}
