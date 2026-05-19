<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();

        $query = AuditLog::with('user')
            ->select('audit_logs.*')
            ->orderBy('audit_logs.created_at', 'DESC');

        // Scope berdasarkan cabang yang diizinkan ke auth user (non super admin)
        if (!$authUser->isSuperAdmin()) {
            $allowedCabangs = $authUser->getCabangCodes();
            if (!empty($allowedCabangs)) {
                // Tampilkan log dari user yang berada di cabang yang sama
                $allowedUserIds = \App\Models\User::whereHas('userkaryawan.karyawan', function ($q) use ($allowedCabangs) {
                    $q->whereIn('kode_cabang', $allowedCabangs);
                })->pluck('id')->toArray();

                // Tambah auth user sendiri (agar log aksinya sendiri juga muncul)
                $allowedUserIds[] = $authUser->id;

                $query->whereIn('audit_logs.user_id', array_unique($allowedUserIds));
            }
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        // Filter by search (description or IP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('ip_address', 'like', '%' . $search . '%');
            });
        }

        $audit_logs = $query->paginate(20);
        $audit_logs->appends($request->all());

        // Get users for filter dropdown — scope ke cabang yang diizinkan
        if ($authUser->isSuperAdmin()) {
            $users = User::select('id', 'name')->orderBy('name')->get();
        } else {
            $allowedCabangs = $authUser->getCabangCodes();
            $users = User::whereHas('userkaryawan.karyawan', function ($q) use ($allowedCabangs) {
                $q->whereIn('kode_cabang', $allowedCabangs);
            })->select('id', 'name')->orderBy('name')->get();
        }

        // Get distinct actions
        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Get distinct modules
        $modules = AuditLog::select('module')
            ->distinct()
            ->whereNotNull('module')
            ->orderBy('module')
            ->pluck('module');

        // Get statistics — scope ke cabang yang sama
        $statsQuery = AuditLog::query();
        if (!$authUser->isSuperAdmin()) {
            $allowedCabangs = $authUser->getCabangCodes();
            $scopedUserIds = User::whereHas('userkaryawan.karyawan', function ($q) use ($allowedCabangs) {
                $q->whereIn('kode_cabang', $allowedCabangs);
            })->pluck('id')->toArray();
            $scopedUserIds[] = $authUser->id;
            $statsQuery->whereIn('user_id', array_unique($scopedUserIds));
        }

        $stats = [
            'total_logs'          => (clone $statsQuery)->count(),
            'today_logs'          => (clone $statsQuery)->whereDate('created_at', today())->count(),
            'total_users'         => (clone $statsQuery)->distinct('user_id')->count('user_id'),
            'total_logins_today'  => (clone $statsQuery)->where('action', 'login')->whereDate('created_at', today())->count(),
        ];

        return view('audit.index', compact('audit_logs', 'users', 'actions', 'modules', 'stats'));
    }

    /**
     * Display the specified audit log.
     */
    public function show($id)
    {
        $audit_log = AuditLog::with('user')->findOrFail($id);
        return view('audit.show', compact('audit_log'));
    }

    /**
     * Delete old audit logs (untuk maintenance).
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 90); // Default 90 hari
        
        $deleted = AuditLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('audit.index')->with('success', "Berhasil menghapus {$deleted} log lama (lebih dari {$days} hari)");
    }

    /**
     * Export audit logs to CSV
     */
    public function export(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();

        $query = AuditLog::with('user')->orderBy('created_at', 'DESC');

        // Scope berdasarkan cabang
        if (!$authUser->isSuperAdmin()) {
            $allowedCabangs = $authUser->getCabangCodes();
            $allowedUserIds = User::whereHas('userkaryawan.karyawan', function ($q) use ($allowedCabangs) {
                $q->whereIn('kode_cabang', $allowedCabangs);
            })->pluck('id')->toArray();
            $allowedUserIds[] = $authUser->id;
            $query->whereIn('user_id', array_unique($allowedUserIds));
        }
        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        $logs = $query->get();

        $filename = 'audit_logs_' . date('YmdHis') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['ID', 'User', 'Action', 'Module', 'Description', 'IP Address', 'Login At', 'Logout At', 'Created At']);

            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : '-',
                    $log->action,
                    $log->module ?? '-',
                    $log->description ?? '-',
                    $log->ip_address ?? '-',
                    $log->login_at ? $log->login_at->format('Y-m-d H:i:s') : '-',
                    $log->logout_at ? $log->logout_at->format('Y-m-d H:i:s') : '-',
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
