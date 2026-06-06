<form action="{{ route('karyawan.update', Crypt::encrypt($karyawan->nik)) }}" id="formcreateKaryawan" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-input-with-icon-label icon="ti ti-barcode" label="NIK" name="nik_show" value="{{ $karyawan->nik_show ?? $karyawan->nik }}" />
    <x-input-with-icon-label icon="ti ti-credit-card" label="No. KTP" name="no_ktp" value="{{ $karyawan->no_ktp }}" />
    <div id="ktp-alert" class="text-danger" style="display: none; font-size: 0.85em; margin-top: -10px; margin-bottom: 10px;">
        <i class="ti ti-alert-circle"></i> Nomor KTP tidak boleh lebih dari 16 digit angka.
    </div>
    <x-input-with-icon-label icon="ti ti-user" label="Nama Karyawan" name="nama_karyawan" value="{{ $karyawan->nama_karyawan }}" />
    <div class="row">
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-map-pin" label="Tempat Lahir" name="tempat_lahir" value="{{ $karyawan->tempat_lahir }}" />
        </div>
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-calendar" type="date" label="Tanggal Lahir" name="tanggal_lahir"
                value="{{ $karyawan->tanggal_lahir }}" />
        </div>
    </div>
    <x-textarea-label label="Alamat" name="alamat" value="{{ $karyawan->alamat }}" />
    <div class="form-group mb-3">
        <label for="exampleFormControlInput1" style="font-weight: 600" class="form-label">Jenis Kelamin</label>
        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
            <option value="">Jenis Kelamin</option>
            <option value="L" {{ $karyawan->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki - Laki</option>
            <option value="P" {{ $karyawan->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
    </div>
    <x-input-with-icon-label icon="ti ti-phone" label="No. HP" name="no_hp" value="{{ $karyawan->no_hp }}" />
    <div id="nohp-alert" class="text-danger" style="display: none; font-size: 0.85em; margin-top: -10px; margin-bottom: 10px;">
        <i class="ti ti-alert-circle"></i> Pesan Error HP.
    </div>
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12">
            <x-select-label label="Status Perkawinan" name="kode_status_kawin" :data="$status_kawin" key="kode_status_kawin" textShow="status_kawin"
                kode="true" selected="{{ $karyawan->kode_status_kawin }}" />
        </div>
        <div class="col-lg-6 col-sm-12 col-md-12">
            <div class="form-group mb-3">
                <label for="exampleFormControlInput1" style="font-weight: 600" class="form-label">Pendidikan
                    Terakhir</label>
                <select name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-select">
                    <option value="">Pendidikan Terakhir</option>
                    <option value="SD" {{ $karyawan->pendidikan_terakhir == 'SD' ? 'selected' : '' }}>SD</option>
                    <option value="SMP" {{ $karyawan->pendidikan_terakhir == 'SMP' ? 'selected' : '' }}>SMP</option>
                    <option value="SMA" {{ $karyawan->pendidikan_terakhir == 'SMA' ? 'selected' : '' }}>SMA</option>
                    <option value="SMK" {{ $karyawan->pendidikan_terakhir == 'SMK' ? 'selected' : '' }}>SMK</option>
                    <option value="D1" {{ $karyawan->pendidikan_terakhir == 'D1' ? 'selected' : '' }}>D1</option>
                    <option value="D2" {{ $karyawan->pendidikan_terakhir == 'D2' ? 'selected' : '' }}>D2</option>
                    <option value="D3" {{ $karyawan->pendidikan_terakhir == 'D3' ? 'selected' : '' }}>D3</option>
                    <option value="D4" {{ $karyawan->pendidikan_terakhir == 'D4' ? 'selected' : '' }}>D4</option>
                    <option value="S1" {{ $karyawan->pendidikan_terakhir == 'S1' ? 'selected' : '' }}>S1</option>
                    <option value="S2" {{ $karyawan->pendidikan_terakhir == 'S2' ? 'selected' : '' }}>S2</option>
                    <option value="S3" {{ $karyawan->pendidikan_terakhir == 'S3' ? 'selected' : '' }}>S3</option>
                </select>
            </div>
        </div>
    </div>
    <x-select-label label="Kantor Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang"
        selected="{{ $karyawan->kode_cabang }}" />
    <x-select-label label="Departemen" name="kode_dept" :data="$departemen" selected="{{ $karyawan->kode_dept }}" key="kode_dept" textShow="nama_dept"
        upperCase="true" />
    
    <!-- Sub Departemen / Team -->
    <div id="subDepartemenContainer" style="display: none;">
        <div class="form-group mb-3">
            <label for="sub_departemen" style="font-weight: 600" class="form-label">Masukan <span id="depName">Team</span> <span id="depNameDynamic"></span></label>
            <select name="sub_departemen" id="sub_departemen" class="form-select">
                <option value="">Pilih Team</option>
            </select>
        </div>
    </div>
            </select>
        </div>
    </div>
    <x-select-label label="Jabatan" name="kode_jabatan" :data="$jabatan" selected="{{ $karyawan->kode_jabatan }}" key="kode_jabatan"
        textShow="nama_jabatan" upperCase="true" />
    <x-input-with-icon-label icon="ti ti-calendar" type="date" label="Tanggal Masuk" name="tanggal_masuk"
        value="{{ $karyawan->tanggal_masuk }}" />
   <div class="form-group mb-3">
    <label for="exampleFormControlInput1" style="font-weight: 600" class="form-label">Status Karyawan</label>
    <select name="status_karyawan" id="status_karyawan" class="form-select">
        <option value="">Status Karyawan</option>
        <option value="K" {{ $karyawan->status_karyawan == 'K' ? 'selected' : '' }}>Kontrak</option>
        <option value="T" {{ $karyawan->status_karyawan == 'T' ? 'selected' : '' }}>Tetap</option>
        <option value="M" {{ $karyawan->status_karyawan == 'M' ? 'selected' : '' }}>Mitra</option>
        <option value="O" {{ $karyawan->status_karyawan == 'O' ? 'selected' : '' }}>Outsourcing</option>
        <option value="P" {{ $karyawan->status_karyawan == 'P' ? 'selected' : '' }}>Probition</option>
        <option value="G" {{ $karyawan->status_karyawan == 'G' ? 'selected' : '' }}>Magang</option>
    </select>
</div>

    <div class="form-group mb-3">
        <label for="exampleFormControlInput1" style="font-weight: 600" class="form-label">Status Aktif Karyawan</label>
        <select name="status_aktif_karyawan" id="status_aktif_karyawan" class="form-select">
            <option value="">Status Aktif Karyawan</option>
            <option value="1" {{ $karyawan->status_aktif_karyawan == '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ $karyawan->status_aktif_karyawan === '0' ? 'selected' : '' }}>Non Aktif</option>
        </select>
    </div>
    <div id="tanggalNonaktifContainer" style="display: {{ $karyawan->status_aktif_karyawan === '0' ? 'block' : 'none' }};">
        <x-input-with-icon-label icon="ti ti-calendar" type="date" label="Tanggal Non-Aktif" name="tanggal_nonaktif"
            value="{{ $karyawan->tanggal_nonaktif }}" />
    </div>

    <div class="row mb-3 mt-3">
        <div class="col-12">
            <label style="font-weight: 600" class="form-label d-block">Pengaturan Presensi</label>
            <div class="d-flex gap-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="lock_location" value="0">
                    <input class="form-check-input" type="checkbox" name="lock_location" value="1" id="lock_location_edit" {{ $karyawan->lock_location == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="lock_location_edit">Lock Location (Radius)</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="lock_jam_kerja" value="0">
                    <input class="form-check-input" type="checkbox" name="lock_jam_kerja" value="1" id="lock_jam_kerja_edit" {{ $karyawan->lock_jam_kerja == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="lock_jam_kerja_edit">Lock Jam Kerja</label>
                </div>
            </div>
        </div>
    </div>
    <x-input-file name="foto" label="Foto" />

    <x-input-with-icon-label icon="ti ti-id" label="RFID UID" name="rfid_uid" value="{{ $karyawan->rfid_uid }}" />
    <x-input-with-icon icon="ti ti-fingerprint" label="PIN Finger Print" name="pin" value="{{ $karyawan->pin }}" />
    <div class="form-group">
        <button class="btn btn-primary w-100" type="submit">
            <ion-icon name="send-outline" class="me-1"></ion-icon>
            Submit
        </button>
    </div>
</form>
<script src="{{ asset('assets/js/pages/karyawan.js') }}"></script>
<script src="{{ asset('assets/js/jquery.mask.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

<script>
    $(document).ready(function() {
        $(".flatpickr-date").flatpickr();
        
        // Set maxlength pada input KTP
        $('#no_ktp').attr('maxlength', '16');

        // Set maxlength pada input No HP
        $('#no_hp').attr('maxlength', '13');

        // Validasi KTP: hanya angka, maksimal 16, tampilkan alert jika lebih
        $('#no_ktp').on('keypress', function(e) {
            var charCode = (e.which) ? e.which : e.keyCode;
            // Hanya izinkan angka
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                e.preventDefault();
                return false;
            }

            // Tampilkan alert jika sudah 16 digit dan mencoba mengetik lagi, kecuali jika ada text yang diblok (selection)
            var selectionLength = this.selectionEnd - this.selectionStart;
            if ($(this).val().length >= 16 && selectionLength === 0) {
                $('#ktp-alert').fadeIn('fast');
                setTimeout(function() {
                    $('#ktp-alert').fadeOut('fast');
                }, 3000);
                e.preventDefault();
                return false;
            }
        });

        // Validasi No HP: hanya angka, wajib berawalan 0, maksimal 13
        $('#no_hp').on('keypress', function(e) {
            var charCode = (e.which) ? e.which : e.keyCode;
            
            // Hanya izinkan angka
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                e.preventDefault();
                return false;
            }

            var val = $(this).val();
            var selectionLength = this.selectionEnd - this.selectionStart;

            // Jika ini karakter pertama, wajib '0'
            if (val.length === 0 && charCode !== 48) {
                $('#nohp-alert').html('<i class="ti ti-alert-circle"></i> Nomor HP wajib diawali dengan angka 0.').fadeIn('fast');
                setTimeout(function() { $('#nohp-alert').fadeOut('fast'); }, 3000);
                e.preventDefault();
                return false;
            }

            // Tampilkan alert jika sudah 13 digit dan mencoba mengetik lagi
            if (val.length >= 13 && selectionLength === 0) {
                $('#nohp-alert').html('<i class="ti ti-alert-circle"></i> Nomor HP maksimal 13 digit angka.').fadeIn('fast');
                setTimeout(function() { $('#nohp-alert').fadeOut('fast'); }, 3000);
                e.preventDefault();
                return false;
            }
        });

        // Tangani jika user melakukan paste pada KTP
        $('#no_ktp').on('paste', function(e) {
            var pastedData = e.originalEvent.clipboardData.getData('text');
            var num = pastedData.replace(/[^0-9]/g, '');
            if ($(this).val().length + num.length > 16) {
                $('#ktp-alert').fadeIn('fast');
                setTimeout(function() {
                    $('#ktp-alert').fadeOut('fast');
                }, 3000);
            }
            
            // Set value secara manual agar tidak berbenturan dengan form validation
            e.preventDefault();
            var newVal = ($(this).val() + num).substring(0, 16);
            $(this).val(newVal).trigger('input'); // trigger input untuk menghilangkan error
        });

        // Tangani jika user melakukan paste pada No HP
        $('#no_hp').on('paste', function(e) {
            var pastedData = e.originalEvent.clipboardData.getData('text');
            var num = pastedData.replace(/[^0-9]/g, '');
            
            e.preventDefault();
            
            var currentVal = $(this).val();
            var newVal = currentVal + num;
            
            // Jika hasilnya tidak diawali 0
            if (newVal.length > 0 && newVal.charAt(0) !== '0') {
                newVal = '0' + newVal;
            }
            
            if (newVal.length > 13) {
                $('#nohp-alert').html('<i class="ti ti-alert-circle"></i> Nomor HP maksimal 13 digit angka.').fadeIn('fast');
                setTimeout(function() { $('#nohp-alert').fadeOut('fast'); }, 3000);
            }

            newVal = newVal.substring(0, 13);
            $(this).val(newVal).trigger('input');
        });
        
        // Variable for pre-selection (used by index.blade.php event delegation)
        window.currentSubDept = "{{ $karyawan->sub_departemen }}";
    });
</script>
