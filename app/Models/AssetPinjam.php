<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPinjam extends Model
{
    use HasFactory;

    protected $table = 'asset_pinjam';

    protected $guarded = [];

    protected $casts = [
        'tanggal_pinjam'          => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_aktual'  => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'kode_asset', 'kode_asset');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    /**
     * Get the approval layer for current approval step.
     * Menggunakan logika prioritas: Cabang (100) > Dept (10) > Jabatan (1)
     */
    public function getNextApprovalLayer()
    {
        $nextLevel    = $this->approval_step;
        $kode_dept    = $this->kode_dept    ?? null;
        $kode_cabang  = $this->kode_cabang  ?? null;
        $kode_jabatan = $this->kode_jabatan ?? null;

        $layers = ApprovalLayer::where('feature', 'PINJAM')
            ->where('level', $nextLevel)
            ->get();

        $validLayers = $layers->filter(function ($layer) use ($kode_cabang, $kode_dept, $kode_jabatan) {
            $cabangMatch  = is_null($layer->kode_cabang)  || $layer->kode_cabang  === $kode_cabang;
            $deptMatch    = is_null($layer->kode_dept)    || $layer->kode_dept    === $kode_dept;
            $jabatanMatch = is_null($layer->kode_jabatan) || $layer->kode_jabatan === $kode_jabatan;
            return $cabangMatch && $deptMatch && $jabatanMatch;
        });

        if ($validLayers->isEmpty()) {
            return null;
        }

        return $validLayers->sortByDesc(function ($layer) {
            $score = 0;
            if (!is_null($layer->kode_cabang))  $score += 100;
            if (!is_null($layer->kode_dept))    $score += 10;
            if (!is_null($layer->kode_jabatan)) $score += 1;
            return $score;
        })->first();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match((int) $this->status) {
            0 => '<span class="badge bg-label-warning">Menunggu Persetujuan</span>',
            1 => '<span class="badge bg-label-primary">Sedang Dipinjam</span>',
            2 => '<span class="badge bg-label-danger">Ditolak</span>',
            3 => '<span class="badge bg-label-success">Dikembalikan</span>',
            default => '<span class="badge bg-label-secondary">-</span>',
        };
    }
}
