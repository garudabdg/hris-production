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
                            <label class="form-label fw-semibold">Kategori Aset</label>
                            <select id="kategori_filter" class="form-select">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Aset <span class="text-danger">*</span></label>
                            <select name="kode_asset" id="kode_asset"
                                class="form-select @error('kode_asset') is-invalid @enderror" required>
                                <option value="">-- Pilih Aset --</option>
                                @foreach ($assets as $a)
                                    <option value="{{ $a->kode_asset }}"
                                        data-nama="{{ $a->nama_asset }}"
                                        data-kategori-id="{{ $a->category_id ?? '' }}"
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Perawatan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_perawatan" id="tanggal_perawatan"
                                class="form-control @error('tanggal_perawatan') is-invalid @enderror"
                                value="{{ old('tanggal_perawatan', date('Y-m-d')) }}" required>
                            @error('tanggal_perawatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
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
$(function() {
    // Select elements
    const $kategoriFilter = $('#kategori_filter');
    const $assetSelect = $('#kode_asset');
    const $checklistSection = $('#checklistSection');
    const $checklistBody = $('#checklistBody');
    const $noAssetMsg = $('#noAssetMsg');
    const $kategoriInfo = $('#kategoriInfo');
    const $kategoriNama = $('#kategoriNama');
    const $totalItems = $('#totalItems');
    const $btnSimpan = $('#btnSimpan');
    const $btnTambahItem = $('#btnTambahItem');

    // Initialize Select2
    if ($kategoriFilter.length) {
        $kategoriFilter.wrap('<div class="position-relative"></div>').select2({
            placeholder: '-- Semua Kategori --',
            allowClear: true,
            dropdownParent: $kategoriFilter.parent()
        });
    }

    if ($assetSelect.length) {
        $assetSelect.wrap('<div class="position-relative"></div>').select2({
            placeholder: '-- Pilih Aset --',
            allowClear: true,
            dropdownParent: $assetSelect.parent()
        });
    }

    // Keep track of the currently loaded asset to avoid duplicate AJAX calls or overwriting page-load selections
    let lastLoadedAsset = $assetSelect.val() || '';

    // Keep original options as DOM elements copy
    const originalOptions = $assetSelect.find('option').slice(1).map(function() {
        return $(this).clone()[0];
    }).get();

    function filterAssets() {
        const categoryId = $kategoriFilter.val();
        const currentSelectedVal = $assetSelect.val();

        // Empty options except placeholder
        $assetSelect.empty().append('<option value="">-- Pilih Aset --</option>');

        // Filter and append
        originalOptions.forEach(option => {
            const $opt = $(option).clone();
            if (!categoryId || $opt.attr('data-kategori-id') == categoryId) {
                $assetSelect.append($opt);
            }
        });

        // Re-select if it still exists in filtered options
        if (currentSelectedVal) {
            const hasVal = $assetSelect.find(`option[value="${currentSelectedVal}"]`).length > 0;
            if (hasVal) {
                $assetSelect.val(currentSelectedVal);
            } else {
                $assetSelect.val('');
            }
        }
        
        $assetSelect.trigger('change'); // update Select2 and trigger change handlers
    }

    $kategoriFilter.on('change', function () {
        filterAssets();
    });

    // If an asset is already selected on page load (e.g., validation redirect)
    if (lastLoadedAsset) {
        const categoryId = $assetSelect.find(':selected').attr('data-kategori-id');
        if (categoryId) {
            $kategoriFilter.val(categoryId).trigger('change');
        }
    }

    $assetSelect.on('change', function () {
        const kodeAsset = $(this).val() || '';
        if (kodeAsset === lastLoadedAsset) {
            return;
        }
        lastLoadedAsset = kodeAsset;

        if (!kodeAsset) {
            $checklistSection.addClass('d-none');
            $noAssetMsg.removeClass('d-none');
            $kategoriInfo.hide();
            $btnSimpan.prop('disabled', true);
            return;
        }

        // Fetch checklist items via AJAX
        $.ajax({
            url: `{{ route('asset-perawatan.checklist-items') }}`,
            type: 'GET',
            data: { kode_asset: kodeAsset },
            cache: false,
            success: function(data) {
                if (!data.items || data.items.length === 0) {
                    $checklistSection.addClass('d-none');
                    $noAssetMsg.removeClass('d-none');
                    $kategoriInfo.hide();
                    $btnSimpan.prop('disabled', true);
                    return;
                }

                // Update kategori info
                $kategoriNama.text(data.kategori || '-');
                $kategoriInfo.show();
                $totalItems.text(data.items.length + ' item');

                renderRows(data.items);
                $checklistSection.removeClass('d-none');
                $noAssetMsg.addClass('d-none');
                $btnSimpan.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Gagal load checklist items:', error);
                lastLoadedAsset = '';
                $checklistSection.addClass('d-none');
                $noAssetMsg.html('<i class="ti ti-alert-circle fs-1 d-block mb-2 text-danger"></i>Gagal memuat item checklist. Silakan refresh halaman.');
                $noAssetMsg.removeClass('d-none');
                $btnSimpan.prop('disabled', true);
            }
        });
    });

    function escHtml(text) {
        return $('<div>').text(text || '').html();
    }

    // Render rows helper
    function renderRows(items) {
        let html = '';
        items.forEach(function (item, i) {
            html += rowHtml(item, i);
        });
        $checklistBody.html(html);
        $totalItems.text(items.length + ' item');
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
    $btnTambahItem.on('click', function () {
        const rowsCount = $checklistBody.find('tr').length;
        $checklistBody.append(rowHtml('', rowsCount));
        reindexRows();
    });

    // Delete row (event delegation)
    $checklistBody.on('click', '.btn-delete-row', function () {
        $(this).closest('tr').remove();
        reindexRows();
    });

    // Reindex names and ids after add/remove
    function reindexRows() {
        const $rows = $checklistBody.find('tr');
        $rows.each(function (idx, tr) {
            const $tr = $(tr);
            $tr.find('input, label').each(function () {
                const $el = $(this);
                const name = $el.attr('name');
                if (name) {
                    $el.attr('name', name.replace(/items\[\d+\]/, `items[${idx}]`));
                }
                const id = $el.attr('id');
                if (id) {
                    $el.attr('id', id.replace(/_(\d+)$/, `_${idx}`));
                }
                const htmlFor = $el.attr('for');
                if (htmlFor) {
                    $el.attr('for', htmlFor.replace(/_(\d+)$/, `_${idx}`));
                }
            });
            const noCell = $tr.find('td.text-center.text-muted');
            if (noCell.length) noCell.text(idx + 1);
        });
        $totalItems.text($rows.length + ' item');
    }
});
</script>
@endpush
