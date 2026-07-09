/**
 * Threat Intelligence Report JS
 */

$(document).ready(function() {
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#table-tir').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });
    }

    // Delete confirmation
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        if (confirm('Apakah Anda yakin ingin menghapus report ini?')) {
            form.submit();
        }
    });
});
