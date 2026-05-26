<form action="#" id="frmKaryawan">
    <div class="row mb-3">
        <div class="col">
            <div class="d-flex justify-content-between">
                <button class="btn btn-primary" id="tambahkansemua"><i class="ti ti-plus me-1"></i> Tambahkan Semua </button>
                <button class="btn btn-danger" id="batalkansemua"><i class="ti ti-circle-minus me-1"></i> Batalkan Semua </button>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-6 col-md-12 col-sm-12">
            <div class="form-group mb-3">
                <label for="kode_dept" class="form-label" style="font-weight: 600;">Departemen</label>
                <select name="kode_dept[]" id="kode_dept" class="form-select select2DeptMultiple" multiple>
                    @foreach ($departemen as $d)
                        <option value="{{ $d->kode_dept }}">{{ strtoupper(strtolower($d->nama_dept)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12">
            <x-input-with-icon label="Nama Karyawan" name="nama_karyawan" icon="ti ti-user" />
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-bordered table-striped table-hover" id="tabelkaryawan">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>NIK</th>
                        <th>Nama Karyawan</th>
                        <th>Dept</th>
                        <th>#</th>
                    </tr>
                </thead>
                <tbody id="loadkaryawan">

                </tbody>
            </table>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        const form = $('#frmKaryawan');

        // Initialize select2 multi-select for departemen
        const select2DeptMultiple = $(".select2DeptMultiple");
        if (select2DeptMultiple.length) {
            select2DeptMultiple.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: "Pilih Departemen (bisa pilih banyak)",
                    dropdownParent: $this.parent(),
                    allowClear: true,
                });
            });
        }

        function loadliburkaryawan() {
            const kode_libur = "{{ Crypt::encrypt($harilibur->kode_libur) }}";
            $("#loadliburkaryawan").html(`<tr><td colspan="4" class="text-center">Loading...</td></tr>`);
            $("#loadliburkaryawan").load(`/harilibur/${kode_libur}/getkaryawanlibur`);
        }

        function loadkaryawan() {
            const kode_libur = "{{ Crypt::encrypt($harilibur->kode_libur) }}";
            const kode_dept = form.find("#kode_dept").val(); // Returns array for multi-select
            const nama_karyawan = form.find("#nama_karyawan").val();
            $.ajax({
                type: 'POST',
                url: `/harilibur/getkaryawan`,
                data: {
                    _token: "{{ csrf_token() }}",
                    kode_libur: kode_libur,
                    kode_dept: kode_dept,
                    nama_karyawan: nama_karyawan
                },
                cache: false,
                success: function(respond) {
                    $("#loadkaryawan").html(respond);
                    loadliburkaryawan();
                }
            })
        }

        loadkaryawan();

        form.find("#kode_dept").change(function() {
            $("#loadkaryawan").html(`<tr><td colspan="5" class="text-center">Tunggu Sebentar...</td></tr>`);
            loadkaryawan();
        });

        form.find("#nama_karyawan").keyup(function() {
            $("#loadkaryawan").html(`<tr><td colspan="5" class="text-center">Tunggu Sebentar...</td></tr>`);
            loadkaryawan();
        });

        $(document).off('click').on('click', '#tabelkaryawan .updateLibur', function(e) {
            e.preventDefault();
            const nik = $(this).attr('nik');
            const kode_libur = "{{ $harilibur->kode_libur }}";
            //Ubah pada kolom Status Jadwal menjadi loading
            $(this).html('<i class="fas fa-spinner fa-spin"></i>');
            $.ajax({
                type: 'POST',
                url: `/harilibur/updateliburkaryawan`,
                data: {
                    _token: "{{ csrf_token() }}",
                    nik: nik,
                    kode_libur: kode_libur
                },
                cache: false,
                success: function(respond) {
                    if (respond.success == true) {
                        loadkaryawan();
                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: respond.message,
                            icon: "warning",
                            showConfirmButton: true,
                        });

                    }
                }
            });
        });

        $("#tambahkansemua").click(function(e) {
            e.preventDefault();
            const kode_libur = "{{ $harilibur->kode_libur }}";
            const kode_dept = form.find("#kode_dept").val(); // Returns array for multi-select
            if (kode_dept === null || kode_dept.length === 0) {
                Swal.fire({
                    title: "Perhatian!",
                    text: "Pilih minimal satu departemen terlebih dahulu",
                    icon: "warning",
                    showConfirmButton: true,
                });
                return;
            }
            Swal.fire({
                title: "Konfirmasi",
                text: "Tambahkan semua karyawan dari " + kode_dept.length + " departemen yang dipilih?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Tambahkan!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#loadkaryawan").html(`<tr><td colspan="5" class="text-center">Tunggu Sebentar....</td></tr>`);
                    $.ajax({
                        type: 'POST',
                        url: `/harilibur/tambahkansemua`,
                        data: {
                            _token: "{{ csrf_token() }}",
                            kode_libur: kode_libur,
                            kode_dept: kode_dept
                        },
                        cache: false,
                        success: function(respond) {
                            if (respond.success == true) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: respond.message,
                                    icon: "success",
                                    showConfirmButton: true,
                                });
                                loadkaryawan();
                            } else {
                                Swal.fire({
                                    title: "Oops!",
                                    text: respond.message,
                                    icon: "warning",
                                    showConfirmButton: true,
                                });
                            }
                        }
                    });
                }
            });
        });

        $("#batalkansemua").click(function(e) {
            e.preventDefault();
            const kode_libur = "{{ $harilibur->kode_libur }}";
            const kode_dept = form.find("#kode_dept").val(); // Returns array for multi-select
            if (kode_dept === null || kode_dept.length === 0) {
                Swal.fire({
                    title: "Perhatian!",
                    text: "Pilih minimal satu departemen terlebih dahulu",
                    icon: "warning",
                    showConfirmButton: true,
                });
                return;
            }
            Swal.fire({
                title: "Konfirmasi",
                text: "Batalkan semua karyawan dari " + kode_dept.length + " departemen yang dipilih?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Batalkan!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#loadkaryawan").html(`<tr><td colspan="5" class="text-center">Tunggu Sebentar....</td></tr>`);
                    $.ajax({
                        type: 'POST',
                        url: `/harilibur/batalkansemua`,
                        data: {
                            _token: "{{ csrf_token() }}",
                            kode_libur: kode_libur,
                            kode_dept: kode_dept
                        },
                        cache: false,
                        success: function(respond) {
                            if (respond.success == true) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: respond.message,
                                    icon: "success",
                                    showConfirmButton: true,
                                });
                                loadkaryawan();
                            } else {
                                Swal.fire({
                                    title: "Oops!",
                                    text: respond.message,
                                    icon: "warning",
                                    showConfirmButton: true,
                                });
                            }
                        }
                    });
                }
            });
        });
    });
</script>
