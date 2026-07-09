<?php

namespace App\Http\Controllers;

use App\Exports\AssetExport;
use App\Exports\AssetTemplateExport;
use App\Imports\AssetImport;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Cabang;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AssetController extends Controller
{
    /**
     * Scope query assets berdasarkan akses cabang user.
     * Super admin bisa lihat semua, user lain hanya cabangnya sendiri.
     */
    private function scopedQuery()
    {
        $user  = auth()->user();
        $query = Asset::with(['category', 'cabang', 'pic']);

        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (empty($userCabangs)) {
                $query->whereRaw('1 = 0'); // tidak ada akses
            } else {
                $query->whereIn('kode_cabang', $userCabangs);
            }
        }

        return $query;
    }

    /**
     * Cek apakah user boleh akses asset tertentu.
     */
    private function authorizeAsset(Asset $asset)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (!in_array($asset->kode_cabang, $userCabangs)) {
                abort(403, 'Anda tidak memiliki akses ke aset ini.');
            }
        }
    }

    public function index(Request $request)
    {
        $user  = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.index'), 403);
        $query = $this->scopedQuery();

        $this->applyFilters($query, $request);

        $assets     = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $categories = AssetCategory::orderBy('nama_kategori')->get();
        $cabang     = $user->getCabang();
        $lokasiList = $this->scopedQuery()->whereNotNull('lokasi')
                                    ->where('lokasi', '!=', '')
                                    ->select('lokasi')
                                    ->distinct()
                                    ->orderBy('lokasi')
                                    ->pluck('lokasi');

        $summary = $this->getSummaryStats();

        return view('assets.index', compact('assets', 'categories', 'cabang', 'lokasiList', 'summary'));
    }

    public function create()
    {
        $user       = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.create'), 403);
        $categories = AssetCategory::orderBy('nama_kategori')->get();
        $cabang     = $user->getCabang();

        $qKaryawan = Karyawan::query()->where('status_aktif_karyawan', 1);
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (!empty($userCabangs)) {
                $qKaryawan->whereIn('kode_cabang', $userCabangs);
            }
        }
        $karyawan = $qKaryawan->orderBy('nama_karyawan')->get(['nik', 'nama_karyawan']);

        return view('assets.create', compact('categories', 'cabang', 'karyawan'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.create'), 403);

        $request->validate([
            'kode_asset'       => 'required|string|max:50|unique:assets,kode_asset',
            'nama_asset'       => 'required|string|max:255',
            'category_id'      => 'nullable|exists:asset_categories,id',
            'kode_cabang'      => 'nullable|exists:cabang,kode_cabang',
            'nik'              => 'nullable|exists:karyawan,nik',
            'merk'             => 'nullable|string|max:100',
            'no_seri'          => 'nullable|string|max:100',
            'kondisi'          => 'required|in:baik,rusak,dalam_perbaikan',
            'status'           => 'required|in:tersedia,dipinjam,tidak_aktif',
            'tanggal_perolehan'=> 'nullable|date',
            'expired_date'     => 'nullable|date',
            'nilai_perolehan'  => 'nullable|numeric|min:0',
            'jumlah_stok'      => 'nullable|integer|min:0',
            'lokasi'           => 'nullable|string|max:255',
            'deskripsi'        => 'nullable|string',
            'catatan'          => 'nullable|string',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'confidentiality'  => 'nullable|integer|in:1,2,3',
            'availability'     => 'nullable|integer|in:1,2,3',
            'integrity'        => 'nullable|integer|in:1,2,3',
        ]);

        $valuationScore = $this->calculateValuationScore($request);

        // Validasi akses cabang jika bukan super admin
        if ($error = $this->checkCabangAccess($user, $request->kode_cabang)) {
            return redirect()->back()->with('error', $error);
        }

        $data = $request->except('foto');
        $data['asset_valuation'] = $valuationScore;
        
        if ($filename = $this->handleFotoUpload($request)) {
            $data['foto'] = $filename;
        }

        Asset::create($data);

        return redirect()->route('assets.index')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function show(Asset $asset)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.show'), 403);
        $this->authorizeAsset($asset);
        $asset->load(['category', 'cabang', 'pic']);

        $activePinjam = null;
        if ($asset->status === 'dipinjam') {
            $activePinjam = \App\Models\AssetPinjam::with('karyawan')
                ->where('kode_asset', $asset->kode_asset)
                ->where('status', 1) // 1 = Sedang Dipinjam
                ->first();
        }

        $pinjamList = \App\Models\AssetPinjam::with('karyawan')
            ->where('kode_asset', $asset->kode_asset)
            ->orderByDesc('tanggal_pinjam')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('assets.show', compact('asset', 'activePinjam', 'pinjamList'));
    }

    public function edit(Asset $asset)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.edit'), 403);
        $this->authorizeAsset($asset);
        $user       = auth()->user();
        $categories = AssetCategory::orderBy('nama_kategori')->get();
        $cabang     = $user->getCabang();

        $qKaryawan = Karyawan::query()->where('status_aktif_karyawan', 1);
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (!empty($userCabangs)) {
                $qKaryawan->whereIn('kode_cabang', $userCabangs);
            }
        }
        $karyawan = $qKaryawan->orderBy('nama_karyawan')->get(['nik', 'nama_karyawan']);

        return view('assets.edit', compact('asset', 'categories', 'cabang', 'karyawan'));
    }

    public function update(Request $request, Asset $asset)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.edit'), 403);
        $this->authorizeAsset($asset);

        $request->validate([
            'kode_asset'       => 'required|string|max:50|unique:assets,kode_asset,' . $asset->id,
            'nama_asset'       => 'required|string|max:255',
            'category_id'      => 'nullable|exists:asset_categories,id',
            'kode_cabang'      => 'nullable|exists:cabang,kode_cabang',
            'nik'              => 'nullable|exists:karyawan,nik',
            'merk'             => 'nullable|string|max:100',
            'no_seri'          => 'nullable|string|max:100',
            'kondisi'          => 'required|in:baik,rusak,dalam_perbaikan',
            'status'           => 'required|in:tersedia,dipinjam,tidak_aktif',
            'tanggal_perolehan'=> 'nullable|date',
            'expired_date'     => 'nullable|date',
            'nilai_perolehan'  => 'nullable|numeric|min:0',
            'jumlah_stok'      => 'nullable|integer|min:0',
            'lokasi'           => 'nullable|string|max:255',
            'deskripsi'        => 'nullable|string',
            'catatan'          => 'nullable|string',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'confidentiality'  => 'nullable|integer|in:1,2,3',
            'availability'     => 'nullable|integer|in:1,2,3',
            'integrity'        => 'nullable|integer|in:1,2,3',
        ]);
        $valuationScore = $this->calculateValuationScore($request);

        // Validasi akses cabang baru jika bukan super admin
        $user = auth()->user();
        if ($error = $this->checkCabangAccess($user, $request->kode_cabang)) {
            return redirect()->back()->with('error', $error);
        }

        $data = $request->except('foto');
        $data['asset_valuation'] = $valuationScore;

        if ($filename = $this->handleFotoUpload($request, $asset->foto)) {
            $data['foto'] = $filename;
        }

        $asset->update($data);

        return redirect()->route('assets.index', request()->query())->with('success', 'Asset berhasil diperbarui.');
    }

    public function generateCode(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.create') || $user->can('asset.edit'), 403);

        $categoryId = $request->get('category_id');
        if (!$categoryId) {
            return response()->json(['code' => '']);
        }

        $category = AssetCategory::find($categoryId);
        if (!$category) {
            return response()->json(['code' => '']);
        }

        $newCode = $this->generateAssetCode($category);

        return response()->json(['code' => $newCode]);
    }

    public function destroy(Asset $asset)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.delete'), 403);
        $this->authorizeAsset($asset);

        if ($asset->foto && Storage::disk('public')->exists('assets/' . $asset->foto)) {
            Storage::disk('public')->delete('assets/' . $asset->foto);
        }
        $asset->delete();

        return redirect()->route('assets.index', request()->query())->with('success', 'Asset berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.delete'), 403);

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:assets,id',
        ]);

        $assets = Asset::whereIn('id', $request->ids)->get();

        foreach ($assets as $asset) {
            $this->authorizeAsset($asset);
            if ($asset->foto && Storage::disk('public')->exists('assets/' . $asset->foto)) {
                Storage::disk('public')->delete('assets/' . $asset->foto);
            }
            $asset->delete();
        }

        return redirect()->route('assets.index', $request->query())->with('success', count($assets) . ' Asset berhasil dihapus.');
    }

    public function bulkBarcode(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.show'), 403);
        
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:assets,id',
        ]);

        $assets = Asset::with(['category', 'cabang'])->whereIn('id', $request->ids)->get();

        return view('assets.bulk_barcode', compact('assets'));
    }

    public function barcode(Asset $asset)
    {
        return view('assets.barcode', compact('asset'));
    }

    // ── Kategori ────────────────────────────────────────────────────────────────

    public function kategoriIndex()
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori.index'), 403);
        $categories = AssetCategory::withCount('assets')->orderBy('nama_kategori')->paginate(20);
        return view('assets.kategori.index', compact('categories'));
    }

    public function kategoriStore(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori.create'), 403);
        $request->validate([
            'kode_kategori'   => 'required|string|max:50|unique:asset_categories,kode_kategori',
            'nama_kategori'   => 'required|string|max:100|unique:asset_categories,nama_kategori',
            'deskripsi'       => 'nullable|string',
            'checklist_items' => 'nullable|string',
        ]);

        AssetCategory::create([
            'kode_kategori'   => $request->kode_kategori,
            'nama_kategori'   => $request->nama_kategori,
            'deskripsi'       => $request->deskripsi,
            'checklist_items' => $this->parseChecklistItems($request->checklist_items),
        ]);

        return redirect()->route('assets.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function kategoriUpdate(Request $request, AssetCategory $category)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori.edit'), 403);
        $request->validate([
            'kode_kategori'   => 'required|string|max:50|unique:asset_categories,kode_kategori,' . $category->id,
            'nama_kategori'   => 'required|string|max:100|unique:asset_categories,nama_kategori,' . $category->id,
            'deskripsi'       => 'nullable|string',
            'checklist_items' => 'nullable|string',
        ]);

        $category->update([
            'kode_kategori'   => $request->kode_kategori,
            'nama_kategori'   => $request->nama_kategori,
            'deskripsi'       => $request->deskripsi,
            'checklist_items' => $this->parseChecklistItems($request->checklist_items),
        ]);

        return redirect()->route('assets.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    private function parseChecklistItems(?string $value): array
    {
        if (!$value) {
            return [];
        }

        // Try to parse as JSON (from table form)
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_filter(array_map('trim', $decoded));
            }
        }

        // Fallback: parse as newline-separated (legacy textarea)
        $normalized = str_replace(["\r\n", "\r"], "\n", $value);
        $items = array_filter(array_map('trim', explode("\n", $normalized)));

        return array_values($items);
    }

    public function kategoriDestroy(AssetCategory $category)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori.delete'), 403);
        if ($category->assets()->count() > 0) {
            return redirect()->route('assets.kategori.index')->with('error', 'Kategori tidak bisa dihapus karena masih digunakan.');
        }
        $category->delete();
        return redirect()->route('assets.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.export'), 403);
        $filters             = $request->only(['search', 'category_id', 'kode_cabang', 'kondisi', 'status']);
        $user                = auth()->user();
        $filters['_scoped']  = !$user->isSuperAdmin();
        $filters['_cabangs'] = $user->isSuperAdmin() ? [] : $user->getCabangCodes();

        $filename = 'daftar-aset-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new AssetExport($filters), $filename);
    }

    public function importTemplate()
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.import'), 403);
        $filename = 'template-import-aset.xlsx';
        return Excel::download(new AssetTemplateExport(), $filename);
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.import'), 403);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes'    => 'Format file harus xlsx, xls, atau csv.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $user           = auth()->user();
        $allowedCabangs = $user->isSuperAdmin() ? [] : $user->getCabangCodes();

        $import = new AssetImport($allowedCabangs);
        Excel::import($import, $request->file('file'));

        // Kumpulkan error dari validation failures (SkipsOnFailure)
        $validationErrors = collect($import->failures())->map(function ($f) {
            return "Baris {$f->row()}: " . implode(', ', $f->errors());
        })->toArray();

        $allErrors = array_merge($validationErrors, $import->errors);

        $msg = "Import selesai. Berhasil: {$import->imported} data";
        if ($import->skipped > 0) {
            $msg .= ", Dilewati: {$import->skipped} data";
        }

        if (!empty($allErrors)) {
            return redirect()->route('assets.index')
                ->with('success', $msg)
                ->with('import_errors', $allErrors);
        }

        return redirect()->route('assets.index')->with('success', $msg . '.');
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_asset', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_asset', 'like', '%' . $request->search . '%')
                  ->orWhere('merk', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('kode_cabang')) {
            $query->where('kode_cabang', $request->kode_cabang);
        }
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('lokasi')) {
            $query->where('lokasi', $request->lokasi);
        }
    }

    private function getSummaryStats()
    {
        $summaryQuery = $this->scopedQuery();
        return [
            'total'           => (clone $summaryQuery)->count(),
            'tersedia'        => (clone $summaryQuery)->where('status', 'tersedia')->count(),
            'dipinjam'        => (clone $summaryQuery)->where('status', 'dipinjam')->count(),
            'dalam_perbaikan' => (clone $summaryQuery)->where('kondisi', 'dalam_perbaikan')->count(),
        ];
    }

    private function calculateValuationScore(Request $request)
    {
        $c = $request->integer('confidentiality', 0);
        $a = $request->integer('availability', 0);
        $i = $request->integer('integrity', 0);
        return ($c && $a && $i) ? ($c + $a + $i) : null;
    }

    private function checkCabangAccess($user, $kodeCabang)
    {
        if (!$user->isSuperAdmin() && !empty($kodeCabang)) {
            $userCabangs = $user->getCabangCodes();
            if (!in_array($kodeCabang, $userCabangs)) {
                return 'Anda tidak memiliki akses ke cabang yang dipilih.';
            }
        }
        return null;
    }

    private function handleFotoUpload(Request $request, $oldFoto = null)
    {
        if ($request->filled('foto_base64')) {
            if ($oldFoto && Storage::disk('public')->exists('assets/' . $oldFoto)) {
                Storage::disk('public')->delete('assets/' . $oldFoto);
            }
            $imgData = $request->foto_base64;
            $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
            $imgData = str_replace('data:image/png;base64,', '', $imgData);
            $imgData = str_replace(' ', '+', $imgData);
            $imgRaw = base64_decode($imgData);
            
            $filename = 'asset_' . Str::random(12) . '.jpg';
            Storage::disk('public')->put('assets/' . $filename, $imgRaw);
            return $filename;
        }

        if ($request->hasFile('foto')) {
            if ($oldFoto && Storage::disk('public')->exists('assets/' . $oldFoto)) {
                Storage::disk('public')->delete('assets/' . $oldFoto);
            }
            $file = $request->file('foto');
            $ext = $file->extension() ?: $file->getClientOriginalExtension();
            if (!$ext) $ext = 'jpg';
            $filename = 'asset_' . Str::random(12) . '.' . $ext;
            $file->storeAs('assets', $filename, 'public');
            return $filename;
        }
        return null;
    }

    private function generateAssetCode(AssetCategory $category)
    {
        $catCode = strtoupper($category->kode_kategori);
        
        if (empty($catCode)) {
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $category->nama_kategori);
            $catCode = strtoupper(substr($cleanName, 0, 3));
            if (strlen($catCode) === 0) {
                $catCode = 'GEN';
            }
        }

        $prefix = 'AST-' . $catCode . '-';

        $lastAsset = Asset::where('kode_asset', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(kode_asset) DESC')
            ->orderBy('kode_asset', 'desc')
            ->first();

        return buatkode($lastAsset ? $lastAsset->kode_asset : '', $prefix, 4);
    }

    public function publicChecklist($kode)
    {
        $kode_dept = '';
        $user = auth()->user();
        if ($user && $user->userkaryawan) {
            $k = \App\Models\Karyawan::where('nik', $user->userkaryawan->nik)->first();
            if ($k) $kode_dept = $k->kode_dept;
        }
        if (!in_array($kode_dept, ['GA', 'IT', 'HRD'])) {
            abort(403, 'Akses ditolak. Fitur ini khusus untuk departemen GA, IT, dan HRD.');
        }

        $asset = Asset::with(['category', 'pic', 'cabang'])->where('kode_asset', $kode)->firstOrFail();
        
        $checklistItems = [];
        if ($asset->category) {
            $checklistItems = \App\Models\AssetPerawatan::checklistItems($asset->category);
        }
        return view('assets.public_checklist', compact('asset', 'checklistItems'));
    }

    public function publicChecklistUpdate(Request $request, $kode)
    {
        $kode_dept = '';
        $user = auth()->user();
        if ($user && $user->userkaryawan) {
            $k = \App\Models\Karyawan::where('nik', $user->userkaryawan->nik)->first();
            if ($k) $kode_dept = $k->kode_dept;
        }
        if (!in_array($kode_dept, ['GA', 'IT', 'HRD'])) {
            abort(403, 'Akses ditolak. Fitur ini khusus untuk departemen GA, IT, dan HRD.');
        }

        $asset = Asset::where('kode_asset', $kode)->firstOrFail();
        
        $request->validate([
            'nama_asset' => 'nullable|string|max:255',
            'merk' => 'nullable|string|max:255',
            'foto' => 'nullable|file|max:30720',
            'petugas' => 'required|string|max:255',
            'catatan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.klasifikasi' => 'required|in:baik,cukup_baik,rusak',
            'items.*.keterangan' => 'nullable|string|max:500',
        ]);
        
        // Generate kode_perawatan
        $last = \App\Models\AssetPerawatan::orderByDesc('id')->first();
        $kodePerawatan = buatkode($last?->kode_perawatan ?? '', 'PRW' . date('ym'), 4);
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Update Asset Name, Brand, and Photo
            if ($request->filled('nama_asset') || $request->filled('merk') || $request->hasFile('foto')) {
                $assetUpdate = [];
                if ($request->filled('nama_asset')) $assetUpdate['nama_asset'] = $request->nama_asset;
                if ($request->filled('merk')) $assetUpdate['merk'] = $request->merk;
                
                if ($filename = $this->handleFotoUpload($request, $asset->foto)) {
                    $assetUpdate['foto'] = $filename;
                }
                
                if (!empty($assetUpdate)) {
                    $asset->update($assetUpdate);
                }
            }

            $perawatan = \App\Models\AssetPerawatan::create([
                'kode_perawatan' => $kodePerawatan,
                'kode_asset' => $asset->kode_asset,
                'tanggal_perawatan' => now()->format('Y-m-d'),
                'petugas' => $request->petugas,
                'catatan' => $request->catatan,
                'id_user' => auth()->id() ?? null,
            ]);
            
            $itemsData = [];
            foreach ($request->items as $item) {
                $itemsData[] = [
                    'asset_perawatan_id' => $perawatan->id,
                    'item_name' => $item['item_name'],
                    'klasifikasi' => $item['klasifikasi'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            \App\Models\AssetPerawatanItem::insert($itemsData);
            
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        return redirect()->back()->with('success', 'Kondisi aset berhasil dilaporkan!');
    }
}
