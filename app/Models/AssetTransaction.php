<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTransaction extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'asset_transactions';

    protected $fillable = [
        'kode_transaksi',
        'kode_asset',
        'tipe',
        'kategori_transaksi',
        'jumlah',
        'tanggal_transaksi',
        'kode_cabang',
        'penanggung_jawab',
        'catatan',
        'foto_bukti',
        'id_user',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'kode_asset', 'kode_asset');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'kode_cabang', 'kode_cabang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getTipeBadgeAttribute(): string
    {
        return match ($this->tipe) {
            'in'  => '<span class="badge bg-label-success"><i class="ti ti-package-import me-1"></i>Barang Masuk</span>',
            'out' => '<span class="badge bg-label-danger"><i class="ti ti-package-export me-1"></i>Barang Keluar</span>',
            default => '<span class="badge bg-label-secondary">-</span>',
        };
    }

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori_transaksi) {
            'pembelian'       => 'Pembelian',
            'donasi_masuk'    => 'Donasi / Hibah',
            'retur'           => 'Retur / Pengembalian',
            'transfer_masuk'  => 'Transfer Masuk',
            'pengeluaran'     => 'Pengeluaran / Pemakaian',
            'rusak'           => 'Rusak / Disposal',
            'donasi_keluar'   => 'Donasi Keluar',
            'transfer_keluar' => 'Transfer Keluar',
            default           => ucwords(str_replace('_', ' ', $this->kategori_transaksi)),
        };
    }
}
