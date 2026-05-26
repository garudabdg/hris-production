<form action="{{ route('harilibur.update', ['kode_libur' => Crypt::encrypt($harilibur->kode_libur)]) }}" method="POST" id="formHariLibur">
    @csrf
    @method('PUT')
    <x-input-with-icon icon="ti ti-calendar" label="Tanggal" name="tanggal" datepicker="flatpickr-date" :value="$harilibur->tanggal" />
    @if ($user->hasRole(['super admin', 'admin pusat']))
        <x-select label="Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang" select2="select2Kodecabang"
            upperCase="true" :selected="$harilibur->kode_cabang" />
    @endif
    <x-textarea label="Keterangan" name="keterangan" :value="$harilibur->keterangan" />
    <div class="form-group mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_cuti_bersama" id="is_cuti_bersama" value="1"
                {{ $harilibur->is_cuti_bersama ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_cuti_bersama">
                <i class="ti ti-calendar-minus me-1 text-warning"></i> Cuti Bersama
            </label>
            <div class="form-text text-muted" style="font-size: 0.75rem;">
                Jika diaktifkan, jatah cuti tahunan karyawan akan otomatis terpotong saat ditambahkan ke hari libur ini.
            </div>
        </div>
    </div>
    <div class="form-group mb-3">
        <button class="btn btn-primary w-100" id="btnSimpan"><i class="ti ti-send me-1"></i>Simpan</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        const form = $('#formHariLibur');
        $(".flatpickr-date").flatpickr();
        const select2Kodecabang = $(".select2Kodecabang");

        if (select2Kodecabang.length) {
            select2Kodecabang.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Pilih Cabang',
                    dropdownParent: $this.parent()
                });
            });
        }





        function buttonDisable() {
            $("#btnSimpan").prop('disabled', true);
            $("#btnSimpan").html(`
            <div class="spinner-border spinner-border-sm text-white me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            Loading..`);
        }
        form.submit(function(e) {
            //e.preventDefault();
            const tanggal = form.find("#tanggal").val();
            const kode_cabang = form.find("#kode_cabang").val();
            const keterangan = form.find("#keterangan").val();

            if (tanggal == "") {
                Swal.fire({
                    title: "Oops!",
                    text: "Tanggal Harus Diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: (e) => {
                        form.find("#tanggal").focus();
                    },
                });
                return false;
            } else if (kode_cabang == "") {
                Swal.fire({
                    title: "Oops!",
                    text: "Cabang Harus Diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: (e) => {
                        form.find("#kode_cabang").focus();
                    },
                });
                return false;
            } else if (keterangan == "") {
                Swal.fire({
                    title: "Oops!",
                    text: "Keterangan Harus Diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: (e) => {
                        form.find("#keterangan").focus();
                    },
                });
                return false;
            } else {
                buttonDisable();
            }
        });

    });
</script>
