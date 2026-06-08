/* ============================================
   File  : presensi_index.js
   Tujuan: Interaksi halaman Monitoring Presensi
   ============================================ */

$(function () {

    // ── Handler: Buka modal koreksi presensi ──
    // Dipanggil saat tombol edit (pensil) diklik
    $(document).on('click', '.koreksiPresensi', function () {
        let nik     = $(this).attr('nik');
        let tanggal = $(this).attr('tanggal');

        $.ajax({
            type  : 'POST',
            url   : window.PresensiConfig.routes.edit,
            data  : {
                _token  : window.PresensiConfig.csrfToken,
                nik     : nik,
                tanggal : tanggal
            },
            cache   : false,
            success : function (res) {
                $('#modal').modal('show');
                $('#modal').find('.modal-title').text('Koreksi Presensi');
                $('#loadmodal').html(res);
            }
        });
    });

    // ── Handler: Tampilkan foto presensi masuk / pulang ──
    // Klik pada jam masuk atau jam pulang untuk melihat foto
    $(".btnShowpresensi_in, .btnShowpresensi_out").click(function (e) {
        e.preventDefault();
        const id     = $(this).attr("id");
        const status = $(this).attr("status");

        // Tampilkan loading di modal
        $("#loadmodal").html(loadingSpinner());
        $("#modal").modal("show");
        $(".modal-title").text("Data Presensi");
        $("#loadmodal").load(`/presensi/${id}/${status}/show`);
    });

    // ── Handler: Ambil data dari mesin absensi ──
    // Klik tombol monitor (ikon desktop)
    $(".btngetDatamesin").click(function (e) {
        e.preventDefault();
        var pin     = $(this).attr("pin");
        var tanggal = $(this).attr("tanggal");

        // Tampilkan loading di modal
        $("#loadmodal").html(loadingSpinner());
        $("#modal").modal("show");
        $(".modal-title").text("Get Data Mesin");

        $.ajax({
            type    : 'POST',
            url     : '/presensi/getdatamesin',
            data    : {
                _token  : window.PresensiConfig.csrfToken,
                pin     : pin,
                tanggal : tanggal
            },
            cache   : false,
            success : function (respond) {
                $("#loadmodal").html(respond);
            }
        });
    });

    // ── Handler: Konfirmasi hapus presensi ──
    // Menampilkan dialog SweetAlert sebelum form submit
    $(".delete-confirm").click(function (e) {
        var form = $(this).closest('form');
        e.preventDefault();

        Swal.fire({
            title              : 'Hapus data ini?',
            text               : 'Data yang dihapus tidak dapat dikembalikan.',
            icon               : 'warning',
            showCancelButton   : true,
            confirmButtonColor : '#d33',
            cancelButtonColor  : '#6c757d',
            confirmButtonText  : 'Ya, Hapus!',
            cancelButtonText   : 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // ── Helper: Membuat HTML loading spinner ──
    function loadingSpinner() {
        return `<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
        </div>`;
    }

});
