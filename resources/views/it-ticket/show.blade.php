@extends('layouts.app')
@section('titlepage', 'Detail Tiket — ' . $itTicket->nomor_tiket)

@section('content')
@section('navigasi')
    <a href="{{ route('it-ticket.index') }}">Ticket Pengaduan Layanan</a>
    <span class="text-muted"> / {{ $itTicket->nomor_tiket }}</span>
@endsection

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-alert-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">
    <!-- Kolom Kiri: Detail Tiket -->
    <div class="col-lg-8">

        <!-- Header Card -->
        <div class="card ticket-header-card mb-4 border-0 shadow-sm">
            <div class="d-flex align-items-md-center justify-content-between flex-column flex-md-row gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-container">
                        @if(strtolower($itTicket->kategori) == 'hardware')
                            <i class="ti ti-device-desktop"></i>
                        @elseif(strtolower($itTicket->kategori) == 'jaringan')
                            <i class="ti ti-wifi"></i>
                        @else
                            <i class="ti ti-printer"></i>
                        @endif
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">{{ $itTicket->judul }}</h4>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-muted" style="font-size: 13px;">#{{ $itTicket->nomor_tiket }}</span>
                            <a href="javascript:void(0)" onclick="navigator.clipboard.writeText('{{ $itTicket->nomor_tiket }}'); toastr.success('Tersalin!');" class="text-muted hover-primary">
                                <i class="ti ti-copy"></i>
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            {!! $itTicket->status_badge !!}
                            {!! $itTicket->priority_badge !!}
                            {!! $itTicket->klasifikasi_badge !!}
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-4">
                    <div class="text-center">
                        <div class="text-muted mb-1"><i class="ti ti-clock me-1"></i>Dibuat</div>
                        <div class="fw-bold" style="font-size: 13px;">{{ $itTicket->created_at->format('d M Y') }}</div>
                        <div class="text-muted" style="font-size: 12px;">{{ $itTicket->created_at->format('H:i') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted mb-1"><i class="ti ti-check me-1"></i>Resolved</div>
                        <div class="fw-bold" style="font-size: 13px;">{{ $itTicket->resolved_at ? $itTicket->resolved_at->format('d M Y') : '-' }}</div>
                        <div class="text-muted" style="font-size: 12px;">{{ $itTicket->resolved_at ? $itTicket->resolved_at->format('H:i') : '' }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted mb-1"><i class="ti ti-target me-1"></i>SLA Target</div>
                        <div class="fw-bold" style="font-size: 13px;">{{ $itTicket->tanggal_target ? $itTicket->tanggal_target->format('d M Y') : '-' }}</div>
                        @if($itTicket->tanggal_target)
                            <div class="badge {{ $itTicket->isOverdue() ? 'bg-label-danger' : 'bg-label-success' }} rounded-pill" style="font-size: 10px;">
                                {{ $itTicket->isOverdue() ? 'Overdue' : 'On Track' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Ticket -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white pb-0">
                <h6 class="fw-bold text-dark mb-0"><i class="ti ti-info-circle text-primary me-2"></i>Informasi Ticket</h6>
            </div>
            <div class="card-body pt-4">
                <div class="info-grid">
                    <div class="info-item">
                        <i class="ti ti-category info-icon"></i>
                        <div>
                            <span class="info-label">Kategori</span>
                            <span class="info-value text-capitalize">{{ $itTicket->kategori }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-alert-triangle info-icon"></i>
                        <div>
                            <span class="info-label">Dampak</span>
                            <span class="info-value text-capitalize">{{ $itTicket->dampak }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-arrow-bar-to-right info-icon"></i>
                        <div>
                            <span class="info-label">Prioritas</span>
                            <span class="info-value text-capitalize">{{ $itTicket->prioritas }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-map-pin info-icon"></i>
                        <div>
                            <span class="info-label">Lokasi</span>
                            <span class="info-value">{{ $itTicket->lokasi ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-user info-icon"></i>
                        <div>
                            <span class="info-label">Pemohon</span>
                            <span class="info-value">{{ $itTicket->pemohon->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-building info-icon"></i>
                        <div>
                            <span class="info-label">Departemen</span>
                            @php
                                $nik = optional($itTicket->pemohon)->userkaryawan ? $itTicket->pemohon->userkaryawan->nik : null;
                                $karyawan = \App\Models\Karyawan::where('nik', $nik)->first();
                                $dept = $karyawan ? optional($karyawan->departemen)->nama_dept : null;
                            @endphp
                            <span class="info-value">{{ $dept ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-users info-icon"></i>
                        <div>
                            <span class="info-label">Assigned To</span>
                            <span class="info-value">{{ $itTicket->assignedTo->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-clock info-icon"></i>
                        <div>
                            <span class="info-label">Resolved At</span>
                            <span class="info-value text-success">{{ $itTicket->resolved_at ? $itTicket->resolved_at->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="ti ti-home info-icon"></i>
                        <div>
                            <span class="info-label">Cabang</span>
                            <span class="info-value">{{ optional($itTicket->cabang)->nama_cabang ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deskripsi Masalah -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white pb-0">
                <h6 class="fw-bold text-dark mb-0"><i class="ti ti-file-description text-primary me-2"></i>Deskripsi Masalah</h6>
            </div>
            <div class="card-body pt-3">
                <div class="p-3 bg-light rounded text-dark" style="white-space:pre-wrap; font-size:14px;">{{ $itTicket->deskripsi }}</div>
                @if($itTicket->lampiran)
                    <div class="mt-3">
                        <a href="{{ asset('storage/it-tickets/' . $itTicket->lampiran) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="ti ti-paperclip me-1"></i> Lihat Lampiran
                        </a>
                    </div>
                @endif

                @if($itTicket->catatan_resolusi)
                    <div class="mt-4 p-3 bg-success-subtle rounded border border-success-subtle text-dark">
                        <h6 class="fw-bold text-success mb-2"><i class="ti ti-circle-check me-2"></i>Catatan Resolusi</h6>
                        <div style="white-space:pre-wrap; font-size:14px;">{{ $itTicket->catatan_resolusi }}</div>
                        @if($itTicket->resolvedBy)
                            <div class="text-muted mt-2" style="font-size:12px;">
                                <i class="ti ti-user me-1"></i> Diselesaikan oleh: <span class="fw-bold">{{ $itTicket->resolvedBy->name }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Aktivitas & Riwayat -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0"><i class="ti ti-history text-primary me-2"></i>Aktivitas & Riwayat</h6>
                <span id="live-indicator" class="d-flex align-items-center gap-1 text-success" style="font-size:11px;font-weight:600;">
                    <span id="live-dot" style="width:8px;height:8px;border-radius:50%;background:#28a745;display:inline-block;animation:pulse-dot 1.5s infinite;"></span>
                    Live
                </span>
            </div>
            <div class="card-body pt-4">
                <div class="timeline-container" id="timeline-container">
                    <!-- Ticket Created -->
                    <div class="timeline-item">
                        <div class="timeline-dot bg-primary text-white border-primary" style="font-size:10px;"><i class="ti ti-plus"></i></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-bold text-dark" style="font-size:14px;">Ticket Created</div>
                                <div class="text-muted" style="font-size:13px;">Tiket dibuat oleh {{ $itTicket->pemohon->name ?? 'Sistem' }}</div>
                            </div>
                            <div class="text-muted" style="font-size:12px;">{{ $itTicket->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    <!-- Assignment if assigned -->
                    @if($itTicket->assignedTo)
                        <div class="timeline-item">
                            <div class="timeline-dot bg-success text-white border-success" style="font-size:10px;"><i class="ti ti-user-check"></i></div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:14px;">Assigned</div>
                                    <div class="text-muted" style="font-size:13px;">Tiket di-assign ke {{ $itTicket->assignedTo->name }}</div>
                                </div>
                                <div class="text-muted" style="font-size:12px;">-</div> <!-- Idealnya ada log tanggal assign -->
                            </div>
                        </div>
                    @endif

                    <!-- Dynamic Responses -->
                    @forelse($itTicket->responses as $resp)
                        <div class="timeline-item new-item" data-id="{{ $resp->id }}">
                            <div class="timeline-dot bg-info text-white border-info" style="font-size:10px;">
                                {{ strtoupper(substr($resp->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:14px;">
                                        {{ $resp->user->name ?? '-' }} 
                                        <span class="ms-1">{!! $resp->tipe_badge !!}</span>
                                    </div>
                                    <div class="text-dark mt-1" style="font-size:13px; white-space:pre-wrap;">{{ $resp->pesan }}</div>
                                    @if($resp->lampiran)
                                        <a href="{{ asset('storage/it-tickets/' . $resp->lampiran) }}" target="_blank" class="btn btn-outline-secondary btn-sm mt-2 rounded-pill" style="font-size:11px;">
                                            <i class="ti ti-paperclip me-1"></i> Lampiran
                                        </a>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:12px;">{{ $resp->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <div id="empty-msg" class="text-muted" style="font-size:13px;">Belum ada aktivitas balasan.</div>
                    @endforelse
                    
                    @if($itTicket->status == 'closed')
                        <!-- Ticket Closed -->
                        <div class="timeline-item">
                            <div class="timeline-dot bg-success text-white border-success" style="font-size:10px;"><i class="ti ti-check"></i></div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:14px;">Closed</div>
                                    <div class="text-muted" style="font-size:13px;">Tiket telah ditutup</div>
                                </div>
                                <div class="text-muted" style="font-size:12px;">{{ $itTicket->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Form Balas -->
        <div class="card mb-4 border-0 shadow-sm" id="reply-form-wrapper">
            <div class="card-header bg-white pb-0">
                <h6 class="fw-bold text-dark mb-0"><i class="ti ti-message-circle text-primary me-2"></i>Balas Ticket</h6>
            </div>
            <div class="card-body pt-3">
                <form id="formReply" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <textarea name="pesan" id="pesanInput" rows="3" class="form-control bg-light border-0"
                            placeholder="Tulis balasan Anda..." style="border-radius: 8px;"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label for="lampiranInput" class="btn btn-light rounded-pill px-3" style="cursor: pointer; font-size: 13px; font-weight:600;">
                                <i class="ti ti-paperclip me-1"></i> Lampiran
                            </label>
                            <input type="file" name="lampiran" id="lampiranInput" class="d-none" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.zip">
                            <span class="text-muted ms-2" style="font-size:12px;">Maks. 5MB</span>
                            <div id="file-name-display" class="mt-1 text-primary" style="font-size:12px;"></div>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnReply">
                            <i class="ti ti-send me-1"></i> Kirim Balasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Aksi & Info -->
    <div class="col-lg-4">
        
        @if($canManage)
        <!-- Quick Actions -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white pb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="ti ti-bolt text-warning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body pt-2">
                <div class="quick-action-list">
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalUpdateStatus" class="qa-blue">
                        <div class="d-flex align-items-center">
                            <div class="icon"><i class="ti ti-edit"></i></div>
                            <div>
                                <div class="fw-bold" style="font-size:14px;">Update Status</div>
                                <div class="text-muted" style="font-size:12px; font-weight:normal;">Ubah status, prioritas, atau klasifikasi</div>
                            </div>
                        </div>
                        <i class="ti ti-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="javascript:void(0)" onclick="$('#select-assign').select2('open');" class="qa-green">
                        <div class="d-flex align-items-center">
                            <div class="icon"><i class="ti ti-user-plus"></i></div>
                            <div>
                                <div class="fw-bold" style="font-size:14px;">Assign Staff</div>
                                <div class="text-muted" style="font-size:12px; font-weight:normal;">Tugaskan ke staf atau tim lain</div>
                            </div>
                        </div>
                        <i class="ti ti-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="javascript:void(0)" onclick="$('#pesanInput').focus();" class="qa-purple">
                        <div class="d-flex align-items-center">
                            <div class="icon"><i class="ti ti-message-plus"></i></div>
                            <div>
                                <div class="fw-bold" style="font-size:14px;">Tambah Balasan</div>
                                <div class="text-muted" style="font-size:12px; font-weight:normal;">Kirim balasan ke pemohon</div>
                            </div>
                        </div>
                        <i class="ti ti-chevron-right text-muted"></i>
                    </a>
                    
                    @if($itTicket->status != 'closed')
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalCloseTicket" class="qa-red">
                        <div class="d-flex align-items-center">
                            <div class="icon"><i class="ti ti-lock"></i></div>
                            <div>
                                <div class="fw-bold" style="font-size:14px;">Close Ticket</div>
                                <div class="text-muted" style="font-size:12px; font-weight:normal; color:#64748b;">Tutup tiket ini</div>
                            </div>
                        </div>
                        <i class="ti ti-chevron-right text-muted"></i>
                    </a>
                    @endif
                    
                    @can('threat-intelligence.create')
                    <a href="{{ route('threat-intelligence-reports.create', ['judul' => $itTicket->judul, 'deskripsi' => $itTicket->deskripsi]) }}" class="qa-blue" style="border-color: #c7d2fe;">
                        <div class="d-flex align-items-center">
                            <div class="icon" style="background: #eef2ff; color: #4f46e5;"><i class="ti ti-shield-check"></i></div>
                            <div>
                                <div class="fw-bold" style="font-size:14px; color: #1e293b;">Eskalasi ke Threat</div>
                                <div class="text-muted" style="font-size:12px; font-weight:normal;">Buat Threat Intelligence Report</div>
                            </div>
                        </div>
                        <i class="ti ti-chevron-right text-muted"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Assigned To -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white pb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="ti ti-user-check text-success me-2"></i>Assigned To</h6>
            </div>
            <div class="card-body pt-2">
                <form action="{{ route('it-ticket.assign', $itTicket->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="d-flex align-items-center bg-light rounded p-2 mb-3 border">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ strtoupper(substr($itTicket->assignedTo->name ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <select name="assigned_to" id="select-assign" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark shadow-none p-0 px-2" style="cursor:pointer;">
                                <option value="">-- Pilih Staff --</option>
                                @foreach($itStaffs as $staff)
                                    <option value="{{ $staff->id }}" {{ $itTicket->assigned_to == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">Ubah Assignment</button>
                </form>
            </div>
        </div>
        @endif

        <!-- ISO 27001 Info -->
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-header bg-white pb-2 border-bottom-0">
                <h6 class="fw-bold text-dark mb-0"><i class="ti ti-shield-check text-primary me-2"></i>ISO 27001 Information</h6>
            </div>
            <div class="card-body pt-2">
                <div class="iso-info-row">
                    <div class="iso-label"><i class="ti ti-box-padding me-1"></i>Klasifikasi Data</div>
                    <div class="iso-value">{!! $itTicket->klasifikasi_badge !!}</div>
                </div>
                <div class="iso-info-row">
                    <div class="iso-label"><i class="ti ti-alert-triangle me-1"></i>Dampak</div>
                    <div class="iso-value text-capitalize">{{ $itTicket->dampak }}</div>
                </div>
                <div class="iso-info-row">
                    <div class="iso-label"><i class="ti ti-clock me-1"></i>SLA Target</div>
                    <div class="iso-value text-primary">{{ $itTicket->tanggal_target ? $itTicket->tanggal_target->format('d M Y') : '-' }}</div>
                </div>
                <div class="iso-info-row mb-0">
                    <div class="iso-label"><i class="ti ti-barcode me-1"></i>Nomor Referensi</div>
                    <div class="iso-value text-danger">{{ $itTicket->nomor_tiket }}</div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modals -->
@if($canManage)
<!-- Update Status Modal -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Update Status Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('it-ticket.update-status', $itTicket->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="open"        {{ $itTicket->status=='open'        ?'selected':'' }}>Open</option>
                            <option value="in_progress" {{ $itTicket->status=='in_progress' ?'selected':'' }}>In Progress</option>
                            <option value="pending"     {{ $itTicket->status=='pending'     ?'selected':'' }}>Pending</option>
                            <option value="resolved"    {{ $itTicket->status=='resolved'    ?'selected':'' }}>Resolved</option>
                            <option value="closed"      {{ $itTicket->status=='closed'      ?'selected':'' }}>Closed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Prioritas</label>
                        <select name="prioritas" class="form-select">
                            <option value="low"      {{ $itTicket->prioritas=='low'      ?'selected':'' }}>Low</option>
                            <option value="medium"   {{ $itTicket->prioritas=='medium'   ?'selected':'' }}>Medium</option>
                            <option value="high"     {{ $itTicket->prioritas=='high'     ?'selected':'' }}>High</option>
                            <option value="critical" {{ $itTicket->prioritas=='critical' ?'selected':'' }}>Critical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Klasifikasi Data</label>
                        <select name="klasifikasi_data" class="form-select">
                            <option value="public"       {{ $itTicket->klasifikasi_data=='public'       ?'selected':'' }}>Public</option>
                            <option value="internal"     {{ $itTicket->klasifikasi_data=='internal'     ?'selected':'' }}>Internal</option>
                            <option value="confidential" {{ $itTicket->klasifikasi_data=='confidential' ?'selected':'' }}>Confidential</option>
                        </select>
                    </div>
                    <div class="mb-3" id="catatan_resolusi_wrapper" style="display: none;">
                        <label class="form-label small fw-semibold">Catatan Resolusi <span class="text-danger">*</span></label>
                        <textarea name="catatan_resolusi" id="catatan_resolusi" rows="3" class="form-control"
                            placeholder="Isi jika status resolved/closed...">{{ $itTicket->catatan_resolusi }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Ticket Modal -->
<div class="modal fade" id="modalCloseTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger">Tutup Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('it-ticket.update-status', $itTicket->id) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="closed">
                <input type="hidden" name="prioritas" value="{{ $itTicket->prioritas }}">
                <input type="hidden" name="klasifikasi_data" value="{{ $itTicket->klasifikasi_data }}">
                
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menutup tiket ini secara permanen?</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan Resolusi Terakhir <span class="text-danger">*</span></label>
                        <textarea name="catatan_resolusi" rows="3" class="form-control" placeholder="Kesimpulan akhir (Wajib diisi)..." required>{{ $itTicket->catatan_resolusi }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tutup Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('mystyle')
<style>
    body {
        background-color: #f4f7fe;
    }
    .ticket-header-card {
        border-radius: 12px;
        background-image: linear-gradient(to right, #ffffff, #fcfdff);
    }
    .icon-container {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background-color: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
        font-size: 28px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 20px;
    }
    @media (min-width: 576px) { .info-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 992px) { .info-grid { grid-template-columns: repeat(3, 1fr); } }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .info-icon {
        color: #64748b;
        font-size: 20px;
        margin-top: 2px;
    }
    .info-label {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 2px;
        display: block;
    }
    .info-value {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }
    .timeline-container {
        position: relative;
        padding-left: 20px;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 8px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-dot {
        position: absolute;
        left: -20px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        margin-top: 3px;
    }
    
    .quick-action-list a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 8px;
        color: #334155;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }
    .quick-action-list a:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .quick-action-list a .icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 16px;
    }
    .qa-blue .icon { background: #eff6ff; color: #3b82f6; }
    .qa-green .icon { background: #ecfdf5; color: #10b981; }
    .qa-purple .icon { background: #faf5ff; color: #a855f7; }
    .qa-red .icon { background: #fef2f2; color: #ef4444; }
    .qa-red { color: #ef4444 !important; }
    .qa-red:hover { background: #fef2f2 !important; border-color: #fca5a5 !important; }

    .iso-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 13px;
    }
    .iso-label { color: #64748b; }
    .iso-value { font-weight: 600; color: #1e293b; text-align: right; }
    
    .hover-primary:hover { color: #3b82f6 !important; }
    
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
@endpush

@push('myscript')
<script>
$(function () {
    // Custom select2 styling if needed, but keeping it standard for now to match backend framework
    $('#select-assign').select2({ width: '100%', dropdownParent: $('#select-assign').parent() });

    // File input name display
    $('#lampiranInput').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if(fileName) {
            $('#file-name-display').html('<i class="ti ti-file me-1"></i>' + fileName);
        } else {
            $('#file-name-display').empty();
        }
    });

    // Dynamic resolution form
    function toggleResolutionForm() {
        const status = $('select[name="status"]').val();
        if (status === 'resolved' || status === 'closed') {
            $('#catatan_resolusi_wrapper').slideDown();
            $('#catatan_resolusi').prop('required', true);
        } else {
            $('#catatan_resolusi_wrapper').slideUp();
            $('#catatan_resolusi').prop('required', false);
        }
    }
    
    $('select[name="status"]').on('change', toggleResolutionForm);
    toggleResolutionForm();

    const RESPOND_URL  = "{{ route('it-ticket.respond', $itTicket->id) }}";
    const POLL_URL     = "{{ route('it-ticket.responses', $itTicket->id) }}";
    const TICKET_ID    = {{ $itTicket->id }};
    const CSRF_TOKEN   = "{{ csrf_token() }}";

    let lastResponseId = {{ $itTicket->responses->max('id') ?? 0 }};
    let isPolling      = false;
    let pollInterval   = null;

    // ── Build HTML item ────────────────────────────────────────────────────
    function buildItem(r) {
        const lampiran = r.lampiran
            ? `<a href="${r.lampiran}" target="_blank" class="btn btn-outline-secondary btn-sm mt-2 rounded-pill" style="font-size:11px;">
                 <i class="ti ti-paperclip me-1"></i> Lampiran
               </a>`
            : '';

        return `<div class="timeline-item new-item" data-id="${r.id}">
            <div class="timeline-dot bg-info text-white border-info" style="font-size:10px;">${r.user_initial}</div>
            <div class="d-flex justify-content-between">
                <div>
                    <div class="fw-bold text-dark" style="font-size:14px;">
                        ${escapeHtml(r.user_name)} 
                        <span class="ms-1 badge bg-label-secondary" style="font-size:10px;">Balasan</span>
                    </div>
                    <div class="text-dark mt-1" style="font-size:13px; white-space:pre-wrap;">${escapeHtml(r.pesan)}</div>
                    ${lampiran}
                </div>
                <div class="text-muted" style="font-size:12px;">${r.created_at}</div>
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
                        // Append before the Closed item if it exists, or at the end
                        const closedItem = $container.find('.timeline-item:last-child').has('i.ti-check');
                        if (closedItem.length > 0) {
                            $(buildItem(r)).insertBefore(closedItem.parent());
                        } else {
                            $container.append(buildItem(r));
                        }
                        lastResponseId = Math.max(lastResponseId, r.id);
                    });
                }
                $('#live-dot').css('background', '#28a745');
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

    // ── AJAX Submit Form ───────────────────────────────────────────────────
    $('#formReply').on('submit', function(e) {
        e.preventDefault();

        const pesan = $('#pesanInput').val().trim();
        if (!pesan) {
            Swal.fire({ title: 'Oops!', text: 'Balasan tidak boleh kosong!', icon: 'warning' });
            return;
        }

        const $btn = $('#btnReply');
        const originalText = $btn.html();
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
                    
                    // Insert carefully before 'closed' state if present
                    const closedItem = $container.children().last();
                    if (closedItem.text().includes('Tiket telah ditutup')) {
                        closedItem.before(buildItem(data.response));
                    } else {
                        $container.append(buildItem(data.response));
                    }
                    
                    lastResponseId = Math.max(lastResponseId, data.response.id);

                    // Reset form
                    $('#pesanInput').val('');
                    $('#lampiranInput').val('');
                    $('#file-name-display').empty();
                } else {
                    Swal.fire({ title: 'Gagal', text: 'Gagal mengirim balasan.', icon: 'error' });
                }
            },
            error: function() {
                Swal.fire({ title: 'Error', text: 'Koneksi bermasalah. Coba lagi.', icon: 'error' });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    startPolling();
});
</script>
@endpush
