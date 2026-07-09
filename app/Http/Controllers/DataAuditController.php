<?php

namespace App\Http\Controllers;

use App\Models\DataAuditLog;
use Illuminate\Http\Request;

class DataAuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = DataAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('data-audit.index', compact('logs'));
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DataAuditExport, 'data_audit_log_'.date('Y-m-d_H-i').'.xlsx');
    }
}
