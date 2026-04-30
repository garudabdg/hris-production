<?php

namespace App\Http\Controllers;

use App\Exports\AssetExport;
use App\Exports\AssetTemplateExport;
use App\Imports\AssetImport;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Cabang;
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
        $query = Asset::with(['category', 'cabang']);

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

        $assets     = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $categories = AssetCategory::orderBy('nama_kategori')->get();
        $cabang     = $user->getCabang();

        // Summary hanya untuk cabang yang diakses user
        $summaryQuery = $this->scopedQuery();
        $summary = [
            'total'           => (clone $summaryQuery)->count(),
            'tersedia'        => (clone $summaryQuery)->where('status', 'tersedia')->count(),
            'dipinjam'        => (clone $summaryQuery)->where('status', 'dipinjam')->count(),
            'dalam_perbaikan' => (clone $summaryQuery)->where('kondisi', 'dalam_perbaikan')->count(),
        ];

        return view('assets.index', compact('assets', 'categories', 'cabang', 'summary'));
    }

    public function create()
    {
        $user       = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->can('asset.create'), 403);
        $categories = AssetCategory::orderBy('nama_kategori')->get();
        $cabang     = $user->getCabang();
        return view('assets.create', compact('categories', 'cabang'));
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
            'merk'             => 'nullable|string|max:100',
            'no_seri'          => 'nullable|string|max:100',
            'kondisi'          => 'required|in:baik,rusak,dalam_perbaikan',
            'status'           => 'required|in:tersedia,dipinjam,tidak_aktif',
            'tanggal_perolehan'=> 'nullable|date',
            'nilai_perolehan'  => 'nullable|numeric|min:0',
            'lokasi'           => 'nullable|string|max:255',
            'deskripsi'        => 'nullable|string',
            'catatan'          => 'nullable|string',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Validasi akses cabang jika bukan super admin
        if (!$user->isSuperAdmin() && $request->filled('kode_cabang')) {
            $userCabangs = $user->getCabangCodes();
            if (!in_array($request->kode_cabang, $userCabangs)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke cabang yang dipilih.');
            }
        }

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'asset_' . Str::random(12) . '.' . $file->extension();
            $file->storeAs('assets', $filename, 'public');
            $data['foto'] = $filename;
        }

        Asset::create($data);

        return redirect()->route('assets.index')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function show(Asset $asset)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.index'), 403);
        $this->authorizeAsset($asset);
        $asset->load(['category', 'cabang']);
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.edit'), 403);
        $this->authorizeAsset($asset);
        $user       = auth()->user();
        $categories = AssetCategory::orderBy('nama_kategori')->get();
        $cabang     = $user->getCabang();
        return view('assets.edit', compact('asset', 'categories', 'cabang'));
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
            'merk'             => 'nullable|string|max:100',
            'no_seri'          => 'nullable|string|max:100',
            'kondisi'          => 'required|in:baik,rusak,dalam_perbaikan',
            'status'           => 'required|in:tersedia,dipinjam,tidak_aktif',
            'tanggal_perolehan'=> 'nullable|date',
            'nilai_perolehan'  => 'nullable|numeric|min:0',
            'lokasi'           => 'nullable|string|max:255',
            'deskripsi'        => 'nullable|string',
            'catatan'          => 'nullable|string',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Validasi akses cabang baru jika bukan super admin
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $request->filled('kode_cabang')) {
            $userCabangs = $user->getCabangCodes();
            if (!in_array($request->kode_cabang, $userCabangs)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke cabang yang dipilih.');
            }
        }

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            if ($asset->foto && Storage::disk('public')->exists('assets/' . $asset->foto)) {
                Storage::disk('public')->delete('assets/' . $asset->foto);
            }
            $file = $request->file('foto');
            $filename = 'asset_' . Str::random(12) . '.' . $file->extension();
            $file->storeAs('assets', $filename, 'public');
            $data['foto'] = $filename;
        }

        $asset->update($data);

        return redirect()->route('assets.index')->with('success', 'Asset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.delete'), 403);
        $this->authorizeAsset($asset);

        if ($asset->foto && Storage::disk('public')->exists('assets/' . $asset->foto)) {
            Storage::disk('public')->delete('assets/' . $asset->foto);
        }
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset berhasil dihapus.');
    }

    // ── Kategori ────────────────────────────────────────────────────────────────

    public function kategoriIndex()
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori'), 403);
        $categories = AssetCategory::withCount('assets')->orderBy('nama_kategori')->paginate(20);
        return view('assets.kategori.index', compact('categories'));
    }

    public function kategoriStore(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori'), 403);
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:asset_categories,nama_kategori',
            'deskripsi'     => 'nullable|string',
        ]);
        AssetCategory::create($request->only('nama_kategori', 'deskripsi'));
        return redirect()->route('assets.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function kategoriUpdate(Request $request, AssetCategory $category)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori'), 403);
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:asset_categories,nama_kategori,' . $category->id,
            'deskripsi'     => 'nullable|string',
        ]);
        $category->update($request->only('nama_kategori', 'deskripsi'));
        return redirect()->route('assets.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function kategoriDestroy(AssetCategory $category)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori'), 403);
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
}
