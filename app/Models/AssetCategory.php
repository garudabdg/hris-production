<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'asset_categories';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'deskripsi',
        'checklist_items',
    ];

    protected $casts = [
        'checklist_items' => 'array',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
