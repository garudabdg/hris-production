// Fungsi untuk mengirim ucapan ulang tahun ke semua karyawan menggunakan job
function kirimUcapanSemua() {
    const btnKirim = document.getElementById('btnKirimUcapan');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');

    if (!btnKirim || !btnText || !btnLoading) {
        return;
    }

    // Disable button dan tampilkan loading
    btnKirim.disabled = true;
    btnText.textContent = 'Mengirim...';
    btnLoading.classList.remove('d-none');

    // Ambil filter dari URL atau form
    const urlParams = new URLSearchParams(window.location.search);
    const kodeCabang = urlParams.get('kode_cabang') || '';
    const kodeDept = urlParams.get('kode_dept') || '';

    // Kirim request ke server menggunakan URL dari window.Config
    fetch(window.Config.kirimUcapanBirthdayUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Config.csrfToken
            },
            body: JSON.stringify({
                kode_cabang: kodeCabang,
                kode_dept: kodeDept
            })
        })
        .then(response => response.json())
        .then(data => {
            // Enable button kembali
            btnKirim.disabled = false;
            btnText.textContent = 'Kirim ke Semua';
            btnLoading.classList.add('d-none');

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            // Enable button kembali
            btnKirim.disabled = false;
            btnText.textContent = 'Kirim ke Semua';
            btnLoading.classList.add('d-none');

            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat mengirim ucapan: ' + error.message
            });
        });
}
