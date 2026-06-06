document.addEventListener('DOMContentLoaded', function() {
    if (window.PreviewKaryawanConfig && window.PreviewKaryawanConfig.successMsg) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: window.PreviewKaryawanConfig.successMsg,
            timer: 2000,
            showConfirmButton: false
        });
    }

    if (window.PreviewKaryawanConfig && window.PreviewKaryawanConfig.errorMsg) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: window.PreviewKaryawanConfig.errorMsg,
            timer: 3000,
            showConfirmButton: true
        });
    }
});
