<div class="modal-body">
    <div class="row g-3">
        {{-- Info Ringkas --}}
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                <div>
                    @php $fotoPath = Storage::url('karyawan/' . $pinjam->foto); @endphp
                    @if (!empty($pinjam->foto) && Storage::disk('public')->exists('/karyawan/' . $pinjam->foto))
                        <img src="{{ $fotoPath }}" class="rounded-circle" style="width:50px;height:50px;object-fit:cover;">
                    @else
                        <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}" class="rounded-circle" style="width:50px;height:50px;object-fit:cover;">
                    @endif
                </div>
                <div style="font-size:13px;">
                    <div class="fw-bold">{{ $pinjam->nama_karyawan }}</div>
                    <div class="text-muted">
                        Meminjam: <strong>{{ $pinjam->nama_asset }}</strong>
                        ({{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d/m/Y') }}
                        s/d {{ \Carbon\Carbon::parse($pinjam->tanggal_kembali_rencana)->format('d/m/Y') }})
                    </div>
                    @if ($pinjam->catatan)
                        <div class="text-muted mt-1">Catatan: {{ $pinjam->catatan }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Foto kondisi aset --}}
        @if ($pinjam->foto_kondisi_pinjam || $pinjam->foto_asset)
        <div class="col-12">
            <label class="form-label fw-semibold mb-1">Foto Kondisi Aset Saat Dipinjam</label>
            @if ($pinjam->foto_kondisi_pinjam)
                <div>
                    <img src="{{ Storage::url($pinjam->foto_kondisi_pinjam) }}" alt="Kondisi Pinjam"
                        style="max-height:180px; border-radius:8px; border:1px solid #ddd;">
                </div>
            @elseif ($pinjam->foto_asset)
                <div>
                    <img src="{{ Storage::url($pinjam->foto_asset) }}" alt="Foto Aset"
                        style="max-height:180px; border-radius:8px; border:1px solid #ddd;">
                    <div class="text-muted mt-1" style="font-size:11px;">* Foto dari data aset</div>
                </div>
            @endif
        </div>
        @endif

        {{-- Riwayat Approval --}}
        @if ($approvals->count() > 0)
        <div class="col-12">
            <label class="form-label fw-semibold mb-1">Riwayat Persetujuan</label>
            @foreach ($approvals as $ap)
                <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded {{ $ap->status == 'approved' ? 'bg-label-success' : 'bg-label-danger' }}"
                    style="font-size:12px;">
                    <i class="ti {{ $ap->status == 'approved' ? 'ti-check text-success' : 'ti-x text-danger' }}"></i>
                    <span class="fw-semibold">Tahap {{ $ap->level }}</span>
                    <span>— {{ $ap->user->name ?? '-' }}</span>
                    <span class="ms-auto text-muted">{{ \Carbon\Carbon::parse($ap->created_at)->format('d/m/Y H:i') }}</span>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Form Approve / Reject --}}
        <div class="col-12">
            <hr>
            <p class="fw-semibold mb-2">Tahap {{ $pinjam->approval_step }} — Keputusan Anda:</p>

            {{-- Approve --}}
            <form action="{{ route('asset-pinjam.storeapprove', Crypt::encrypt($pinjam->id)) }}" method="POST">
                @csrf
                <div class="d-flex gap-2 mb-3">
                    <button type="submit" name="approve" value="1" class="btn btn-success flex-fill" id="btnApproveSubmit">
                        <i class="ti ti-check me-1"></i>Setujui
                    </button>
                </div>

                {{-- Reject section --}}
                <div class="mb-2">
                    <label class="form-label fw-semibold text-danger">Tolak & Alasan Penolakan</label>
                    <textarea name="catatan_penolakan" class="form-control" rows="2"
                        placeholder="Wajib diisi jika menolak..." id="catatanPenolakan"></textarea>
                </div>
                <button type="submit" name="reject" value="1" class="btn btn-danger w-100" id="btnRejectSubmit">
                    <i class="ti ti-x me-1"></i>Tolak
                </button>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
</div>

<script>
    document.getElementById('btnRejectSubmit').addEventListener('click', function(e) {
        const catatan = document.getElementById('catatanPenolakan').value.trim();
        if (!catatan) {
            e.preventDefault();
            alert('Alasan penolakan wajib diisi.');
        }
    });
</script>
