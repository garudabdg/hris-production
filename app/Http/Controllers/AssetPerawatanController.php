<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetPerawatan;
use App\Models\AssetPerawatanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetPerawatanController extends Controller
{
    private function scopedAssetQuery()
    {
        $user  = auth()->user();
        $query = Asset::with(['category', 'cabang']);

        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (empty($userCabangs)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('kode_cabang', $userCabangs);
            }
        }

        return $query;
    }

    private function scopedQuery()
    {
        $user  = auth()->user();
        $query = AssetPerawatan::with(['asset.category', 'asset.cabang', 'user', 'items']);

        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (empty($userCabangs)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('asset', function ($q) use ($userCabangs) {
                    $q->whereIn('kode_cabang', $userCabangs);
                });
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.perawatan.index'), 403);

        $query = $this->scopedQuery();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_perawatan', 'like', '%' . $request->search . '%')
                  ->orWhere('petugas', 'like', '%' . $request->search . '%')
                  ->orWhereHas('asset', function ($qa) use ($request) {
                      $qa->where('nama_asset', 'like', '%' . $request->search . '%')
                         ->orWhere('kode_asset', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('kode_asset')) {
            $query->where('kode_asset', $request->kode_asset);
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal_perawatan', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_perawatan', '<=', $request->sampai);
        }

        $perawatans = $query->orderByDesc('tanggal_perawatan')->orderByDesc('id')->paginate(15)->withQueryString();

        $assets = $this->scopedAssetQuery()->orderBy('nama_asset')->get(['kode_asset', 'nama_asset']);

        $allForSummary = $this->scopedQuery()->get();
        $summary = [
            'total'      => $allForSummary->count(),
            'baik'       => $allForSummary->filter(fn($p) => $p->hasil_keseluruhan === 'baik')->count(),
            'cukup_baik' => $allForSummary->filter(fn($p) => $p->hasil_keseluruhan === 'cukup_baik')->count(),
            'rusak'      => $allForSummary->filter(fn($p) => $p->hasil_keseluruhan === 'rusak')->count(),
        ];

        return view('asset-perawatan.index', compact('perawatans', 'assets', 'summary'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.perawatan.create'), 403);

        $assets = $this->scopedAssetQuery()->orderBy('nama_asset')->get();
        $categories = AssetCategory::orderBy('nama_kategori')->get();

        $selectedAsset     = null;
        $checklistItems    = [];

        $selectedAssetCode = $request->filled('kode_asset') ? $request->kode_asset : session()->getOldInput('kode_asset');
        if ($selectedAssetCode) {
            $selectedAsset  = $assets->firstWhere('kode_asset', $selectedAssetCode);
            if ($selectedAsset && $selectedAsset->category) {
                $checklistItems = AssetPerawatan::checklistItems($selectedAsset->category->nama_kategori);
            } else {
                $checklistItems = AssetPerawatan::checklistItems('');
            }
        }

        return view('asset-perawatan.create', compact('assets', 'selectedAsset', 'checklistItems', 'categories'));
    }

    public function getChecklistItems(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.perawatan.create'), 403);

        $asset = Asset::with('category')->where('kode_asset', $request->kode_asset)->first();
        if (!$asset) {
            return response()->json(['items' => []]);
        }

        $items = AssetPerawatan::checklistItems($asset->category);
        $kategoriNama = $asset->category?->nama_kategori ?? '';

        return response()->json([
            'items'    => $items,
            'kategori' => $kategoriNama,
            'asset'    => [
                'kode_asset' => $asset->kode_asset,
                'nama_asset' => $asset->nama_asset,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.perawatan.create'), 403);

        $request->validate([
            'kode_asset'         => 'required|exists:assets,kode_asset',
            'tanggal_perawatan'  => 'required|date',
            'petugas'            => 'nullable|string|max:255',
            'catatan'            => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.item_name'  => 'required|string|max:255',
            'items.*.klasifikasi'=> 'required|in:baik,cukup_baik,rusak',
            'items.*.keterangan' => 'nullable|string|max:500',
        ]);

        // Generate kode_perawatan
        $last           = AssetPerawatan::orderByDesc('id')->first();
        $kodePerawatan  = buatkode($last?->kode_perawatan ?? '', 'PRW' . date('ym'), 4);

        DB::beginTransaction();
        try {
            $perawatan = AssetPerawatan::create([
                'kode_perawatan'    => $kodePerawatan,
                'kode_asset'        => $request->kode_asset,
                'tanggal_perawatan' => $request->tanggal_perawatan,
                'petugas'           => $request->petugas,
                'catatan'           => $request->catatan,
                'id_user'           => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                AssetPerawatanItem::create([
                    'asset_perawatan_id' => $perawatan->id,
                    'item_name'          => $item['item_name'],
                    'klasifikasi'        => $item['klasifikasi'],
                    'keterangan'         => $item['keterangan'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('asset-perawatan.index')
                ->with('success', 'Checklist perawatan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(AssetPerawatan $assetPerawatan)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.perawatan.index'), 403);

        $assetPerawatan->load(['asset.category', 'asset.cabang', 'user', 'items']);

        return view('asset-perawatan.show', compact('assetPerawatan'));
    }

    public function destroy(AssetPerawatan $assetPerawatan)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.perawatan.delete'), 403);

        $assetPerawatan->delete();

        return redirect()->route('asset-perawatan.index')
            ->with('success', 'Data perawatan berhasil dihapus.');
    }
}
