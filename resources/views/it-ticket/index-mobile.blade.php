@extends('layouts.mobile.modern')
@section('title', 'Ticket Pengaduan Layanan')

@section('header_left')
    <a href="{{ route('dashboard.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@section('header_right')
    @can('it-ticket.create')
    <a href="{{ route('it-ticket.create') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="add-outline" class="text-xl"></ion-icon>
    </a>
    @endcan
@endsection

@push('mystyle')
<style>
    body { background-color: #f8fafc; }

    .filter-section {
        background: #fff;
        border-bottom: 1px solid #e1e8f0;
        padding: 10px 12px;
        position: sticky;
        top: 60px;
        z-index: 40;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
    }
    .filter-chip.active {
        background: {{ $t['primary'] }};
        border-color: {{ $t['primary'] }};
        color: #fff;
    }

    .ticket-card {
        border: none;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        margin-bottom: 10px;
        transition: all 0.2s ease;
        overflow: hidden;
        display: block;
        position: relative;
    }
    .ticket-card:active { transform: scale(0.98); background: #f8fafc; }
    .ticket-card .priority-bar {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        border-radius: 14px 0 0 14px;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }

    /* Status colors */
    .s-open       { background: #dbeafe; color: #1d4ed8; }
    .s-in_progress{ background: #fef3c7; color: #d97706; }
    .s-pending    { background: #f3e8ff; color: #7c3aed; }
    .s-resolved   { background: #dcfce7; color: #16a34a; }
    .s-closed     { background: #f1f5f9; color: #64748b; }

    /* Priority dot colors */
    .p-critical { background: #dc3545; }
    .p-high     { background: #fd7e14; }
    .p-medium   { background: #0d6efd; }
    .p-low      { background: #6c757d; }

    #skeleton-container { display: block; }
    #real-content { display: none; }

    .skeleton {
        background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: 4px;
    }
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .fab-button.modern {
        position: fixed;
        bottom: 110px; right: 20px;
        z-index: 50;
        width: 56px; height: 56px;
        border-radius: 16px;
        background: {{ $t['primary'] }};
        color: white;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 10px 25px -5px {{ $t['primary'] }}40;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .fab-button.modern:active {
        transform: scale(0.92);
        background: {{ $t['primary_light'] ?? $t['primary'] }};
    }
    .fab-button.modern ion-icon { font-size: 28px; }
</style>
@endpush

@section('content')

    {{-- Summary Cards --}}
    <div class="px-3 pt-3 pb-1 grid grid-cols-4 gap-2">
        @php
            $cards = [
                ['label'=>'Total',    'val'=>$summary['total'],   'icon'=>'ticket-outline',        'bg'=>'#e0e7ff','ic'=>'#4f46e5'],
                ['label'=>'Aktif',    'val'=>$summary['open'],    'icon'=>'loader-outline',         'bg'=>'#fef3c7','ic'=>'#d97706'],
                ['label'=>'Resolved', 'val'=>$summary['resolved'],'icon'=>'checkmark-circle-outline','bg'=>'#dcfce7','ic'=>'#16a34a'],
                ['label'=>'Overdue',  'val'=>$summary['overdue'], 'icon'=>'alert-circle-outline',  'bg'=>'#fee2e2','ic'=>'#dc2626'],
            ];
        @endphp
        @foreach($cards as $c)
        <div class="rounded-xl p-2 text-center" style="background:{{ $c['bg'] }};">
            <ion-icon name="{{ $c['icon'] }}" style="font-size:22px;color:{{ $c['ic'] }};"></ion-icon>
            <div style="font-size:16px;font-weight:800;color:{{ $c['ic'] }};">{{ $c['val'] }}</div>
            <div style="font-size:9px;font-weight:600;color:{{ $c['ic'] }}88;">{{ $c['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Status Filter Chips --}}
    <div class="filter-section">
        <form method="GET" action="{{ route('it-ticket.index') }}" id="filterForm">
            <div class="flex gap-2 overflow-x-auto pb-1" style="-webkit-overflow-scrolling:touch;">
                <button type="submit" name="status" value="" class="filter-chip {{ !request('status') ? 'active' : '' }}">Semua</button>
                <button type="submit" name="status" value="open" class="filter-chip {{ request('status')=='open' ? 'active' : '' }}">Open</button>
                <button type="submit" name="status" value="in_progress" class="filter-chip {{ request('status')=='in_progress' ? 'active' : '' }}">In Progress</button>
                <button type="submit" name="status" value="pending" class="filter-chip {{ request('status')=='pending' ? 'active' : '' }}">Pending</button>
                <button type="submit" name="status" value="resolved" class="filter-chip {{ request('status')=='resolved' ? 'active' : '' }}">Resolved</button>
                <button type="submit" name="status" value="closed" class="filter-chip {{ request('status')=='closed' ? 'active' : '' }}">Closed</button>
            </div>
        </form>
    </div>

    {{-- Skeleton --}}
    <div id="skeleton-container" class="px-3 pt-2 pb-24">
        @for($i = 0; $i < 5; $i++)
        <div class="ticket-card">
            <div class="p-3 flex gap-3 items-start">
                <div class="skeleton" style="width:36px;height:36px;border-radius:10px;flex-shrink:0;"></div>
                <div class="flex-1">
                    <div class="skeleton h-3 w-3/4 mb-2"></div>
                    <div class="skeleton h-3 w-1/2 mb-2"></div>
                    <div class="skeleton h-3 w-2/3"></div>
                </div>
                <div class="skeleton" style="width:55px;height:20px;border-radius:20px;"></div>
            </div>
        </div>
        @endfor
    </div>

    {{-- Real Content --}}
    <div id="real-content" class="px-3 pt-2 pb-28">

        @if(session('success'))
        <div class="mb-2 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-xl text-xs font-medium flex items-center gap-2">
            <ion-icon name="checkmark-circle" class="text-base"></ion-icon> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-2 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-xl text-xs font-medium flex items-center gap-2">
            <ion-icon name="alert-circle" class="text-base"></ion-icon> {{ session('error') }}
        </div>
        @endif

        @forelse($tickets as $ticket)
        @php
            $prioColors = ['critical'=>'#dc3545','high'=>'#fd7e14','medium'=>'#0d6efd','low'=>'#6c757d'];
            $prioColor  = $prioColors[$ticket->prioritas] ?? '#6c757d';
            $prioBg     = ['critical'=>'#fee2e2','high'=>'#ffedd5','medium'=>'#dbeafe','low'=>'#f1f5f9'];
            $bg         = $prioBg[$ticket->prioritas] ?? '#f1f5f9';
        @endphp
        <a href="{{ route('it-ticket.show', $ticket->id) }}" class="ticket-card">
            <div class="priority-bar p-{{ $ticket->prioritas }}" style="background:{{ $prioColor }};"></div>
            <div class="pl-4 pr-3 py-3 flex items-start gap-3">
                {{-- Icon --}}
                <div class="shrink-0 flex items-center justify-center rounded-xl" style="width:40px;height:40px;background:{{ $prioColor }}18;">
                    @php
                        $icons = ['hardware'=>'desktop-outline','software'=>'code-slash-outline','jaringan'=>'wifi-outline','keamanan'=>'lock-closed-outline','akses'=>'key-outline','data'=>'folder-open-outline','lainnya'=>'ellipsis-horizontal-outline'];
                    @endphp
                    <ion-icon name="{{ $icons[$ticket->kategori] ?? 'headset-outline' }}" style="font-size:22px;color:{{ $prioColor }};"></ion-icon>
                </div>
                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-1 mb-1">
                        <span style="font-size:13px;font-weight:700;color:#1e293b;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;">{{ $ticket->judul }}</span>
                        <span class="status-chip s-{{ $ticket->status }}">{{ str_replace('_',' ',strtoupper($ticket->status)) }}</span>
                    </div>
                    <div style="font-size:10px;color:#94a3b8;margin-bottom:4px;">{{ $ticket->nomor_tiket }}</div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span style="font-size:10px;font-weight:700;color:{{ $prioColor }};background:{{ $prioColor }}18;padding:2px 7px;border-radius:20px;">{{ ucfirst($ticket->prioritas) }}</span>
                        <span style="font-size:10px;color:#64748b;">{{ $ticket->created_at->format('d/m/Y') }}</span>
                        @if($ticket->isOverdue())
                            <span style="font-size:10px;font-weight:700;color:#dc2626;background:#fee2e2;padding:2px 7px;border-radius:20px;">⚠ Overdue</span>
                        @else
                            <span style="font-size:10px;color:#94a3b8;">SLA: {{ $ticket->tanggal_target?->format('d/m') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="flex flex-col items-center justify-center py-16 opacity-60">
            <ion-icon name="headset-outline" style="font-size:64px;color:#cbd5e1;"></ion-icon>
            <h4 class="text-sm font-semibold text-slate-600 mt-3">Belum ada tiket</h4>
            <p class="text-xs text-slate-400 mt-1 text-center px-6">Buat tiket baru untuk melaporkan masalah layanan.</p>
        </div>
        @endforelse

        @if($tickets->hasPages())
        <div class="mt-4 flex justify-center pb-4">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>

    @can('it-ticket.create')
    <a href="{{ route('it-ticket.create') }}" class="fab-button modern">
        <ion-icon name="add-outline"></ion-icon>
    </a>
    @endcan

@endsection

@push('myscript')
<script>
    $(document).ready(function() {
        setTimeout(() => {
            document.getElementById('skeleton-container').style.display = 'none';
            document.getElementById('real-content').style.display = 'block';
        }, 500);
    });
</script>
@endpush
