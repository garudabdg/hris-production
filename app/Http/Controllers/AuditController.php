<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');
        
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        $logs = $query->paginate(50);
        return view('audit.index', compact('logs'));
    }

    public function export(Request $request)
    {
        return back()->with('success', 'Fitur export segera tersedia.');
    }

    public function cleanup(Request $request)
    {
        AuditLog::where('created_at', '<', Carbon::now()->subMonths(3))->delete();
        return back()->with('success', 'Log lama berhasil dibersihkan.');
    }

    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        return back();
    }
}
