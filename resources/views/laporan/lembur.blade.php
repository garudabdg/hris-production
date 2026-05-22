@extends('layouts.app')
@section('titlepage', 'Laporan Lembur')

@section('content')
@section('navigasi')
    <span>Laporan Lembur</span>
@endsection
<div class="row">
    <div class="col-lg-6 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('laporan.cetaklembur') }}" method="POST" target="_blank" id="formLaporanLembur">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label text-muted small mb-1">Cabang</label>
                        <select name="kode_cabang[]" id="kode_cabang_lembur" class="form-select select2Kodecabanglembur" multiple>
                            @foreach ($cabang as $d)
                                <option value="{{ $d->kode_cabang }}">{{ textUpperCase($d->nama_cabang) }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted small mt-1"><i class="ti ti-info-circle me-1"></i>Kosongkan untuk semua cabang</div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label text-muted small mb-1">Departemen</label>
                        <select name="kode_dept[]" id="kode_dept_lembur" class="form-select select2Kodedeptlembur" multiple>
                            @foreach ($departemen as $d)
                                <option value="{{ $d->kode_dept }}">{{ textUpperCase($d->nama_dept) }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted small mt-1"><i class="ti ti-info-circle me-1"></i>Kosongkan untuk semua departemen</div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label text-muted small mb-1">Karyawan</label>
                        <select name="nik[]" id="nik_lembur" class="form-select select2Niklembur" multiple>
                        </select>
                        <div class="text-muted small mt-1"><i class="ti ti-info-circle me-1"></i>Kosongkan untuk semua karyawan</div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label text-muted small mb-1">Status Persetujuan</label>
                        <select name="status" id="status" class="form-select select2Status">
                            <option value="">Semua Status</option>
                            <option value="0">Pending (Menunggu)</option>
                            <option value="1">Disetujui</option>
                            <option value="2">Ditolak</option>
                        </select>
                    </div>
                    <div class="row" id="baris_tanggal">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="form-label text-muted small mb-1">Dari Tanggal</label>
                                <input type="text" name="dari" id="dari" class="form-control flatpickr-date"
                                    placeholder="Dari" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="form-label text-muted small mb-1">Sampai Tanggal</label>
                                <input type="text" name="sampai" id="sampai" class="form-control flatpickr-date"
                                    placeholder="Sampai" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
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
        const select2Kodecabanglembur = $(".select2Kodecabanglembur");
        if (select2Kodecabanglembur.length) {
            select2Kodecabanglembur.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Cabang',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        const select2Kodedeptlembur = $(".select2Kodedeptlembur");
        if (select2Kodedeptlembur.length) {
            select2Kodedeptlembur.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Departemen',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        const select2Niklembur = $(".select2Niklembur");
        if (select2Niklembur.length) {
            select2Niklembur.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Karyawan',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        const select2Status = $(".select2Status");
        if (select2Status.length) {
            select2Status.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Status',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        function loadKaryawan() {
            const kode_cabang = $("#kode_cabang_lembur").val(); // array
            const kode_dept = $("#kode_dept_lembur").val();     // array
            
            $.ajax({
                type: "GET",
                url: "{{ route('karyawan.getkaryawan') }}",
                data: {
                    kode_cabang: kode_cabang,
                    kode_dept: kode_dept
                },
                cache: false,
                success: function(respond) {
                    const selected = $("#nik_lembur").val() || [];

                    $("#nik_lembur").empty();
                    respond.forEach(function(item) {
                        const isSelected = selected.includes(item.nik);
                        $("#nik_lembur").append(
                            $("<option>", {
                                value: item.nik,
                                text: item.nik + " - " + item.nama_karyawan,
                                selected: isSelected
                            })
                        );
                    });
                    $("#nik_lembur").trigger("change");
                }
            });
        }

        $("#kode_cabang_lembur, #kode_dept_lembur").change(function() {
            loadKaryawan();
        });

        loadKaryawan();

        $("#formLaporanLembur").submit(function(e) {
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
        });
    });
</script>
@endpush
