@extends('layouts.app')
@section('titlepage', 'Manajemen Izin Akses Aset')

@section('content')
@section('navigasi')
    <span class="text-muted fw-light">Settings</span> / <strong>Manajemen Izin Akses Aset</strong>
@endsection

<div class="row align-items-center mb-4">
    <div class="col-12 d-flex flex-column flex-sm-row justify-content-between align-items-center">
        <div>
            <h4 class="mb-3 mb-sm-0">
                <i class="ti ti-shield-lock me-2"></i>Manajemen Izin Akses Aset
            </h4>
            <small class="text-muted">Kelola izin akses untuk setiap role terhadap modul aset</small>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    @forelse($roles as $role)
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center py-3"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div>
                        <h6 class="m-0 fw-bold text-uppercase" style="font-size: 0.9rem; color: #fff;">
                            {{ ucwords($role->name) }}
                        </h6>
                        <small style="color: rgba(255,255,255,0.8);">
                            {{ count($rolePermissions[$role->id] ?? []) }} izin akses aktif
                        </small>
                    </div>
                </div>

                <form action="{{ route('roles.update-asset-permissions', Crypt::encrypt($role->id)) }}" method="POST" class="role-permission-form">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        @forelse($groupedPermissions as $category => $permissions)
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input category-toggle" type="checkbox"
                                            data-category="{{ $category }}"
                                            style="cursor: pointer; width: 2em; height: 1em;"
                                            id="toggle-{{ $role->id }}-{{ $category }}">
                                    </div>
                                    <label class="form-check-label fw-semibold ms-2 mb-0 flex-grow-1"
                                        for="toggle-{{ $role->id }}-{{ $category }}"
                                        style="cursor: pointer; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        @if($category === 'main')
                                            Aset Umum
                                        @elseif($category === 'kategori')
                                            Kategori Aset
                                        @elseif($category === 'transaksi')
                                            Transaksi Aset
                                        @else
                                            {{ ucwords(str_replace('_', ' ', $category)) }}
                                        @endif
                                    </label>
                                </div>

                                <div class="permission-group" data-category="{{ $category }}">
                                    @foreach($permissions as $permission)
                                        @php
                                            $isChecked = in_array($permission->name, $rolePermissions[$role->id] ?? []);
                                            $permLabel = str_replace('asset.', '', str_replace('.', ' - ', $permission->name));
                                            $permLabel = ucwords(str_replace('_', ' ', $permLabel));
                                        @endphp
                                        <div class="form-check form-check-inline d-block mb-3 ms-3">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->name }}"
                                                id="perm-{{ $role->id }}-{{ $permission->id }}"
                                                data-category="{{ $category }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                                style="cursor: pointer; width: 1.2em; height: 1.2em;">
                                            <label class="form-check-label ms-2 mt-1" 
                                                for="perm-{{ $role->id }}-{{ $permission->id }}"
                                                style="cursor: pointer; font-size: 0.9rem;">
                                                {{ $permLabel }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                Tidak ada izin akses aset yang tersedia
                            </div>
                        @endforelse
                    </div>

                    <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="ti ti-alert-circle me-1"></i>
                            Perubahan akan langsung diterapkan
                        </small>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="ti ti-alert-triangle me-2"></i>
                Belum ada role yang tersedia. Silakan buat role terlebih dahulu.
            </div>
        </div>
    @endforelse
</div>

@endsection

@push('myscript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle category toggle
    document.querySelectorAll('.category-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const category = this.dataset.category;
            const isChecked = this.checked;
            const form = this.closest('form');
            
            form.querySelectorAll(`.permission-checkbox[data-category="${category}"]`).forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            
            updateCategoryToggleState(form);
        });
    });

    // Handle individual permission checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const form = this.closest('form');
            updateCategoryToggleState(form);
        });
    });

    // Update category toggle state based on checkboxes
    function updateCategoryToggleState(form) {
        form.querySelectorAll('.category-toggle').forEach(toggle => {
            const category = toggle.dataset.category;
            const checkboxes = form.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
            const checkedCount = form.querySelectorAll(`.permission-checkbox[data-category="${category}"]:checked`).length;
            const allChecked = checkedCount === checkboxes.length && checkboxes.length > 0;
            const someChecked = checkedCount > 0 && checkedCount < checkboxes.length;
            
            toggle.indeterminate = someChecked;
            toggle.checked = allChecked;
        });
    }

    // Initialize category toggle states
    document.querySelectorAll('form').forEach(form => {
        updateCategoryToggleState(form);
    });

    // Handle form submission
    document.querySelectorAll('.role-permission-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Menyimpan...';

            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 3000);
        });
    });
});
</script>
@endpush

@push('mystyle')
<style>
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .permission-group {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin-left: 0.5rem;
    }

    @media (max-width: 768px) {
        .permission-group {
            grid-template-columns: 1fr;
        }
    }

    .form-check-input {
        border-color: #dee2e6;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .form-check-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .card-header {
        border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    }
</style>
@endpush
