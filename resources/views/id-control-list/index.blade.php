@extends('layouts.app')
@section('title', 'ID Control List')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">ID Control List</h1>
        <div class="d-flex" style="gap: 10px;">
            <a href="{{ route('id-control-list.export-pdf', request()->query()) }}" class="btn btn-outline-danger shadow-sm">
                <i class="ti ti-file-pdf"></i> Export PDF
            </a>
            @can('create id control list')
            <button class="btn btn-info-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAplikasi">
                <i class="fas fa-list fa-sm"></i> Kelola Aplikasi
            </button>
            <a href="{{ route('id-control-list.create') }}" class="btn btn-primary-custom shadow-sm">
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

    <div class="id-control-card shadow mb-4">
        <div class="id-control-header">
            <h6 class="m-0 font-weight-bold text-white">Daftar ID Control List</h6>
        </div>
        <div class="id-control-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table-id-control-list" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Nama Aplikasi</th>
                            <th>Role / ID Name</th>
                            <th>Nama Pengguna / ID User</th>
                            <th>Divisi</th>
                            <th>Lokasi</th>
                            <th>Type ID</th>
                            <th>Remarks</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lists as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->period }}</td>
                            <td>{{ $item->nama_aplikasi }}</td>
                            <td>{{ $item->role }}</td>
                            <td>{{ $item->karyawan ? $item->karyawan->nama_karyawan : $item->nama_pengguna }}</td>
                            <td>{{ $item->division }}</td>
                            <td>{{ $item->cabang ? $item->cabang->nama_cabang : $item->location }}</td>
                            <td>{{ $item->type_id }}</td>
                            <td>
                                @if($item->remarks == '1')
                                    <span class="badge bg-success">Active</span>
                                @elseif($item->remarks == '0')
                                    <span class="badge bg-danger">non-Active</span>
                                @else
                                    {{ $item->remarks }}
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center" style="gap: 5px;">
                                    <a href="{{ route('id-control-list.show', $item->id) }}" class="btn btn-info-custom btn-sm" style="padding: 0.25rem 0.5rem;" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit id control list')
                                    <a href="{{ route('id-control-list.edit', $item->id) }}" class="btn btn-warning-custom btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete id control list')
                                    <form action="{{ route('id-control-list.destroy', $item->id) }}" method="POST" class="d-inline">
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
        </div>
    </div>
</div>

<!-- Modal Kelola Aplikasi -->
<div class="modal fade" id="modalAplikasi" tabindex="-1" aria-labelledby="modalAplikasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAplikasiLabel">Kelola Master Aplikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('aplikasi.store') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Kode Aplikasi</label>
                                <input type="text" name="kode_aplikasi" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label>Nama Aplikasi</label>
                                <input type="text" name="nama_aplikasi" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-aplikasi" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Aplikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aplikasis as $idx => $app)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $app->kode_aplikasi }}</td>
                                <td>{{ $app->nama_aplikasi }}</td>
                                <td class="text-center">
                                    <form action="{{ route('aplikasi.destroy', $app->kode_aplikasi) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<style>
    <?php include public_path('assets/css/id_control_list.css'); ?>
</style>
<script>
    window.IdControlConfig = {
        csrfToken: '{{ csrf_token() }}'
    };

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-aplikasi').DataTable({
                responsive: true,
                destroy: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                }
            });
        }
        
        // Pastikan modal tertutup sempurna dan body bisa di-scroll kembali
        $('#modalAplikasi').on('hidden.bs.modal', function () {
            $('body').removeClass('modal-open').css({
                'overflow': '',
                'padding-right': ''
            });
            $('.modal-backdrop').remove();
        });
    });
</script>
<script src="{{ asset('assets/js/id_control_list.js') }}?v={{ time() }}"></script>
@endpush
