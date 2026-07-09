@extends('layouts.app')
@section('titlepage', 'Data Calon Nasabah')

@section('content')
@section('navigasi')
    <span>Data Calon Nasabah</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <a href="{{ route('data-calon-nasabah.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>Tambah Data Nasabah
                </a>
                <form action="{{ route('data-calon-nasabah.export-excel') }}" method="GET" class="d-inline">
                    @if(request('nik'))
                        <input type="hidden" name="nik" value="{{ request('nik') }}">
                    @endif
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-file-spreadsheet me-2"></i>Export Excel
                    </button>
                </form>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('data-calon-nasabah.index') }}" method="GET">
                            <div class="row">
                                @if (!auth()->user()->hasRole('karyawan'))
                                    <div class="col-lg-4 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <input type="text" name="nik" class="form-control" placeholder="Cari NIK..." value="{{ request('nik') }}">
                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Karyawan (NIK)</th>
                                        <th>Nama Nasabah</th>
                                        <th>Status Lead</th>
                                        <th>No WhatsApp</th>
                                        <th>Sosmed</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($nasabahs as $index => $nasabah)
                                        <tr>
                                            <td>{{ $nasabahs->firstItem() + $index }}</td>
                                            <td>{{ \Carbon\Carbon::parse($nasabah->tanggal)->translatedFormat('d M Y') }}</td>
                                            <td>{{ $nasabah->karyawan ? $nasabah->karyawan->nama_karyawan : '-' }} <br> <small>{{ $nasabah->nik }}</small></td>
                                            <td>{{ $nasabah->nama }}</td>
                                            <td>
                                                @if($nasabah->status_lead == 'hot')
                                                    <span class="badge bg-danger">Hot</span>
                                                @elseif($nasabah->status_lead == 'warm')
                                                    <span class="badge bg-warning">Warm</span>
                                                @else
                                                    <span class="badge bg-secondary">Cold</span>
                                                @endif
                                            </td>
                                            <td>{{ $nasabah->no_whatsapp ?? '-' }}</td>
                                            <td>{{ $nasabah->akun_sosial_media ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('data-calon-nasabah.edit', $nasabah->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('data-calon-nasabah.destroy', $nasabah->id) }}" method="POST" class="d-inline form-delete">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm btn-delete" title="Hapus">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data calon nasabah.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $nasabahs->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        $('.btn-delete').click(function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data nasabah ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
