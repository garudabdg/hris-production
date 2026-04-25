@extends('layouts.app')
@section('titlepage', 'Laporan Presensi & Gaji')

@section('content')
@section('navigasi')
    <span>Laporan Presensi & Gaji</span>
@endsection
<div class="row">
    <div class="col-lg-6 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('laporan.cetakpresensi') }}" method="POST" target="_blank" id="formPresensi">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label text-muted small mb-1">Cabang</label>
                        <select name="kode_cabang[]" id="kode_cabang_presensi" class="form-select select2Kodecabangpresensi" multiple>
                            @foreach ($cabang as $d)
                                <option value="{{ $d->kode_cabang }}">{{ textUpperCase($d->nama_cabang) }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted small mt-1"><i class="ti ti-info-circle me-1"></i>Kosongkan untuk semua cabang</div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label text-muted small mb-1">Departemen</label>
                        <select name="kode_dept[]" id="kode_dept_presensi" class="form-select select2Kodedeptpresensi" multiple>
                            @foreach ($departemen as $d)
                                <option value="{{ $d->kode_dept }}">{{ textUpperCase($d->nama_dept) }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted small mt-1"><i class="ti ti-info-circle me-1"></i>Kosongkan untuk semua departemen</div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label text-muted small mb-1">Karyawan</label>
                        <select name="nik[]" id="nik_presensi" class="form-select select2Nikpresensi" multiple>
                        </select>
                        <div class="text-muted small mt-1"><i class="ti ti-info-circle me-1"></i>Kosongkan untuk semua karyawan</div>
                    </div>
                    <div class="form-group mb-3">
                        <select name="periode_laporan" id="periode_laporan" class="form-select">
                            <option value="">Periode Laporan</option>
                            <option value="1" selected>Periode Gaji</option>
                            <option value="2">Bulan Berjalan</option>
                            <option value="3">Range Tanggal</option>
                        </select>
                    </div>
                    <div class="row" id="baris_tanggal">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <input type="text" name="dari" id="dari" class="form-control flatpickr-date"
                                    placeholder="Dari">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <input type="text" name="sampai" id="sampai" class="form-control flatpickr-date"
                                    placeholder="Sampai">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <select name="format_laporan" id="format_laporan" class="form-select">
                            <option value="">Format Laporan</option>
                            <option value="1" selected>Laporan Presensi</option>
                            <option value="2">Laporan Gaji</option>
                            <option value="3">Slip Gaji</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="baris_jenis_upah">
                        <select name="jenis_upah" id="jenis_upah" class="form-select">
                            <option value="" disabled selected>Pilih Jenis Upah</option>
                            <option value="Bulanan">Bulanan</option>
                            <option value="Harian">Harian</option>
                        </select>
                    </div>
                    {{-- <div class="form-group mb-3">
                        <select name="format_laporan" id="format_laporan" class="form-select">
                            <option value="">Format Laporan</option>
                            <option value="1" selected>Standar</option>
                            <option value="2">P/S/M</option>
                        </select>
                    </div> --}}

                    <div class="row" id="baris_bulan">
                        <div class="col">
                            <div class="form-group mb-3">
                                <select name="bulan" id="bulan" class="form-select">
                                    <option value="">Bulan</option>
                                    @foreach ($list_bulan as $d)
                                        <option {{ date('m') == $d['kode_bulan'] ? 'selected' : '' }} value="{{ $d['kode_bulan'] }}">
                                            {{ $d['nama_bulan'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="row" id="baris_tahun">
                        <div class="co">
                            <div class="form-group mb-3">
                                <select name="tahun" id="tahun" class="form-select">
                                    <option value="">Tahun</option>
                                    @for ($t = $start_year; $t <= date('Y'); $t++)
                                        <option {{ date('Y') == $t ? 'selected' : '' }} value="{{ $t }}">{{ $t }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <button type="submit" name="submitButton" class="btn btn-primary w-100" id="submitButton">
                                <i class="ti ti-printer me-1"></i> Cetak
                            </button>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <button type="submit" name="exportButton" class="btn btn-success w-100" id="exportButton">
                                <i class="ti ti-download me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
@push('myscript')
<script>
    $(function() {
        const select2Kodecabangpresensi = $(".select2Kodecabangpresensi");
        if (select2Kodecabangpresensi.length) {
            select2Kodecabangpresensi.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Cabang',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        const select2Kodedeptpresensi = $(".select2Kodedeptpresensi");
        if (select2Kodedeptpresensi.length) {
            select2Kodedeptpresensi.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Departemen',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        const select2Nikpresensi = $(".select2Nikpresensi");
        if (select2Nikpresensi.length) {
            select2Nikpresensi.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Karyawan',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        function loadKaryawan() {
            const kode_cabang = $("#kode_cabang_presensi").val(); // array
            const kode_dept = $("#kode_dept_presensi").val();     // array
            
            $.ajax({
                type: "GET",
                url: "{{ route('karyawan.getkaryawan') }}",
                data: {
                    kode_cabang: kode_cabang,
                    kode_dept: kode_dept
                },
                cache: false,
                success: function(respond) {
                    // Simpan pilihan yang sudah diselect sebelumnya
                    const selected = $("#nik_presensi").val() || [];

                    $("#nik_presensi").empty();
                    respond.forEach(function(item) {
                        const isSelected = selected.includes(item.nik);
                        $("#nik_presensi").append(
                            $("<option>", {
                                value: item.nik,
                                text: item.nik + " - " + item.nama_karyawan,
                                selected: isSelected
                            })
                        );
                    });
                    $("#nik_presensi").trigger("change");
                }
            });
        }

        $("#kode_cabang_presensi, #kode_dept_presensi").change(function() {
            loadKaryawan();
        });

        // Load karyawan saat pertama kali
        loadKaryawan();

        $("#formPresensi").submit(function(e) {
            const periode_laporan = $("#periode_laporan").val();
            const format_laporan = $("#format_laporan").val();
            const bulan = $(this).find("#bulan").val();
            const tahun = $(this).find("#tahun").val();
            if (periode_laporan == "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Periode Laporan harus diisi!',
                    showConfirmButton: true,
                    didClose: () => {
                        $("#periode_laporan").focus();
                    }
                });
                return false;
            } else if (format_laporan == "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Format Laporan harus diisi!',
                    showConfirmButton: true,
                    didClose: () => {
                        $("#format_laporan").focus();
                    }
                });
                return false;
            } else if (bulan == "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Bulan harus diisi!',
                    showConfirmButton: true,
                    didClose: () => {
                        $("#bulan").focus();
                    }
                });
                return false;
            } else if (tahun == "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Tahun harus diisi!',
                    showConfirmButton: true,
                    didClose: () => {
                        $("#tahun").focus();
                    }
                });
                return false;
            } else if (periode_laporan == "3") {
                const dari = $("#dari").val();
                const sampai = $("#sampai").val();
                if (dari == "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Tanggal Dari harus diisi!',
                        showConfirmButton: true,
                        didClose: () => {
                            $("#dari").focus();
                        }
                    });
                    return false;
                } else if (sampai == "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Tanggal Sampai harus diisi!',
                        showConfirmButton: true,
                        didClose: () => {
                            $("#sampai").focus();
                        }
                    });
                    return false;
                }
            }
        });

        function togglePeriodeLaporan() {
            const periode_laporan = $("#periode_laporan").val();
            if (periode_laporan == "3") {
                $("#baris_tanggal").show();
                $("#baris_bulan").hide();
                $("#baris_tahun").hide();
            } else {
                $("#baris_tanggal").hide();
                $("#baris_bulan").show();
                $("#baris_tahun").show();
            }
        }

        function toggleJenisUpah() {
            const format_laporan = $("#format_laporan").val();
            // 1 = Laporan Presensi, 2 = Laporan Gaji, 3 = Slip Gaji
            if (format_laporan == "1") {
                $("#baris_jenis_upah").hide();
                $("#jenis_upah").removeAttr('required');
            } else {
                $("#baris_jenis_upah").show();
                $("#jenis_upah").attr('required', true);
            }
        }

        togglePeriodeLaporan();
        toggleJenisUpah();

        $("#periode_laporan").change(function() {
            togglePeriodeLaporan();
        });

        $("#format_laporan").change(function() {
            toggleJenisUpah();
        });
    });
</script>
@endpush
