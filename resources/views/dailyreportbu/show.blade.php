@extends('layouts.app')
@section('titlepage', 'Detail Daily Report Business')

@section('content')
@section('navigasi')
    <span>Daily Report Business</span> / <span>Detail</span>
@endsection

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Daily Report Business</h5>
                <div>
                    <a href="{{ route('dailyreportbu.export.pdf', ['id' => $report->id]) }}" target="_blank" class="btn btn-danger btn-sm">
                        <i class="ti ti-file-export me-1"></i> Export PDF
                    </a>
                    <a href="{{ route('dailyreportbu.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Header Info --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 150px;">Nama Karyawan</th>
                                <td>: {{ $report->karyawan->nama_karyawan }}</td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td>: {{ $report->nik }}</td>
                            </tr>
                            <tr>
                                <th>Team (Sub Dept)</th>
                                <td>: {{ $report->sub_departemen ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 150px;">Tanggal Report</th>
                                <td>: {{ \Carbon\Carbon::parse($report->tanggal)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Dibuat Pada</th>
                                <td>: {{ $report->created_at->translatedFormat('d F Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Total Online</th>
                                <td>: <span class="badge bg-info">{{ $report->total_online ?? 0 }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                {{-- Section 1: Aktivitas Online --}}
                <h6 class="fw-bold mt-4 mb-3 text-primary"><i class="ti ti-world me-2"></i>SECTION 1: AKTIVITAS ONLINE</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Platform</th>
                                <th>Posting</th>
                                <th>Share Group</th>
                                <th>Add Group</th>
                                <th>Add Friend</th>
                                <th>Inbox</th>
                                <th>Story</th>
                                <th>Broadcast</th>
                                <th>Fanspage</th>
                                <th>Link Postingan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totals = ['posting'=>0, 'share_group'=>0, 'add_group'=>0, 'add_friend'=>0, 'inbox'=>0, 'story'=>0, 'broadcast'=>0, 'fanspage'=>0, 'subtotal'=>0];
                            @endphp
                            @foreach ($platforms as $platform)
                                @php
                                    $act = $report->onlineActivities->where('platform', $platform)->first();
                                    if($act) {
                                        $totals['posting'] += $act->posting;
                                        $totals['share_group'] += $act->share_group;
                                        $totals['add_group'] += $act->add_group;
                                        $totals['add_friend'] += $act->add_friend;
                                        $totals['inbox'] += $act->inbox;
                                        $totals['story'] += $act->story;
                                        $totals['broadcast'] += $act->broadcast;
                                        $totals['fanspage'] += $act->fanspage;
                                        $totals['subtotal'] += $act->subtotal;
                                    }
                                @endphp
                                <tr>
                                    <td class="text-start text-capitalize fw-bold"><i class="ti ti-brand-{{ $platform }} me-1"></i> {{ $platform }}</td>
                                    <td>{{ $act->posting ?? 0 }}</td>
                                    <td>{{ $act->share_group ?? 0 }}</td>
                                    <td>{{ $act->add_group ?? 0 }}</td>
                                    <td>{{ $act->add_friend ?? 0 }}</td>
                                    <td>{{ $act->inbox ?? 0 }}</td>
                                    <td>{{ $act->story ?? 0 }}</td>
                                    <td>{{ $act->broadcast ?? 0 }}</td>
                                    <td>{{ $act->fanspage ?? 0 }}</td>
                                    <td>
                                        @if(!empty($act->link_postingan))
                                            <a href="{{ $act->link_postingan }}" target="_blank" class="text-primary text-decoration-underline" title="{{ $act->link_postingan }}">
                                                <i class="ti ti-external-link"></i> Buka Link
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-primary">{{ $act->subtotal ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="text-end">TOTAL KESELURUHAN</td>
                                <td>{{ $totals['posting'] }}</td>
                                <td>{{ $totals['share_group'] }}</td>
                                <td>{{ $totals['add_group'] }}</td>
                                <td>{{ $totals['add_friend'] }}</td>
                                <td>{{ $totals['inbox'] }}</td>
                                <td>{{ $totals['story'] }}</td>
                                <td>{{ $totals['broadcast'] }}</td>
                                <td>{{ $totals['fanspage'] }}</td>
                                <td>-</td>
                                <td class="text-primary fs-5">{{ $totals['subtotal'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Section 2: Aktivitas Offline --}}
                <h6 class="fw-bold mt-5 mb-3 text-warning"><i class="ti ti-users me-2"></i>SECTION 2: AKTIVITAS OFFLINE (APPOINTMENT / CTO / CANVASING)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Tipe Kegiatan</th>
                                <th>Nama Prospek</th>
                                <th>No WhatsApp</th>
                                <th>Alamat / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report->offlineActivities as $index => $offline)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-capitalize">
                                        @if($offline->tipe == 'appointment')
                                            <span class="badge bg-primary">Appointment</span>
                                        @elseif($offline->tipe == 'cto')
                                            <span class="badge bg-success">CTO</span>
                                        @else
                                            <span class="badge bg-info">Canvasing</span>
                                        @endif
                                    </td>
                                    <td>{{ $offline->nama_prospek }}</td>
                                    <td>{{ $offline->whatsapp }}</td>
                                    <td>{{ $offline->alamat }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada data aktivitas offline</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Section 3: Data Nasabah --}}
                <h6 class="fw-bold mt-5 mb-3 text-success"><i class="ti ti-address-book me-2"></i>SECTION 3: PENGOLAHAN DATA CALON NASABAH</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Nama Prospek</th>
                                <th>Akun Sosial Media</th>
                                <th>No WhatsApp</th>
                                <th class="text-center">Status Lead</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report->nasabahData as $index => $nasabah)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $nasabah->nama }}</td>
                                    <td>{{ $nasabah->akun_sosial_media }}</td>
                                    <td>{{ $nasabah->no_whatsapp }}</td>
                                    <td class="text-center text-capitalize">
                                        @if($nasabah->status_lead == 'hot')
                                            <span class="badge bg-danger"><i class="ti ti-flame"></i> Hot</span>
                                        @elseif($nasabah->status_lead == 'warm')
                                            <span class="badge bg-warning"><i class="ti ti-sun"></i> Warm</span>
                                        @else
                                            <span class="badge bg-info"><i class="ti ti-snowflake"></i> Cold</span>
                                        @endif
                                    </td>
                                    <td>{{ $nasabah->keterangan }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada pengolahan data nasabah</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Catatan --}}
                @if($report->catatan)
                    <div class="mt-4">
                        <h6 class="fw-bold mb-2">Catatan Laporan:</h6>
                        <div class="p-3 bg-light rounded border">
                            {{ $report->catatan }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
