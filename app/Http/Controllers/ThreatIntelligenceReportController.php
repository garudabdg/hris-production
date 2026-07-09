<?php

namespace App\Http\Controllers;

use App\Models\ThreatIntelligenceReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ThreatIntelligenceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ThreatIntelligenceReport::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('jenis_ancaman', 'like', "%{$search}%")
                  ->orWhere('sumber_ancaman', 'like', "%{$search}%")
                  ->orWhere('deskripsi_insiden', 'like', "%{$search}%");
        }

        $reports = $query->orderBy('tanggal', 'desc')->paginate(10);

        return view('threat-intelligence-report.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('threat-intelligence-report.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_ancaman' => 'required|string|max:255',
            'sumber_ancaman' => 'required|string|max:255',
            'deskripsi_insiden' => 'required|string',
            'dampak' => 'required|string',
            'tindakan_yang_diambil' => 'required|string',
            'status' => 'required|string|max:255',
        ]);

        ThreatIntelligenceReport::create($request->all());

        return redirect()->route('threat-intelligence-reports.index')->with('success', 'Report berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $report = ThreatIntelligenceReport::findOrFail($id);
        return view('threat-intelligence-report.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $report = ThreatIntelligenceReport::findOrFail($id);
        return view('threat-intelligence-report.edit', compact('report'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_ancaman' => 'required|string|max:255',
            'sumber_ancaman' => 'required|string|max:255',
            'deskripsi_insiden' => 'required|string',
            'dampak' => 'required|string',
            'tindakan_yang_diambil' => 'required|string',
            'status' => 'required|string|max:255',
        ]);

        $report = ThreatIntelligenceReport::findOrFail($id);
        $report->update($request->all());

        return redirect()->route('threat-intelligence-reports.index')->with('success', 'Report berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $report = ThreatIntelligenceReport::findOrFail($id);
        $report->delete();

        return redirect()->route('threat-intelligence-reports.index')->with('success', 'Report berhasil dihapus');
    }

    /**
     * Export all reports or filtered reports to PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = ThreatIntelligenceReport::query();

        // Optional filter by status if provided in request
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('tanggal', 'desc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('threat-intelligence-report.pdf', compact('reports'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('threat-intelligence-report.pdf');
    }
}
