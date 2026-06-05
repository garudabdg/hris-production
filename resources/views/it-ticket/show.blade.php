@extends('layouts.app')
@section('titlepage', 'Detail Tiket — ' . $itTicket->nomor_tiket)

@section('content')
@section('navigasi')
    <a href="{{ route('it-ticket.index') }}">IT Ticket</a>
    <span> / {{ $itTicket->nomor_tiket }}</span>
@endsection

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-alert-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">
    {{-- Kolom Kiri: Detail Tiket --}}
    <div class="col-lg-8">

        {{-- Header Card --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">
                        <code class="text-primary me-2">{{ $itTicket->nomor_tiket }}</code>
                        {{ $itTicket->judul }}
                        @if($itTicket->nomor_urut)
                            <span class="badge bg-label-primary ms-2" style="font-size: 14px;"><i class="ti ti-list-numbers me-1"></i>Antrean Hari Ini: #{{ $itTicket->nomor_urut }}</span>
                        @endif
                    </h5>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        {!! $itTicket->status_badge !!}
                        {!! $itTicket->priority_badge !!}
                        {!! $itTicket->klasifikasi_badge !!}
                        @if($itTicket->isOverdue())
                            <span class="badge bg-danger"><i class="ti ti-alert-triangle me-1"></i>Overdue</span>
                        @endif
                    </div>
                </div>
                <div class="text-muted small text-end">
                    <div>Dibuat: {{ $itTicket->created_at->format('d/m/Y H:i') }}</div>
                    <div>SLA: <strong class="{{ $itTicket->isOverdue() ? 'text-danger' : '' }}">
                        {{ $itTicket->tanggal_target ? $itTicket->tanggal_target->format('d/m/Y') : '-' }}
                    </strong></div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Kategori</small>
                        <span class="badge bg-label-secondary text-capitalize">{{ $itTicket->kategori }}</span>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Dampak</small>
                        <span class="fw-semibold text-capitalize">{{ $itTicket->dampak }}</span>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Cabang</small>
                        <span class="fw-semibold">{{ optional($itTicket->cabang)->nama_cabang ?? '-' }}</span>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Lokasi Detail</small>
                        <span class="fw-semibold">{{ $itTicket->lokasi ?? '-' }}</span>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Pemohon</small>
                        <span class="fw-semibold">{{ $itTicket->pemohon->name ?? '-' }}</span>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Departemen</small>
                        @php
                            $karyawan = \App\Models\Karyawan::where('nik', $itTicket->pemohon->username ?? null)->first();
                            $dept = $karyawan ? optional($karyawan->departemen)->nama_dept : null;
                            $subDept = $karyawan ? $karyawan->sub_departemen : null;
                        @endphp
                        <span class="fw-semibold">{{ $dept ?? '-' }}</span>
                        @if($subDept)
                            <small class="text-muted d-block" style="font-size:10px;">{{ $subDept }}</small>
                        @endif
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Assigned To</small>
                        @if($itTicket->assignedTo)
                            <span class="badge bg-label-info">{{ $itTicket->assignedTo->name }}</span>
                        @else
                            <span class="text-muted">Belum di-assign</span>
                        @endif
                    </div>
                    @if($itTicket->resolved_at)
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Resolved At</small>
                            <span class="text-success fw-semibold">{{ $itTicket->resolved_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                <hr>
                <h6 class="fw-semibold">Deskripsi Masalah</h6>
                <p style="white-space:pre-wrap;">{{ $itTicket->deskripsi }}</p>

                @if($itTicket->lampiran)
                    <div class="mt-2">
                        <a href="{{ asset('storage/it-tickets/' . $itTicket->lampiran) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-paperclip me-1"></i> Lihat Lampiran
                        </a>
                    </div>
                @endif

                @if($itTicket->catatan_resolusi)
                    <div class="alert alert-success mt-3">
                        <h6 class="fw-bold"><i class="ti ti-circle-check me-2"></i>Catatan Resolusi</h6>
                        <p class="mb-0" style="white-space:pre-wrap;">{{ $itTicket->catatan_resolusi }}</p>
                        @if($itTicket->resolvedBy)
                            <small class="text-muted">— Diselesaikan oleh {{ $itTicket->resolvedBy->name }}</small>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Timeline Responses --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ti ti-messages me-2"></i>Riwayat & Aktivitas Tiket</h6>
                <span id="live-indicator" class="d-flex align-items-center gap-1 text-success" style="font-size:12px;font-weight:600;">
                    <span id="live-dot" style="width:8px;height:8px;border-radius:50%;background:#28a745;display:inline-block;animation:pulse-dot 1.5s infinite;"></span>
                    Live
                </span>
            </div>
            <div class="card-body" id="timeline-container" style="max-height:500px;overflow-y:auto;">
                @forelse($itTicket->responses as $resp)
                    <div class="d-flex gap-3 mb-4 timeline-item" data-id="{{ $resp->id }}">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ strtoupper(substr($resp->user->name ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <strong>{{ $resp->user->name ?? '-' }}</strong>
                                    <span class="ms-2">{!! $resp->tipe_badge !!}</span>
                                </div>
                                <small class="text-muted">{{ $resp->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="card bg-light border-0 p-3" style="white-space:pre-wrap;">{{ $resp->pesan }}</div>
                            @if($resp->lampiran)
                                <a href="{{ asset('storage/it-tickets/' . $resp->lampiran) }}" target="_blank" class="btn btn-outline-secondary btn-sm mt-1">
                                    <i class="ti ti-paperclip me-1"></i> Lampiran
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3" id="empty-msg">Belum ada aktivitas.</p>
                @endforelse
            </div>

                {{-- Form Balas --}}
                    <div class="card-footer" id="reply-form-wrapper">
                        <h6 class="fw-semibold mb-3"><i class="ti ti-send me-2"></i>Tambah Balasan</h6>
                        <form id="formReply" autocomplete="off">
                            @csrf
                            <div class="mb-3">
                                <textarea name="pesan" id="pesanInput" rows="3" class="form-control"
                                    placeholder="Tulis balasan..."></textarea>
                            </div>
                            <div class="mb-3">
                                <input type="file" name="lampiran" id="lampiranInput" class="form-control"
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.zip">
                                <div class="form-text">Opsional. Maks 5MB.</div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm" id="btnReply">
                                <i class="ti ti-send me-1"></i> Kirim Balasan
                            </button>
                        </form>
                    </div>
        </div>
    </div>

    {{-- Kolom Kanan: Panel Manajemen --}}
    <div class="col-lg-4">

        @if($canManage)
        {{-- Update Status --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="ti ti-settings me-2"></i>Update Status</h6></div>
            <div class="card-body">
                <form action="{{ route('it-ticket.update-status', $itTicket->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="open"        {{ $itTicket->status=='open'        ?'selected':'' }}>Open</option>
                            <option value="in_progress" {{ $itTicket->status=='in_progress' ?'selected':'' }}>In Progress</option>
                            <option value="pending"     {{ $itTicket->status=='pending'     ?'selected':'' }}>Pending</option>
                            <option value="resolved"    {{ $itTicket->status=='resolved'    ?'selected':'' }}>Resolved</option>
                            <option value="closed"      {{ $itTicket->status=='closed'      ?'selected':'' }}>Closed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Prioritas</label>
                        <select name="prioritas" class="form-select form-select-sm">
                            <option value="low"      {{ $itTicket->prioritas=='low'      ?'selected':'' }}>Low</option>
                            <option value="medium"   {{ $itTicket->prioritas=='medium'   ?'selected':'' }}>Medium</option>
                            <option value="high"     {{ $itTicket->prioritas=='high'     ?'selected':'' }}>High</option>
                            <option value="critical" {{ $itTicket->prioritas=='critical' ?'selected':'' }}>Critical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Klasifikasi Data</label>
                        <select name="klasifikasi_data" class="form-select form-select-sm">
                            <option value="public"       {{ $itTicket->klasifikasi_data=='public'       ?'selected':'' }}>Public</option>
                            <option value="internal"     {{ $itTicket->klasifikasi_data=='internal'     ?'selected':'' }}>Internal</option>
                            <option value="confidential" {{ $itTicket->klasifikasi_data=='confidential' ?'selected':'' }}>Confidential</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan Resolusi</label>
                        <textarea name="catatan_resolusi" rows="3" class="form-control form-control-sm"
                            placeholder="Isi jika resolved/closed...">{{ $itTicket->catatan_resolusi }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm w-100">
                        <i class="ti ti-refresh me-1"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        {{-- Assign --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="ti ti-user-check me-2"></i>Assign ke IT Staff</h6></div>
            <div class="card-body">
                <form action="{{ route('it-ticket.assign', $itTicket->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <select name="assigned_to" class="form-select form-select-sm select2">
                            <option value="">-- Pilih IT Staff --</option>
                            @foreach($itStaffs as $staff)
                                <option value="{{ $staff->id }}" {{ $itTicket->assigned_to == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-info btn-sm w-100">
                        <i class="ti ti-user-plus me-1"></i> Assign
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Info ISO 27001 --}}
        <div class="card border-warning">
            <div class="card-header bg-warning-subtle">
                <h6 class="mb-0 text-warning"><i class="ti ti-shield-lock me-2"></i>ISO 27001 Info</h6>
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <span class="text-muted">Klasifikasi Data:</span><br>
                    <strong>{!! $itTicket->klasifikasi_badge !!}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Dampak:</span><br>
                    <strong class="text-capitalize">{{ $itTicket->dampak }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted">SLA Target:</span><br>
                    <strong class="{{ $itTicket->isOverdue() ? 'text-danger' : 'text-success' }}">
                        {{ $itTicket->tanggal_target ? $itTicket->tanggal_target->format('d/m/Y') : '-' }}
                    </strong>
                </div>
                <div class="mb-0">
                    <span class="text-muted">Nomor Referensi:</span><br>
                    <code>{{ $itTicket->nomor_tiket }}</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<style>
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.4; transform: scale(0.7); }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .timeline-item.new-item { animation: fadeInUp .4s ease; }
</style>
<script>
$(function () {
    $('.select2').select2({ width: '100%' });

    const RESPOND_URL  = "{{ route('it-ticket.respond', $itTicket->id) }}";
    const POLL_URL     = "{{ route('it-ticket.responses', $itTicket->id) }}";
    const TICKET_ID    = {{ $itTicket->id }};

    let lastResponseId = {{ $itTicket->responses->max('id') ?? 0 }};
    let isPolling      = false;
    let pollInterval   = null;

    // ── Build HTML item ────────────────────────────────────────────────────
    function buildItem(r) {
        const lampiran = r.lampiran
            ? `<a href="${r.lampiran}" target="_blank" class="btn btn-outline-secondary btn-sm mt-1">
                 <i class="ti ti-paperclip me-1"></i> Lampiran
               </a>`
            : '';

        return `<div class="d-flex gap-3 mb-4 timeline-item new-item" data-id="${r.id}">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded-circle bg-label-primary">${r.user_initial}</span>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div><strong>${escapeHtml(r.user_name)}</strong>
                        <span class="ms-2 badge bg-label-secondary">Balasan</span>
                    </div>
                    <small class="text-muted">${r.created_at}</small>
                </div>
                <div class="card bg-light border-0 p-3" style="white-space:pre-wrap;">${escapeHtml(r.pesan)}</div>
                ${lampiran}
            </div>
        </div>`;
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    // ── Polling ────────────────────────────────────────────────────────────
    function pollResponses() {
        if (isPolling) return;
        isPolling = true;

        $.ajax({
            url: POLL_URL + '?after=' + lastResponseId,
            method: 'GET',
            success: function(data) {
                const $container = $('#timeline-container');

                if (data.responses && data.responses.length > 0) {
                    $('#empty-msg').remove();
                    data.responses.forEach(function(r) {
                        $container.append(buildItem(r));
                        lastResponseId = Math.max(lastResponseId, r.id);
                    });
                    // Scroll ke bawah
                    $container.scrollTop($container[0].scrollHeight);
                }

                // Update tampilan badge status
                $('.badge-status').html(data.status_badge);$('#live-dot').css('background', '#28a745');
            },
            error: function() {
                $('#live-dot').css('background', '#dc3545');
            },
            complete: function() {
                isPolling = false;
            }
        });
    }

    function startPolling() {
        pollInterval = setInterval(pollResponses, 4000);
    }

    function stopPolling() {
        clearInterval(pollInterval);
    }

    // ── AJAX Submit Form ───────────────────────────────────────────────────
    $('#formReply').on('submit', function(e) {
        e.preventDefault();

        const pesan = $('#pesanInput').val().trim();
        if (!pesan) {
            Swal.fire({ title: 'Oops!', text: 'Balasan tidak boleh kosong!', icon: 'warning' });
            return;
        }

        const $btn = $('#btnReply');
        $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Mengirim...');

        const formData = new FormData(this);
        formData.append('_token', CSRF_TOKEN);

        $.ajax({
            url: RESPOND_URL,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'Accept': 'application/json' },
            success: function(data) {
                if (data.success) {
                    const $container = $('#timeline-container');
                    $('#empty-msg').remove();
                    $container.append(buildItem(data.response));
                    lastResponseId = Math.max(lastResponseId, data.response.id);
                    $container.scrollTop($container[0].scrollHeight);

                    // Reset form
                    $('#pesanInput').val('');
                    $('#lampiranInput').val('');
                } else {
                    Swal.fire({ title: 'Gagal', text: 'Gagal mengirim balasan.', icon: 'error' });
                }
            },
            error: function() {
                Swal.fire({ title: 'Error', text: 'Koneksi bermasalah. Coba lagi.', icon: 'error' });
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Kirim Balasan');
            }
        });
    });

    startPolling();
});
</script>
@endpush
