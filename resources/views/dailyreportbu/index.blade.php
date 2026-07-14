@extends('layouts.app')
@section('titlepage', 'Daily Report Business')

@section('content')
@section('navigasi')
    <span>Daily Report Business</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                @if(auth()->user()->can('dailyreportbu.create') || auth()->user()->hasRole('karyawan'))
                    <a href="{{ route('dailyreportbu.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-2"></i>Buat Report Baru
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('dailyreportbu.index') }}" method="GET">
                            <div class="row">
                                @if (!auth()->user()->hasRole('karyawan'))
                                    <div class="col-lg-3 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <select name="nik" id="nik" class="form-select select2Nik">
                                                <option value="">Semua Karyawan</option>
                                                @foreach ($karyawans as $karyawan)
                                                    <option value="{{ $karyawan->nik }}" {{ Request('nik') == $karyawan->nik ? 'selected' : '' }}>
                                                        {{ $karyawan->nik }} - {{ $karyawan->nama_karyawan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <select name="sub_departemen" id="sub_departemen" class="form-select">
                                                <option value="">Semua Team (Sub Departemen)</option>
                                                @foreach ($subDepartemens as $sub)
                                                    <option value="{{ $sub }}" {{ Request('sub_departemen') == $sub ? 'selected' : '' }}>
                                                        {{ $sub }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                            <input type="text" class="form-control flatpickr-date" id="tanggal_awal" name="tanggal_awal"
                                                placeholder="Tanggal Awal" value="{{ Request('tanggal_awal') }}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                            <input type="text" class="form-control flatpickr-date" id="tanggal_akhir" name="tanggal_akhir"
                                                placeholder="Tanggal Akhir" value="{{ Request('tanggal_akhir') }}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Nama Karyawan</th>
                                        <th>Team (Sub Dept)</th>
                                        <th class="text-center">Online (Total)</th>
                                        <th class="text-center">Offline (Prospek)</th>
                                        <th class="text-center">Nasabah</th>
                                        <th class="text-center">Validasi Link</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reports as $index => $report)
                                        <tr>
                                            <td>{{ $reports->firstItem() + $index }}</td>
                                            <td>{{ \Carbon\Carbon::parse($report->tanggal)->translatedFormat('d F Y') }}</td>
                                            <td>
                                                <strong>{{ $report->nama_karyawan }}</strong><br>
                                                <small class="text-muted">{{ $report->nik }}</small>
                                            </td>
                                            <td>{{ $report->sub_departemen ?? '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $report->total_online ?? 0 }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning">{{ $report->offlineActivities->count() }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $report->nasabahData->count() }}</span>
                                            </td>

                                            {{-- Kolom Validasi Link Postingan --}}
                                            <td class="text-center">
                                                @php
                                                    // Ambil hanya online activities yang punya link_postingan
                                                    $linksWithUrl = $report->onlineActivities->filter(fn($a) => !empty($a->link_postingan));
                                                    $totalLink    = $linksWithUrl->count();
                                                    $verified     = $linksWithUrl->where('status_validasi', 'verified')->count();
                                                @endphp

                                                @if ($totalLink === 0)
                                                    <span class="text-muted">-</span>
                                                @elseif (auth()->user()->can('dailyreportbu.verify'))
                                                    {{-- Admin: badge yang bisa diklik untuk buka modal --}}
                                                    <button type="button"
                                                        class="btn btn-sm {{ $verified === $totalLink ? 'btn-success' : 'btn-warning' }} btn-verify-modal"
                                                        data-report-id="{{ $report->id }}"
                                                        title="Klik untuk verifikasi link postingan">
                                                        @if ($verified === $totalLink)
                                                            <i class="ti ti-circle-check me-1"></i>Verified
                                                        @else
                                                            <i class="ti ti-clock me-1"></i>Pending
                                                            @if ($verified > 0)
                                                                <span class="badge bg-light text-dark ms-1">{{ $verified }}/{{ $totalLink }}</span>
                                                            @endif
                                                        @endif
                                                    </button>
                                                @else
                                                    {{-- Karyawan: hanya badge status (read-only) --}}
                                                    @if ($verified === $totalLink)
                                                        <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Verified</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending</span>
                                                    @endif
                                                @endif
                                            </td>

                                            <td>
                                                <div class="d-flex gap-1">
                                                    @if(auth()->user()->can('dailyreportbu.index') || auth()->user()->hasRole('karyawan'))
                                                        <a href="{{ route('dailyreportbu.show', $report->id) }}" class="btn btn-info btn-sm" title="Detail">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                        <a href="{{ route('dailyreportbu.export.pdf', ['id' => $report->id]) }}" class="btn btn-danger btn-sm" target="_blank" title="Export PDF">
                                                            <i class="ti ti-file-export"></i>
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()->can('dailyreportbu.edit') || auth()->user()->hasRole('karyawan'))
                                                        <a href="{{ route('dailyreportbu.edit', $report->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                            <i class="ti ti-pencil"></i>
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()->can('dailyreportbu.delete') || auth()->user()->hasRole('karyawan'))
                                                        <form action="{{ route('dailyreportbu.destroy', $report->id) }}" method="POST" class="d-inline form-delete">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete" title="Hapus">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">Tidak ada data daily report.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $reports->withQueryString()->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Verifikasi Link Postingan --}}
@if(auth()->user()->can('dailyreportbu.verify'))
<div class="modal fade" id="modalVerifikasiLink" tabindex="-1" aria-labelledby="modalVerifikasiLinkLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVerifikasiLinkLabel">
                    <i class="ti ti-link me-2"></i>Verifikasi Link Postingan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalVerifikasiBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('myscript')
<script>
    $(function() {
        // ------------------------------------------------
        // Select2 untuk filter karyawan
        // ------------------------------------------------
        if ($('.select2Nik').length) {
            $('.select2Nik').select2({
                placeholder: 'Semua Karyawan',
                allowClear: true,
                width: '100%'
            });
        }

        // ------------------------------------------------
        // Flatpickr untuk filter tanggal
        // ------------------------------------------------
        if ($('.flatpickr-date').length) {
            $('.flatpickr-date').flatpickr({
                dateFormat: "Y-m-d",
            });
        }

        // ------------------------------------------------
        // Konfirmasi hapus report
        // ------------------------------------------------
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

        // ------------------------------------------------
        // Modal Verifikasi Link Postingan
        // Data link tiap platform di-load dinamis dari DOM
        // ------------------------------------------------
        var verifyBaseUrl = '{{ rtrim(url('/'), '/') }}/dailyreportbu/online/';
        var verifyCsrf   = '{{ csrf_token() }}';

        // Mapping data online activities per report — disiapkan dari Blade
        var reportLinksData = {};
        @foreach($reports as $report)
            reportLinksData[{{ $report->id }}] = [
                @foreach($report->onlineActivities->filter(fn($a) => !empty($a->link_postingan)) as $act)
                {
                    id: {{ $act->id }},
                    platform: '{{ $act->platform }}',
                    link: '{{ addslashes($act->link_postingan) }}',
                    status: '{{ $act->status_validasi }}'
                },
                @endforeach
            ];
        @endforeach

        /**
         * Render isi modal verifikasi berdasarkan data link
         */
        function renderModalBody(reportId) {
            var links = reportLinksData[reportId] || [];
            if (links.length === 0) {
                return '<div class="alert alert-info">Tidak ada link postingan pada report ini.</div>';
            }

            var rows = links.map(function(item) {
                var isVerified = item.status === 'verified';
                var badgeHtml = isVerified
                    ? '<span class="badge bg-success" id="badge-' + item.id + '"><i class="ti ti-circle-check me-1"></i>Verified</span>'
                    : '<span class="badge bg-warning text-dark" id="badge-' + item.id + '"><i class="ti ti-clock me-1"></i>Pending</span>';
                var btnLabel = isVerified ? 'Batalkan' : 'Verifikasi';
                var btnClass = isVerified ? 'btn-outline-warning' : 'btn-outline-success';

                return '<tr id="row-' + item.id + '">' +
                    '<td class="text-capitalize fw-bold"><i class="ti ti-brand-' + item.platform + ' me-1"></i>' + item.platform + '</td>' +
                    '<td><a href="' + item.link + '" target="_blank" class="text-truncate d-inline-block" style="max-width:300px;" title="' + item.link + '">' + item.link + '</a></td>' +
                    '<td class="text-center">' + badgeHtml + '</td>' +
                    '<td class="text-center">' +
                        '<button class="btn btn-sm ' + btnClass + ' btn-toggle-verify" data-id="' + item.id + '" data-report-id="' + reportId + '">' +
                            '<i class="ti ti-check me-1"></i>' + btnLabel +
                        '</button>' +
                    '</td>' +
                '</tr>';
            }).join('');

            return '<div class="table-responsive">' +
                '<table class="table table-bordered align-middle">' +
                '<thead class="table-dark"><tr>' +
                '<th>Platform</th><th>Link Postingan</th><th class="text-center">Status</th><th class="text-center">Aksi</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '</table></div>' +
                '<p class="text-muted small mb-0"><i class="ti ti-info-circle me-1"></i>Klik tombol untuk toggle status Pending ↔ Verified.</p>';
        }

        // Buka modal saat klik tombol badge pada baris tabel
        $(document).on('click', '.btn-verify-modal', function() {
            var reportId = $(this).data('report-id');
            $('#modalVerifikasiBody').html(renderModalBody(reportId));
            var modal = new bootstrap.Modal(document.getElementById('modalVerifikasiLink'));
            modal.show();
        });

        // Toggle verify via AJAX
        $(document).on('click', '.btn-toggle-verify', function() {
            var btn      = $(this);
            var onlineId = btn.data('id');
            var reportId = btn.data('report-id');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: verifyBaseUrl + onlineId + '/verify',
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': verifyCsrf },
                success: function(res) {
                    if (res.success) {
                        var isVerified = res.status === 'verified';

                        // Update badge di dalam modal
                        var badgeHtml = isVerified
                            ? '<span class="badge bg-success" id="badge-' + onlineId + '"><i class="ti ti-circle-check me-1"></i>Verified</span>'
                            : '<span class="badge bg-warning text-dark" id="badge-' + onlineId + '"><i class="ti ti-clock me-1"></i>Pending</span>';
                        $('#badge-' + onlineId).replaceWith(badgeHtml);

                        // Update tombol
                        var btnLabel = isVerified ? 'Batalkan' : 'Verifikasi';
                        var btnClass = isVerified ? 'btn-outline-warning' : 'btn-outline-success';
                        btn.removeClass('btn-outline-success btn-outline-warning').addClass(btnClass)
                            .prop('disabled', false)
                            .html('<i class="ti ti-check me-1"></i>' + btnLabel);

                        // Update data lokal
                        var links = reportLinksData[reportId];
                        if (links) {
                            for (var i = 0; i < links.length; i++) {
                                if (links[i].id === onlineId) {
                                    links[i].status = res.status;
                                    break;
                                }
                            }
                        }

                        // Hitung ulang status badge di baris tabel utama
                        var allLinks   = reportLinksData[reportId] || [];
                        var totalLink  = allLinks.length;
                        var verifiedCt = allLinks.filter(function(l) { return l.status === 'verified'; }).length;
                        var rowBtn = $('button.btn-verify-modal[data-report-id="' + reportId + '"]');
                        if (totalLink > 0) {
                            if (verifiedCt === totalLink) {
                                rowBtn.removeClass('btn-warning').addClass('btn-success')
                                    .html('<i class="ti ti-circle-check me-1"></i>Verified');
                            } else {
                                var badge = verifiedCt > 0
                                    ? ' <span class="badge bg-light text-dark ms-1">' + verifiedCt + '/' + totalLink + '</span>'
                                    : '';
                                rowBtn.removeClass('btn-success').addClass('btn-warning')
                                    .html('<i class="ti ti-clock me-1"></i>Pending' + badge);
                            }
                        }

                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    } else {
                        btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Error');
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Retry');
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan. Silakan coba lagi.' });
                }
            });
        });
    });
</script>
@endpush
