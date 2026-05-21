<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'kode_asset',
        'nama_asset',
        'category_id',
        'kode_cabang',
        'merk',
        'no_seri',
        'kondisi',
        'status',
        'tanggal_perolehan',
        'nilai_perolehan',
        'jumlah_stok',
        'deskripsi',
        'foto',
        'lokasi',
        'catatan',
        'confidentiality',
        'availability',
        'integrity',
        'asset_valuation',
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
        'nilai_perolehan'   => 'decimal:2',
        'confidentiality'   => 'integer',
        'availability'      => 'integer',
        'integrity'         => 'integer',
        'asset_valuation'   => 'integer',
    ];

    /**
     * Label teks untuk nilai C/A/I: 1=Low, 2=Medium, 3=High
     */
    public static function valuationLabel(?int $value): string
    {
        return match($value) {
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            default => '-',
        };
    }

    /**
     * Label total asset valuation berdasarkan skor:
     * 3-4 = Low, 5-6 = Medium, 7-9 = High
     */
    public static function valuationTotalLabel(?int $score): string
    {
        if ($score === null) return '-';
        if ($score <= 4) return 'Low';
        if ($score <= 6) return 'Medium';
        return 'High';
    }

    public function getAssetValuationBadgeAttribute(): string
    {
        $score = $this->asset_valuation;
        if ($score === null) return '<span class="badge bg-label-secondary">-</span>';
        $label = self::valuationTotalLabel($score);
        $class = match($label) {
            'Low'    => 'bg-label-info',
            'Medium' => 'bg-label-warning',
            'High'   => 'bg-label-danger',
            default  => 'bg-label-secondary',
        };
        return "<span class=\"badge {$class}\">{$label} ({$score})</span>";
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'kode_cabang', 'kode_cabang');
    }

    public function transactions()
    {
        return $this->hasMany(AssetTransaction::class, 'kode_asset', 'kode_asset');
    }

    public function perawatan()
    {
        return $this->hasMany(\App\Models\AssetPerawatan::class, 'kode_asset', 'kode_asset');
    }

    public function getKondisiBadgeAttribute(): string
    {
        return match($this->kondisi) {
            'baik'            => '<span class="badge bg-label-success">Baik</span>',
            'rusak'           => '<span class="badge bg-label-danger">Rusak</span>',
            'dalam_perbaikan' => '<span class="badge bg-label-warning">Dalam Perbaikan</span>',
            default           => '<span class="badge bg-label-secondary">-</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'tersedia'   => '<span class="badge bg-label-success">Tersedia</span>',
            'dipinjam'   => '<span class="badge bg-label-warning">Dipinjam</span>',
            'tidak_aktif'=> '<span class="badge bg-label-secondary">Tidak Aktif</span>',
            default      => '<span class="badge bg-label-secondary">-</span>',
        };
    }
}
