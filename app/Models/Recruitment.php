<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    use HasFactory;

    protected $table = 'recruitments';

    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir'        => 'date',
        'tanggal_melamar'      => 'date',
        'tanggal_tersedia'     => 'date',
        'tanggal_interview'    => 'date',
        'konfirmasi_at'        => 'datetime',
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

    public function prosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh', 'id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => '<span class="badge bg-secondary">Pending</span>',
            'review'    => '<span class="badge bg-info">Review</span>',
            'interview' => '<span class="badge bg-warning text-dark">Interview</span>',
            'offering'  => '<span class="badge bg-primary">Penawaran</span>',
            'diterima'  => '<span class="badge bg-success">Diterima</span>',
            'ditolak'   => '<span class="badge bg-danger">Ditolak</span>',
            default     => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }

    public static function generateKode(): string
    {
        $prefix = 'RCT-' . date('Ym') . '-';
        $last = self::where('kode_recruitment', 'like', $prefix . '%')
            ->orderByDesc('kode_recruitment')
            ->first();
        $num = $last ? ((int) substr($last->kode_recruitment, -4)) + 1 : 1;
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
