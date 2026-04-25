<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApprovalLayer;

class Lembur extends Model
{
    use HasFactory;
    protected $table = 'lembur';
    protected $fillable = [
        'nik',
        'tanggal',
        'lembur_mulai',
        'lembur_selesai',
        'keterangan',
        'status',
        'approval_step',
        'lembur_in',
        'lembur_out',
        'foto_lembur_in',
        'foto_lembur_out',
        'lokasi_lembur_in',
        'lokasi_lembur_out',
    ];

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function getNextApprovalLayer()
    {
        $nextLevel = $this->approval_step;
        $kode_dept    = $this->kode_dept    ?? null;
        $kode_jabatan = $this->kode_jabatan ?? null;
        $kode_cabang  = $this->kode_cabang  ?? null;

        $layer = ApprovalLayer::where('feature', 'IZIN')
            ->where('level', $nextLevel)
            ->where(function ($q) use ($kode_dept) {
                $q->where('kode_dept', $kode_dept)->orWhereNull('kode_dept');
            })
            ->first();

        return $layer;
    }
}
