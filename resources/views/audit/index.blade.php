@extends('layouts.app')
@section('titlepage', 'Audit Log')

@section('content')
@section('navigasi')
    <span>Audit Log</span>
@endsection

<div class="row">
    <div class="col-lg-12">
        {{-- Statistics Cards --}}
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-primary-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 bg-primary text-white rounded">
                                <i class="ti ti-file-text fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Logs</small>
                                <h4 class="mb-0">{{ number_format($stats['total_logs']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-success-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 bg-success text-white rounded">
                                <i class="ti ti-calendar-event fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Logs Hari Ini</small>
                                <h4 class="mb-0">{{ number_format($stats['today_logs']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-info-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 bg-info text-white rounded">
                                <i class="ti ti-users fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Users</small>
                                <h4 class="mb-0">{{ number_format($stats['total_users']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-warning-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 bg-warning text-white rounded">
                                <i class="ti ti-login fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Login Hari Ini</small>
                                <h4 class="mb-0">{{ number_format($stats['total_logins_today']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Search Card --}}
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-filter me-2"></i>Filter Data</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('audit.index') }}" method="GET">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select select2">
                                <option value="">Semua User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ Request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">Action</label>
                            <select name="action" class="form-select">
                                <option value="">Semua Action</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ Request('action') == $action ? 'selected' : '' }}>
                                        {{ ucfirst($action) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">Module</label>
                            <select name="module" class="form-select">
                                <option value="">Semua Module</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module }}" {{ Request('module') == $module ? 'selected' : '' }}>
                                        {{ ucfirst($module) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="tanggal_dari" class="form-control" value="{{ Request('tanggal_dari') }}">
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="tanggal_sampai" class="form-control" value="{{ Request('tanggal_sampai') }}">
                        </div>
                        <div class="col-lg-1 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-10 col-md-8 col-sm-12 mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Cari description atau IP address..." value="{{ Request('search') }}">
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-12 mb-3">
                            <a href="{{ route('audit.index') }}" class="btn btn-secondary w-100">
                                <i class="ti ti-refresh me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="ti ti-list me-2"></i>Daftar Audit Log</h5>
                    <div>
                        <a href="{{ route('audit.export', request()->all()) }}" class="btn btn-success btn-sm">
                            <i class="ti ti-download me-1"></i>Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Audit Logs Table --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="12%">User</th>
                                <th width="8%">Action</th>
                                <th width="8%">Module</th>
                                <th width="25%">Description</th>
                                <th width="10%">IP Address</th>
                                <th width="12%">Login At</th>
                                <th width="12%">Logout At</th>
                                <th width="8%">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audit_logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        @if($log->user)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2 bg-primary-subtle text-primary rounded">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <span>{{ $log->user->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $actionColors = [
                                                'login' => 'success',
                                                'logout' => 'secondary',
                                                'create' => 'info',
                                                'update' => 'warning',
                                                'delete' => 'danger',
                                            ];
                                            $color = $actionColors[$log->action] ?? 'primary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst($log->action) }}</span>
                                    </td>
                                    <td>
                                        @if($log->module)
                                            <span class="badge bg-label-secondary">{{ ucfirst($log->module) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->description ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <code class="text-muted" style="font-size: 11px;">{{ $log->ip_address ?? '-' }}</code>
                                    </td>
                                    <td>
                                        @if($log->login_at)
                                            <small class="text-success">
                                                <i class="ti ti-login me-1"></i>{{ $log->login_at->format('d/m/Y H:i:s') }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->logout_at)
                                            <small class="text-danger">
                                                <i class="ti ti-logout me-1"></i>{{ $log->logout_at->format('d/m/Y H:i:s') }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                                        <br>
                                        <small class="text-info">{{ $log->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="ti ti-file-off fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Tidak ada data audit log</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $audit_logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
    $(function() {
        // Initialize Select2
        if ($('.select2').length) {
            $('.select2').select2({
                placeholder: 'Pilih User',
                allowClear: true
            });
        }
    });
</script>
@endpush
