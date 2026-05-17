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
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
        'nilai_perolehan'   => 'decimal:2',
    ];

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
