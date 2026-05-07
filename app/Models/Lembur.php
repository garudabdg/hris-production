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
        $nextLevel    = $this->approval_step;
        $kode_dept    = $this->kode_dept    ?? null;
        $kode_jabatan = $this->kode_jabatan ?? null;
        $kode_cabang  = $this->kode_cabang  ?? null;

        // Ambil semua kandidat layer yang cocok untuk level ini
        $layers = ApprovalLayer::where('feature', 'IZIN')
            ->where('level', $nextLevel)
            ->get();

        $validLayers = $layers->filter(function ($layer) use ($kode_cabang, $kode_dept, $kode_jabatan) {
            $cabangMatch   = is_null($layer->kode_cabang)  || $layer->kode_cabang  === $kode_cabang;
            $deptMatch     = is_null($layer->kode_dept)    || $layer->kode_dept    === $kode_dept;
            $jabatanMatch  = is_null($layer->kode_jabatan) || $layer->kode_jabatan === $kode_jabatan;
            return $cabangMatch && $deptMatch && $jabatanMatch;
        });

        if ($validLayers->isEmpty()) {
            return null;
        }

        // Prioritas: Cabang (100) > Dept (10) > Jabatan (1)
        return $validLayers->sortByDesc(function ($layer) {
            $score = 0;
            if (!is_null($layer->kode_cabang))  $score += 100;
            if (!is_null($layer->kode_dept))    $score += 10;
            if (!is_null($layer->kode_jabatan)) $score += 1;
            return $score;
        })->first();
    }
}
