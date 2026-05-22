@extends('layouts.app')
@section('titlepage', 'Buat Checklist Perawatan')

@section('content')
@section('navigasi')
    <span>Manajemen Aset</span>
    <span> / <a href="{{ route('asset-perawatan.index') }}">Checklist Perawatan</a></span>
    <span> / Buat Checklist</span>
@endsection

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-checklist me-2"></i>Buat Checklist Perawatan Aset</h5>
                <small class="text-muted">Pilih aset lalu isi hasil pemeriksaan untuk setiap item checklist</small>
            </div>
            <form method="POST" action="{{ route('asset-perawatan.store') }}" id="formPerawatan">
                @csrf
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Info Umum --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Aset <span class="text-danger">*</span></label>
                            <select name="kode_asset" id="kode_asset"
                                class="form-select @error('kode_asset') is-invalid @enderror" required>
                                <option value="">-- Pilih Aset --</option>
                                @foreach ($assets as $a)
                                    <option value="{{ $a->kode_asset }}"
                                        data-nama="{{ $a->nama_asset }}"
                                        data-kategori="{{ $a->category?->nama_kategori ?? '' }}"
                                        {{ (old('kode_asset', $selectedAsset?->kode_asset) == $a->kode_asset) ? 'selected' : '' }}>
                                        {{ $a->nama_asset }} — {{ $a->kode_asset }}
                                        @if ($a->category) ({{ $a->category->nama_kategori }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_asset')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Perawatan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_perawatan" id="tanggal_perawatan"
                                class="form-control @error('tanggal_perawatan') is-invalid @enderror"
                                value="{{ old('tanggal_perawatan', date('Y-m-d')) }}" required>
                            @error('tanggal_perawatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Petugas</label>
                            <input type="text" name="petugas" id="petugas"
                                class="form-control @error('petugas') is-invalid @enderror"
                                value="{{ old('petugas') }}" placeholder="Nama petugas perawatan">
                            @error('petugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2"
                                placeholder="Catatan umum perawatan...">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    {{-- Badge info kategori --}}
                    <div id="kategoriInfo" class="mb-3" style="{{ $selectedAsset ? '' : 'display:none;' }}">
                        <div class="alert alert-info d-flex align-items-center gap-2 py-2">
                            <i class="ti ti-info-circle"></i>
                            <span>Kategori aset: <strong id="kategoriNama">{{ $selectedAsset?->category?->nama_kategori ?? '' }}</strong>
                            — Checklist item akan muncul di bawah.</span>
                        </div>
                    </div>

                    {{-- Checklist Items --}}
                    <div id="checklistSection" class="{{ ($selectedAsset && count($checklistItems) > 0) ? '' : 'd-none' }}">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0"><i class="ti ti-list-check me-2"></i>Item Checklist</h6>
                            <div class="d-flex gap-2 align-items-center">
                                <button type="button" id="btnTambahItem" class="btn btn-sm btn-outline-primary">Tambah Item</button>
                                <span class="badge bg-label-primary" id="totalItems">{{ count($checklistItems) }} item</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="checklistTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">No</th>
                                        <th>Item Pemeriksaan</th>
                                        <th style="width:280px;">Klasifikasi</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="checklistBody">
                                    @foreach ($checklistItems as $i => $item)
                                    <tr>
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <input type="text" name="items[{{ $i }}][item_name]" value="{{ $item }}" class="form-control form-control-sm item-name-input" required>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="items[{{ $i }}][klasifikasi]"
                                                        id="baik_{{ $i }}" value="baik"
                                                        {{ old("items.$i.klasifikasi", 'baik') == 'baik' ? 'checked' : '' }} required>
                                                    <label class="form-check-label text-success fw-semibold" for="baik_{{ $i }}">
                                                        <i class="ti ti-circle-check me-1"></i>Baik
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="items[{{ $i }}][klasifikasi]"
                                                        id="cukup_{{ $i }}" value="cukup_baik"
                                                        {{ old("items.$i.klasifikasi") == 'cukup_baik' ? 'checked' : '' }}>
                                                    <label class="form-check-label text-warning fw-semibold" for="cukup_{{ $i }}">
                                                        <i class="ti ti-alert-triangle me-1"></i>Cukup Baik
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="items[{{ $i }}][klasifikasi]"
                                                        id="rusak_{{ $i }}" value="rusak"
                                                        {{ old("items.$i.klasifikasi") == 'rusak' ? 'checked' : '' }}>
                                                    <label class="form-check-label text-danger fw-semibold" for="rusak_{{ $i }}">
                                                        <i class="ti ti-x me-1"></i>Rusak
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <input type="text" name="items[{{ $i }}][keterangan]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Keterangan (opsional)"
                                                    value="{{ old("items.$i.keterangan") }}">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" title="Hapus baris">&times;</button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="noAssetMsg" class="{{ $selectedAsset ? 'd-none' : '' }} text-center py-5 text-muted">
                        <i class="ti ti-hand-click fs-1 d-block mb-2"></i>
                        Pilih aset terlebih dahulu untuk menampilkan item checklist
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('asset-perawatan.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnSimpan" {{ $selectedAsset ? '' : 'disabled' }}>
                        <i class="ti ti-device-floppy me-1"></i> Simpan Checklist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const assetSelect      = document.getElementById('kode_asset');
    const checklistSection = document.getElementById('checklistSection');
    const checklistBody    = document.getElementById('checklistBody');
    const noAssetMsg       = document.getElementById('noAssetMsg');
    const kategoriInfo     = document.getElementById('kategoriInfo');
    const kategoriNama     = document.getElementById('kategoriNama');
    const totalItems       = document.getElementById('totalItems');
    const btnSimpan        = document.getElementById('btnSimpan');
        const btnTambahItem    = document.getElementById('btnTambahItem');

    assetSelect.addEventListener('change', function () {
        const kodeAsset = this.value;
        if (!kodeAsset) {
            checklistSection.classList.add('d-none');
            noAssetMsg.classList.remove('d-none');
            kategoriInfo.style.display = 'none';
            btnSimpan.disabled = true;
            return;
        }

        // Fetch checklist items via AJAX
        fetch(`{{ route('asset-perawatan.checklist-items') }}?kode_asset=${encodeURIComponent(kodeAsset)}`, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                if (!data.items || data.items.length === 0) {
                    checklistSection.classList.add('d-none');
                    noAssetMsg.classList.remove('d-none');
                    kategoriInfo.style.display = 'none';
                    btnSimpan.disabled = true;
                    return;
                }

                // Update kategori info
                kategoriNama.textContent = data.kategori || '-';
                kategoriInfo.style.display = '';
                totalItems.textContent = data.items.length + ' item';

                    renderRows(data.items);
                checklistSection.classList.remove('d-none');
                noAssetMsg.classList.add('d-none');
                btnSimpan.disabled = false;
            })
            .catch((err) => {
                console.error('Gagal load checklist items:', err);
                checklistSection.classList.add('d-none');
                noAssetMsg.innerHTML = '<i class="ti ti-alert-circle fs-1 d-block mb-2 text-danger"></i>Gagal memuat item checklist. Silakan refresh halaman.';
                noAssetMsg.classList.remove('d-none');
                btnSimpan.disabled = true;
            });
    });

    function escHtml(text) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(text));
        return d.innerHTML;
    }
        // Render rows helper
        function renderRows(items) {
            let html = '';
            items.forEach(function (item, i) {
                html += rowHtml(item, i);
            });
            checklistBody.innerHTML = html;
            totalItems.textContent = items.length + ' item';
            reindexRows();
        }

        function rowHtml(item, i) {
            const name = escHtml(item || '');
            return `
                <tr>
                    <td class="text-center text-muted">${i + 1}</td>
                    <td>
                        <input type="text" name="items[${i}][item_name]" value="${name}" class="form-control form-control-sm item-name-input" required>
                    </td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="items[${i}][klasifikasi]" id="baik_${i}" value="baik" checked required>
                                <label class="form-check-label text-success fw-semibold" for="baik_${i}"><i class="ti ti-circle-check me-1"></i>Baik</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="items[${i}][klasifikasi]" id="cukup_${i}" value="cukup_baik">
                                <label class="form-check-label text-warning fw-semibold" for="cukup_${i}"><i class="ti ti-alert-triangle me-1"></i>Cukup Baik</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="items[${i}][klasifikasi]" id="rusak_${i}" value="rusak">
                                <label class="form-check-label text-danger fw-semibold" for="rusak_${i}"><i class="ti ti-x me-1"></i>Rusak</label>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <input type="text" name="items[${i}][keterangan]" class="form-control form-control-sm" placeholder="Keterangan (opsional)">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" title="Hapus baris">&times;</button>
                        </div>
                    </td>
                </tr>`;
        }

        // Add new empty item
        btnTambahItem.addEventListener('click', function () {
            const rows = checklistBody.querySelectorAll('tr');
            const nextIndex = rows.length;
            const temp = document.createElement('tbody');
            temp.innerHTML = rowHtml('', nextIndex);
            checklistBody.appendChild(temp.firstElementChild);
            reindexRows();
            totalItems.textContent = checklistBody.querySelectorAll('tr').length + ' item';
        });

        // Delete row (event delegation)
        checklistBody.addEventListener('click', function (e) {
            if (e.target.closest('.btn-delete-row')) {
                const btn = e.target.closest('.btn-delete-row');
                const row = btn.closest('tr');
                row.remove();
                reindexRows();
                totalItems.textContent = checklistBody.querySelectorAll('tr').length + ' item';
            }
        });

        // Reindex names and ids after add/remove
        function reindexRows() {
            const rows = Array.from(checklistBody.querySelectorAll('tr'));
            rows.forEach(function (tr, idx) {
                tr.querySelectorAll('input, label').forEach(function (el) {
                    if (el.name) {
                        el.name = el.name.replace(/items\[\d+\]/, `items[${idx}]`);
                    }
                    if (el.id) {
                        el.id = el.id.replace(/_(\d+)$/, `_${idx}`);
                    }
                    if (el.htmlFor) {
                        el.htmlFor = el.htmlFor.replace(/_(\d+)$/, `_${idx}`);
                    }
                });
                const noCell = tr.querySelector('td.text-center.text-muted');
                if (noCell) noCell.textContent = idx + 1;
            });
        }

});
</script>
@endpush
