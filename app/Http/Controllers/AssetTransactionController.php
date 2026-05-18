<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetTransaction;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetTransactionController extends Controller
{
    /**
     * Scope query transaksi berdasarkan akses cabang user.
     */
    private function scopedQuery()
    {
        $user  = auth()->user();
        $query = AssetTransaction::with(['asset.category', 'cabang', 'user']);

        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (empty($userCabangs)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('asset_transactions.kode_cabang', $userCabangs);
            }
        }

        return $query;
    }

    /**
     * Daftar transaksi barang in/out.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.transaksi.index'), 403);

        $query = $this->scopedQuery();

        // Filter: search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_transaksi', 'like', '%' . $request->search . '%')
                  ->orWhere('penanggung_jawab', 'like', '%' . $request->search . '%')
                  ->orWhereHas('asset', function ($qa) use ($request) {
                      $qa->where('nama_asset', 'like', '%' . $request->search . '%')
                         ->orWhere('kode_asset', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter: tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter: cabang
        if ($request->filled('kode_cabang')) {
            $query->where('asset_transactions.kode_cabang', $request->kode_cabang);
        }

        // Filter: tanggal range
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->sampai);
        }

        $transactions = $query->orderByDesc('tanggal_transaksi')
                              ->orderByDesc('created_at')
                              ->paginate(15)
                              ->withQueryString();

        // Summary
        $summaryBase = $this->scopedQuery();
        $summary = [
            'total'      => (clone $summaryBase)->count(),
            'barang_in'  => (clone $summaryBase)->where('tipe', 'in')->count(),
            'barang_out' => (clone $summaryBase)->where('tipe', 'out')->count(),
        ];

        $cabang = $user->getCabang();

        return view('asset-transaksi.index', compact('transactions', 'summary', 'cabang'));
    }

    /**
     * Form tambah transaksi (dimuat dalam modal via AJAX).
     */
    public function create()
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.transaksi.create'), 403);

        // Asset tersedia sesuai akses cabang
        $qAsset = Asset::query()->with('category');
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (!empty($userCabangs)) {
                $qAsset->whereIn('kode_cabang', $userCabangs);
            }
        }
        $assets = $qAsset->orderBy('nama_asset')->get();
        $cabang = $user->getCabang();
        $categories = AssetCategory::orderBy('nama_kategori')->get();

        return view('asset-transaksi.create', compact('assets', 'cabang', 'categories'));
    }

    /**
     * Simpan transaksi baru + update stok.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.transaksi.create'), 403);

        $request->validate([
            'kode_asset'          => 'required_unless:kategori_transaksi,pembelian|nullable|exists:assets,kode_asset',
            'nama_asset_baru'     => 'required_if:kategori_transaksi,pembelian|nullable|string|max:255',
            'category_id_baru'    => 'nullable|exists:asset_categories,id',
            'merk_baru'           => 'nullable|string|max:100',
            'no_seri_baru'        => 'nullable|string|max:100',
            'nilai_perolehan_baru'=> 'nullable|numeric|min:0',
            'tipe'                => 'required|in:in,out',
            'kategori_transaksi'  => 'required|string|max:50',
            'jumlah'              => 'required|integer|min:1',
            'tanggal_transaksi'   => 'required|date',
            'kode_cabang'         => 'nullable|exists:cabang,kode_cabang',
            'penanggung_jawab'    => 'nullable|string|max:255',
            'catatan'             => 'nullable|string|max:1000',
            'foto_bukti'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Jika pembelian → buat asset baru terlebih dahulu
        if ($request->kategori_transaksi === 'pembelian') {
            // Generate kode_asset baru
            $lastAsset = Asset::orderByDesc('id')->first();
            $kodeAsset = buatkode($lastAsset?->kode_asset ?? '', 'AST', 5);

            $asset = Asset::create([
                'kode_asset'       => $kodeAsset,
                'nama_asset'       => $request->nama_asset_baru,
                'category_id'      => $request->category_id_baru ?: null,
                'kode_cabang'      => $request->kode_cabang ?: null,
                'merk'             => $request->merk_baru ?: null,
                'no_seri'          => $request->no_seri_baru ?: null,
                'nilai_perolehan'  => $request->nilai_perolehan_baru ?: null,
                'tanggal_perolehan'=> $request->tanggal_transaksi,
                'kondisi'          => 'baik',
                'status'           => 'tersedia',
                'jumlah_stok'      => 0, // akan di-increment setelah transaksi
            ]);

            $request->merge(['kode_asset' => $kodeAsset]);
        } else {
            $asset = Asset::where('kode_asset', $request->kode_asset)->firstOrFail();
        }

        // Validasi stok cukup untuk barang keluar (pembelian selalu 'in', skip)
        if ($request->tipe === 'out' && $asset->jumlah_stok < $request->jumlah) {
            return Redirect::back()
                ->withInput()
                ->with(messageError('Stok tidak cukup! Stok saat ini: ' . $asset->jumlah_stok . ' unit.'));
        }

        // Validasi akses cabang
        if (!$user->isSuperAdmin() && $request->filled('kode_cabang')) {
            $userCabangs = $user->getCabangCodes();
            if (!in_array($request->kode_cabang, $userCabangs)) {
                return Redirect::back()->with(messageError('Anda tidak memiliki akses ke cabang yang dipilih.'));
            }
        }

        // Generate kode_transaksi
        $prefix = $request->tipe === 'in' ? 'ATI' : 'ATO';
        $prefix .= date('ym');
        $last = AssetTransaction::where('tipe', $request->tipe)
            ->orderByDesc('id')
            ->first();
        $kode_transaksi = buatkode($last?->kode_transaksi ?? '', $prefix, 4);

        // Upload foto
        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $file = $request->file('foto_bukti');
            $filename = 'trx_' . Str::random(12) . '.' . $file->extension();
            $file->storeAs('asset-transaksi', $filename, 'public');
            $fotoPath = $filename;
        }

        DB::beginTransaction();
        try {
            AssetTransaction::create([
                'kode_transaksi'     => $kode_transaksi,
                'kode_asset'         => $request->kode_asset,
                'tipe'               => $request->tipe,
                'kategori_transaksi' => $request->kategori_transaksi,
                'jumlah'             => $request->jumlah,
                'tanggal_transaksi'  => $request->tanggal_transaksi,
                'kode_cabang'        => $request->kode_cabang,
                'penanggung_jawab'   => $request->penanggung_jawab,
                'catatan'            => $request->catatan,
                'foto_bukti'         => $fotoPath,
                'id_user'            => auth()->id(),
            ]);

            // Update stok
            if ($request->tipe === 'in') {
                $asset->increment('jumlah_stok', $request->jumlah);
            } else {
                $asset->decrement('jumlah_stok', $request->jumlah);
            }

            DB::commit();

            $tipeLabel = $request->tipe === 'in' ? 'Barang Masuk' : 'Barang Keluar';
            return Redirect::route('asset-transaksi.index')
                ->with(messageSuccess("Transaksi {$tipeLabel} berhasil dicatat. Kode: {$kode_transaksi}"));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Detail transaksi (dimuat dalam modal via AJAX).
     */
    public function show($id)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.transaksi.index'), 403);

        $transaction = AssetTransaction::with(['asset.category', 'asset.cabang', 'cabang', 'user'])
            ->findOrFail($id);

        return view('asset-transaksi.show', compact('transaction'));
    }

    /**
     * Hapus transaksi + rollback stok.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.transaksi.delete'), 403);

        $transaction = AssetTransaction::findOrFail($id);
        $asset = Asset::where('kode_asset', $transaction->kode_asset)->first();

        DB::beginTransaction();
        try {
            // Rollback stok
            if ($asset) {
                if ($transaction->tipe === 'in') {
                    $asset->decrement('jumlah_stok', $transaction->jumlah);
                } else {
                    $asset->increment('jumlah_stok', $transaction->jumlah);
                }
            }

            // Hapus foto
            if ($transaction->foto_bukti && Storage::disk('public')->exists('asset-transaksi/' . $transaction->foto_bukti)) {
                Storage::disk('public')->delete('asset-transaksi/' . $transaction->foto_bukti);
            }

            $transaction->delete();

            DB::commit();
            return Redirect::back()->with(messageSuccess('Transaksi berhasil dihapus dan stok dikembalikan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
