let map = null;
let marker = null;
let mapInterval = null;

$(function () {
    // DatePicker configs
    const localeIndo = {
        days: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
        daysShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
        daysMin: ['Mg', 'Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb'],
        months: [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ],
        monthsShort: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des',
        ],
        today: 'Hari ini',
        clear: 'Hapus',
        dateFormat: 'yyyy-MM-dd',
        timeFormat: 'HH:mm',
        firstDay: 1,
    };
    const dpOpt = {
        locale: localeIndo,
        autoClose: true,
        isMobile: true,
        buttons: ['today', 'clear'],
        position: 'bottom center',
    };
    new AirDatepicker('#tanggal_awal', dpOpt);
    new AirDatepicker('#tanggal_akhir', dpOpt);

    function showDetailModal(id, aktivitas, tanggal, waktu, lokasi, foto) {
        $('#modalDate').text(tanggal);
        $('#modalTime').text(waktu);
        $('#modalDescription').text(aktivitas);

        let coords = null;
        if (lokasi && lokasi !== '' && lokasi !== '---') {
            $('#modalLocation').text(lokasi);
            $('#modalLocationLink')
                .attr('href', 'https://www.google.com/maps?q=' + lokasi)
                .removeClass('hidden');
            $('#modalLocationEmpty').addClass('hidden');

            const parts = lokasi.split(',');
            if (parts.length === 2) {
                coords = [parseFloat(parts[0]), parseFloat(parts[1])];
            }
        } else {
            $('#modalLocationLink').addClass('hidden');
            $('#modalLocationEmpty').removeClass('hidden');
        }

        // Photo Handling
        if (foto && foto !== 'null' && foto !== '') {
            $('#modalImg')
                .attr(
                    'src',
                    window.Config.assetAktivitas + '/' + foto,
                )
                .removeClass('hidden');
            $('#modalIconWrapper').addClass('hidden');
        } else {
            $('#modalImg').addClass('hidden');
            $('#modalIconWrapper').removeClass('hidden');
        }

        const $modal = $('#detailModal');
        $modal.addClass('show').css({
            display: 'flex',
            'pointer-events': 'auto',
        });

        // Handle Map
        if (coords) {
            $('#mapWrapper').show();
            if (!map) {
                map = L.map('activityMap', {
                    zoomControl: false,
                    attributionControl: false,
                    fadeAnimation: true,
                    markerZoomAnimation: true,
                }).setView(coords, 18);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    updateWhenIdle: true,
                    keepBuffer: 2,
                }).addTo(map);
            } else {
                map.setView(coords, 18);
            }

            if (marker) map.removeLayer(marker);
            marker = L.marker(coords).addTo(map);

            // Brute-force stabilization (Matches Kunjungan & Presensi approach)
            if (mapInterval) clearInterval(mapInterval);
            mapInterval = setInterval(function () {
                if (map) map.invalidateSize();
            }, 100);
        } else {
            $('#mapWrapper').hide();
            if (mapInterval) {
                clearInterval(mapInterval);
                mapInterval = null;
            }
        }

        setTimeout(() => {
            $modal.addClass('opacity-100');
        }, 50);
    }

    window.showDetailModal = showDetailModal;

    function closeDetailModal() {
        if (mapInterval) {
            clearInterval(mapInterval);
            mapInterval = null;
        }
        const $modal = $('#detailModal');
        $modal.removeClass('opacity-100');
        setTimeout(() => {
            $modal.removeClass('show').css({
                display: 'none',
                'pointer-events': 'none',
            });
        }, 300);
    }

    window.closeDetailModal = closeDetailModal;

    window.refreshMap = function (e) {
        if (e) e.stopPropagation();
        if (map) {
            map.invalidateSize();
            if (marker) map.setView(marker.getLatLng());
        }
    };

    // Close modal on backdrop click
    $('#detailModal').on('click', function (e) {
        if (e.target === this) closeDetailModal();
    });

    // Delete Confirmation
    $('.delete-confirm').click(function (e) {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Hapus Aktivitas?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
