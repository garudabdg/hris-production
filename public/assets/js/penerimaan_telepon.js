// ── Inisialisasi Komponen ──
// Menyiapkan komponen UI saat dokumen siap
$(function() {
    // Inisialisasi flatpickr untuk input tanggal
    $('.flatpickr-date').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true
    });

    // Inisialisasi select2 untuk dropdown Karyawan di dalam modal tambah
    $('#tujuan').select2({
        placeholder: "-- Pilih Karyawan --",
        allowClear: true,
        dropdownParent: $('#tambahTeleponModal')
    });

    // Inisialisasi select2 untuk Edit Modal
    $('.select2-edit').select2({
        placeholder: "-- Pilih Karyawan --",
        allowClear: true,
        dropdownParent: $('#editTeleponModal')
    });

    // ── Handler Autocomplete Departemen ──
    // Mengisi otomatis input departemen berdasarkan karyawan yang dipilih
    $('#tujuan').on('select2:select', function (e) {
        var dept = $(e.params.data.element).attr('data-departemen');
        $('#departemen').val(dept || '');
    });
    
    // Untuk fitur clear (bila allowClear diaktifkan)
    $('#tujuan').on('select2:unselect', function (e) {
        $('#departemen').val('');
    });

    $('#e_tujuan').on('select2:select', function (e) {
        var dept = $(e.params.data.element).attr('data-departemen');
        $('#e_departemen').val(dept || '');
    });

    $('#e_tujuan').on('select2:unselect', function (e) {
        $('#e_departemen').val('');
    });

    // ── Handler Tombol View ──
    // Mengisi data ke modal view saat tombol detail diklik
    $('.btn-view').click(function() {
        $('#v_nama').text($(this).data('nama'));
        $('#v_notelp').text($(this).data('notelp'));
        $('#v_perusahaan').text($(this).data('perusahaan') || '-');
        $('#v_tujuan').text($(this).data('tujuan'));
        $('#v_departemen').text($(this).data('departemen'));
        $('#v_keperluan').text($(this).data('keperluan'));
        $('#v_tindak').text($(this).data('tindak'));
        $('#v_pesan').text($(this).data('pesan') || '-');
        $('#v_waktu').text($(this).data('waktu'));
        
        $('#viewTeleponModal').modal('show');
    });

    // ── Handler Tombol Edit ──
    // Mengisi data ke form edit saat tombol edit diklik
    $('.btn-edit').click(function() {
        var id = $(this).data('id');
        $('#formEditTelepon').attr('action', window.PenerimaanTeleponConfig.baseUrl + '/' + id);
        
        $('#e_nama_penelpon').val($(this).data('nama'));
        $('#e_no_telp').val($(this).data('notelp'));
        $('#e_nama_perusahaan').val($(this).data('perusahaan'));
        $('#e_departemen').val($(this).data('departemen'));
        $('#e_keperluan').val($(this).data('keperluan'));
        $('#e_pesan').val($(this).data('pesan'));
        
        // Set Select2
        $('#e_tujuan').val($(this).data('tujuan')).trigger('change');
        
        // Set Radio Buttons for tindak_lanjut
        var tindak = $(this).data('tindak');
        $('.e_tindak_lanjut').each(function() {
            if ($(this).val() == tindak) {
                $(this).prop('checked', true);
            }
        });
        
        $('#editTeleponModal').modal('show');
    });

    // ── Handler Hapus Data ──
    // Konfirmasi sebelum menghapus data menggunakan SweetAlert
    $('.delete-confirm').click(function(e) {
        var form = $(this).closest('form');
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus data ini?',
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
