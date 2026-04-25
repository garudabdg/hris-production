@extends('layouts.app')
@section('titlepage', 'Notifikasi')

@section('content')
@section('navigasi')
    <span>Notifikasi</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pusat Notifikasi</h5>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notification.mark-all-as-read') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-check-all me-1"></i>Tandai Semua Sudah Dibaca
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body">
                @if($notifications->count() == 0)
                    <div class="text-center p-5">
                        <i class="ti ti-bell-x text-muted" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                        <p class="text-muted">Tidak ada notifikasi</p>
                    </div>
                @else
                    <div class="notification-list">
                        @foreach($notifications as $notif)
                            <div class="notification-item border-bottom pb-3 mb-3 @if(!$notif['is_read']) bg-light p-3 rounded @endif" data-id="{{ $notif['id'] }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            @if($notif['status'] == 1)
                                                <span class="badge bg-success me-2">✅ Disetujui</span>
                                            @elseif($notif['status'] == 2)
                                                <span class="badge bg-danger me-2">❌ Ditolak</span>
                                            @else
                                                <span class="badge bg-warning me-2">⏳ Pending</span>
                                            @endif
                                            @if(!$notif['is_read'])
                                                <span class="badge bg-primary">Baru</span>
                                            @endif
                                        </div>
                                        
                                        <h6 class="mt-2 mb-1">
                                            @if($notif['approval_type'] == 'IZIN_SAKIT')
                                                <i class="ti ti-medical-cross text-danger me-2"></i>
                                            @elseif($notif['approval_type'] == 'IZIN_ABSEN')
                                                <i class="ti ti-calendar-x text-warning me-2"></i>
                                            @elseif($notif['approval_type'] == 'IZIN_CUTI')
                                                <i class="ti ti-calendar text-info me-2"></i>
                                            @elseif($notif['approval_type'] == 'IZIN_DINAS')
                                                <i class="ti ti-briefcase text-success me-2"></i>
                                            @endif
                                            {{ $notif['title'] }}
                                        </h6>
                                        
                                        <p class="mb-2 text-muted">
                                            {{ $notif['message'] }}
                                        </p>
                                        
                                        <div class="small text-muted">
                                            <i class="ti ti-user me-1"></i>Dari: {{ $notif['approver_name'] }} <br>
                                            <i class="ti ti-clock me-1"></i>{{ $notif['created_at']->diffForHumans() }}
                                        </div>

                                        @if($notif['notes'])
                                            <div class="alert alert-info mt-2 mb-0 py-2">
                                                <small><strong>Catatan:</strong> {{ $notif['notes'] }}</small>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ms-3">
                                        @if(!$notif['is_read'])
                                            <form method="POST" action="{{ route('notification.mark-as-read', $notif['id']) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link text-muted" title="Tandai sudah dibaca">
                                                    <i class="ti ti-circle-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('notification.delete', $notif['id']) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger" title="Hapus" onclick="return confirm('Hapus notifikasi ini?')">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
    // Mark as read when user clicks on the notification
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('form')) return; // Don't trigger if clicking on form buttons
            
            const notifId = this.dataset.id;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/notification/${notifId}/mark-as-read`;
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
            form.style.display = 'none';
            document.body.appendChild(form);
            // Don't auto-submit, let user manually mark as read
        });
    });
</script>
@endpush
