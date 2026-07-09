<?php

namespace App\Http\Controllers;

use App\Models\IdControlList;
use Illuminate\Http\Request;

class IdControlListController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view id control list', ['only' => ['index', 'show', 'exportPdf']]);
        $this->middleware('permission:create id control list', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit id control list', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete id control list', ['only' => ['destroy']]);
    }

    /**
     * Export all ID Control List to PDF.
     */
    public function exportPdf()
    {
        $lists = IdControlList::with(['karyawan', 'cabang'])->orderBy('id', 'desc')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('id-control-list.pdf', compact('lists'))
                ->setPaper('a4', 'landscape');
        return $pdf->download('id-control-list.pdf');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lists = IdControlList::with(['karyawan', 'cabang'])->orderBy('id', 'desc')->get();
        $aplikasis = \App\Models\Aplikasi::orderBy('nama_aplikasi', 'asc')->get();
        return view('id-control-list.index', compact('lists', 'aplikasis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawans = \App\Models\Karyawan::with(['departemen', 'cabang'])->where('status_aktif_karyawan', '1')->orderBy('nama_karyawan', 'asc')->get();
        $cabangs = \App\Models\Cabang::orderBy('nama_cabang', 'asc')->get();
        $aplikasis = \App\Models\Aplikasi::orderBy('nama_aplikasi', 'asc')->get();
        return view('id-control-list.create', compact('karyawans', 'cabangs', 'aplikasis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required|string|max:255',
            'nama_aplikasi' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'type_id' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        IdControlList::create($request->all());

        return redirect()->route('id-control-list.index')->with('success', 'Data ID Control List berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $idControlList = IdControlList::with(['karyawan', 'cabang'])->findOrFail($id);
        return view('id-control-list.show', compact('idControlList'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $idControlList = IdControlList::findOrFail($id);
        $karyawans = \App\Models\Karyawan::with(['departemen', 'cabang'])->where('status_aktif_karyawan', '1')->orderBy('nama_karyawan', 'asc')->get();
        $cabangs = \App\Models\Cabang::orderBy('nama_cabang', 'asc')->get();
        $aplikasis = \App\Models\Aplikasi::orderBy('nama_aplikasi', 'asc')->get();
        return view('id-control-list.edit', compact('idControlList', 'karyawans', 'cabangs', 'aplikasis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_aplikasi' => 'required',
            'role' => 'required',
            'nama_pengguna' => 'required',
        ]);

        $idControlList = IdControlList::findOrFail($id);
        $idControlList->update($request->all());

        return redirect()->route('id-control-list.index')->with('success', 'Data ID Control List berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $idControlList = IdControlList::findOrFail($id);
        $idControlList->delete();

        return redirect()->route('id-control-list.index')->with('success', 'Data ID Control List berhasil dihapus');
    }
}
