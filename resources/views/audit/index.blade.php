@extends('layouts.app')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Audit Log Login & Aktivitas
                </h2>
                <div class="text-muted mt-1">Riwayat login, logout, dan aktivitas user.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <form action="{{ route('audit.cleanup') }}" method="POST" onsubmit="return confirm('Bersihkan log yang lebih dari 3 bulan?');">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="ti ti-trash me-2"></i> Bersihkan Log Lama
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aktivitas</th>
                            <th>Modul</th>
                            <th>Keterangan</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y H:i:s') }}</td>
                            <td>{{ $log->user ? $log->user->name : 'Sistem' }}</td>
                            <td>
                                @if($log->action == 'login')
                                    <span class="badge bg-success">Login</span>
                                @elseif($log->action == 'logout')
                                    <span class="badge bg-secondary">Logout</span>
                                @else
                                    <span class="badge bg-primary">{{ ucfirst($log->action) }}</span>
                                @endif
                            </td>
                            <td>{{ $log->module ?? '-' }}</td>
                            <td>{{ $log->description ?? '-' }}</td>
                            <td>{{ $log->ip_address }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada riwayat aktivitas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
