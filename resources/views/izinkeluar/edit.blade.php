<form action="{{ route('izinkeluar.update', Crypt::encrypt($izinkeluar->kode_izin_keluar)) }}" id="formEditIzinKeluar" method="POST">
    @csrf
    @method('PUT')
    <x-select label="Karyawan" name="nik" :data="$karyawan" key="nik" textShow="nama_karyawan" upperCase="true"
        select2="select2Karyawan" selected="{{ $izinkeluar->nik }}" />
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <x-input-with-icon label="Tanggal" name="tanggal" icon="ti ti-calendar" datepicker="flatpickr-date" value="{{ $izinkeluar->tanggal }}" />
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-12 col-sm-12">
            <x-input-with-icon label="Jam Keluar" name="jam_keluar" icon="ti ti-clock" type="time" value="{{ $izinkeluar->jam_keluar }}" />
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12">
            <x-input-with-icon label="Jam Kembali (Opsional)" name="jam_kembali" icon="ti ti-clock" type="time" value="{{ $izinkeluar->jam_kembali }}" />
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <x-textarea label="Keperluan" name="keperluan" value="{{ $izinkeluar->keperluan }}" />
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <button class="btn btn-primary w-100"><i class="ti ti-send me-1"></i>Submit</button>
        </div>
    </div>
</form>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script>
    $(function() {
        $(".flatpickr-date").flatpickr();

        const select2Karyawan = $('.select2Karyawan');
        if (select2Karyawan.length) {
            select2Karyawan.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Pilih Karyawan',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        $("#formEditIzinKeluar").submit(function() {
            const nik = $(this).find("#nik").val();
            const tanggal = $(this).find("#tanggal").val();
            const jam_keluar = $(this).find("#jam_keluar").val();
            const keperluan = $(this).find("#keperluan").val();
            if (nik == "") {
                Swal.fire({
                    title: "Oops!",
                    text: "Karyawan Harus Diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: (e) => {
                        $(this).find("#nik").focus();
                    },
                });
                return false;
            } else if (tanggal == "") {
                Swal.fire({
                    title: "Oops!",
                    text: "Tanggal Harus Diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: (e) => {
                        $(this).find("#tanggal").focus();
                    },
                });
                return false;
            } else if (jam_keluar == "") {
                Swal.fire({
                    title: "Oops!",
                    text: "Jam Keluar Harus Diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: (e) => {
                        $(this).find("#jam_keluar").focus();
                    },
                });
                return false;
            } else if (keperluan == "") {
                Swal.fire({
                    title: "Oops!",
                    text: "Keperluan Harus Diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: (e) => {
                        $(this).find("#keperluan").focus();
                    },
                });
                return false;
            }
        });
    });
</script>
