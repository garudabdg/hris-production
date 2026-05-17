<form action="{{ route('asset-transaksi.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-3">
        {{-- Tipe Transaksi --}}
        <div class="col-12">
            <label class="form-label fw-semibold">Tipe Transaksi <span class="text-danger">*</span></label>
            <div class="d-flex gap-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipe" id="tipeIn" value="in"
                        {{ old('tipe', 'in') == 'in' ? 'checked' : '' }}>
                    <label class="form-check-label text-success fw-semibold" for="tipeIn">
                        <i class="ti ti-package-import me-1"></i>Barang Masuk
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipe" id="tipeOut" value="out"
                        {{ old('tipe') == 'out' ? 'checked' : '' }}>
                    <label class="form-check-label text-danger fw-semibold" for="tipeOut">
                        <i class="ti ti-package-export me-1"></i>Barang Keluar
                    </label>
                </div>
            </div>
            @error('tipe') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        {{-- Kategori Transaksi --}}
        <div class="col-md-6">
            <label class="form-label">Kategori Transaksi <span class="text-danger">*</span></label>
            <select name="kategori_transaksi" id="kategoriTransaksi" class="form-select @error('kategori_transaksi') is-invalid @enderror">
                <option value="">-- Pilih Kategori --</option>
            </select>
            @error('kategori_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Aset --}}
        <div class="col-md-6">
            <label class="form-label">Aset <span class="text-danger">*</span></label>
            <select name="kode_asset" class="form-select @error('kode_asset') is-invalid @enderror">
                <option value="">-- Pilih Aset --</option>
                @foreach ($assets as $a)
                    <option value="{{ $a->kode_asset }}" {{ old('kode_asset') == $a->kode_asset ? 'selected' : '' }}
                        data-stok="{{ $a->jumlah_stok }}">
                        {{ $a->nama_asset }} ({{ $a->kode_asset }}) — Stok: {{ $a->jumlah_stok }}
                    </option>
                @endforeach
            </select>
            @error('kode_asset') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Jumlah --}}
        <div class="col-md-4">
            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
            <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror"
                value="{{ old('jumlah', 1) }}" min="1" placeholder="1">
            @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Tanggal --}}
        <div class="col-md-4">
            <label class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
            <input type="text" name="tanggal_transaksi" class="form-control flatpickr-date @error('tanggal_transaksi') is-invalid @enderror"
                value="{{ old('tanggal_transaksi', date('Y-m-d')) }}" placeholder="Pilih tanggal">
            @error('tanggal_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Cabang --}}
        <div class="col-md-4">
            <label class="form-label">Cabang</label>
            <select name="kode_cabang" class="form-select @error('kode_cabang') is-invalid @enderror">
                <option value="">-- Pilih Cabang --</option>
                @foreach ($cabang as $c)
                    <option value="{{ $c->kode_cabang }}" {{ old('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                        {{ textUpperCase($c->nama_cabang) }}
                    </option>
                @endforeach
            </select>
            @error('kode_cabang') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Penanggung Jawab --}}
        <div class="col-md-6">
            <label class="form-label">Penanggung Jawab / PIC</label>
            <input type="text" name="penanggung_jawab" class="form-control @error('penanggung_jawab') is-invalid @enderror"
                value="{{ old('penanggung_jawab') }}" placeholder="Nama penerima / pengirim">
            @error('penanggung_jawab') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Foto Bukti --}}
        <div class="col-md-6">
            <label class="form-label">Foto Bukti</label>
            <input type="file" name="foto_bukti" class="form-control @error('foto_bukti') is-invalid @enderror" accept="image/*">
            <div class="text-muted small mt-1">Format: JPG, PNG, WEBP. Maks 2MB.</div>
            @error('foto_bukti') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Catatan --}}
        <div class="col-12">
            <label class="form-label">Catatan</label>
            <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                rows="3" placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
    </div>
</form>

<script>
$(function() {
    const kategoriIn = [
        { value: 'pembelian', label: 'Pembelian' },
        { value: 'donasi_masuk', label: 'Donasi / Hibah' },
        { value: 'retur', label: 'Retur / Pengembalian' },
        { value: 'transfer_masuk', label: 'Transfer Masuk' },
    ];
    const kategoriOut = [
        { value: 'pengeluaran', label: 'Pengeluaran / Pemakaian' },
        { value: 'rusak', label: 'Rusak / Disposal' },
        { value: 'donasi_keluar', label: 'Donasi Keluar' },
        { value: 'transfer_keluar', label: 'Transfer Keluar' },
    ];

    function updateKategori() {
        const tipe = $('input[name="tipe"]:checked').val();
        const options = tipe === 'out' ? kategoriOut : kategoriIn;
        const $sel = $('#kategoriTransaksi');
        const oldVal = '{{ old("kategori_transaksi") }}';
        $sel.html('<option value="">-- Pilih Kategori --</option>');
        options.forEach(function(item) {
            const selected = oldVal === item.value ? 'selected' : '';
            $sel.append(`<option value="${item.value}" ${selected}>${item.label}</option>`);
        });
    }

    $('input[name="tipe"]').on('change', updateKategori);
    updateKategori();

    // Reinitialize flatpickr for dynamically loaded content
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.flatpickr-date', {
            dateFormat: 'Y-m-d',
            allowInput: true,
        });
    }
});
</script>
