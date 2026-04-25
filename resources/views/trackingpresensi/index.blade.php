@extends('layouts.app')
@section('titlepage', 'Tracking Presensi')

@section('content')
@section('navigasi')
    <span>Tracking Absensi</span>
@endsection

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tracking Absen Karyawan</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                            <input type="text" class="form-control flatpickr-date" id="tanggal" name="tanggal"
                                value="{{ $tanggal }}" placeholder="Pilih tanggal">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="kode_cabang" class="form-label">Cabang</label>
                        <select class="form-select" id="kode_cabang" name="kode_cabang">
                            <option value="">Semua Cabang</option>
                            @foreach ($cabangs as $cabang)
                                <option value="{{ $cabang->kode_cabang }}">{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="kode_dept" class="form-label">Departemen</label>
                        <select class="form-select" id="kode_dept" name="kode_dept">
                            <option value="">Semua Departemen</option>
                            @php
                                $departements = App\Models\Departemen::orderBy('nama_dept')->get();
                            @endphp
                            @foreach ($departements as $dept)
                                <option value="{{ $dept->kode_dept }}">{{ $dept->nama_dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sub_departemen" class="form-label">Team</label>
                        <select class="form-select" id="sub_departemen" name="sub_departemen">
                            <option value="">Semua Team</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="btn-filter">
                                <i class="ti ti-search me-2"></i>Filter
                            </button>
                            <button type="button" class="btn btn-secondary" id="btn-reset">
                                <i class="ti ti-refresh me-2"></i>Reset
                            </button>
                            <button type="button" class="btn btn-info" id="btn-toggle-radius">
                                <i class="ti ti-circle me-2"></i>Toggle Radius
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div id="map" style="height: 500px; border-radius: 8px; border: 1px solid #ddd;"></div>
                    </div>
                </div>

                <!-- Info Panel -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Info:</strong> Peta menampilkan lokasi presensi karyawan berdasarkan koordinat dari field lokasi_in.
                            <ul class="mb-0 mt-2">
                                <li><strong>Klik marker</strong> untuk melihat detail presensi dan foto</li>
                                <li><strong>Klik baris tabel</strong> untuk fokus ke lokasi marker di peta</li>
                                <li><strong>Klik area kosong di peta</strong> untuk melihat koordinat lokasi</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Statistics Panel -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white mb-0">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-map-pin fs-1"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h4 class="card-title mb-0" id="stat-total">0</h4>
                                        <p class="card-text mb-0">Total Presensi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white mb-0">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-clock-check fs-1"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h4 class="card-title mb-0" id="stat-tepat">0</h4>
                                        <p class="card-text mb-0">Sudah Keluar</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white mb-0">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-clock fs-1"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h4 class="card-title mb-0" id="stat-belum">0</h4>
                                        <p class="card-text mb-0">Belum Keluar</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white mb-0">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-alert-triangle fs-1"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h4 class="card-title mb-0" id="stat-overlap">0</h4>
                                        <p class="card-text mb-0">Lokasi Overlap</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="card mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="mb-0"><i class="ti ti-table me-2"></i>Daftar Presensi Karyawan</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm" style="width: 240px;">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" id="table-search" placeholder="Cari nama / NIK...">
                            </div>
                            <button class="btn btn-sm btn-outline-success" id="btn-export-csv">
                                <i class="ti ti-download me-1"></i>Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0" id="presensi-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45px;">#</th>
                                        <th>Karyawan</th>
                                        <th>Cabang</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                        <th>Lokasi</th>
                                        <th>Koordinat</th>
                                        <!-- <th>Foto</th> -->
                                        <th style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="presensi-tbody">
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="ti ti-loader me-2"></i>Memuat data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="text-muted small" id="table-info">Menampilkan 0 data</div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="table-pagination"></ul>
                        </nav>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Foto -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Foto Presensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Foto Presensi" class="img-fluid"
                    style="max-height: 70vh; border-radius: 8px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a id="downloadImage" href="" download class="btn btn-primary">
                    <i class="ti ti-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<style>
    .custom-marker {
        background: transparent !important;
        border: none !important;
    }
    .custom-marker div { pointer-events: none; }
    .leaflet-marker-icon { z-index: 1000 !important; }
    .marker-label {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    .custom-marker img { transition: transform 0.2s ease, box-shadow 0.2s ease; pointer-events: auto; }
    .custom-marker:hover img { transform: scale(1.1); box-shadow: 0 6px 12px rgba(0,0,0,0.5); }

    /* Table styles */
    #presensi-table th { font-size: 12px; font-weight: 600; white-space: nowrap; vertical-align: middle; }
    #presensi-table td { vertical-align: middle; font-size: 13px; }
    #presensi-table tbody tr { cursor: pointer; }
    #presensi-table tbody tr.table-active { background-color: #e8f4fd !important; }

    .employee-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px; color: white; flex-shrink: 0;
    }
    .employee-name { font-weight: 600; font-size: 13px; color: #1e293b; }
    .employee-nik { font-size: 11px; color: #94a3b8; }

    .loc-cell { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; color: #64748b; }
    .coord-cell { font-size: 11px; font-family: monospace; color: #64748b; white-space: nowrap; }

    .btn-focus-map { padding: 3px 8px; font-size: 11px; }

    .foto-badges { display: flex; flex-direction: column; gap: 3px; }
</style>

<script>
$(document).ready(function () {

    // ─── Flatpickr ───────────────────────────────────────────────
    $('.flatpickr-date').flatpickr({
        dateFormat: 'Y-m-d',
        defaultDate: '{{ $tanggal }}'
    });

    // ─── Leaflet Map ─────────────────────────────────────────────
    var map = L.map('map').setView([-6.2088, 106.8456], 10);

    var osm          = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' });
    var googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',   { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'] });
    var googleHybrid  = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'] });
    var googleSat     = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',   { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'] });
    var googleTerrain = L.tileLayer('http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}',   { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'] });
    osm.addTo(map);

    L.control.layers({
        "OpenStreetMap": osm,
        "Google Streets": googleStreets,
        "Google Hybrid": googleHybrid,
        "Google Satellite": googleSat,
        "Google Terrain": googleTerrain
    }).addTo(map);

    // Click on map – show coordinate popup
    map.on('click', function (e) {
        var lat = e.latlng.lat.toFixed(6);
        var lng = e.latlng.lng.toFixed(6);
        L.popup()
            .setLatLng(e.latlng)
            .setContent(`
                <div style="text-align:center;min-width:200px;">
                    <h6 style="margin:0 0 10px 0;color:#007bff;"><i class="ti ti-map-pin me-2"></i>Koordinat Lokasi</h6>
                    <div style="background:#f8f9fa;padding:10px;border-radius:6px;margin-bottom:10px;">
                        <p style="margin:0;font-size:14px;"><strong>Latitude:</strong> ${lat}</p>
                        <p style="margin:0;font-size:14px;"><strong>Longitude:</strong> ${lng}</p>
                    </div>
                    <div style="display:flex;gap:5px;justify-content:center;">
                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('${lat}, ${lng}')" style="font-size:11px;padding:4px 8px;">
                            <i class="ti ti-copy me-1"></i>Copy
                        </button>
                        <button class="btn btn-sm btn-success" onclick="addCustomMarker(${lat}, ${lng})" style="font-size:11px;padding:4px 8px;">
                            <i class="ti ti-plus me-1"></i>Add Marker
                        </button>
                    </div>
                </div>
            `)
            .openOn(map);
    });

    // ─── Data ────────────────────────────────────────────────────
    var markers       = [];
    var radiusCircles = [];
    var customMarkers = [];
    var presensiData  = @json($presensis);
    var cabangRadius  = @json($cabangRadius);

    // ─── Table state ─────────────────────────────────────────────
    var tableData     = [];
    var filteredData  = [];
    var currentPage   = 1;
    var perPage       = 10;
    var activeRow     = null;

    // ─── Avatar colors ───────────────────────────────────────────
    var avatarColors  = ['#3b82f6','#8b5cf6','#ec4899','#f59e0b','#10b981','#06b6d4','#ef4444','#84cc16'];
    function getAvatarColor(str) {
        var hash = 0;
        for (var i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
        return avatarColors[Math.abs(hash) % avatarColors.length];
    }

    // ─── Radius circles ──────────────────────────────────────────
    function addRadiusCirclesToMap(data) {
        radiusCircles.forEach(c => map.removeLayer(c));
        radiusCircles = [];
        if (!data || !data.length) return;
        data.forEach(function (cabang) {
            if (cabang.latitude && cabang.longitude && cabang.radius_cabang) {
                var circle = L.circle([cabang.latitude, cabang.longitude], {
                    color: '#dc3545', fillColor: '#dc3545', fillOpacity: 0.1,
                    radius: cabang.radius_cabang
                }).addTo(map);
                circle.bindPopup(`
                    <div style="text-align:center;min-width:200px;">
                        <h6 style="margin:0 0 10px 0;color:#dc3545;"><i class="ti ti-building me-2"></i>Area Cabang</h6>
                        <div style="background:#f8f9fa;padding:10px;border-radius:6px;">
                            <p style="margin:0;font-size:14px;"><strong>Nama Cabang:</strong> ${cabang.nama_cabang}</p>
                            <p style="margin:0;font-size:14px;"><strong>Kode Cabang:</strong> ${cabang.kode_cabang}</p>
                            <p style="margin:0;font-size:14px;"><strong>Radius:</strong> ${cabang.radius_cabang} meter</p>
                            <p style="margin:0;font-size:14px;"><strong>Lokasi:</strong> ${cabang.lokasi_cabang}</p>
                        </div>
                    </div>
                `);
                radiusCircles.push(circle);
            }
        });
    }

    // ─── Center map on cabang ────────────────────────────────────
    function centerMapOnCabang(cabangData) {
        if (!cabangData || !cabangData.length) return;
        if (cabangData.length === 1) {
            var c = cabangData[0];
            if (!c.latitude || !c.longitude) return;
            var zoom = 15;
            if (c.radius_cabang) {
                if (c.radius_cabang <= 100)  zoom = 18;
                else if (c.radius_cabang <= 500)  zoom = 16;
                else if (c.radius_cabang <= 1000) zoom = 14;
                else zoom = 12;
            }
            map.setView([c.latitude, c.longitude], zoom);
        } else {
            var bounds = L.latLngBounds();
            cabangData.forEach(c => { if (c.latitude && c.longitude) bounds.extend([c.latitude, c.longitude]); });
            if (!bounds.isEmpty()) map.fitBounds(bounds, { padding: [20, 20] });
        }
    }

    // ─── Map markers ─────────────────────────────────────────────
    function addMarkersToMap(data) {
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        if (!data.length) {
            Swal.fire({ icon: 'info', title: 'Tidak Ada Data', text: 'Tidak ada data presensi untuk tanggal dan cabang yang dipilih.', confirmButtonText: 'OK' });
            updateStats([]);
            renderTable([]);
            return;
        }

        var bounds = L.latLngBounds();

        data.forEach(function (presensi, idx) {
            if (!presensi.latitude || !presensi.longitude) return;

            var markerColor = presensi.marker_count > 1 ? '#ef4444' : '#3b82f6';

            var customIcon = L.divIcon({
                className: 'custom-marker',
                html: `
                    <div style="position:relative;display:inline-block;">
                        <div style="position:relative;width:50px;height:50px;">
                            ${presensi.foto_in ? `
                                <img src="/storage/uploads/absensi/${presensi.foto_in}"
                                     style="width:50px;height:50px;object-fit:cover;border-radius:50%;border:3px solid ${markerColor};box-shadow:0 4px 8px rgba(0,0,0,0.4);cursor:pointer;"
                                     onclick="showImageModal('/storage/uploads/absensi/${presensi.foto_in}','Foto Presensi - ${presensi.nama_karyawan}')"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div style="display:none;width:50px;height:50px;background-color:${markerColor};border-radius:50%;border:3px solid white;box-shadow:0 4px 8px rgba(0,0,0,0.4);align-items:center;justify-content:center;">
                                    <i class="ti ti-user" style="color:white;font-size:20px;"></i>
                                </div>
                            ` : `
                                <div style="width:50px;height:50px;background-color:${markerColor};border-radius:50%;border:3px solid white;box-shadow:0 4px 8px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;">
                                    <i class="ti ti-user" style="color:white;font-size:20px;"></i>
                                </div>
                            `}
                            <div style="position:absolute;bottom:-2px;right:-2px;width:16px;height:16px;background-color:${markerColor};border:2px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="ti ti-check" style="color:white;font-size:8px;"></i>
                            </div>
                        </div>
                        <div class="marker-label" style="position:absolute;top:-40px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.8);color:white;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap;box-shadow:0 2px 4px rgba(0,0,0,0.3);z-index:1000;">
                            ${presensi.nama_karyawan}
                            <div style="position:absolute;top:100%;left:50%;transform:translateX(-50%);width:0;height:0;border-left:5px solid transparent;border-right:5px solid transparent;border-top:5px solid rgba(0,0,0,0.8);"></div>
                        </div>
                    </div>
                `,
                iconSize: [50, 50],
                iconAnchor: [25, 50]
            });

            var jamIn  = presensi.jam_in  ? new Date(presensi.jam_in).toLocaleTimeString('id-ID')  : '-';
            var jamOut = presensi.jam_out ? new Date(presensi.jam_out).toLocaleTimeString('id-ID') : '-';

            var marker = L.marker([presensi.latitude, presensi.longitude], { icon: customIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="min-width:260px;">
                        <h6><strong>${presensi.nama_karyawan}</strong></h6>
                        <p><strong>NIK:</strong> ${presensi.nik}</p>
                        <p><strong>Cabang:</strong> ${presensi.nama_cabang}</p>
                        <p><strong>Tanggal:</strong> ${presensi.tanggal}</p>
                        <p><strong>Jam Masuk:</strong> ${jamIn}</p>
                        <p><strong>Jam Keluar:</strong> ${jamOut}</p>
                        <p><strong>Lokasi:</strong> ${presensi.lokasi_in}</p>
                        ${presensi.marker_count > 1 ? `<p><strong><span style="color:red;">⚠️ ${presensi.marker_count} karyawan di lokasi yang sama</span></strong></p>` : ''}
                        <div style="margin-top:15px;">
                            <h6><strong>Foto Presensi:</strong></h6>
                            <div style="display:flex;gap:15px;justify-content:center;align-items:flex-start;">
                                <div style="flex:1;text-align:center;">
                                    <div style="background:linear-gradient(135deg,#007bff,#0056b3);color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;margin-bottom:8px;">
                                        <i class="ti ti-login" style="margin-right:4px;"></i>Masuk
                                    </div>
                                    ${presensi.foto_in ? `
                                        <img src="/storage/uploads/absensi/${presensi.foto_in}"
                                             style="width:90px;height:90px;object-fit:cover;border-radius:12px;border:3px solid #007bff;cursor:pointer;box-shadow:0 4px 8px rgba(0,123,255,0.3);"
                                             onclick="showImageModal('/storage/uploads/absensi/${presensi.foto_in}','Foto Masuk - ${presensi.nama_karyawan}')"
                                             onerror="this.style.display='none';">
                                    ` : `
                                        <div style="width:90px;height:90px;background:#f8f9fa;border:2px dashed #dee2e6;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#6c757d;flex-direction:column;margin:0 auto;">
                                            <i class="ti ti-photo-off" style="font-size:20px;margin-bottom:4px;"></i><span>Tidak ada foto</span>
                                        </div>
                                    `}
                                </div>
                                <div style="width:1px;height:90px;background:linear-gradient(to bottom,transparent,#dee2e6,transparent);margin:0 5px;"></div>
                                <div style="flex:1;text-align:center;">
                                    <div style="background:linear-gradient(135deg,#28a745,#1e7e34);color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;margin-bottom:8px;">
                                        <i class="ti ti-logout" style="margin-right:4px;"></i>Keluar
                                    </div>
                                    ${presensi.foto_out ? `
                                        <img src="/storage/uploads/absensi/${presensi.foto_out}"
                                             style="width:90px;height:90px;object-fit:cover;border-radius:12px;border:3px solid #28a745;cursor:pointer;box-shadow:0 4px 8px rgba(40,167,69,0.3);"
                                             onclick="showImageModal('/storage/uploads/absensi/${presensi.foto_out}','Foto Keluar - ${presensi.nama_karyawan}')"
                                             onerror="this.style.display='none';">
                                    ` : `
                                        <div style="width:90px;height:90px;background:#f8f9fa;border:2px dashed #dee2e6;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#6c757d;flex-direction:column;margin:0 auto;">
                                            <i class="ti ti-photo-off" style="font-size:20px;margin-bottom:4px;"></i><span>Tidak ada foto</span>
                                        </div>
                                    `}
                                </div>
                            </div>
                        </div>
                    </div>
                `);

            marker._presensiIndex = idx;
            markers.push(marker);
            bounds.extend([presensi.latitude, presensi.longitude]);
        });

        if (markers.length > 0) map.fitBounds(bounds);

        // Update stats & table
        updateStats(data);
        renderTable(data);
    }

    // ─── Statistics ──────────────────────────────────────────────
    function updateStats(data) {
        var total   = data.length;
        var sudah   = data.filter(d => d.jam_out).length;
        var belum   = data.filter(d => !d.jam_out).length;
        var overlap = data.filter(d => d.marker_count > 1).length;
        $('#stat-total').text(total);
        $('#stat-tepat').text(sudah);
        $('#stat-belum').text(belum);
        $('#stat-overlap').text(overlap);
    }

    // ─── Table rendering ─────────────────────────────────────────
    function renderTable(data) {
        tableData    = data;
        filteredData = applySearch(data, $('#table-search').val());
        currentPage  = 1;
        drawTable();
    }

    function applySearch(data, q) {
        if (!q) return data;
        q = q.toLowerCase();
        return data.filter(d =>
            (d.nama_karyawan && d.nama_karyawan.toLowerCase().includes(q)) ||
            (d.nik && d.nik.toLowerCase().includes(q)) ||
            (d.nama_cabang && d.nama_cabang.toLowerCase().includes(q))
        );
    }

    function drawTable() {
        var total  = filteredData.length;
        var start  = (currentPage - 1) * perPage;
        var end    = Math.min(start + perPage, total);
        var slice  = filteredData.slice(start, end);
        var tbody  = $('#presensi-tbody');

        if (!slice.length) {
            tbody.html('<tr><td colspan="10" class="text-center py-4 text-muted"><i class="ti ti-database-off me-2"></i>Tidak ada data ditemukan</td></tr>');
            $('#table-info').text('Menampilkan 0 data');
            $('#table-pagination').html('');
            return;
        }

        tbody.html(slice.map(function (d, i) {
            var idx     = start + i;
            var jamIn   = d.jam_in  ? new Date(d.jam_in).toLocaleTimeString('id-ID',  { hour:'2-digit', minute:'2-digit' }) : '-';
            var jamOut  = d.jam_out ? new Date(d.jam_out).toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' }) : null;
            var overlap = d.marker_count > 1;
            var color   = getAvatarColor(d.nama_karyawan || '');
            var initial = (d.nama_karyawan || '?').charAt(0).toUpperCase();

            var statusBadge = jamOut
                ? '<span class="badge bg-label-success">Sudah Keluar</span>'
                : '<span class="badge bg-label-warning">Belum Keluar</span>';
            if (overlap) statusBadge += '<br><span class="badge bg-label-danger mt-1">⚠️ Overlap</span>';

            // var fotoBadge = '';
            // if (d.foto_in)  fotoBadge += '<span class="badge bg-label-primary">📷 Masuk</span> ';
            // if (d.foto_out) fotoBadge += '<span class="badge bg-label-success">📷 Keluar</span>';
            // if (!d.foto_in && !d.foto_out) fotoBadge = '<span class="badge bg-label-secondary">Tidak Ada</span>';

            return `
                <tr data-idx="${idx}" onclick="focusMarkerByIndex(${idx})">
                    <td class="text-muted">${idx + 1}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="employee-avatar" style="background:${color};">${initial}</div>
                            <div>
                                <div class="employee-name">${d.nama_karyawan || '-'}</div>
                                <div class="employee-nik">${d.nik || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-label-secondary">${d.nama_cabang || '-'}</span></td>
                    <td>
    <div class="d-flex align-items-center gap-2">
        ${d.foto_in
            ? `<img src="/storage/uploads/absensi/${d.foto_in}"
                    style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:2px solid #3b82f6;cursor:pointer;flex-shrink:0;"
                    onclick="event.stopPropagation();showImageModal('/storage/uploads/absensi/${d.foto_in}','Foto Masuk - ${d.nama_karyawan}')"
                    onerror="this.style.display='none';">`
            : `<div style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                   <i class="ti ti-photo-off" style="font-size:14px;color:#94a3b8;"></i>
               </div>`
        }
        <span class="badge bg-label-primary">${jamIn}</span>
    </div>
</td>
                    <td>
    <div class="d-flex align-items-center gap-2">
        ${d.foto_out
            ? `<img src="/storage/uploads/absensi/${d.foto_out}"
                    style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:2px solid #10b981;cursor:pointer;flex-shrink:0;"
                    onclick="event.stopPropagation();showImageModal('/storage/uploads/absensi/${d.foto_out}','Foto Keluar - ${d.nama_karyawan}')"
                    onerror="this.style.display='none';">`
            : `<div style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                   <i class="ti ti-photo-off" style="font-size:14px;color:#94a3b8;"></i>
               </div>`
        }
        ${jamOut
            ? `<span class="badge bg-label-success">${jamOut}</span>`
            : `<span class="badge bg-label-danger">Belum Keluar</span>`
        }
    </div>
</td>
                    <td>${statusBadge}</td>
                    <td><div class="loc-cell" title="${d.lokasi_in || ''}"><i class="ti ti-map-pin me-1 text-muted"></i>${d.lokasi_in || '-'}</div></td>
                    <td class="coord-cell">${d.original_latitude || d.latitude || '-'},<br>${d.original_longitude || d.longitude || '-'}</td>
                    
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-focus-map" onclick="event.stopPropagation();focusMarkerByIndex(${idx})">
                            <i class="ti ti-map-pin"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join(''));

        // Pagination info
        $('#table-info').text(`Menampilkan ${start + 1}–${end} dari ${total} data`);

        // Pagination buttons
        var totalPages = Math.ceil(total / perPage);
        var paginationHtml = '';
        paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goPage(${currentPage - 1});return false;">‹</a></li>`;
        for (var p = 1; p <= totalPages; p++) {
            if (totalPages > 7 && Math.abs(p - currentPage) > 2 && p !== 1 && p !== totalPages) {
                if (p === 2 || p === totalPages - 1) { paginationHtml += `<li class="page-item disabled"><a class="page-link">…</a></li>`; }
                continue;
            }
            paginationHtml += `<li class="page-item ${p === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goPage(${p});return false;">${p}</a></li>`;
        }
        paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goPage(${currentPage + 1});return false;">›</a></li>`;
        $('#table-pagination').html(paginationHtml);
    }

    window.goPage = function (p) {
        var totalPages = Math.ceil(filteredData.length / perPage);
        currentPage = Math.max(1, Math.min(totalPages, p));
        drawTable();
    };

    // Search
    $('#table-search').on('input', function () {
        filteredData = applySearch(tableData, $(this).val());
        currentPage  = 1;
        drawTable();
    });

    // ─── Focus marker from table ──────────────────────────────────
    window.focusMarkerByIndex = function (idx) {
        var d = filteredData[idx];
        if (!d || !d.latitude || !d.longitude) return;

        // Highlight row
        $('#presensi-tbody tr').removeClass('table-active');
        $(`#presensi-tbody tr[data-idx="${idx}"]`).addClass('table-active');

        // Move map
        map.setView([d.latitude, d.longitude], 17, { animate: true });

        // Open popup
        if (markers[idx]) {
            markers[idx].openPopup();
        }

        // Scroll to map
        $('html, body').animate({ scrollTop: $('#map').offset().top - 80 }, 400);
    };

    // ─── Image modal ─────────────────────────────────────────────
    window.showImageModal = function (src, title) {
        $('#imageModalTitle').text(title);
        $('#modalImage').attr('src', src);
        $('#downloadImage').attr('href', src);
        $('#imageModal').modal('show');
    };

    // ─── Copy to clipboard ───────────────────────────────────────
    window.copyToClipboard = function (text) {
        var notify = function () {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Koordinat disalin: ' + text, timer: 2000, showConfirmButton: false });
        };
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(notify);
        } else {
            var el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            notify();
        }
    };

    // ─── Custom marker ───────────────────────────────────────────
    window.addCustomMarker = function (lat, lng) {
        var icon = L.divIcon({
            className: 'custom-marker',
            html: `
                <div style="position:relative;display:inline-block;">
                    <div style="background-color:#ff6b35;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 3px 6px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-map-pin" style="color:white;font-size:12px;"></i>
                    </div>
                    <div class="marker-label" style="position:absolute;top:-35px;left:50%;transform:translateX(-50%);background:rgba(255,107,53,0.9);color:white;padding:4px 8px;border-radius:6px;font-size:10px;font-weight:600;white-space:nowrap;z-index:1000;">
                        ${lat}, ${lng}
                        <div style="position:absolute;top:100%;left:50%;transform:translateX(-50%);width:0;height:0;border-left:5px solid transparent;border-right:5px solid transparent;border-top:5px solid rgba(255,107,53,0.9);"></div>
                    </div>
                </div>
            `,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        var mIdx = customMarkers.length;
        var marker = L.marker([lat, lng], { icon })
            .addTo(map)
            .bindPopup(`
                <div style="text-align:center;min-width:200px;">
                    <h6 style="margin:0 0 10px 0;color:#ff6b35;"><i class="ti ti-map-pin me-2"></i>Custom Marker</h6>
                    <div style="background:#f8f9fa;padding:10px;border-radius:6px;margin-bottom:10px;">
                        <p style="margin:0;font-size:14px;"><strong>Latitude:</strong> ${lat}</p>
                        <p style="margin:0;font-size:14px;"><strong>Longitude:</strong> ${lng}</p>
                    </div>
                    <div style="display:flex;gap:5px;justify-content:center;">
                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('${lat}, ${lng}')" style="font-size:11px;padding:4px 8px;">
                            <i class="ti ti-copy me-1"></i>Copy
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="removeCustomMarker(${mIdx})" style="font-size:11px;padding:4px 8px;">
                            <i class="ti ti-trash me-1"></i>Remove
                        </button>
                    </div>
                </div>
            `);
        customMarkers.push(marker);
        Swal.fire({ icon: 'success', title: 'Marker Ditambahkan!', text: `Custom marker ditambahkan di ${lat}, ${lng}`, timer: 2000, showConfirmButton: false });
    };

    window.removeCustomMarker = function (i) {
        if (customMarkers[i]) {
            map.removeLayer(customMarkers[i]);
            customMarkers.splice(i, 1);
            Swal.fire({ icon: 'success', title: 'Marker Dihapus!', timer: 1500, showConfirmButton: false });
        }
    };

    // ─── Initial load ─────────────────────────────────────────────
    addMarkersToMap(presensiData);
    addRadiusCirclesToMap(cabangRadius);

    // ─── Filter button ───────────────────────────────────────────
    $('#btn-filter').click(function () {
        var tanggal       = $('#tanggal').val();
        var kode_cabang   = $('#kode_cabang').val();
        var kode_dept     = $('#kode_dept').val();
        var sub_departemen = $('#sub_departemen').val();

        if (!tanggal) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Silakan pilih tanggal terlebih dahulu.', confirmButtonText: 'OK' });
            return;
        }

        Swal.fire({ title: 'Memuat Data...', text: 'Sedang mengambil data presensi', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: '{{ route('trackingpresensi.getData') }}',
            type: 'GET',
            data: { tanggal, kode_cabang, kode_dept, sub_departemen },
            success: function (res) {
                Swal.close();
                addMarkersToMap(res.presensis);
                addRadiusCirclesToMap(res.cabangRadius);
                if (kode_cabang) centerMapOnCabang(res.cabangRadius);
            },
            error: function () {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan saat mengambil data presensi.', confirmButtonText: 'OK' });
            }
        });
    });

    // ─── Reset button ────────────────────────────────────────────
    $('#btn-reset').click(function () {
        $('#tanggal').val('{{ $tanggal }}');
        $('#kode_cabang').val('');
        $('#kode_dept').val('');
        $('#sub_departemen').val('');
        $('#sub_departemen').html('<option value="">Semua Team</option>');
        $('#btn-filter').click();
    });

    // ─── Departemen change - update team dropdown ─────────────────
    $('#kode_dept').change(function () {
        var kodeDept = $(this).val();
        var teamSelect = $('#sub_departemen');
        
        teamSelect.html('<option value="">Semua Team</option>');
        
        if (!kodeDept) {
            console.log('No departemen selected');
            return;
        }
        
        console.log('Fetching sub departemen for:', kodeDept);
        
        // Fetch team/sub_departemen dari API
        $.ajax({
            url: `/api/departemen/${kodeDept}/sub-departemen`,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                console.log('✓ AJAX Success - Full response:', response);
                console.log('  - response.success:', response.success);
                console.log('  - response.sub_departemen type:', typeof response.sub_departemen);
                console.log('  - response.sub_departemen value:', response.sub_departemen);
                
                // Check all conditions
                if (!response.success) {
                    console.warn('  ✗ response.success is false');
                    return;
                }
                
                if (!response.sub_departemen) {
                    console.warn('  ✗ response.sub_departemen is null/undefined');
                    return;
                }
                
                if (!Array.isArray(response.sub_departemen)) {
                    console.warn('  ✗ response.sub_departemen is not an array');
                    return;
                }
                
                if (response.sub_departemen.length === 0) {
                    console.warn('  ✗ response.sub_departemen is empty array');
                    return;
                }
                
                console.log('  ✓ All checks passed, adding', response.sub_departemen.length, 'teams');
                response.sub_departemen.forEach(function (team) {
                    console.log('    - Adding team:', team);
                    teamSelect.append(`<option value="${team}">${team}</option>`);
                });
                console.log('  ✓ Teams added, select now has', teamSelect.find('option').length - 1, 'teams');
            },
            error: function (xhr, status, error) {
                console.error('✗ AJAX Error:');
                console.error('  - Status:', status);
                console.error('  - Error:', error);
                console.error('  - Response Text:', xhr.responseText);
                console.error('  - Status Code:', xhr.status);
            }
        });
    });

    // ─── Toggle Radius ───────────────────────────────────────────
    var radiusVisible = true;
    $('#btn-toggle-radius').click(function () {
        if (radiusVisible) {
            radiusCircles.forEach(c => map.removeLayer(c));
            $(this).html('<i class="ti ti-circle-off me-2"></i>Show Radius').removeClass('btn-info').addClass('btn-warning');
        } else {
            addRadiusCirclesToMap(cabangRadius);
            $(this).html('<i class="ti ti-circle me-2"></i>Hide Radius').removeClass('btn-warning').addClass('btn-info');
        }
        radiusVisible = !radiusVisible;
    });

    // ─── Cabang dropdown ─────────────────────────────────────────
    $('#kode_cabang').change(function () {
        var val = $(this).val();
        if (val) {
            var selected = cabangRadius.filter(c => c.kode_cabang === val);
            if (selected.length) centerMapOnCabang(selected);
        } else {
            map.setView([-6.2088, 106.8456], 10);
        }
    });

    // ─── Export CSV ───────────────────────────────────────────────
    $('#btn-export-csv').click(function () {
        if (!filteredData.length) {
            Swal.fire({ icon: 'warning', title: 'Tidak Ada Data', text: 'Tidak ada data untuk di-export.', confirmButtonText: 'OK' });
            return;
        }
        var headers = ['No','NIK','Nama Karyawan','Cabang','Tanggal','Jam Masuk','Jam Keluar','Lokasi','Latitude','Longitude','Overlap'];
        var rows = filteredData.map((d, i) => [
            i + 1,
            d.nik || '',
            d.nama_karyawan || '',
            d.nama_cabang || '',
            d.tanggal || '',
            d.jam_in  ? new Date(d.jam_in).toLocaleTimeString('id-ID')  : '-',
            d.jam_out ? new Date(d.jam_out).toLocaleTimeString('id-ID') : '-',
            `"${(d.lokasi_in || '').replace(/"/g, '""')}"`,
            d.original_latitude  || d.latitude  || '',
            d.original_longitude || d.longitude || '',
            d.marker_count > 1 ? 'Ya' : 'Tidak'
        ]);
        var csv  = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href = url; a.download = 'presensi_' + ($('#tanggal').val() || 'export') + '.csv'; a.click();
        URL.revokeObjectURL(url);
    });

});
</script>
@endpush