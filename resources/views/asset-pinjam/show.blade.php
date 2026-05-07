<div class="modal-body">
    <div class="row g-3">
        {{-- Info Aset --}}
        <div class="col-12">
            <div class="card border mb-0">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2"><i class="ti ti-package me-1"></i>Informasi Aset</h6>
                    <div class="row g-2" style="font-size:13px;">
                        <div class="col-md-6">
                            <span class="text-muted">Nama Aset:</span><br>
                            <span class="fw-semibold">{{ $pinjam->nama_asset }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted">Kode Aset:</span><br>
                            <span class="fw-semibold">{{ $pinjam->kode_asset }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted">Merk / No Seri:</span><br>
                            <span>{{ $pinjam->merk ?? '-' }} / {{ $pinjam->no_seri ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted">Kondisi Saat Dipinjam:</span><br>
                            @php
                                $kondisiMap = ['baik'=>'success','rusak'=>'danger','dalam_perbaikan'=>'warning'];
                                $kondisiLabel = ['baik'=>'Baik','rusak'=>'Rusak','dalam_perbaikan'=>'Dalam Perbaikan'];
                            @endphp
                            <span class="badge bg-label-{{ $kondisiMap[$pinjam->kondisi] ?? 'secondary' }}">
                                {{ $kondisiLabel[$pinjam->kondisi] ?? $pinjam->kondisi }}
                            </span>
                        </div>
                        @if ($pinjam->foto_kondisi_pinjam)
                        <div class="col-12">
                            <span class="text-muted">Foto Kondisi Saat Dipinjam:</span><br>
                            <img src="{{ Storage::url($pinjam->foto_kondisi_pinjam) }}" alt="Foto Kondisi"
                                style="max-height:160px; border-radius:8px; border:1px solid #ddd; margin-top:6px;">
                        </div>
                        @endif
                        @if ($pinjam->foto_kondisi_kembali)
                        <div class="col-12">
                            <span class="text-muted">Foto Kondisi Saat Dikembalikan:</span><br>
                            <img src="{{ Storage::url($pinjam->foto_kondisi_kembali) }}" alt="Foto Kondisi Kembali"
                                style="max-height:160px; border-radius:8px; border:1px solid #ddd; margin-top:6px;">
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Peminjam --}}
        <div class="col-12">
            <div class="card border mb-0">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2"><i class="ti ti-user me-1"></i>Peminjam</h6>
                    <div class="d-flex align-items-center gap-3">
                        @php $fotoPath = Storage::url('karyawan/' . $pinjam->foto); @endphp
                        @if (!empty($pinjam->foto) && Storage::disk('public')->exists('/karyawan/' . $pinjam->foto))
                            <img src="{{ $fotoPath }}" class="rounded-circle" style="width:50px;height:50px;object-fit:cover;">
                        @else
                            <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}" class="rounded-circle" style="width:50px;height:50px;object-fit:cover;">
                        @endif
                        <div style="font-size:13px;">
                            <div class="fw-bold">{{ $pinjam->nama_karyawan }}</div>
                            <div class="text-muted">{{ $pinjam->nama_dept }} · {{ $pinjam->nama_cabang }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Peminjaman --}}
        <div class="col-12">
            <div class="card border mb-0">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2"><i class="ti ti-calendar me-1"></i>Detail Peminjaman</h6>
                    <div class="row g-2" style="font-size:13px;">
                        <div class="col-md-4">
                            <span class="text-muted">Kode Pinjam:</span><br>
                            <span class="fw-semibold">{{ $pinjam->kode_pinjam }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted">Tgl Pinjam:</span><br>
                            <span>{{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d M Y') }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted">Rencana Kembali:</span><br>
                            <span>{{ \Carbon\Carbon::parse($pinjam->tanggal_kembali_rencana)->format('d M Y') }}</span>
                        </div>
                        @if ($pinjam->tanggal_kembali_aktual)
                        <div class="col-md-4">
                            <span class="text-muted">Aktual Kembali:</span><br>
                            <span class="text-success fw-semibold">{{ \Carbon\Carbon::parse($pinjam->tanggal_kembali_aktual)->format('d M Y') }}</span>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <span class="text-muted">Status:</span><br>
                            {!! $pinjam->status_badge !!}
                        </div>
                        @if ($pinjam->catatan)
                        <div class="col-12">
                            <span class="text-muted">Catatan:</span><br>
                            <span>{{ $pinjam->catatan }}</span>
                        </div>
                        @endif
                        @if ($pinjam->catatan_penolakan)
                        <div class="col-12">
                            <span class="text-muted">Alasan Penolakan:</span><br>
                            <span class="text-danger">{{ $pinjam->catatan_penolakan }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Approval --}}
        <div class="col-12">
            <div class="card border mb-0">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2"><i class="ti ti-check-circle me-1"></i>Riwayat Approval</h6>
                    @if ($pinjam->approvals->count() > 0)
                        @foreach ($pinjam->approvals->sortBy('level') as $ap)
                            <div class="d-flex align-items-center gap-2 mb-2" style="font-size:12px;">
                                <span class="badge {{ $ap->status == 'approved' ? 'bg-success' : 'bg-danger' }}">
                                    Tahap {{ $ap->level }}
                                </span>
                                <span>{{ $ap->user->name ?? '-' }}</span>
                                <span class="text-muted">{{ $ap->keterangan }}</span>
                                <span class="ms-auto text-muted">{{ \Carbon\Carbon::parse($ap->created_at)->format('d/m/Y H:i') }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0" style="font-size:13px;">Belum ada riwayat approval.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>
