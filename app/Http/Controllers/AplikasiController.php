<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Aplikasi;

class AplikasiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kode_aplikasi' => 'required|string|unique:aplikasis,kode_aplikasi',
            'nama_aplikasi' => 'required|string|max:255',
        ]);

        Aplikasi::create($request->all());

        return redirect()->back()->with('success', 'Aplikasi berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $aplikasi = Aplikasi::findOrFail($id);
        
        $request->validate([
            'kode_aplikasi' => 'required|string|unique:aplikasis,kode_aplikasi,' . $id . ',kode_aplikasi',
            'nama_aplikasi' => 'required|string|max:255',
        ]);

        $aplikasi->update($request->all());

        return redirect()->back()->with('success', 'Aplikasi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $aplikasi = Aplikasi::findOrFail($id);
        $aplikasi->delete();

        return redirect()->back()->with('success', 'Aplikasi berhasil dihapus!');
    }
}
