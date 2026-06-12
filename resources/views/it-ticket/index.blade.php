@extends('layouts.app')
@section('titlepage', 'IT Ticket')

@section('content')
@section('navigasi')
    <span>IT Ticket</span>
@endsection

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-alert-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="ti ti-ticket fs-4"></i></span></div>
                <div><p class="mb-0 small text-muted">Total Tiket</p><h4 class="mb-0">{{ $summary['total'] }}</h4></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar"><span class="avatar-initial rounded bg-label-info"><i class="ti ti-loader fs-4"></i></span></div>
                <div><p class="mb-0 small text-muted">Open / In Progress</p><h4 class="mb-0">{{ $summary['open'] }}</h4></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="ti ti-circle-check fs-4"></i></span></div>
                <div><p class="mb-0 small text-muted">Resolved</p><h4 class="mb-0">{{ $summary['resolved'] }}</h4></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="ti ti-alert-triangle fs-4"></i></span></div>
                <div><p class="mb-0 small text-muted">Overdue</p><h4 class="mb-0">{{ $summary['overdue'] }}</h4></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="mb-0">
                <i class="ti ti-headset me-2"></i>Daftar IT Ticket
                <span class="badge bg-success ms-2" id="realtimeIndicator" style="font-size:10px;">
                    <i class="ti ti-wifi"></i> Real-time Active
                </span>
            </h5>
            <small class="text-muted">Pengaduan layanan IT — standar ISO 27001</small>
        </div>
        <a href="{{ route('it-ticket.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i> Buat Tiket
        </a>
    </div>
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('it-ticket.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nomor / judul..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="open" {{ request('status')=='open'?'selected':'' }}>Open</option>
                    <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="resolved" {{ request('status')=='resolved'?'selected':'' }}>Resolved</option>
                    <option value="closed" {{ request('status')=='closed'?'selected':'' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="prioritas" class="form-select form-select-sm">
                    <option value="">Semua Prioritas</option>
                    <option value="critical" {{ request('prioritas')=='critical'?'selected':'' }}>Critical</option>
                    <option value="high" {{ request('prioritas')=='high'?'selected':'' }}>High</option>
                    <option value="medium" {{ request('prioritas')=='medium'?'selected':'' }}>Medium</option>
                    <option value="low" {{ request('prioritas')=='low'?'selected':'' }}>Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <option value="hardware" {{ request('kategori')=='hardware'?'selected':'' }}>Hardware</option>
                    <option value="software" {{ request('kategori')=='software'?'selected':'' }}>Software</option>
                    <option value="jaringan" {{ request('kategori')=='jaringan'?'selected':'' }}>Jaringan</option>
                    <option value="keamanan" {{ request('kategori')=='keamanan'?'selected':'' }}>Keamanan</option>
                    <option value="akses" {{ request('kategori')=='akses'?'selected':'' }}>Akses</option>
                    <option value="data" {{ request('kategori')=='data'?'selected':'' }}>Data</option>
                    <option value="lainnya" {{ request('kategori')=='lainnya'?'selected':'' }}>Lainnya</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-search"></i></button>
                <a href="{{ route('it-ticket.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-x"></i></a>
            </div>
        </form>
    </div>

    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('it staff'))
    <form id="formBulkUpdate" method="POST" action="{{ route('it-ticket.bulk-update') }}">
        @csrf
        <div class="bg-light p-2 border-bottom d-none align-items-center gap-2" id="bulkActionPanel">
            <span class="fw-semibold ms-2"><span id="selectedCount">0</span> tiket terpilih:</span>
            
            <select name="prioritas" class="form-select form-select-sm w-auto">
                <option value="">-- Ubah Prioritas --</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
            
            <select name="klasifikasi_data" class="form-select form-select-sm w-auto">
                <option value="">-- Ubah Klasifikasi --</option>
                <option value="public">Public</option>
                <option value="internal">Internal</option>
                <option value="confidential">Confidential</option>
            </select>
            
            <button type="submit" class="btn btn-warning btn-sm" id="btnApplyBulk">
                <i class="ti ti-check me-1"></i>Terapkan
            </button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('it staff'))
                        <th class="text-center" style="width: 40px;">
                            <input class="form-check-input" type="checkbox" id="checkAllTickets">
                        </th>
                    @endif
                    <th>No. Tiket</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Prioritas</th>
                    <th>Klasifikasi</th>
                    <th>Cabang</th>
                    <th>Pemohon</th>
                    <th>Departemen</th>
                    <th>Assigned To</th>
                    <th>SLA Target</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $t)
                    <tr class="{{ $t->isOverdue() ? 'table-danger' : '' }}" style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button') && !event.target.closest('form') && !event.target.closest('input')) { window.location.href='{{ route('it-ticket.show', $t->id) }}'; }">
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('it staff'))
                            <td class="text-center">
                                <input class="form-check-input ticket-checkbox" type="checkbox" name="ticket_ids[]" value="{{ $t->id }}">
                            </td>
                        @endif
                        <td>
                            <code class="text-primary">{{ $t->nomor_tiket }}</code>
                            @if($t->isOverdue())
                                <br><span class="badge bg-danger" style="font-size:10px;"><i class="ti ti-alert-triangle me-1"></i>Overdue</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ Str::limit($t->judul, 50) }}</span>
                            <br><small class="text-muted">{{ $t->kategori }}</small>
                        </td>
                        <td><span class="badge bg-label-secondary text-capitalize">{{ $t->kategori }}</span></td>
                        <td>{!! $t->priority_badge !!}</td>
                        <td>{!! $t->klasifikasi_badge !!}</td>
                        <td><small>{{ optional($t->cabang)->nama_cabang ?? '-' }}</small></td>
                        <td>
                            <small>{{ $t->pemohon->name ?? '-' }}</small>
                        </td>
                        <td>
                            @php
                                $karyawan = \App\Models\Karyawan::where('nik', $t->pemohon->username ?? null)->first();
                                $dept = $karyawan ? optional($karyawan->departemen)->nama_dept : null;
                                $subDept = $karyawan ? $karyawan->sub_departemen : null;
                            @endphp
                            @if($dept)
                                <small class="d-block fw-semibold">{{ $dept }}</small>
                                @if($subDept)
                                    <small class="text-muted" style="font-size:10px;">{{ $subDept }}</small>
                                @endif
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            @if($t->assignedTo)
                                <span class="badge bg-label-info">{{ $t->assignedTo->name }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @if($t->tanggal_target)
                                <small class="{{ $t->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ $t->tanggal_target->format('d/m/Y') }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{!! $t->status_badge !!}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('it-ticket.show', $t->id) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="ti ti-eye"></i></a>
                                @if(auth()->user()->isSuperAdmin())
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $t->id }}" data-nomor="{{ $t->nomor_tiket }}" title="Hapus"><i class="ti ti-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4 text-muted">
                            <i class="ti ti-ticket-off fs-2 d-block mb-2"></i>Belum ada tiket.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('it staff'))
    </form>
    @endif

    @if($tickets->hasPages())
        <div class="card-footer">{{ $tickets->links() }}</div>
    @endif
</div>

<form id="formDelete" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endsection

@push('myscript')
<script>
$(function () {
    // Ambil lastTicketId dari localStorage atau dari database
    const currentMaxId = {{ $tickets->first()->id ?? 0 }};
    const storedLastId = localStorage.getItem('lastTicketId');
    let lastTicketId = storedLastId ? Math.max(parseInt(storedLastId), currentMaxId) : currentMaxId;
    
    // Update localStorage
    localStorage.setItem('lastTicketId', lastTicketId);
    
    let pollingInterval = null;

    // Delete handler
    $('.btn-delete').on('click', function () {
        const id    = $(this).data('id');
        const nomor = $(this).data('nomor');
        Swal.fire({
            icon: 'warning', title: 'Hapus Tiket?',
            text: `Tiket "${nomor}" akan dihapus permanen.`,
            showCancelButton: true, confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                $('#formDelete').attr('action', `{{ url('it-ticket') }}/${id}`).submit();
            }
        });
    });

    // Bulk Action Logic
    const $checkAll = $('#checkAllTickets');
    const $checkboxes = $('.ticket-checkbox');
    const $bulkPanel = $('#bulkActionPanel');
    const $selectedCount = $('#selectedCount');

    function updateBulkPanel() {
        const checkedCount = $('.ticket-checkbox:checked').length;
        $selectedCount.text(checkedCount);
        
        if (checkedCount > 0) {
            $bulkPanel.removeClass('d-none').addClass('d-flex');
        } else {
            $bulkPanel.addClass('d-none').removeClass('d-flex');
        }
    }

    $checkAll.on('change', function() {
        $checkboxes.prop('checked', $(this).prop('checked'));
        updateBulkPanel();
    });

    $checkboxes.on('change', function() {
        if (!$(this).prop('checked')) {
            $checkAll.prop('checked', false);
        } else if ($('.ticket-checkbox:checked').length === $checkboxes.length) {
            $checkAll.prop('checked', true);
        }
        updateBulkPanel();
    });

    $('#formBulkUpdate').on('submit', function(e) {
        const prio = $('select[name="prioritas"]').val();
        const klas = $('select[name="klasifikasi_data"]').val();
        if (!prio && !klas) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Pilih aksi', text: 'Pilih prioritas atau klasifikasi data yang ingin diubah.' });
        }
    });

    // Real-time polling untuk tiket baru
    function checkNewTickets() {
        // Blink indicator
        $('#realtimeIndicator').addClass('opacity-50');
        
        $.ajax({
            url: '{{ route("it-ticket.check-new") }}',
            method: 'GET',
            data: { last_id: lastTicketId },
            success: function(response) {
                // Reset indicator
                $('#realtimeIndicator').removeClass('opacity-50');
                
                if (response.has_new && response.tickets.length > 0) {
                    // Update lastTicketId and save to localStorage
                    lastTicketId = response.tickets[0].id;
                    localStorage.setItem('lastTicketId', lastTicketId);
                    
                    // Tampilkan notifikasi
                    const count = response.tickets.length;
                    const ticketWord = count > 1 ? 'tiket baru' : 'tiket baru';
                    
                    Swal.fire({
                        icon: 'info',
                        title: `${count} ${ticketWord}!`,
                        text: `Ada ${count} tiket baru. Halaman akan di-refresh.`,
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                    });

                    // Refresh halaman setelah 3 detik
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                }
            },
            error: function(xhr) {
                console.error('Error checking new tickets:', xhr);
                
                // Show error indicator
                $('#realtimeIndicator').removeClass('bg-success').addClass('bg-danger')
                    .html('<i class="ti ti-wifi-off"></i> Connection Lost');
                
                // Try to reconnect after 30 seconds
                setTimeout(function() {
                    $('#realtimeIndicator').removeClass('bg-danger').addClass('bg-success')
                        .html('<i class="ti ti-wifi"></i> Real-time Active');
                }, 30000);
            }
        });
    }

    // Mulai polling setiap 15 detik
    pollingInterval = setInterval(checkNewTickets, 15000);

    // Stop polling ketika user leave page
    $(window).on('beforeunload', function() {
        if (pollingInterval) clearInterval(pollingInterval);
    });
});
</script>
@endpush
