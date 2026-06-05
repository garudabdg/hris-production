<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ItTicket extends Model
{
    protected $fillable = [
        'nomor_tiket', 'nomor_urut', 'pemohon_id', 'kode_cabang', 'lokasi', 'judul', 'deskripsi',
        'kategori', 'prioritas', 'klasifikasi_data', 'dampak', 'status',
        'assigned_to', 'assigned_at', 'tanggal_target', 'resolved_at', 'resolved_by',
        'catatan_resolusi', 'lampiran',
    ];

    protected $casts = [
        'tanggal_target' => 'date',
        'assigned_at'    => 'datetime',
        'resolved_at'    => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'kode_cabang', 'kode_cabang');
    }

    public function responses()
    {
        return $this->hasMany(ItTicketResponse::class, 'ticket_id')->orderBy('created_at');
    }

    // ── Accessors / Badge ─────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        $map = [
            'open'        => ['label' => 'Open',        'color' => 'primary'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'info'],
            'pending'     => ['label' => 'Pending',     'color' => 'warning'],
            'resolved'    => ['label' => 'Resolved',    'color' => 'success'],
            'closed'      => ['label' => 'Closed',      'color' => 'secondary'],
        ];
        $s = $map[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
        return "<span class=\"badge bg-{$s['color']}\">{$s['label']}</span>";
    }

    public function getPriorityBadgeAttribute(): string
    {
        $map = [
            'critical' => ['label' => 'Critical', 'color' => 'danger'],
            'high'     => ['label' => 'High',     'color' => 'warning'],
            'medium'   => ['label' => 'Medium',   'color' => 'info'],
            'low'      => ['label' => 'Low',      'color' => 'secondary'],
        ];
        $p = $map[$this->prioritas] ?? ['label' => $this->prioritas, 'color' => 'secondary'];
        return "<span class=\"badge bg-{$p['color']}\">{$p['label']}</span>";
    }

    public function getKlasifikasiBadgeAttribute(): string
    {
        $map = [
            'confidential' => ['label' => 'Confidential', 'color' => 'danger'],
            'internal'     => ['label' => 'Internal',     'color' => 'warning'],
            'public'       => ['label' => 'Public',       'color' => 'success'],
        ];
        $k = $map[$this->klasifikasi_data] ?? ['label' => $this->klasifikasi_data, 'color' => 'secondary'];
        return "<span class=\"badge bg-label-{$k['color']}\">{$k['label']}</span>";
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Generate nomor tiket: TKT-YYYYMM-XXXX
     */
    public static function generateNomor(): string
    {
        $prefix = 'TKT-' . now()->format('Ym') . '-';
        $last   = self::where('nomor_tiket', 'like', $prefix . '%')
                      ->orderByDesc('nomor_tiket')
                      ->value('nomor_tiket');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor urut antrean harian berdasarkan tanggal tiket dibuat
     */
    public static function generateNomorUrut(): int
    {
        $today = now()->toDateString();
        $max = self::whereDate('created_at', $today)->max('nomor_urut');
        return $max ? $max + 1 : 1;
    }

    /**
     * SLA target days berdasarkan prioritas (ISO 27001 incident response guideline)
     */
    public static function slaDays(string $prioritas): int
    {
        return match ($prioritas) {
            'critical' => 1,
            'high'     => 3,
            'medium'   => 7,
            'low'      => 14,
            default    => 7,
        };
    }

    public function isOverdue(): bool
    {
        return $this->tanggal_target
            && now()->gt($this->tanggal_target)
            && !in_array($this->status, ['resolved', 'closed']);
    }
}
