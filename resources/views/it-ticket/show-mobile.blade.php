@extends('layouts.mobile.modern')
@section('title', $itTicket->nomor_tiket)

@section('header_left')
    <a href="{{ route('it-ticket.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
<style>
    body { background: #f8fafc !important; }

    .detail-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 12px;
    }
    .detail-card .card-header-mobile {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
    }
    .detail-card .card-body-mobile { padding: 14px 16px; }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 6px 0;
        border-bottom: 1px solid #f8fafc;
        gap: 10px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 11px; color: #94a3b8; font-weight: 600; flex-shrink: 0; }
    .info-value { font-size: 12px; color: #1e293b; font-weight: 600; text-align: right; }

    /* Status chips */
    .s-open       { background: #dbeafe; color: #1d4ed8; }
    .s-in_progress{ background: #fef3c7; color: #d97706; }
    .s-pending    { background: #f3e8ff; color: #7c3aed; }
    .s-resolved   { background: #dcfce7; color: #16a34a; }
    .s-closed     { background: #f1f5f9; color: #64748b; }

    .badge-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Timeline */
    .timeline-item {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
        position: relative;
    }
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 34px;
        bottom: -8px;
        width: 1.5px;
        background: #e2e8f0;
    }
    .timeline-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        background: {{ $t['primary'] ?? '#2d5a4c' }}20;
        flex-shrink: 0;
    }
    .timeline-bubble {
        flex: 1;
        background: #f8fafc;
        border-radius: 0 12px 12px 12px;
        padding: 8px 12px;
        font-size: 12px;
        color: #334155;
        border: 1px solid #e2e8f0;
        white-space: pre-wrap;
        word-break: break-word;
    }

    /* Form Reply */
    .form-label-group {
        position: relative;
        margin-bottom: 10px;
        border: 1.5px solid {{ $t['primary'] ?? '#2d5a4c' }};
        border-radius: 14px;
        overflow: hidden;
    }
    .form-label-group .input-icon {
        position: absolute;
        left: 12px; top: 14px;
        font-size: 18px;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        z-index: 5;
        pointer-events: none;
    }
    .form-label-group textarea {
        width: 100% !important;
        height: 90px;
        padding: 20px 12px 6px 38px !important;
        font-size: 13px;
        background: transparent !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        resize: none;
        color: #1e293b;
    }
    .form-label-group label {
        position: absolute;
        top: 12px; left: 38px;
        font-size: 12px;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        opacity: 0.75;
        pointer-events: none;
        transition: all 0.2s;
        z-index: 5;
    }
    .form-label-group textarea:focus ~ label,
    .form-label-group textarea:not(:placeholder-shown) ~ label {
        top: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; opacity: 1;
    }

    .btn-send {
        width: 100%; height: 46px;
        background: {{ $t['primary'] ?? '#2d5a4c' }};
        color: #fff; border: none; border-radius: 12px;
        font-size: 14px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: all 0.3s;
    }
    .btn-send:active { transform: scale(0.97); }

    /* IT Staff Management Panel */
    .manage-select {
        width: 100%;
        padding: 10px 12px;
        font-size: 13px;
        font-weight: 500;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        color: #1e293b;
        appearance: none;
        outline: none;
        margin-bottom: 8px;
    }
    .btn-action {
        width: 100%; height: 40px;
        border: none; border-radius: 10px;
        font-size: 13px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.3s; cursor: pointer; margin-bottom: 8px;
    }
    .btn-warning { background: #fef3c7; color: #d97706; }
    .btn-info    { background: #dbeafe; color: #1d4ed8; }
    .btn-warning:active, .btn-info:active { transform: scale(0.97); }
</style>
@endpush

@section('content')

@php
    $user = auth()->user();
    $prioColors = ['critical'=>'#dc3545','high'=>'#fd7e14','medium'=>'#0d6efd','low'=>'#6c757d'];
    $prioColor = $prioColors[$itTicket->prioritas] ?? '#6c757d';
@endphp

<div class="px-3 pt-3 pb-28">

    @if(session('success'))
    <div class="mb-3 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-xl text-xs font-medium flex items-center gap-2">
        <ion-icon name="checkmark-circle"></ion-icon> {{ session('success') }}
    </div>
    @endif

    {{-- Header Tiket --}}
    <div class="detail-card">
        <div style="background:{{ $prioColor }};padding:14px 16px;">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                    <span style="font-size:10px;color:#ffffffbb;font-weight:600;">{{ $itTicket->nomor_tiket }}</span>
                    <h2 style="font-size:16px;font-weight:800;color:#fff;line-height:1.3;margin-top:2px;">{{ $itTicket->judul }}</h2>
                </div>
                <span class="badge-chip s-{{ $itTicket->status }}" style="font-size:10px;flex-shrink:0;margin-top:2px;">
                    {{ str_replace('_',' ',strtoupper($itTicket->status)) }}
                </span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($itTicket->nomor_urut)
                    <span style="font-size:10px;background:#ffffff;color:#000;padding:2px 8px;border-radius:20px;font-weight:800;">
                        <ion-icon name="list-outline" style="vertical-align:-2px;margin-right:2px;"></ion-icon>Antrean #{{ $itTicket->nomor_urut }}
                    </span>
                @endif
                <span style="font-size:10px;background:#ffffff30;color:#fff;padding:2px 8px;border-radius:20px;font-weight:700;">{{ ucfirst($itTicket->prioritas) }}</span>
                <span style="font-size:10px;background:#ffffff20;color:#ffffffcc;padding:2px 8px;border-radius:20px;">{{ ucfirst($itTicket->kategori) }}</span>
                @if($itTicket->isOverdue())
                    <span style="font-size:10px;background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:20px;font-weight:700;">⚠ Overdue</span>
                @endif
            </div>
        </div>
        <div class="card-body-mobile">
            <div class="info-row">
                <span class="info-label">Pemohon</span>
                <span class="info-value">{{ $itTicket->pemohon->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cabang</span>
                <span class="info-value">{{ optional($itTicket->cabang)->nama_cabang ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dampak</span>
                <span class="info-value" style="text-transform:capitalize;">{{ $itTicket->dampak }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Assigned To</span>
                <span class="info-value">{{ $itTicket->assignedTo->name ?? 'Belum di-assign' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">SLA Target</span>
                <span class="info-value" style="{{ $itTicket->isOverdue() ? 'color:#dc2626;' : 'color:#16a34a;' }}">
                    {{ $itTicket->tanggal_target ? $itTicket->tanggal_target->format('d/m/Y') : '-' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Dibuat</span>
                <span class="info-value">{{ $itTicket->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($itTicket->resolved_at)
            <div class="info-row">
                <span class="info-label">Resolved</span>
                <span class="info-value" style="color:#16a34a;">{{ $itTicket->resolved_at->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Deskripsi --}}
    <div class="detail-card">
        <div class="card-header-mobile">
            <ion-icon name="document-text-outline" style="color:{{ $prioColor }};"></ion-icon>
            Deskripsi Masalah
        </div>
        <div class="card-body-mobile">
            <p style="font-size:13px;color:#334155;line-height:1.6;white-space:pre-wrap;margin:0;">{{ $itTicket->deskripsi }}</p>
            @if($itTicket->lampiran)
            <a href="{{ asset('storage/it-tickets/' . $itTicket->lampiran) }}" target="_blank"
               class="inline-flex items-center gap-1 mt-3 px-3 py-1.5 rounded-lg text-xs font-semibold"
               style="background:#f1f5f9;color:#475569;">
                <ion-icon name="attach-outline"></ion-icon> Lihat Lampiran
            </a>
            @endif
        </div>
    </div>

    @if($itTicket->catatan_resolusi)
    {{-- Catatan Resolusi --}}
    <div class="detail-card" style="border:1.5px solid #bbf7d0;">
        <div class="card-header-mobile" style="background:#f0fdf4;color:#16a34a;">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
            Catatan Resolusi
        </div>
        <div class="card-body-mobile">
            <p style="font-size:13px;color:#166534;line-height:1.6;white-space:pre-wrap;margin:0;">{{ $itTicket->catatan_resolusi }}</p>
            @if($itTicket->resolvedBy)
                <p style="font-size:10px;color:#94a3b8;margin-top:6px;margin-bottom:0;">— {{ $itTicket->resolvedBy->name }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- IT Staff: Update Status & Assign --}}
    @if($canManage)
    <div class="detail-card">
        <div class="card-header-mobile">
            <ion-icon name="settings-outline" style="color:{{ $t['primary'] ?? '#2d5a4c' }};"></ion-icon>
            Manajemen Tiket
        </div>
        <div class="card-body-mobile">
            {{-- Update Status --}}
            <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Update Status</p>
            <form action="{{ route('it-ticket.update-status', $itTicket->id) }}" method="POST">
                @csrf @method('PUT')
                <select name="status" class="manage-select">
                    <option value="open"        {{ $itTicket->status=='open'        ?'selected':'' }}>Open</option>
                    <option value="in_progress" {{ $itTicket->status=='in_progress' ?'selected':'' }}>In Progress</option>
                    <option value="pending"     {{ $itTicket->status=='pending'     ?'selected':'' }}>Pending</option>
                    <option value="resolved"    {{ $itTicket->status=='resolved'    ?'selected':'' }}>Resolved</option>
                    <option value="closed"      {{ $itTicket->status=='closed'      ?'selected':'' }}>Closed</option>
                </select>
                <textarea name="catatan_resolusi" class="manage-select" style="height:70px;resize:none;" placeholder="Catatan resolusi (jika resolved/closed)...">{{ $itTicket->catatan_resolusi }}</textarea>
                <button type="submit" class="btn-action btn-warning">
                    <ion-icon name="refresh-outline"></ion-icon> Update Status
                </button>
            </form>

            <hr style="border-color:#f1f5f9;margin:10px 0;">

            {{-- Assign IT Staff --}}
            <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Assign IT Staff</p>
            <form action="{{ route('it-ticket.assign', $itTicket->id) }}" method="POST">
                @csrf @method('PUT')
                <select name="assigned_to" class="manage-select">
                    <option value="">-- Pilih IT Staff --</option>
                    @foreach($itStaffs as $staff)
                        <option value="{{ $staff->id }}" {{ $itTicket->assigned_to == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-action btn-info">
                    <ion-icon name="person-add-outline"></ion-icon> Assign
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Timeline / Riwayat --}}
    <div class="detail-card">
        <div class="card-header-mobile">
            <ion-icon name="chatbubbles-outline" style="color:{{ $t['primary'] ?? '#2d5a4c' }};"></ion-icon>
            Riwayat Aktivitas
            <span id="live-indicator" class="ml-auto flex items-center gap-1" style="font-size:10px;color:#16a34a;font-weight:600;">
                <span id="live-dot" style="width:7px;height:7px;border-radius:50%;background:#16a34a;display:inline-block;animation:pulse-dot 1.5s infinite;"></span>
                Live
            </span>
        </div>
        <div class="card-body-mobile" id="timeline-container">
            @forelse($itTicket->responses as $resp)
            <div class="timeline-item" data-id="{{ $resp->id }}">
                <div class="timeline-avatar">{{ strtoupper(substr($resp->user->name ?? '?', 0, 1)) }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span style="font-size:11px;font-weight:700;color:#1e293b;">{{ $resp->user->name ?? '-' }}</span>
                        <span style="font-size:10px;color:#94a3b8;">{{ $resp->created_at->format('d/m H:i') }}</span>
                    </div>
                    <div class="timeline-bubble">{{ $resp->pesan }}</div>
                    @if($resp->lampiran)
                    <a href="{{ asset('storage/it-tickets/' . $resp->lampiran) }}" target="_blank"
                       class="inline-flex items-center gap-1 mt-1 px-2 py-1 rounded text-xs font-semibold"
                       style="background:#f1f5f9;color:#475569;">
                        <ion-icon name="attach-outline"></ion-icon> Lampiran
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <p id="empty-msg" style="font-size:12px;color:#94a3b8;text-align:center;padding:12px 0;margin:0;">Belum ada aktivitas.</p>
            @endforelse
        </div>
    </div>

    {{-- Form Balas --}}
    <div class="detail-card" id="reply-card">
        <div class="card-header-mobile">
            <ion-icon name="send-outline" style="color:{{ $t['primary'] ?? '#2d5a4c' }};"></ion-icon>
            Tambah Balasan
        </div>
        <div class="card-body-mobile">
            <form id="formReply" autocomplete="off">
                @csrf
                <div class="form-label-group">
                    <ion-icon name="chatbox-outline" class="input-icon"></ion-icon>
                    <textarea name="pesan" id="pesanInput" placeholder=" " required></textarea>
                    <label for="pesanInput">Tulis balasan...</label>
                </div>
                <div class="mb-3">
                    <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">Lampiran (Opsional)</label>
                    <div class="relative" style="border:1.5px dashed #cbd5e1;border-radius:12px;padding:10px;background:#f8fafc;text-align:center;">
                        <input type="file" name="lampiran" id="lampiranReply" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.zip"
                               onchange="updateReplyFile(this)"
                               style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                        <ion-icon name="attach-outline" style="font-size:18px;color:#94a3b8;"></ion-icon>
                        <span id="reply-file-label" style="font-size:11px;color:#94a3b8;margin-left:6px;">Ketuk untuk lampirkan file</span>
                    </div>
                </div>
                <button type="submit" class="btn-send" id="btnReply">
                    <ion-icon name="paper-plane-outline"></ion-icon>
                    Kirim Balasan
                </button>
            </form>
        </div>
    </div>

</div>

@endsection

@push('myscript')
<script>
    const TICKET_ID    = {{ $itTicket->id }};
    const RESPOND_URL  = "{{ route('it-ticket.respond', $itTicket->id) }}";
    const POLL_URL     = "{{ route('it-ticket.responses', $itTicket->id) }}";
    const CSRF_TOKEN   = "{{ csrf_token() }}";
    const PRIMARY_COLOR = "{{ $t['primary'] ?? '#2d5a4c' }}";

    // Track last response id yang sudah tampil
    let lastResponseId = {{ $itTicket->responses->max('id') ?? 0 }};
    let pollInterval   = null;
    let isPolling      = false;

    // ── Build timeline item HTML ────────────────────────────────────────────
    function buildTimelineItem(r, highlight = false) {
        const lampiran = r.lampiran
            ? `<a href="${r.lampiran}" target="_blank"
                 class="inline-flex items-center gap-1 mt-1 px-2 py-1 rounded text-xs font-semibold"
                 style="background:#f1f5f9;color:#475569;">
                 <ion-icon name="attach-outline"></ion-icon> Lampiran
               </a>`
            : '';

        const animation = highlight ? 'style="animation:fadeIn .4s ease;"' : '';

        return `<div class="timeline-item" data-id="${r.id}" ${animation}>
            <div class="timeline-avatar">${r.user_initial}</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                    <span style="font-size:11px;font-weight:700;color:#1e293b;">${r.user_name}</span>
                    <span style="font-size:10px;color:#94a3b8;">${r.created_at}</span>
                </div>
                <div class="timeline-bubble">${escapeHtml(r.pesan)}</div>
                ${lampiran}
            </div>
        </div>`;
    }

    function escapeHtml(text) {
        return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                   .replace(/"/g,'&quot;').replace(/\n/g,'<br>');
    }

    // ── Polling: fetch new responses ────────────────────────────────────────
    function pollResponses() {
        if (isPolling) return;
        isPolling = true;

        fetch(`${POLL_URL}?after=${lastResponseId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('timeline-container');

            if (data.responses && data.responses.length > 0) {
                // Hapus empty msg jika ada
                const emptyMsg = document.getElementById('empty-msg');
                if (emptyMsg) emptyMsg.remove();

                data.responses.forEach(r => {
                    container.insertAdjacentHTML('beforeend', buildTimelineItem(r, true));
                    lastResponseId = Math.max(lastResponseId, r.id);
                });

                // Scroll ke bawah
                container.scrollTop = container.scrollHeight;
                container.parentElement.scrollIntoView({ behavior: 'smooth', block: 'end' });

                // Rebind ionicons
                if (typeof customElements !== 'undefined' && customElements.get('ion-icon')) {
                    // ionicons auto-renders new elements
                }
            }
        })
        .catch(() => {
            // silent fail — dot merah
            const dot = document.getElementById('live-dot');
            if (dot) dot.style.background = '#dc2626';
        })
        .finally(() => {
            isPolling = false;
        });
    }

    function startPolling() {
        pollInterval = setInterval(pollResponses, 4000);
    }

    function stopPolling() {
        if (pollInterval) clearInterval(pollInterval);
        const indicator = document.getElementById('live-indicator');
        if (indicator) indicator.style.display = 'none';
    }

    // ── AJAX Form Submit ────────────────────────────────────────────────────
    function updateReplyFile(input) {
        const label = document.getElementById('reply-file-label');
        label.textContent = input.files[0] ? input.files[0].name : 'Ketuk untuk lampirkan file';
    }

    const formReply = document.getElementById('formReply');
    if (formReply) {
        formReply.addEventListener('submit', function(e) {
            e.preventDefault();

            const pesan = document.getElementById('pesanInput').value.trim();
            if (!pesan) {
                Swal.fire({ title: 'Oops!', text: 'Balasan tidak boleh kosong!', icon: 'warning' });
                return;
            }

            const btn      = document.getElementById('btnReply');
            btn.disabled   = true;
            btn.innerHTML  = '<ion-icon name="sync-outline" class="animate-spin"></ion-icon><span>Mengirim...</span>';

            const formData = new FormData(formReply);
            formData.append('_token', CSRF_TOKEN);

            fetch(RESPOND_URL, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('timeline-container');
                    const emptyMsg  = document.getElementById('empty-msg');
                    if (emptyMsg) emptyMsg.remove();

                    container.insertAdjacentHTML('beforeend', buildTimelineItem(data.response, true));
                    lastResponseId = Math.max(lastResponseId, data.response.id);

                    // Reset form
                    document.getElementById('pesanInput').value = '';
                    document.getElementById('reply-file-label').textContent = 'Ketuk untuk lampirkan file';
                    const fileInput = document.getElementById('lampiranReply');
                    if (fileInput) fileInput.value = '';

                    // Scroll ke timeline bawah
                    container.scrollTop = container.scrollHeight;
                    container.parentElement.scrollIntoView({ behavior: 'smooth', block: 'end' });
                } else {
                    Swal.fire({ title: 'Gagal', text: 'Gagal mengirim balasan.', icon: 'error' });
                }
            })
            .catch(() => {
                Swal.fire({ title: 'Error', text: 'Koneksi bermasalah. Coba lagi.', icon: 'error' });
            })
            .finally(() => {
                btn.disabled  = false;
                btn.innerHTML = '<ion-icon name="paper-plane-outline"></ion-icon><span>Kirim Balasan</span>';
            });
        });
    }

    // ── Init ────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        startPolling();
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.5; transform: scale(0.7); }
    }
</style>
@endpush
