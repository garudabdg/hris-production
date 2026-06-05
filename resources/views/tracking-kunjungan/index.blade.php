@extends('layouts.app')
@section('titlepage', 'Tracking Kunjungan')

@push('styles')
    <style>
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }

            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        .custom-marker {
            animation: pulse 2s infinite;
        }

        .custom-marker-start {
            animation: pulse 2s infinite;
        }

        .custom-marker-end {
            animation: pulse 2s infinite;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h4>Tracking Kunjungan</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form action="{{ route('tracking-kunjungan.index') }}" method="GET">
                                <div class="row">
                                    <div class="col-lg-3 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-calendar"></i></span>
                                                <input type="text" class="form-control flatpickr-date" id="tanggal" name="tanggal"
                                                    placeholder="Pilih Tanggal" value="{{ Request('tanggal', date('Y-m-d')) }}" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <select name="nik" id="nik_tracking" class="form-select select2NikTracking">
                                                <option value="">Pilih Karyawan</option>
                                                @foreach ($karyawans as $karyawan)
                                                    <option value="{{ $karyawan->nik }}" {{ Request('nik') == $karyawan->nik ? 'selected' : '' }}>
                                                        {{ $karyawan->nik }} - {{ $karyawan->nama_karyawan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-12 col-md-12">
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
                                            <a href="{{ route('tracking-kunjungan.index') }}" class="btn btn-secondary">
                                                <i class="ti ti-refresh me-1"></i>Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Maps Container -->
                    <div class="row">
                        <div class="col-12">
                        <div class="col-12">
                            <div id="map" style="height: 600px; border-radius: 8px; border: 1px solid #e0e0e0;"></div>
                        </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Karyawan</th>
                                            <th>Deskripsi</th>
                                            <th>Lokasi</th>
                                            <th>Jarak</th>
                                            <th>Durasi</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            @forelse($kunjungans as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $item->nama_karyawan }}</td>
                                                    <td>{{ Str::limit($item->deskripsi, 50) }}</td>
                                                    <td>
                                                        @if ($item->lokasi)
                                                            <span class="badge bg-info">{{ $item->lokasi }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($item->distance_from_previous)
                                                            <span class="badge bg-success">{{ $item->distance_from_previous }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($item->duration_from_previous)
                                                            <span class="badge bg-warning">{{ $item->duration_from_previous }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->created_at }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="ti ti-map-pin" style="font-size: 48px; opacity: 0.3;"></i>
                                                            <p class="mt-2">Tidak ada data kunjungan pada tanggal ini</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        $(function() {
            // Initialize Select2 for karyawan dropdown
            const select2NikTracking = $(".select2NikTracking");
            if (select2NikTracking.length) {
                select2NikTracking.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Pilih Karyawan',
                        allowClear: true,
                        dropdownParent: $this.parent(),
                        width: '100%'
                    });
                });
            }


                // Function untuk format tanggal dan waktu
                function formatDateTime(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleString('id-ID', {
                        weekday: 'short',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                // Function untuk menghitung bearing (arah)
                function calculateBearing(lat1, lng1, lat2, lng2) {
                    var dLng = (lng2 - lng1) * Math.PI / 180;
                    var lat1Rad = lat1 * Math.PI / 180;
                    var lat2Rad = lat2 * Math.PI / 180;

                    var y = Math.sin(dLng) * Math.cos(lat2Rad);
                    var x = Math.cos(lat1Rad) * Math.sin(lat2Rad) - Math.sin(lat1Rad) * Math.cos(lat2Rad) * Math.cos(dLng);

                    var bearing = Math.atan2(y, x) * 180 / Math.PI;
                    return (bearing + 360) % 360;
                }

                // Initialize map
                var map = L.map('map').setView([-7.3256, 108.2140], 13);

                // Define layers
                var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                });

                var googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });

                var googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });

                var googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });

                var googleTerrain = L.tileLayer('http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });

                // Add default layer
                osm.addTo(map);

                // Layer control
                var baseMaps = {
                    "OpenStreetMap": osm,
                    "Google Streets": googleStreets,
                    "Google Hybrid": googleHybrid,
                    "Google Satellite": googleSat,
                    "Google Terrain": googleTerrain
                };

                L.control.layers(baseMaps).addTo(map);

                // Data kunjungan dari server
                var kunjunganData = @json($kunjungans);

                // Group markers and paths by NIK
                var bounds = [];
                var routesByNik = {};
                var dataByNik = {};

                // Generate consistent colors for different NIKs
                function stringToColor(str) {
                    var hash = 0;
                    for (var i = 0; i < str.length; i++) {
                        hash = str.charCodeAt(i) + ((hash << 5) - hash);
                    }
                    var color = '#';
                    for (var i = 0; i < 3; i++) {
                        var value = (hash >> (i * 8)) & 0xFF;
                        // Pastikan warna cukup gelap untuk background putih
                        value = Math.max(0, Math.min(200, value));
                        color += ('00' + value.toString(16)).substr(-2);
                    }
                    return color;
                }
                
                function adjustColor(color, amount) {
                    return '#' + color.replace(/^#/, '').replace(/../g, color => ('0'+Math.min(255, Math.max(0, parseInt(color, 16) + amount)).toString(16)).substr(-2));
                }

                kunjunganData.forEach(function(item, index) {
                    if (item.latitude && item.longitude) {
                        var nik = item.nik;
                        if (!routesByNik[nik]) {
                            routesByNik[nik] = [];
                            dataByNik[nik] = [];
                        }
                        
                        var primaryColor = stringToColor(nik);
                        var secondaryColor = adjustColor(primaryColor, -40);
                        
                        var nikIndex = routesByNik[nik].length;

                        // Create custom icon
                        var customIcon = L.divIcon({
                            className: 'custom-marker',
                            html: '<div style="' +
                                'background: linear-gradient(135deg, ' + primaryColor + ' 0%, ' + secondaryColor + ' 100%); ' +
                                'width: 40px; ' +
                                'height: 40px; ' +
                                'border-radius: 50%; ' +
                                'border: 3px solid white; ' +
                                'box-shadow: 0 4px 12px ' + primaryColor + '66, 0 2px 4px rgba(0,0,0,0.2); ' +
                                'display: flex; ' +
                                'align-items: center; ' +
                                'justify-content: center; ' +
                                'color: white; ' +
                                'font-weight: bold; ' +
                                'font-size: 16px; ' +
                                'position: relative; ' +
                                'animation: pulse 2s infinite;' +
                                '">' +
                                '<div style="' +
                                'position: absolute; ' +
                                'top: -2px; ' +
                                'right: -2px; ' +
                                'background: #EF4444; ' +
                                'color: white; ' +
                                'border-radius: 50%; ' +
                                'width: 18px; ' +
                                'height: 18px; ' +
                                'display: flex; ' +
                                'align-items: center; ' +
                                'justify-content: center; ' +
                                'font-size: 10px; ' +
                                'font-weight: bold; ' +
                                'border: 2px solid white;' +
                                '">' +
                                (nikIndex + 1) +
                                '</div>' +
                                '<i class="ti ti-map-pin" style="font-size: 18px;"></i>' +
                                '</div>',
                            iconSize: [40, 40],
                            iconAnchor: [20, 20]
                        });

                        var marker = L.marker([item.latitude, item.longitude], {
                            icon: customIcon
                        }).addTo(map);

                        // Popup content
                        var popupContent = `
                        <div style="min-width: 250px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                            <div style="display: flex; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #E5E7EB;">
                                <div style="background: linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; margin-right: 12px; box-shadow: 0 2px 8px ${primaryColor}4D;">${nikIndex + 1}</div>
                                <div>
                                    <h6 style="margin: 0; color: #1F2937; font-size: 16px; font-weight: 600;">Kunjungan #${nikIndex + 1}</h6>
                                    <p style="margin: 0; color: #6B7280; font-size: 12px;">${item.nama_karyawan}</p>
                                </div>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <p style="margin: 0 0 8px 0; font-size: 14px; color: #374151; line-height: 1.4;">${item.deskripsi}</p>
                            </div>
                            ${item.foto ? `
                                                    <div style="margin-bottom: 8px;">
                                                        <img src="/storage/uploads/kunjungan/${item.foto}" alt="Foto Kunjungan" style="width: 100%; max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    </div>
                                                    ` : ''}
                            <div style="background: #F9FAFB; padding: 8px; border-radius: 6px; margin-bottom: 8px;">
                                <p style="margin: 0; font-size: 12px; color: #6B7280; display: flex; align-items: center;">
                                    <i class="ti ti-clock" style="margin-right: 6px; color: ${primaryColor};"></i> ${formatDateTime(item.created_at)}
                                </p>
                            </div>
                        </div>
                        `;

                        marker.bindPopup(popupContent);
                        bounds.push([item.latitude, item.longitude]);
                        routesByNik[nik].push([item.latitude, item.longitude]);
                        dataByNik[nik].push(item);
                    }
                });

                // Buat polyline untuk jalur kunjungan dengan arrow untuk tiap karyawan
                Object.keys(routesByNik).forEach(function(nik) {
                    var coords = routesByNik[nik];
                    var nikData = dataByNik[nik];
                    
                    if (coords.length > 1) {
                        var primaryColor = stringToColor(nik);
                        
                        var arrowPattern = {
                            color: primaryColor,
                            weight: 5,
                            opacity: 0.9,
                            smoothFactor: 1,
                            dashArray: '15, 8',
                            lineCap: 'round',
                            lineJoin: 'round'
                        };

                        var routePolyline = L.polyline(coords, arrowPattern).addTo(map);

                        // Arrow markers
                        for (var i = 0; i < coords.length - 1; i++) {
                            var start = coords[i];
                            var end = coords[i + 1];

                            var midLat = (start[0] + end[0]) / 2;
                            var midLng = (start[1] + end[1]) / 2;
                            var bearing = calculateBearing(start[0], start[1], end[0], end[1]);

                            var arrowIcon = L.divIcon({
                                className: 'arrow-marker',
                                html: '<div style="transform: rotate(' + bearing + 'deg); color: ' + primaryColor +
                                    '; font-size: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); font-weight: bold;">→</div>',
                                iconSize: [20, 20],
                                iconAnchor: [10, 10]
                            });

                            L.marker([midLat, midLng], {
                                icon: arrowIcon
                            }).addTo(map);
                        }

                        // Start Marker
                        var startIcon = L.divIcon({
                            className: 'custom-marker-start',
                            html: '<div style="' +
                                'background: linear-gradient(135deg, #10B981 0%, #059669 100%); ' +
                                'width: 50px; ' +
                                'height: 50px; ' +
                                'border-radius: 50%; ' +
                                'border: 4px solid white; ' +
                                'box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4), 0 2px 4px rgba(0,0,0,0.2); ' +
                                'display: flex; ' +
                                'align-items: center; ' +
                                'justify-content: center; ' +
                                'color: white; ' +
                                'font-weight: bold; ' +
                                'font-size: 18px; ' +
                                'animation: pulse 2s infinite;' +
                                '">' +
                                '<i class="ti ti-play" style="font-size: 20px;"></i>' +
                                '</div>',
                            iconSize: [50, 50],
                            iconAnchor: [25, 25]
                        });

                        var startMarker = L.marker(coords[0], {
                            icon: startIcon
                        }).addTo(map);
                        
                        var startKunjungan = nikData[0];
                        var startPopupContent = `
                            <div style="min-width: 250px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                <div style="display: flex; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #E5E7EB;">
                                    <div style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; margin-right: 12px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">🚀</div>
                                    <div>
                                        <h6 style="margin: 0; color: #1F2937; font-size: 16px; font-weight: 600;">Titik Awal</h6>
                                        <p style="margin: 0; color: #6B7280; font-size: 12px;">${startKunjungan.nama_karyawan}</p>
                                    </div>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <p style="margin: 0 0 8px 0; font-size: 14px; color: #374151; line-height: 1.4;">${startKunjungan.deskripsi}</p>
                                </div>
                                ${startKunjungan.foto ? `
                                                    <div style="margin-bottom: 8px;">
                                                        <img src="/storage/uploads/kunjungan/${startKunjungan.foto}" alt="Foto Kunjungan" style="width: 100%; max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    </div>
                                                    ` : ''}
                                <div style="background: #F9FAFB; padding: 8px; border-radius: 6px; margin-bottom: 8px;">
                                    <p style="margin: 0; font-size: 12px; color: #6B7280; display: flex; align-items: center;">
                                        <i class="ti ti-clock" style="margin-right: 6px; color: #10B981;"></i> ${formatDateTime(startKunjungan.created_at)}
                                    </p>
                                </div>
                            </div>
                        `;
                        startMarker.bindPopup(startPopupContent);

                        // End Marker
                        var endIcon = L.divIcon({
                            className: 'custom-marker-end',
                            html: '<div style="' +
                                'background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); ' +
                                'width: 50px; ' +
                                'height: 50px; ' +
                                'border-radius: 50%; ' +
                                'border: 4px solid white; ' +
                                'box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4), 0 2px 4px rgba(0,0,0,0.2); ' +
                                'display: flex; ' +
                                'align-items: center; ' +
                                'justify-content: center; ' +
                                'color: white; ' +
                                'font-weight: bold; ' +
                                'font-size: 18px; ' +
                                'animation: pulse 2s infinite;' +
                                '">' +
                                '<i class="ti ti-flag" style="font-size: 20px;"></i>' +
                                '</div>',
                            iconSize: [50, 50],
                            iconAnchor: [25, 25]
                        });

                        var endMarker = L.marker(coords[coords.length - 1], {
                            icon: endIcon
                        }).addTo(map);
                        
                        var endKunjungan = nikData[nikData.length - 1];
                        var endPopupContent = `
                            <div style="min-width: 250px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                <div style="display: flex; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #E5E7EB;">
                                    <div style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; margin-right: 12px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);">🏁</div>
                                    <div>
                                        <h6 style="margin: 0; color: #1F2937; font-size: 16px; font-weight: 600;">Titik Akhir</h6>
                                        <p style="margin: 0; color: #6B7280; font-size: 12px;">${endKunjungan.nama_karyawan}</p>
                                    </div>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <p style="margin: 0 0 8px 0; font-size: 14px; color: #374151; line-height: 1.4;">${endKunjungan.deskripsi}</p>
                                </div>
                                ${endKunjungan.foto ? `
                                                    <div style="margin-bottom: 8px;">
                                                        <img src="/storage/uploads/kunjungan/${endKunjungan.foto}" alt="Foto Kunjungan" style="width: 100%; max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    </div>
                                                    ` : ''}
                                <div style="background: #F9FAFB; padding: 8px; border-radius: 6px; margin-bottom: 8px;">
                                    <p style="margin: 0; font-size: 12px; color: #6B7280; display: flex; align-items: center;">
                                        <i class="ti ti-clock" style="margin-right: 6px; color: #EF4444;"></i> ${formatDateTime(endKunjungan.created_at)}
                                    </p>
                                </div>
                            </div>
                        `;
                        endMarker.bindPopup(endPopupContent);
                    }
                });

                // Fit map to show all markers
                if (bounds.length > 0) {
                    map.fitBounds(bounds, {
                        padding: [20, 20]
                    });
                }

                // Add legend dengan desain yang lebih menarik
                var legend = L.control({
                    position: 'bottomright'
                });
                legend.onAdd = function(map) {
                    var legendPrimary = '#3B82F6';
                    var legendSecondary = '#1E40AF';
                    var div = L.DomUtil.create('div', 'info legend');
                    div.style.background = 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)';
                    div.style.padding = '16px';
                    div.style.borderRadius = '12px';
                    div.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                    div.style.border = '1px solid #e5e7eb';
                    div.innerHTML =
                        '<div style="display: flex; align-items: center; margin-bottom: 8px;">' +
                        '<div style="background: linear-gradient(135deg, ' + legendPrimary + ' 0%, ' + legendSecondary +
                        ' 100%); width: 20px; height: 20px; border-radius: 50%; margin-right: 8px; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);"></div>' +
                        '<h6 style="margin: 0; color: #1F2937; font-size: 14px; font-weight: 600;">Kunjungan</h6>' +
                        '</div>' +
                        '<p style="margin: 0; font-size: 12px; color: #6B7280;">Klik marker untuk detail kunjungan</p>';
                    return div;
                };
                legend.addTo(map);

        });
    </script>
@endpush
