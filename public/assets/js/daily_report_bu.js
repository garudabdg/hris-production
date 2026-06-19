/**
 * JavaScript for Daily Report BU
 */

$(function() {
    // =========================================
    // ADMIN VIEW - SELECT2 & FLATPICKR
    // =========================================
    if ($('.select2').length) {
        $('.select2').select2({ width: '100%' });
    }
    if ($('.select2Nik').length) {
        $('.select2Nik').select2({
            placeholder: 'Semua Karyawan',
            allowClear: true,
            width: '100%'
        });
    }
    if ($('.flatpickr-date').length) {
        $('.flatpickr-date').flatpickr({
            dateFormat: "Y-m-d",
        });
    }

    // =========================================
    // ADMIN VIEW - DELETE REPORT CONFIRMATION
    // =========================================
    $('.btn-delete').click(function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data report yang dihapus tidak dapat dikembalikan!",
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

    // =========================================
    // KARYAWAN & ADMIN VIEW - ONLINE CALCULATION
    // =========================================
    $(document).on('input', '.online-input', function() {
        let total = 0;
        $('.online-input').each(function() {
            let val = parseInt($(this).val()) || 0;
            total += val;
        });
        $('#total_online').text(total);
    });

    $(document).on('input', '.online-input-admin', function() {
        let total = 0;
        $('.online-input-admin').each(function() {
            let val = parseInt($(this).val()) || 0;
            total += val;
        });
        $('#total_online_admin').text(total);
    });

    // =========================================
    // KARYAWAN VIEW - DYNAMIC ROWS FUNCTIONS (Exposed to window)
    // =========================================
    window.addOfflineRow = function(tipeOptionsHtml, offlineIndex) {
        let row = `
        <tr class="border-b border-gray-100">
            <td class="px-2 py-2">
                <select name="offline[${offlineIndex}][tipe]" class="form-input-bu">
                    ${tipeOptionsHtml}
                </select>
            </td>
            <td class="px-2 py-2"><input type="text" name="offline[${offlineIndex}][nama_prospek]" class="form-input-bu" placeholder="Nama Prospek"></td>
            <td class="px-2 py-2"><input type="text" name="offline[${offlineIndex}][whatsapp]" class="form-input-bu" placeholder="No WhatsApp"></td>
            <td class="px-2 py-2"><input type="text" name="offline[${offlineIndex}][alamat]" class="form-input-bu" placeholder="Alamat/Keterangan"></td>
            <td class="px-2 py-2 text-center">
                <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="$(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
            </td>
        </tr>`;
        $('#offlineBody').append(row);
    };

    window.addNasabahRow = function(nasabahIndex) {
        let row = `
        <tr class="border-b border-gray-100">
            <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][nama]" class="form-input-bu" placeholder="Nama Prospek"></td>
            <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][akun_sosial_media]" class="form-input-bu" placeholder="IG/FB/TikTok dll"></td>
            <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][no_whatsapp]" class="form-input-bu" placeholder="No WhatsApp"></td>
            <td class="px-2 py-2">
                <div class="flex gap-2 justify-center">
                    <label class="cursor-pointer">
                        <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" value="cold" checked class="peer sr-only">
                        <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-blue-500 peer-checked:text-white transition-colors">Cold</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" value="warm" class="peer sr-only">
                        <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-yellow-500 peer-checked:text-white transition-colors">Warm</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" value="hot" class="peer sr-only">
                        <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-red-500 peer-checked:text-white transition-colors">Hot</span>
                    </label>
                </div>
            </td>
            <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][keterangan]" class="form-input-bu" placeholder="Keterangan"></td>
            <td class="px-2 py-2 text-center">
                <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="$(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
            </td>
        </tr>`;
        $('#nasabahBody').append(row);
    };

    // =========================================
    // ADMIN VIEW - DYNAMIC ROWS FUNCTIONS (Exposed to window)
    // =========================================
    window.addOfflineRowAdmin = function(tipeOptionsHtml, offlineIdxAdmin) {
        let row = `
        <tr>
            <td>
                <select name="offline[${offlineIdxAdmin}][tipe]" class="form-select">
                    ${tipeOptionsHtml}
                </select>
            </td>
            <td><input type="text" name="offline[${offlineIdxAdmin}][nama_prospek]" class="form-control" placeholder="Nama Prospek"></td>
            <td><input type="text" name="offline[${offlineIdxAdmin}][whatsapp]" class="form-control" placeholder="No WhatsApp"></td>
            <td><input type="text" name="offline[${offlineIdxAdmin}][alamat]" class="form-control" placeholder="Alamat/Keterangan"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
            </td>
        </tr>`;
        $('#offlineBodyAdmin').append(row);
    };

    window.addNasabahRowAdmin = function(nasabahIdxAdmin) {
        let row = `
        <tr>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][nama]" class="form-control" placeholder="Nama Prospek"></td>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][akun_sosial_media]" class="form-control" placeholder="Sosmed"></td>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][no_whatsapp]" class="form-control" placeholder="No WhatsApp"></td>
            <td class="text-center">
                <select name="nasabah[${nasabahIdxAdmin}][status_lead]" class="form-select">
                    <option value="cold">Cold</option>
                    <option value="warm">Warm</option>
                    <option value="hot">Hot</option>
                </select>
            </td>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][keterangan]" class="form-control" placeholder="Keterangan"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
            </td>
        </tr>`;
        $('#nasabahBodyAdmin').append(row);
    };

    // =========================================
    // SUBMIT CONFIRMATION (Exposed to window)
    // =========================================
    window.submitReportForm = function(isUpdate = false) {
        let title = isUpdate ? 'Perbarui Daily Report?' : 'Simpan Daily Report?';
        let confirmText = isUpdate ? 'Ya, Perbarui' : 'Ya, Simpan';
        let confirmColor = isUpdate ? '#eab308' : '#3085d6';
        
        Swal.fire({
            title: title,
            text: "Pastikan data yang diisi sudah benar",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#d33',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formReport').submit();
            }
        });
    };
});
