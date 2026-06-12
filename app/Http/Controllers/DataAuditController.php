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
}
