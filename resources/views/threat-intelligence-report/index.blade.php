@extends('layouts.app')
@section('titlepage', 'Threat Intelligence Report')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Threat Intelligence Report</h1>
        <div class="d-flex" style="gap: 10px;">
            <a href="{{ route('threat-intelligence-reports.export-pdf', request()->query()) }}" class="btn btn-outline-danger shadow-sm">
                <i class="ti ti-file-pdf"></i> Export PDF
            </a>
            @can('threat-intelligence.create')
            <a href="{{ route('threat-intelligence-reports.create') }}" class="btn btn-primary-custom shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Data
            </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="tir-card shadow mb-4">
        <div class="tir-header">
            <h6 class="m-0 font-weight-bold text-white">Daftar Threat Intelligence Report</h6>
        </div>
        <div class="tir-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-tir" id="table-tir" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jenis Ancaman</th>
                            <th>Sumber Ancaman</th>
                            <th>Deskripsi Insiden</th>
                            <th>Dampak</th>
                            <th>Tindakan yang diambil</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $item->jenis_ancaman }}</td>
                            <td>{{ $item->sumber_ancaman }}</td>
                            <td>{{ Str::limit($item->deskripsi_insiden, 50) }}</td>
                            <td>{{ Str::limit($item->dampak, 50) }}</td>
                            <td>{{ Str::limit($item->tindakan_yang_diambil, 50) }}</td>
                            <td>
                                @php
                                    $statusClass = 'status-closed';
                                    if(strtolower($item->status) == 'open' || strtolower($item->status) == 'ada masalah') $statusClass = 'status-open';
                                    elseif(strtolower($item->status) == 'investigating' || strtolower($item->status) == 'proses') $statusClass = 'status-investigating';
                                    elseif(strtolower($item->status) == 'resolved' || strtolower($item->status) == 'tidak ada masalah') $statusClass = 'status-resolved';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $item->status }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center" style="gap: 5px;">
                                    @can('threat-intelligence.edit')
                                    <a href="{{ route('threat-intelligence-reports.edit', $item->id) }}" class="btn btn-warning-custom btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('threat-intelligence.delete')
                                    <form action="{{ route('threat-intelligence-reports.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger-custom btn-sm btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<style>
    <?php include public_path('assets/css/threat-intelligence-report.css'); ?>
</style>
<script src="{{ asset('assets/js/threat-intelligence-report.js') }}?v={{ time() }}"></script>
@endpush
