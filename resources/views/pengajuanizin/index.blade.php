@extends('layouts.mobile.modern')

@section('title', 'Pengajuan Izin')

@section('header_left')
    <a href="{{ route('dashboard.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-90 transition-transform">
        <ion-icon name="chevron-back-outline" class="text-base"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        .sk {
            background: linear-gradient(90deg, #e2e8f0 0%, #f1f5f9 40%, #f8fafc 50%, #f1f5f9 60%, #e2e8f0 100%);
            background-size: 800px 100%;
            animation: shimmer 1.5s infinite linear;
        }
        .content-hide {
            display: none;
        }

    </style>
@endpush


@section('content')
    {{-- ===== SKELETON LOADER ===== --}}
    <div id="skeleton-loader" class="space-y-1 mt-1">
        @for ($i = 0; $i < 6; $i++)
            <div class="flex items-center gap-2 p-1 bg-white rounded-[10px] border border-gray-100 shadow-sm overflow-hidden">
                <div class="sk rounded-[12px] w-[45px] h-[45px] flex-shrink-0"></div>
                <div class="flex-1 space-y-1.5 pr-1">
                    <div class="flex items-center justify-between">
                        <div class="sk h-3.5 w-1/3 rounded"></div>
                        <div class="sk h-4 w-12 rounded"></div>
                    </div>
                    <div class="sk h-3 w-1/2 rounded"></div>
                    <div class="sk h-2.5 w-3/4 rounded italic"></div>
                </div>
            </div>
        @endfor
    </div>


    {{-- ===== REAL CONTENT ===== --}}
    <div id="real-content" class="content-hide space-y-1 mt-1 pb-20">
        @foreach ($pengajuan_izin as $index => $d)
            @php
                if ($d->ket == 'i') {
                    $route = 'izinabsen.delete';
                    $ket_text = 'Izin Absen';
                    $st_color = '#1e90ff';
                    $st_rgb = '30, 144, 255';
                } elseif ($d->ket == 's') {
                    $route = 'izinsakit.delete';
                    $ket_text = 'Izin Sakit';
                    $st_color = '#ff6384';
                    $st_rgb = '255, 99, 132';
                } elseif ($d->ket == 'c') {
                    $route = 'izincuti.delete';
                    $ket_text = 'Izin Cuti';
                    $st_color = '#ff9f40';
                    $st_rgb = '255, 159, 64';
                } elseif ($d->ket == 'd') {
                    $route = 'izindinas.delete';
                    $ket_text = 'Izin Dinas';
                    $st_color = '#4bc0c0';
                    $st_rgb = '75, 192, 192';
                }
                
                $namahari = ['Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'];
                $day_eng = date('D', strtotime($d->dari));
                $day_indo = $namahari[$day_eng] ?? $day_eng;
                $day_short = strtoupper(substr($day_indo, 0, 3));
                $tgl = date('d', strtotime($d->dari));
                $jml_hari = date_diff(date_create($d->dari), date_create($d->sampai))->format('%a') + 1;
                
                // Status mapping to color/label
                $statusColors = [
                    0 => ['bg' => '#FFF9C4', 'text' => '#F57F17', 'label' => 'Pending'],
                    1 => ['bg' => '#E8F5E9', 'text' => '#2E7D32', 'label' => 'Disetujui'],
                    2 => ['bg' => '#FFEBEE', 'text' => '#C62828', 'label' => 'Ditolak'],
                ];
                $s = $statusColors[$d->status_izin] ?? $statusColors[0];
            @endphp

            <form method="POST" name="deleteform" class="deleteform" action="{{ route($route, Crypt::encrypt($d->kode)) }}">
                @csrf
                @method('DELETE')
                <div class="fade-up card press mb-1 overflow-hidden {{ $d->status_izin == 0 ? 'cancel-confirm' : 'cursor-default' }}"
                     style="border: 1px solid {{ $t['primary'] }}; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); background: #fff; animation-delay: {{ $index * 0.04 }}s;">
                    <div class="card-body p-1 flex items-center gap-2">
                        {{-- Date Badge --}}
                        <div class="flex-shrink-0 w-[45px] h-[45px] flex flex-col items-center justify-center rounded-[12px]"
                             style="background: rgba({{ $st_rgb }}, 0.1); color: {{ $st_color }};">
                            <span class="text-[10px] font-bold leading-none uppercase">{{ $day_short }}</span>
                            <span class="text-[16px] font-extrabold leading-tight mt-0.5">{{ $tgl }}</span>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0 pr-1">
                            <div class="flex items-center justify-between mb-0.5">
                                <h3 class="text-[14px] font-bold text-gray-800 truncate" style="color: #333;">{{ $ket_text }}</h3>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold tracking-wide uppercase border" 
                                      style="background: {{ $s['bg'] }}; color: {{ $s['text'] }}; border-color: {{ $s['text'] }}30;">
                                    {{ $s['label'] }}
                                </span>
                            </div>

                            {{-- Waiting Role Info (hanya saat pending) --}}
                            @if ($d->status_izin == 0 && !empty($d->waiting_role))
                                <div class="flex items-center gap-1 mb-0.5">
                                    <ion-icon name="hourglass-outline" style="font-size: 10px; color: #F57F17; flex-shrink: 0;"></ion-icon>
                                    <span class="text-[10px] font-semibold truncate" style="color: #F57F17;">
                                        Menunggu: {{ $d->waiting_role }}
                                    </span>
                                </div>
                            @endif
                            
                            <div class="flex items-center gap-1.5 text-[12px] font-medium mb-0.5" style="color: #555;">
                                <span>{{ date('d M', strtotime($d->dari)) }}</span>
                                <span style="color: #ccc;">-</span>
                                <span>{{ date('d M', strtotime($d->sampai)) }}</span>
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-[#666] text-[10px] font-bold">
                                    {{ $jml_hari }} Hari
                                </span>
                            </div>
                            
                            <p class="text-[11px] text-gray-400 truncate italic leading-none">
                                "{{ $d->keterangan }}"
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        @endforeach
    </div>


    {{-- ===== FLOATING ACTION BUTTON ===== --}}
    <div class="fixed bottom-28 right-4 z-[100] group">
        <button class="w-14 h-14 rounded-full flex items-center justify-center text-white shadow-2xl active:scale-90 transition-all duration-150 bg-[{{ $t['primary'] }}] shadow-[{{ $t['primary'] }}]/40" 
                onclick="document.getElementById('fab-menu').classList.toggle('hidden')">
            <ion-icon name="add-outline" class="text-3xl transition-transform duration-200 group-active:rotate-45"></ion-icon>
        </button>
        
        <div id="fab-menu" class="hidden absolute bottom-16 right-0 space-y-2 w-40">
            <a href="{{ route('izinabsen.create') }}" class="flex items-center justify-end gap-2 group/item transform transition-all hover:-translate-x-1">
                <span class="bg-gray-800 text-white text-[11px] font-bold px-2.5 py-1 rounded-lg opacity-0 group-hover/item:opacity-100 transition-opacity shadow-sm">Izin Absen</span>
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-600 shadow-md border border-gray-100">
                    <ion-icon name="document-text-outline" class="text-xl"></ion-icon>
                </div>
            </a>
            <a href="{{ route('izinsakit.create') }}" class="flex items-center justify-end gap-2 group/item transform transition-all hover:-translate-x-1">
                <span class="bg-gray-800 text-white text-[11px] font-bold px-2.5 py-1 rounded-lg opacity-0 group-hover/item:opacity-100 transition-opacity shadow-sm">Izin Sakit</span>
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-600 shadow-md border border-gray-100">
                    <ion-icon name="medkit-outline" class="text-xl"></ion-icon>
                </div>
            </a>
            @if(!$hideCuti)
                <a href="{{ route('izincuti.create') }}" class="flex items-center justify-end gap-2 group/item transform transition-all hover:-translate-x-1">
                    <span class="bg-gray-800 text-white text-[11px] font-bold px-2.5 py-1 rounded-lg opacity-0 group-hover/item:opacity-100 transition-opacity shadow-sm">Izin Cuti</span>
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-600 shadow-md border border-gray-100">
                        <ion-icon name="calendar-outline" class="text-xl"></ion-icon>
                    </div>
                </a>
            @endif
            <a href="{{ route('izindinas.create') }}" class="flex items-center justify-end gap-2 group/item transform transition-all hover:-translate-x-1">
                <span class="bg-gray-800 text-white text-[11px] font-bold px-2.5 py-1 rounded-lg opacity-0 group-hover/item:opacity-100 transition-opacity shadow-sm">Izin Dinas</span>
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-600 shadow-md border border-gray-100">
                    <ion-icon name="airplane-outline" class="text-xl"></ion-icon>
                </div>
            </a>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const loader = document.getElementById('skeleton-loader');
                const content = document.getElementById('real-content');
                if(loader) loader.style.display = 'none';
                if(content) content.classList.remove('content-hide');
            }, 400);
        });


        // Close FAB menu when clicking outside
        document.addEventListener('click', function(e) {
            const fab = document.querySelector('.fixed.bottom-28.right-4');
            if (fab && !fab.contains(e.target)) {
                const menu = document.getElementById('fab-menu');
                if (menu) menu.classList.add('hidden');
            }
        });

        // Handle Cancellation for Pending Items
        $(document).on('click', '.cancel-confirm', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const type = $(this).find('h3').text();
            
            Swal.fire({
                title: 'Batalkan ' + type + '?',
                text: "Apakah Anda yakin ingin membatalkan pengajuan ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '{{ $t["primary"] }}',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Tidak',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'rounded-lg px-4 py-2 font-bold',
                    cancelButton: 'rounded-lg px-4 py-2 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@endpush

