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
                                <div class="col-lg-6 col-sm-12 col-md-12">
                                    <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
                                    <a href="{{ route('tamu.exportExcel', ['tanggal' => $tanggal]) }}" class="btn btn-success ms-1"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</a>
                                    <a href="{{ route('tamu.exportPdf', ['tanggal' => $tanggal]) }}" target="_blank" class="btn btn-danger ms-1"><i class="ti ti-file-text me-1"></i>Print PDF</a>
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
                                    <td>
                                        <div class="fw-bold">{{ $tamu->nama_tamu }}</div>
                                        <div class="text-muted" style="font-size: 0.85rem;">{{ $tamu->no_telp }}</div>
                                    </td>
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
                                            
                                            <button type="button" class="btn btn-sm btn-info ms-1 btn-view" title="Detail" 
                                                data-nama="{{ $tamu->nama_tamu }}" 
                                                data-notelp="{{ $tamu->no_telp }}" 
                                                data-plat="{{ $tamu->plat_nomor }}" 
                                                data-tujuan="{{ $tamu->tujuan }}" 
                                                data-keperluan="{{ $tamu->keperluan }}" 
                                                data-jamin="{{ \Carbon\Carbon::parse($tamu->created_at)->format('H:i') }}"
                                                data-jamout="{{ $tamu->jam_out ? $tamu->jam_out : '-' }}"
                                                data-fotowajah="{{ $tamu->foto_wajah ? asset('storage/' . $tamu->foto_wajah) : '' }}" 
                                                data-fotoktp="{{ $tamu->foto_ktp ? asset('storage/' . $tamu->foto_ktp) : '' }}">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-sm btn-primary ms-1 btn-edit" title="Edit"
                                                data-id="{{ $tamu->id }}"
                                                data-nama="{{ $tamu->nama_tamu }}" 
                                                data-notelp="{{ $tamu->no_telp }}" 
                                                data-plat="{{ $tamu->plat_nomor }}" 
                                                data-tujuan="{{ $tamu->tujuan }}" 
                                                data-keperluan="{{ $tamu->keperluan }}"
                                                data-fotowajah="{{ $tamu->foto_wajah }}" 
                                                data-fotoktp="{{ $tamu->foto_ktp }}">
                                                <i class="ti ti-pencil"></i>
                                            </button>

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
                        <select class="form-select" id="nama_tamu" name="nama_tamu" style="width: 100%;" required>
                            <option value="">Ketik Nama Baru atau Cari Data Tamu Sebelumnya...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="no_telp" class="form-label">Nomor Telpon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="no_telp" name="no_telp" required placeholder="Masukkan Nomor Telpon">
                    </div>
                    <div class="mb-3">
                        <label for="plat_nomor" class="form-label">Plat Nomor Kendaraan (Opsional)</label>
                        <input type="text" class="form-control" id="plat_nomor" name="plat_nomor" placeholder="Masukkan Plat Nomor Kendaraan">
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
                    
                    <div class="mb-3">
                        <label class="form-label">Ambil Foto Wajah & KTP <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-12 mb-2 text-center">
                                <div id="my_camera" style="width: 320px; height: 240px; margin: 0 auto; background: #ccc; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;"></div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-info mb-1" id="btn-capture-wajah"><i class="ti ti-camera"></i> Ambil Foto Wajah</button>
                                    <button type="button" class="btn btn-sm btn-info mb-1" id="btn-capture-ktp"><i class="ti ti-id-badge"></i> Ambil Foto KTP</button>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <h6>Hasil Foto Wajah</h6>
                                <div id="results_wajah" style="width: 100%; height: 140px; background: #eee; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <span class="text-muted text-sm" style="font-size: 0.8rem;">Belum ada foto</span>
                                </div>
                                <input type="hidden" name="foto_wajah" id="foto_wajah" required>
                            </div>
                            <div class="col-6">
                                <h6>Hasil Foto KTP</h6>
                                <div id="results_ktp" style="width: 100%; height: 140px; background: #eee; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <span class="text-muted text-sm" style="font-size: 0.8rem;">Belum ada foto</span>
                                </div>
                                <input type="hidden" name="foto_ktp" id="foto_ktp" required>
                            </div>
                        </div>
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

<!-- Modal View Tamu -->
<div class="modal fade" id="viewTamuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Tamu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th style="width: 30%;">Nama Tamu</th><td id="v_nama"></td></tr>
                    <tr><th>No Telpon</th><td id="v_notelp"></td></tr>
                    <tr><th>Plat Nomor</th><td id="v_plat"></td></tr>
                    <tr><th>Bertemu Dengan</th><td id="v_tujuan"></td></tr>
                    <tr><th>Keperluan</th><td id="v_keperluan"></td></tr>
                    <tr><th>Jam Masuk</th><td id="v_jamin"></td></tr>
                    <tr><th>Jam Keluar</th><td id="v_jamout"></td></tr>
                </table>
                <div class="row mt-3 text-center">
                    <div class="col-6">
                        <h6>Foto Wajah</h6>
                        <img id="v_fotowajah" src="" style="max-width:100%; max-height:200px; border-radius:8px; border:1px solid #ddd; object-fit:contain;" alt="Belum ada foto">
                    </div>
                    <div class="col-6">
                        <h6>Foto KTP</h6>
                        <img id="v_fotoktp" src="" style="max-width:100%; max-height:200px; border-radius:8px; border:1px solid #ddd; object-fit:contain;" alt="Belum ada foto">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Tamu -->
<div class="modal fade" id="editTamuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditTamu" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tamu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tamu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="e_nama" name="nama_tamu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Telpon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="e_notelp" name="no_telp" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plat Nomor Kendaraan</label>
                        <input type="text" class="form-control" id="e_plat" name="plat_nomor">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bertemu Dengan (Tujuan) <span class="text-danger">*</span></label>
                        <select class="form-select select2-edit" id="e_tujuan" name="tujuan" required style="width: 100%;">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->nama_karyawan }}">{{ $k->nama_karyawan }} ({{ $k->jabatan->nama_jabatan ?? 'Karyawan' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="e_keperluan" name="keperluan" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Update Foto Wajah & KTP (Opsional)</label>
                        <div class="row">
                            <div class="col-md-12 mb-2 text-center">
                                <div id="my_camera_edit" style="width: 320px; height: 240px; margin: 0 auto; background: #ccc; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;"></div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-info mb-1" id="btn-capture-wajah-edit"><i class="ti ti-camera"></i> Update Foto Wajah</button>
                                    <button type="button" class="btn btn-sm btn-info mb-1" id="btn-capture-ktp-edit"><i class="ti ti-id-badge"></i> Update Foto KTP</button>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <h6>Foto Wajah</h6>
                                <div id="e_results_wajah" style="width: 100%; height: 140px; background: #eee; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <span class="text-muted text-sm">Belum ada foto</span>
                                </div>
                                <input type="hidden" name="foto_wajah" id="e_foto_wajah">
                            </div>
                            <div class="col-6">
                                <h6>Foto KTP</h6>
                                <div id="e_results_ktp" style="width: 100%; height: 140px; background: #eee; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <span class="text-muted text-sm">Belum ada foto</span>
                                </div>
                                <input type="hidden" name="foto_ktp" id="e_foto_ktp">
                            </div>
                        </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
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

        // Initialize select2 ajax dengan tags untuk nama_tamu
        $('#nama_tamu').select2({
            placeholder: "Ketik Nama Baru atau Cari Data Tamu Sebelumnya...",
            allowClear: true,
            tags: true, // Memungkinkan input teks kustom (nama baru)
            dropdownParent: $('#tambahTamuModal'),
            ajax: {
                url: '{{ route("tamu.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            },
            createTag: function (params) {
                var term = $.trim(params.term);

                if (term === '') {
                    return null;
                }

                return {
                    id: term,
                    text: term,
                    newTag: true // custom flag
                }
            }
        });

        // Event saat tamu dipilih atau diketik
        $('#nama_tamu').on('select2:select', function (e) {
            var data = e.params.data;
            
            if(data.newTag) {
                // Nama baru diketik, bersihkan field lain
                $('#no_telp').val('');
                $('#plat_nomor').val('');
                $('#results_wajah').html('<span class="text-muted text-sm" style="font-size: 0.8rem;">Belum ada foto</span>');
                $('#results_ktp').html('<span class="text-muted text-sm" style="font-size: 0.8rem;">Belum ada foto</span>');
                $('#foto_wajah').val('');
                $('#foto_ktp').val('');
            } else if(data.nama_tamu) {
                // Data lama dipilih, auto fill field lain
                $('#no_telp').val(data.no_telp);
                $('#plat_nomor').val(data.plat_nomor);
                
                if(data.foto_wajah) {
                    $('#results_wajah').html('<img src="{{ asset("storage") }}/'+data.foto_wajah+'" style="width:100%; height:100%; object-fit:contain;"/>');
                    $('#foto_wajah').val(data.foto_wajah);
                }
                if(data.foto_ktp) {
                    $('#results_ktp').html('<img src="{{ asset("storage") }}/'+data.foto_ktp+'" style="width:100%; height:100%; object-fit:contain;"/>');
                    $('#foto_ktp').val(data.foto_ktp);
                }
            }
        });

        // Webcam setup when modal opens
        let webcamAttached = false;
        $('#tambahTamuModal').on('shown.bs.modal', function () {
            if(!webcamAttached) {
                Webcam.set({
                    width: 320,
                    height: 240,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });
                Webcam.attach('#my_camera');
                webcamAttached = true;
            }
        });

        $('#tambahTamuModal').on('hidden.bs.modal', function () {
            if(webcamAttached) {
                Webcam.reset();
                webcamAttached = false;
            }
        });

        $('#btn-capture-wajah').click(function() {
            Webcam.snap(function(data_uri) {
                $('#results_wajah').html('<img src="'+data_uri+'" style="width:100%; height:100%; object-fit:contain;"/>');
                $('#foto_wajah').val(data_uri);
            });
        });

        $('#btn-capture-ktp').click(function() {
            Webcam.snap(function(data_uri) {
                $('#results_ktp').html('<img src="'+data_uri+'" style="width:100%; height:100%; object-fit:contain;"/>');
                $('#foto_ktp').val(data_uri);
            });
        });

        // Form Validation for hidden webcam inputs
        $('#tambahTamuModal form').on('submit', function(e) {
            if (!$('#foto_wajah').val()) {
                e.preventDefault();
                Swal.fire('Peringatan', 'Silakan ambil foto wajah terlebih dahulu.', 'warning');
                return false;
            }
            if (!$('#foto_ktp').val()) {
                e.preventDefault();
                Swal.fire('Peringatan', 'Silakan ambil foto KTP terlebih dahulu.', 'warning');
                return false;
            }
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

        // Handler untuk tombol View
        $('.btn-view').click(function() {
            $('#v_nama').text($(this).data('nama'));
            $('#v_notelp').text($(this).data('notelp'));
            $('#v_plat').text($(this).data('plat') || '-');
            $('#v_tujuan').text($(this).data('tujuan'));
            $('#v_keperluan').text($(this).data('keperluan'));
            $('#v_jamin').text($(this).data('jamin'));
            $('#v_jamout').text($(this).data('jamout'));
            
            var fw = $(this).data('fotowajah');
            var fk = $(this).data('fotoktp');
            
            if(fw) {
                $('#v_fotowajah').attr('src', fw).show();
            } else {
                $('#v_fotowajah').hide();
            }
            
            if(fk) {
                $('#v_fotoktp').attr('src', fk).show();
            } else {
                $('#v_fotoktp').hide();
            }
            
            $('#viewTamuModal').modal('show');
        });

        // Initialize select2 for Edit Modal
        $('.select2-edit').select2({
            placeholder: "-- Pilih Karyawan --",
            allowClear: true,
            dropdownParent: $('#editTamuModal')
        });

        // Webcam Edit setup
        let webcamEditAttached = false;
        $('#editTamuModal').on('shown.bs.modal', function () {
            if(!webcamEditAttached) {
                Webcam.set({
                    width: 320,
                    height: 240,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });
                Webcam.attach('#my_camera_edit');
                webcamEditAttached = true;
            }
        });

        $('#editTamuModal').on('hidden.bs.modal', function () {
            if(webcamEditAttached) {
                Webcam.reset();
                webcamEditAttached = false;
            }
        });

        $('#btn-capture-wajah-edit').click(function() {
            Webcam.snap(function(data_uri) {
                $('#e_results_wajah').html('<img src="'+data_uri+'" style="width:100%; height:100%; object-fit:contain;"/>');
                $('#e_foto_wajah').val(data_uri);
            });
        });

        $('#btn-capture-ktp-edit').click(function() {
            Webcam.snap(function(data_uri) {
                $('#e_results_ktp').html('<img src="'+data_uri+'" style="width:100%; height:100%; object-fit:contain;"/>');
                $('#e_foto_ktp').val(data_uri);
            });
        });

        // Handler untuk tombol Edit
        $('.btn-edit').click(function() {
            var id = $(this).data('id');
            $('#formEditTamu').attr('action', '{{ url("tamu") }}/' + id);
            
            $('#e_nama').val($(this).data('nama'));
            $('#e_notelp').val($(this).data('notelp'));
            $('#e_plat').val($(this).data('plat'));
            $('#e_keperluan').val($(this).data('keperluan'));
            $('#e_tujuan').val($(this).data('tujuan')).trigger('change');
            
            var fw = $(this).data('fotowajah');
            var fk = $(this).data('fotoktp');
            
            if(fw) {
                $('#e_results_wajah').html('<img src="{{ asset("storage") }}/'+fw+'" style="width:100%; height:100%; object-fit:contain;"/>');
                $('#e_foto_wajah').val(fw);
            } else {
                $('#e_results_wajah').html('<span class="text-muted text-sm">Belum ada foto</span>');
                $('#e_foto_wajah').val('');
            }
            
            if(fk) {
                $('#e_results_ktp').html('<img src="{{ asset("storage") }}/'+fk+'" style="width:100%; height:100%; object-fit:contain;"/>');
                $('#e_foto_ktp').val(fk);
            } else {
                $('#e_results_ktp').html('<span class="text-muted text-sm">Belum ada foto</span>');
                $('#e_foto_ktp').val('');
            }
            
            $('#editTamuModal').modal('show');
        });

    });
</script>
@endpush
