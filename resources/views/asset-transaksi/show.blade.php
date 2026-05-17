<div class="row g-3">
    {{-- Header Info --}}
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 p-3 rounded-3"
            style="background: {{ $transaction->tipe === 'in' ? 'rgba(40,199,111,0.08)' : 'rgba(234,84,85,0.08)' }};">
            <div class="avatar avatar-lg">
                <span class="avatar-initial rounded bg-label-{{ $transaction->tipe === 'in' ? 'success' : 'danger' }}" style="width:50px;height:50px;">
                    <i class="ti ti-package-{{ $transaction->tipe === 'in' ? 'import' : 'export' }} fs-3"></i>
                </span>
            </div>
            <div>
                <h5 class="mb-1">{{ $transaction->tipe === 'in' ? 'Barang Masuk' : 'Barang Keluar' }}</h5>
                <code class="fw-bold">{{ $transaction->kode_transaksi }}</code>
                <span class="badge bg-label-{{ $transaction->tipe === 'in' ? 'info' : 'warning' }} ms-2">
                    {{ $transaction->kategori_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- Detail Grid --}}
    <div class="col-md-6">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td class="text-muted" style="width:130px;">Aset</td>
                <td class="fw-semibold">
                    {{ $transaction->asset->nama_asset ?? '-' }}
                    <br><small class="text-muted">{{ $transaction->kode_asset }}</small>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Kategori Aset</td>
                <td>{{ $transaction->asset->category->nama_kategori ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Jumlah</td>
                <td>
                    <span class="fw-bold fs-5 {{ $transaction->tipe === 'in' ? 'text-success' : 'text-danger' }}">
                        {{ $transaction->tipe === 'in' ? '+' : '-' }}{{ $transaction->jumlah }}
                    </span>
                    <span class="text-muted">unit</span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Stok Saat Ini</td>
                <td><span class="badge bg-label-primary">{{ $transaction->asset->jumlah_stok ?? 0 }} unit</span></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td class="text-muted" style="width:130px;">Tanggal</td>
                <td class="fw-semibold">{{ $transaction->tanggal_transaksi->format('d F Y') }}</td>
            </tr>
            <tr>
                <td class="text-muted">Cabang</td>
                <td>{{ optional($transaction->cabang)->nama_cabang ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-muted">PIC</td>
                <td>{{ $transaction->penanggung_jawab ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Dicatat oleh</td>
                <td>{{ optional($transaction->user)->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Waktu Input</td>
                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    {{-- Catatan --}}
    @if ($transaction->catatan)
        <div class="col-12">
            <label class="form-label text-muted mb-1"><i class="ti ti-notes me-1"></i>Catatan</label>
            <div class="p-3 bg-light rounded-3">{{ $transaction->catatan }}</div>
        </div>
    @endif

    {{-- Foto Bukti --}}
    @if ($transaction->foto_bukti)
        <div class="col-12">
            <label class="form-label text-muted mb-1"><i class="ti ti-photo me-1"></i>Foto Bukti</label>
            <div>
                <img src="{{ asset('storage/asset-transaksi/' . $transaction->foto_bukti) }}"
                    class="rounded shadow-sm" style="max-width:100%; max-height:300px; object-fit:contain;">
            </div>
        </div>
    @endif
</div>
