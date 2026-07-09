// ── Inisialisasi Fitur ID Control List ──
// Menyiapkan DataTable dan SweetAlert untuk aksi hapus
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi DataTable jika digunakan (asumsi menggunakan id table-id-control-list)
    if ($.fn.DataTable && $('#table-id-control-list').length > 0) {
        if ($.fn.DataTable.isDataTable('#table-id-control-list')) {
            $('#table-id-control-list').DataTable().destroy();
        }
        $('#table-id-control-list').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });
    }

    $(function() {
        if ($.fn.select2) {
            const selectNamaPengguna = $(".select2NamaPengguna");
            if (selectNamaPengguna.length) {
                selectNamaPengguna.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Pilih Karyawan...',
                        allowClear: true,
                        dropdownParent: $this.parent(),
                        width: '100%'
                    });
                });
            }
        }
    });

    // Handle Delete Button dengan SweetAlert
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
