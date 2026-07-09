@extends('layouts.app')
@section('title', 'Detail ID Control List')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail ID Control List</h1>
        <a href="{{ route('id-control-list.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="id-control-card shadow mb-4">
        <div class="id-control-header">
            <h6 class="m-0 font-weight-bold text-white">Data ID Control List #{{ $idControlList->id }}</h6>
        </div>
        <div class="id-control-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%" class="bg-light">Periode</th>
                    <td>{{ $idControlList->period }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Nama Aplikasi (System Name)</th>
                    <td>{{ $idControlList->nama_aplikasi }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Role / ID Name</th>
                    <td>{{ $idControlList->role }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Nama Pengguna / ID User</th>
                    <td>
                        {{ $idControlList->karyawan ? $idControlList->karyawan->nama_karyawan : $idControlList->nama_pengguna }}
                        @if($idControlList->karyawan)
                            <br><small class="text-muted">NIK: {{ $idControlList->nama_pengguna }}</small>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="bg-light">Divisi</th>
                    <td>{{ $idControlList->division ?: '-' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Lokasi / Cabang</th>
                    <td>{{ $idControlList->cabang ? $idControlList->cabang->nama_cabang : ($idControlList->location ?: '-') }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Type ID</th>
                    <td>{{ $idControlList->type_id ?: '-' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Remarks</th>
                    <td>
                        @if($idControlList->remarks == '1')
                            <span class="badge bg-success">Active</span>
                        @elseif($idControlList->remarks == '0')
                            <span class="badge bg-danger">non-Active</span>
                        @else
                            {{ $idControlList->remarks ?: '-' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="bg-light">Tanggal Dibuat</th>
                    <td>{{ $idControlList->created_at ? $idControlList->created_at->format('d M Y H:i') : '-' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Terakhir Diperbarui</th>
                    <td>{{ $idControlList->updated_at ? $idControlList->updated_at->format('d M Y H:i') : '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<style>
    <?php include public_path('assets/css/id_control_list.css'); ?>
</style>
@endpush
