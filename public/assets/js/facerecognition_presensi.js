document.getElementById('qrForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const nik = document.getElementById('nik').value.trim();
    const errorMessage = document.getElementById('errorMessage');
    const qrResult = document.getElementById('qrResult');

    // Reset
    errorMessage.style.display = 'none';
    qrResult.style.display = 'none';

    if (!nik) {
        showError('NIK tidak boleh kosong');
        return;
    }

    if (nik.length !== 9) {
        showError('NIK harus 9 digit');
        return;
    }

    // Generate QR Code
    generateQRCode(nik);
});

function generateQRCode(nik) {
    fetch(`/facerecognition-presensi/generate/${nik}`)
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                showQRCode(data);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Terjadi kesalahan saat generate QR Code');
            console.error('Error:', error);
        });
}

function showQRCode(data) {
    const qrResult = document.getElementById('qrResult');
    const qrCode = document.getElementById('qrCode');
    const employeeInfo = document.getElementById('employeeInfo');

    qrCode.innerHTML = `<img src="data:image/png;base64,${data.qr_code}" alt="QR Code">`;

    employeeInfo.innerHTML = `
        <h5>${data.karyawan.nama_karyawan}</h5>
        <p class="mb-1"><strong>NIK:</strong> ${data.karyawan.nik}</p>
        <p class="mb-0"><strong>Status:</strong> ${data.karyawan.status_aktif_karyawan == '1' ? 'Aktif' : 'Tidak Aktif'}</p>
    `;

    qrResult.style.display = 'block';
}

function showError(message) {
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = message;
    errorMessage.style.display = 'block';
}
