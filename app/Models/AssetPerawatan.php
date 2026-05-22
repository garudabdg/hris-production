<?php

namespace App\Models;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPerawatan extends Model
{
    use HasFactory;

    protected $table = 'asset_perawatan';

    protected $fillable = [
        'kode_perawatan',
        'kode_asset',
        'tanggal_perawatan',
        'petugas',
        'catatan',
        'id_user',
    ];

    protected $casts = [
        'tanggal_perawatan' => 'date',
    ];

    /**
     * Daftar item checklist per kategori.
     */
    public static function checklistItems(AssetCategory|array|string|null $category = null): array
    {
        if ($category instanceof AssetCategory) {
            if (!empty($category->checklist_items)) {
                return $category->checklist_items;
            }
            $kategori = strtolower($category->nama_kategori);
        } elseif (is_array($category)) {
            return $category;
        } else {
            $kategori = strtolower((string) ($category ?? ''));
        }

        if (str_contains($kategori, 'komputer') || str_contains($kategori, 'computer') || str_contains($kategori, 'laptop') || str_contains($kategori, 'pc')) {
            return [
                'Cleaning',
                'Disk Capacity',
                'Power Ups',
                'Perangkat Lunak',
                'Kabel & Konektor',
                'Uji Fungsi Penyimpanan',
                'Antivirus Update',
                'Katasandi',
                'Backup & Restore',
                'Performa',
            ];
        }

        // Default generic items untuk kategori lain
        return [
            'Kondisi Fisik',
            'Kebersihan',
            'Fungsi Utama',
            'Kelengkapan Aksesori',
            'Keamanan',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'kode_asset', 'kode_asset');
    }

    public function items()
    {
        return $this->hasMany(AssetPerawatanItem::class, 'asset_perawatan_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user');
    }

    public function getHasilKeseluruhanAttribute(): string
    {
        $items = $this->items;
        if ($items->isEmpty()) {
            return '-';
        }
        if ($items->where('klasifikasi', 'rusak')->count() > 0) {
            return 'rusak';
        }
        if ($items->where('klasifikasi', 'cukup_baik')->count() > 0) {
            return 'cukup_baik';
        }
        return 'baik';
    }

    public function getHasilBadgeAttribute(): string
    {
        return match($this->hasil_keseluruhan) {
            'baik'       => '<span class="badge bg-label-success">Baik</span>',
            'cukup_baik' => '<span class="badge bg-label-warning">Cukup Baik</span>',
            'rusak'      => '<span class="badge bg-label-danger">Rusak</span>',
            default      => '<span class="badge bg-label-secondary">-</span>',
        };
    }
}
