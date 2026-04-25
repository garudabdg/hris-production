<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\RecruitmentVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecruitmentVacancyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedCabang = $user->getCabangCodes();

        $query = RecruitmentVacancy::with(['cabang', 'departemen', 'jabatan'])
            ->when($allowedCabang, fn($q) => $q->whereIn('kode_cabang', $allowedCabang))
            ->when($request->cabang, fn($q, $v) => $q->where('kode_cabang', $v))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, fn($q, $v) => $q->where('posisi', 'like', "%$v%"))
            ->orderByDesc('created_at');

        $vacancies = $query->paginate(15)->withQueryString();
        $cabangs = Cabang::when($allowedCabang, fn($q) => $q->whereIn('kode_cabang', $allowedCabang))->get();
        $departements = Departemen::orderBy('nama_dept')->get();
        $jabatans = Jabatan::all();

        return view('recruitment.vacancy.index', compact('vacancies', 'cabangs', 'departements', 'jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_cabang'       => 'required',
            'kode_dept'         => 'required',
            'kode_jabatan'      => 'required',
            'posisi'            => 'required|string|max:255',
            'kuota'             => 'required|integer|min:1',
            'deadline'          => 'nullable|date',
            'deskripsi_pekerjaan' => 'nullable|string',
            'kualifikasi'       => 'nullable|string',
            'status'            => 'required|in:buka,tutup',
        ]);

        RecruitmentVacancy::create($request->only([
            'kode_cabang', 'kode_dept', 'kode_jabatan', 'posisi',
            'kuota', 'deadline', 'deskripsi_pekerjaan', 'kualifikasi', 'status',
        ]));

        return redirect()->route('recruitment.vacancy.index')->with('success', 'Lowongan berhasil ditambahkan.');
    }

    public function update(Request $request, RecruitmentVacancy $vacancy)
    {
        $request->validate([
            'kode_cabang'       => 'required',
            'kode_dept'         => 'required',
            'kode_jabatan'      => 'required',
            'posisi'            => 'required|string|max:255',
            'kuota'             => 'required|integer|min:1',
            'deadline'          => 'nullable|date',
            'deskripsi_pekerjaan' => 'nullable|string',
            'kualifikasi'       => 'nullable|string',
            'status'            => 'required|in:buka,tutup',
        ]);

        $vacancy->update($request->only([
            'kode_cabang', 'kode_dept', 'kode_jabatan', 'posisi',
            'kuota', 'deadline', 'deskripsi_pekerjaan', 'kualifikasi', 'status',
        ]));

        return redirect()->route('recruitment.vacancy.index')->with('success', 'Lowongan berhasil diupdate.');
    }

    public function toggleStatus(RecruitmentVacancy $vacancy)
    {
        $vacancy->update(['status' => $vacancy->status === 'buka' ? 'tutup' : 'buka']);
        return back()->with('success', 'Status lowongan berhasil diubah.');
    }

    public function destroy(RecruitmentVacancy $vacancy)
    {
        $vacancy->delete();
        return back()->with('success', 'Lowongan berhasil dihapus.');
    }
}
