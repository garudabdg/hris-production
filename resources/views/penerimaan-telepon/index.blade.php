@extends('layouts.app')
@section('titlepage', 'Penerimaan Telepon Masuk')

@section('content')
@section('navigasi')
    <span>Penerimaan Telepon Masuk</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                @can('bukutamu.index')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTeleponModal">
                        <i class="ti ti-plus me-2"></i>Tambah Data Telepon
                    </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('penerimaan-telepon.index') }}" method="GET">
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
                                <div class="col-lg-6 col-sm-12 col-md-12">
                                    <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
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
                                <th>Jam</th>
                                <th>Nama Penelpon</th>
                                <th>Perusahaan/Instansi</th>
                                <th>Tujuan Panggilan</th>
                                <th>Tindak Lanjut</th>
                                <th style="width: 15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($telepons as $telepon)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="badge bg-success">{{ \Carbon\Carbon::parse($telepon->waktu)->format('H:i') }}</span></td>
                                    <td>
                                        <div class="fw-bold">{{ $telepon->nama_penelpon }}</div>
                                        <div class="text-muted" style="font-size: 0.85rem;">{{ $telepon->no_telp }}</div>
                                    </td>
                                    <td>{{ $telepon->nama_perusahaan ?? '-' }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $telepon->tujuan }}</div>
                                        <div class="text-muted" style="font-size: 0.85rem;">{{ $telepon->departemen }}</div>
                                    </td>
                                    <td>{{ $telepon->tindak_lanjut }}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info ms-1 btn-view" title="Detail" 
                                                data-nama="{{ $telepon->nama_penelpon }}" 
                                                data-perusahaan="{{ $telepon->nama_perusahaan }}" 
                                                data-notelp="{{ $telepon->no_telp }}" 
                                                data-tujuan="{{ $telepon->tujuan }}" 
                                                data-departemen="{{ $telepon->departemen }}" 
                                                data-keperluan="{{ $telepon->keperluan }}" 
                                                data-tindak="{{ $telepon->tindak_lanjut }}" 
                                                data-pesan="{{ $telepon->pesan }}" 
                                                data-waktu="{{ \Carbon\Carbon::parse($telepon->waktu)->format('H:i') }}">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-sm btn-primary ms-1 btn-edit" title="Edit"
                                                data-id="{{ $telepon->id }}"
                                                data-nama="{{ $telepon->nama_penelpon }}" 
                                                data-perusahaan="{{ $telepon->nama_perusahaan }}" 
                                                data-notelp="{{ $telepon->no_telp }}" 
                                                data-tujuan="{{ $telepon->tujuan }}" 
                                                data-departemen="{{ $telepon->departemen }}" 
                                                data-keperluan="{{ $telepon->keperluan }}" 
                                                data-tindak="{{ $telepon->tindak_lanjut }}" 
                                                data-pesan="{{ $telepon->pesan }}">
                                                <i class="ti ti-pencil"></i>
                                            </button>

                                            <form action="{{ route('penerimaan-telepon.destroy', $telepon->id) }}" method="POST" class="d-inline deleteform">
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
                                    <td colspan="7" class="text-center">Tidak ada data penerimaan telepon pada tanggal ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Telepon -->
<div class="modal fade" id="tambahTeleponModal" tabindex="-1" aria-labelledby="tambahTeleponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('penerimaan-telepon.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahTeleponModalLabel">Form Penerimaan Telepon Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-1"></i> Tanggal dan Waktu akan terisi secara otomatis sesuai waktu saat ini.
                    </div>

                    <h6 class="mb-3 border-bottom pb-2">Data Penelpon</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_penelpon" class="form-label">Nama Penelpon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_penelpon" name="nama_penelpon" required placeholder="Masukkan Nama Penelpon">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_telp" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="no_telp" name="no_telp" required placeholder="Masukkan Nomor Telepon">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nama_perusahaan" class="form-label">Nama Perusahaan/Instansi (Opsional)</label>
                        <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" placeholder="Masukkan Nama Perusahaan/Instansi">
                    </div>

                    <h6 class="mb-3 border-bottom pb-2 mt-4">Tujuan Panggilan</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tujuan" class="form-label">Ditujukan Kepada <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="tujuan" name="tujuan" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->nama_karyawan }}" data-departemen="{{ $k->departemen->nama_dept ?? '' }}">{{ $k->nama_karyawan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="departemen" class="form-label">Departemen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="departemen" name="departemen" required placeholder="Cth: HRD, IT, Marketing, dll">
                        </div>
                    </div>

                    <h6 class="mb-3 border-bottom pb-2 mt-4">Detail Keperluan</h6>
                    <div class="mb-3">
                        <label for="keperluan" class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keperluan" name="keperluan" rows="3" required placeholder="Jelaskan keperluan penelpon"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tindak Lanjut <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tindak_lanjut" id="tl_1" value="Langsung tersambung ke pihak yang dituju" required>
                            <label class="form-check-label" for="tl_1">Langsung tersambung ke pihak yang dituju</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tindak_lanjut" id="tl_2" value="Pihak yang dituju sedang tidak tersedia">
                            <label class="form-check-label" for="tl_2">Pihak yang dituju sedang tidak tersedia</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tindak_lanjut" id="tl_3" value="Diminta untuk menghubungi kembali">
                            <label class="form-check-label" for="tl_3">Diminta untuk menghubungi kembali</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tindak_lanjut" id="tl_4" value="Pesan telah diteruskan">
                            <label class="form-check-label" for="tl_4">Pesan telah diteruskan</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tindak_lanjut" id="tl_5" value="Memerlukan tindak lanjut">
                            <label class="form-check-label" for="tl_5">Memerlukan tindak lanjut</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pesan" class="form-label">Pesan yang Disampaikan (Opsional)</label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="3" placeholder="Tuliskan pesan jika ada"></textarea>
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

<!-- Modal View Telepon -->
<div class="modal fade" id="viewTeleponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Penerimaan Telepon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="border-bottom pb-2 text-primary">Informasi Penelpon</h6>
                <table class="table table-bordered mb-4">
                    <tr><th style="width: 30%;">Nama Penelpon</th><td id="v_nama"></td></tr>
                    <tr><th>Nomor Telepon</th><td id="v_notelp"></td></tr>
                    <tr><th>Perusahaan/Instansi</th><td id="v_perusahaan"></td></tr>
                    <tr><th>Waktu Panggilan</th><td id="v_waktu"></td></tr>
                </table>

                <h6 class="border-bottom pb-2 text-primary">Tujuan & Keperluan</h6>
                <table class="table table-bordered mb-4">
                    <tr><th style="width: 30%;">Ditujukan Kepada</th><td id="v_tujuan"></td></tr>
                    <tr><th>Departemen</th><td id="v_departemen"></td></tr>
                    <tr><th>Keperluan</th><td id="v_keperluan"></td></tr>
                </table>

                <h6 class="border-bottom pb-2 text-primary">Tindak Lanjut & Pesan</h6>
                <table class="table table-bordered mb-4">
                    <tr><th style="width: 30%;">Tindak Lanjut</th><td><span class="badge bg-label-info" id="v_tindak"></span></td></tr>
                    <tr><th>Pesan</th><td id="v_pesan"></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Telepon -->
<div class="modal fade" id="editTeleponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditTelepon" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Telepon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3 border-bottom pb-2">Data Penelpon</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Penelpon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_nama_penelpon" name="nama_penelpon" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_no_telp" name="no_telp" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Perusahaan/Instansi (Opsional)</label>
                        <input type="text" class="form-control" id="e_nama_perusahaan" name="nama_perusahaan">
                    </div>

                    <h6 class="mb-3 border-bottom pb-2 mt-4">Tujuan Panggilan</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ditujukan Kepada <span class="text-danger">*</span></label>
                            <select class="form-select select2-edit" id="e_tujuan" name="tujuan" required style="width: 100%;">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->nama_karyawan }}" data-departemen="{{ $k->departemen->nama_dept ?? '' }}">{{ $k->nama_karyawan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departemen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_departemen" name="departemen" required>
                        </div>
                    </div>

                    <h6 class="mb-3 border-bottom pb-2 mt-4">Detail Keperluan</h6>
                    <div class="mb-3">
                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="e_keperluan" name="keperluan" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tindak Lanjut <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input e_tindak_lanjut" type="radio" name="tindak_lanjut" id="e_tl_1" value="Langsung tersambung ke pihak yang dituju" required>
                            <label class="form-check-label" for="e_tl_1">Langsung tersambung ke pihak yang dituju</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input e_tindak_lanjut" type="radio" name="tindak_lanjut" id="e_tl_2" value="Pihak yang dituju sedang tidak tersedia">
                            <label class="form-check-label" for="e_tl_2">Pihak yang dituju sedang tidak tersedia</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input e_tindak_lanjut" type="radio" name="tindak_lanjut" id="e_tl_3" value="Diminta untuk menghubungi kembali">
                            <label class="form-check-label" for="e_tl_3">Diminta untuk menghubungi kembali</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input e_tindak_lanjut" type="radio" name="tindak_lanjut" id="e_tl_4" value="Pesan telah diteruskan">
                            <label class="form-check-label" for="e_tl_4">Pesan telah diteruskan</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input e_tindak_lanjut" type="radio" name="tindak_lanjut" id="e_tl_5" value="Memerlukan tindak lanjut">
                            <label class="form-check-label" for="e_tl_5">Memerlukan tindak lanjut</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pesan yang Disampaikan (Opsional)</label>
                        <textarea class="form-control" id="e_pesan" name="pesan" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('myscript')
    <style>
        /* ============================================
           Section: Penerimaan Telepon
           Kustomisasi tampilan khusus untuk modul telepon
           ============================================ */
        .select2-container {
            z-index: 100000;
        }
    </style>
    <script>
        window.PenerimaanTeleponConfig = { 
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ url("penerimaan-telepon") }}'
        };
    </script>
    <script src="{{ asset('assets/js/penerimaan_telepon.js') }}?v={{ time() }}"></script>
@endpush
