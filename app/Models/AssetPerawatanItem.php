<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPerawatanItem extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'asset_perawatan_items';

    protected $fillable = [
        'asset_perawatan_id',
        'item_name',
        'klasifikasi',
        'keterangan',
    ];

    public function perawatan()
    {
        return $this->belongsTo(AssetPerawatan::class, 'asset_perawatan_id');
    }

    public function getKlasifikasiBadgeAttribute(): string
    {
        return match($this->klasifikasi) {
            'baik'       => '<span class="badge bg-label-success">Baik</span>',
            'cukup_baik' => '<span class="badge bg-label-warning">Cukup Baik</span>',
            'rusak'      => '<span class="badge bg-label-danger">Rusak</span>',
            default      => '<span class="badge bg-label-secondary">-</span>',
        };
    }

    public function getKlasifikasiLabelAttribute(): string
    {
        return match($this->klasifikasi) {
            'baik'       => 'Baik',
            'cukup_baik' => 'Cukup Baik',
            'rusak'      => 'Rusak',
            default      => '-',
        };
    }
}
