@extends('layouts.app')
@section('titlepage', 'Buku Tamu')

@section('content')
@section('navigasi')
    <span>Buku Tamu</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                @can('bukutamu.index')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTamuModal">
                        <i class="ti ti-plus me-2"></i>Tambah Tamu
                    </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('tamu.index') }}" method="GET">
                            <div class="row">
                                <div class="col-lg-3 col-sm-12 col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                            <input type="text" class="form-control flatpickr-date" id="tanggal" name="tanggal"
                                                placeholder="Pilih Tanggal" value="{{ $tanggal }}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-12 col-md-12">
                                    <button class="btn btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th>Nama Tamu</th>
                                <th>Jam Masuk</th>
                                <th>Tujuan</th>
                                <th>Keperluan</th>
                                <th>Jam Keluar</th>
                                <th style="width: 15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tamus as $tamu)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $tamu->nama_tamu }}</td>
                                    <td><span class="badge bg-success">{{ $tamu->jam_in }}</span></td>
                                    <td>{{ $tamu->tujuan }}</td>
                                    <td>{{ $tamu->keperluan }}</td>
                                    <td>
                                        @if($tamu->jam_out)
                                            <span class="badge bg-danger">{{ $tamu->jam_out }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Belum Keluar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            @if(!$tamu->jam_out)
                                                <form action="{{ route('tamu.updateOut', $tamu->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Set Jam Keluar">
                                                        <i class="ti ti-logout text-dark"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('tamu.destroy', $tamu->id) }}" method="POST" class="d-inline deleteform">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger delete-confirm ms-1" title="Hapus">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data tamu pada tanggal ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Tamu -->
<div class="modal fade" id="tambahTamuModal" tabindex="-1" aria-labelledby="tambahTamuModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tamu.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahTamuModalLabel">Form Tambah Tamu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_tamu" class="form-label">Nama Tamu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_tamu" name="nama_tamu" required placeholder="Masukkan Nama Tamu">
                    </div>
                    <div class="mb-3">
                        <label for="tujuan" class="form-label">Bertemu Dengan (Tujuan) <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="tujuan" name="tujuan" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->nama_karyawan }}">{{ $k->nama_karyawan }} ({{ $k->jabatan->nama_jabatan ?? 'Karyawan' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="keperluan" class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keperluan" name="keperluan" rows="3" required placeholder="Jelaskan keperluan tamu"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-1"></i> Jam Masuk (Jam In) dan Tanggal akan terisi secara otomatis sesuai waktu sekarang.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
    $(function() {
        // Initialize flatpickr for date inputs
        $('.flatpickr-date').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        // Initialize select2 untuk dropdown Karyawan di dalam modal
        $('#tujuan').select2({
            placeholder: "-- Pilih Karyawan --",
            allowClear: true,
            dropdownParent: $('#tambahTamuModal')
        });

        $('.delete-confirm').click(function(e) {
            var form = $(this).closest('form');
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data tamu ini?',
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
