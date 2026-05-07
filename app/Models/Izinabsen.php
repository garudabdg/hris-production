<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApprovalLayer;

class Izinabsen extends Model
{
    use HasFactory;
    protected $table = 'presensi_izinabsen';
    protected $primaryKey = 'kode_izin';
    public $incrementing = false;
    protected $guarded = [];

    public function getNextApprovalLayer()
    {
        $nextLevel    = $this->approval_step;
        $kode_dept    = $this->kode_dept    ?? null;
        $kode_cabang  = $this->kode_cabang  ?? null;
        $kode_jabatan = $this->kode_jabatan ?? null;

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

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function isWaitingFor($roleName)
    {
        if ($this->status != 0) return false;
        
        $nextLayer = $this->getNextApprovalLayer();
        if (!$nextLayer) return false;

        return $nextLayer->role_name === $roleName;
    }
}
