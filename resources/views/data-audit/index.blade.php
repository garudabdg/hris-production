@extends('layouts.app')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Audit Trail (Log Riwayat Perubahan)
                </h2>
                <div class="text-muted mt-1">Lacak semua perubahan data krusial di sistem.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('data-audit.export') }}" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-excel" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                            <path d="M10 12l4 4m0 -4l-4 4"></path>
                        </svg>
                        Export Excel
                    </a>
                </div>
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
                            <th>Aktor (User)</th>
                            <th>Aktivitas</th>
                            <th>Tabel/Model</th>
                            <th>ID Data</th>
                            <th>Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y H:i:s') }}</td>
                            <td>{{ $log->user ? $log->user->name : 'Sistem' }}</td>
                            <td>
                                @if($log->action == 'create')
                                    <span class="badge bg-success">Create</span>
                                @elseif($log->action == 'update')
                                    <span class="badge bg-warning">Update</span>
                                @elseif($log->action == 'delete')
                                    <span class="badge bg-danger">Delete</span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->action }}</span>
                                @endif
                            </td>
                            <td>{{ class_basename($log->model_type) }}</td>
                            <td>{{ $log->model_id }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-audit-{{ $log->id }}">
                                    Detail
                                </button>
                                
                                <!-- Modal Detail Perubahan -->
                                <div class="modal modal-blur fade" id="modal-audit-{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Perubahan Data ({{ class_basename($log->model_type) }} ID: {{ $log->model_id }})</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="mb-2 font-weight-bold text-danger">Data Lama (Old Values)</div>
                                                        <pre class="bg-dark text-white p-2 rounded" style="max-height:300px; overflow-y:auto;"><code class="language-json">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="mb-2 font-weight-bold text-success">Data Baru (New Values)</div>
                                                        <pre class="bg-dark text-white p-2 rounded" style="max-height:300px; overflow-y:auto;"><code class="language-json">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-muted small">
                                                    IP Address: {{ $log->ip_address }} <br>
                                                    User Agent: {{ $log->user_agent }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada riwayat perubahan data.</td>
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
