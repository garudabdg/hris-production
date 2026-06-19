<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Izinkeluar extends Model
{
    use HasFactory, \App\Traits\Auditable;
    
    protected $table = 'presensi_izinkeluar';
    protected $primaryKey = 'kode_izin_keluar';
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function getNextApprovalLayer()
    {
        // Feature Code untuk model ini adalah 'IZIN'
        $nextLevel = $this->approval_step;
        
        $kode_dept = $this->karyawan->kode_dept ?? null;

        $layer = ApprovalLayer::where('feature', 'IZIN')
            ->where('level', $nextLevel)
            ->where(function ($q) use ($kode_dept) {
                $q->where('kode_dept', $kode_dept)
                  ->orWhereNull('kode_dept');
            })
            ->first();

        return $layer;
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function driver()
    {
        return $this->belongsTo(Karyawan::class, 'driver_nik', 'nik');
    }

    public function kendaraan()
    {
        return $this->belongsTo(Asset::class, 'kode_asset_kendaraan', 'kode_asset');
    }
}
