<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentVacancy extends Model
{
    protected $table = 'recruitment_vacancies';
    protected $guarded = [];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'kode_cabang', 'kode_cabang');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'kode_dept', 'kode_dept');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'kode_jabatan', 'kode_jabatan');
    }

    public function recruitments()
    {
        return $this->hasMany(Recruitment::class, 'posisi_dilamar', 'posisi');
    }

    public function getPelamarCountAttribute(): int
    {
        return Recruitment::where('posisi_dilamar', $this->posisi)
            ->where('kode_cabang', $this->kode_cabang)
            ->count();
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'buka'
            ? '<span class="badge bg-success">Buka</span>'
            : '<span class="badge bg-danger">Tutup</span>';
    }

    public function isBuka(): bool
    {
        if ($this->status !== 'buka') return false;
        if ($this->deadline && $this->deadline->isPast()) return false;
        return true;
    }
}
