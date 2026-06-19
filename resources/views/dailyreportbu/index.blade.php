@extends('layouts.app')
@section('titlepage', 'Daily Report Business')

@section('content')
@section('navigasi')
    <span>Daily Report Business</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                @can('dailyreportbu.create')
                    <a href="{{ route('dailyreportbu.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-2"></i>Buat Report Baru
                    </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('dailyreportbu.index') }}" method="GET">
                            <div class="row">
                                @if (!auth()->user()->hasRole('karyawan'))
                                    <div class="col-lg-3 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <select name="nik" id="nik" class="form-select select2Nik">
                                                <option value="">Semua Karyawan</option>
                                                @foreach ($karyawans as $karyawan)
                                                    <option value="{{ $karyawan->nik }}" {{ Request('nik') == $karyawan->nik ? 'selected' : '' }}>
                                                        {{ $karyawan->nik }} - {{ $karyawan->nama_karyawan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <select name="sub_departemen" id="sub_departemen" class="form-select">
                                                <option value="">Semua Team (Sub Departemen)</option>
                                                @foreach ($subDepartemens as $sub)
                                                    <option value="{{ $sub }}" {{ Request('sub_departemen') == $sub ? 'selected' : '' }}>
                                                        {{ $sub }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                            <input type="text" class="form-control flatpickr-date" id="tanggal_awal" name="tanggal_awal"
                                                placeholder="Tanggal Awal" value="{{ Request('tanggal_awal') }}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                            <input type="text" class="form-control flatpickr-date" id="tanggal_akhir" name="tanggal_akhir"
                                                placeholder="Tanggal Akhir" value="{{ Request('tanggal_akhir') }}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
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
                                        <th>Nama Karyawan</th>
                                        <th>Team (Sub Dept)</th>
                                        <th class="text-center">Online (Total)</th>
                                        <th class="text-center">Offline (Prospek)</th>
                                        <th class="text-center">Nasabah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reports as $index => $report)
                                        <tr>
                                            <td>{{ $reports->firstItem() + $index }}</td>
                                            <td>{{ \Carbon\Carbon::parse($report->tanggal)->translatedFormat('d F Y') }}</td>
                                            <td>
                                                <strong>{{ $report->nama_karyawan }}</strong><br>
                                                <small class="text-muted">{{ $report->nik }}</small>
                                            </td>
                                            <td>{{ $report->sub_departemen ?? '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $report->total_online ?? 0 }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning">{{ $report->offlineActivities->count() }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $report->nasabahData->count() }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @can('dailyreportbu.index')
                                                        <a href="{{ route('dailyreportbu.show', $report->id) }}" class="btn btn-info btn-sm" title="Detail">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                        <a href="{{ route('dailyreportbu.export.pdf', ['id' => $report->id]) }}" class="btn btn-danger btn-sm" target="_blank" title="Export PDF">
                                                            <i class="ti ti-file-export"></i>
                                                        </a>
                                                    @endcan
                                                    @can('dailyreportbu.edit')
                                                        <a href="{{ route('dailyreportbu.edit', $report->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                            <i class="ti ti-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('dailyreportbu.delete')
                                                        <form action="{{ route('dailyreportbu.destroy', $report->id) }}" method="POST" class="d-inline form-delete">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete" title="Hapus">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data daily report.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $reports->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
    $(function() {
        if ($('.select2Nik').length) {
            $('.select2Nik').select2({
                placeholder: 'Semua Karyawan',
                allowClear: true,
                width: '100%'
            });
        }

        if ($('.flatpickr-date').length) {
            $('.flatpickr-date').flatpickr({
                dateFormat: "Y-m-d",
            });
        }

        $('.btn-delete').click(function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data report yang dihapus tidak dapat dikembalikan!",
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
