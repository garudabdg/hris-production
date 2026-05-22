@extends('layouts.app')
@section('titlepage', 'Kategori Aset')

@section('content')
@section('navigasi')
    <a href="{{ route('assets.index') }}">Manajemen Aset</a>
    <span> / Kategori</span>
@endsection

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-5 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-tags me-2"></i>Tambah Kategori</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('assets.kategori.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control @error('nama_kategori') is-invalid @enderror"
                            value="{{ old('nama_kategori') }}" placeholder="Contoh: Elektronik, Furniture, Kendaraan">
                        @error('nama_kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                            rows="2" placeholder="Deskripsi singkat kategori">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Checklist Default</label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <button type="button" id="btnTambahItemTambah" class="btn btn-sm btn-outline-primary">Tambah Item</button>
                            <small class="text-muted">Jika kosong, gunakan checklist default umum.</small>
                        </div>
                        <div class="table-responsive" style="max-height:250px; overflow-y:auto;">
                            <table class="table table-bordered table-sm align-middle" id="checklistTableTambah">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">No</th>
                                        <th>Item Checklist</th>
                                        <th style="width:50px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="checklistBodyTambah">
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" id="checklistItemsInput" name="checklist_items">
                        @error('checklist_items') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-plus me-1"></i> Tambah Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Kategori</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Checklist</th>
                            <th class="text-center">Jml Aset</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $cat)
                            <tr>
                                <td>{{ $categories->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $cat->nama_kategori }}</td>
                                <td class="text-muted small">{{ $cat->deskripsi ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary">{{ count($cat->checklist_items ?? []) }} item</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-primary">{{ $cat->assets_count }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-outline-primary btn-edit-cat"
                                            data-id="{{ $cat->id }}"
                                            data-nama="{{ $cat->nama_kategori }}"
                                            data-deskripsi="{{ $cat->deskripsi }}"
                                            data-checklist='@json($cat->checklist_items ?? [])'>
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-cat"
                                            data-id="{{ $cat->id }}"
                                            data-nama="{{ $cat->nama_kategori }}"
                                            data-count="{{ $cat->assets_count }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Belum ada kategori.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())
                <div class="card-footer">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modalEditKat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditKat" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="editNamaKat" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="editDeskripsiKat" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Checklist Default</label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <button type="button" id="btnTambahItemEdit" class="btn btn-sm btn-outline-primary">Tambah Item</button>
                            <small class="text-muted">Jika kosong, gunakan checklist default umum.</small>
                        </div>
                        <div class="table-responsive" style="max-height:250px; overflow-y:auto;">
                            <table class="table table-bordered table-sm align-middle" id="checklistTableEdit">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">No</th>
                                        <th>Item Checklist</th>
                                        <th style="width:50px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="checklistBodyEdit">
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" id="editChecklistItemsInput" name="checklist_items">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Form delete --}}
<form id="formDeleteKat" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection

@push('myscript')
<script>
$(function () {
    // Checklist table helpers - TAMBAH
    const bodyTambah = $('#checklistBodyTambah');
    const inputTambah = $('#checklistItemsInput');
    const btnTambahItemTambah = $('#btnTambahItemTambah');

    // Checklist table helpers - EDIT
    const bodyEdit = $('#checklistBodyEdit');
    const inputEdit = $('#editChecklistItemsInput');
    const btnTambahItemEdit = $('#btnTambahItemEdit');

    function rowHtml(item, i) {
        return `
            <tr>
                <td class="text-center">${i + 1}</td>
                <td>
                    <input type="text" class="form-control form-control-sm item-input" value="${item}" placeholder="Item checklist">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-item">&times;</button>
                </td>
            </tr>`;
    }

    function renderRows(container, items) {
        let html = '';
        items.forEach((item, i) => {
            html += rowHtml(item, i);
        });
        container.html(html);
    }

    function reindexRows(container) {
        container.find('tr').each(function(idx) {
            $(this).find('td:first').text(idx + 1);
        });
    }

    function getItems(container) {
        const items = [];
        container.find('.item-input').each(function() {
            const val = $(this).val().trim();
            if (val) items.push(val);
        });
        return items;
    }

    // TAMBAH KATEGORI
    btnTambahItemTambah.on('click', function(e) {
        e.preventDefault();
        const items = getItems(bodyTambah);
        renderRows(bodyTambah, [...items, '']);
    });

    bodyTambah.on('click', '.btn-delete-item', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        reindexRows(bodyTambah);
    });

    $('form[action*="kategori"]').on('submit', function(e) {
        if ($(this).attr('id') !== 'formEditKat') {
            const items = getItems(bodyTambah);
            inputTambah.val(JSON.stringify(items));
        }
    });

    // EDIT KATEGORI
    $('.btn-edit-cat').on('click', function () {
        const id        = $(this).data('id');
        const nama      = $(this).data('nama');
        const desk      = $(this).data('deskripsi') || '';
        const checklist = $(this).data('checklist') || [];
        
        $('#formEditKat').attr('action', `/manajemen-aset/kategori/${id}`);
        $('#editNamaKat').val(nama);
        $('#editDeskripsiKat').val(desk);
        
        const items = Array.isArray(checklist) ? checklist : [];
        renderRows(bodyEdit, items);
        
        new bootstrap.Modal(document.getElementById('modalEditKat')).show();
    });

    btnTambahItemEdit.on('click', function(e) {
        e.preventDefault();
        const items = getItems(bodyEdit);
        renderRows(bodyEdit, [...items, '']);
    });

    bodyEdit.on('click', '.btn-delete-item', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        reindexRows(bodyEdit);
    });

    $('#formEditKat').on('submit', function(e) {
        const items = getItems(bodyEdit);
        inputEdit.val(JSON.stringify(items));
    });

    // DELETE
    $('.btn-delete-cat').on('click', function () {
        const id    = $(this).data('id');
        const nama  = $(this).data('nama');
        const count = $(this).data('count');
        if (count > 0) {
            Swal.fire({ icon: 'warning', title: 'Tidak Bisa Dihapus', text: `Kategori "${nama}" masih digunakan oleh ${count} aset.` });
            return;
        }
        Swal.fire({
            icon: 'warning',
            title: 'Hapus Kategori?',
            text: `"${nama}" akan dihapus.`,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(r => {
            if (r.isConfirmed) {
                $('#formDeleteKat').attr('action', `/manajemen-aset/kategori/${id}`).submit();
            }
        });
    });
});
</script>
@endpush
