@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Setting Lowongan Tersedia</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
                    <i class="ti ti-plus me-1"></i> Tambah Lowongan
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Link Recruitment Per Cabang --}}
    <div class="card mb-3">
        <div class="card-header py-2">
            <h6 class="mb-0"><i class="ti ti-link me-2"></i>Link Form Lamaran Per Cabang</h6>
        </div>
        <div class="card-body py-2">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cabang</th>
                            <th>Link</th>
                            <th width="80">Salin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cabangs as $c)
                        @php $link = url('/recruitment/form/' . $c->kode_cabang); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $c->nama_cabang }}</td>
                            <td>
                                <a href="{{ $link }}" target="_blank" class="text-primary small">{{ $link }}</a>
                            </td>
                            <td>
                                <button class="btn btn-outline-secondary btn-sm btnCopyLink" data-link="{{ $link }}" title="Salin link">
                                    <i class="ti ti-copy"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1 small">Cabang</label>
                    <select name="cabang" class="form-select form-select-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($cabangs as $c)
                        <option value="{{ $c->kode_cabang }}" @selected(request('cabang') == $c->kode_cabang)>{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="buka" @selected(request('status')=='buka')>Buka</option>
                        <option value="tutup" @selected(request('status')=='tutup')>Tutup</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small">Cari Posisi</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Nama posisi...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="ti ti-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('recruitment.vacancy.index') }}" class="btn btn-secondary btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Posisi</th>
                            <th>Cabang</th>
                            <th>Departemen</th>
                            <th>Jabatan</th>
                            <th>Kuota</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacancies as $i => $v)
                        <tr>
                            <td class="ps-3">{{ $vacancies->firstItem() + $i }}</td>
                            <td>
                                <div class="fw-semibold">{{ $v->posisi }}</div>
                                @if($v->deadline && $v->deadline->isPast())
                                    <small class="text-danger"><i class="ti ti-clock-off"></i> Deadline lewat</small>
                                @elseif($v->deadline)
                                    <small class="text-muted">Deadline: {{ $v->deadline->format('d M Y') }}</small>
                                @endif
                            </td>
                            <td>{{ $v->cabang->nama_cabang ?? $v->kode_cabang }}</td>
                            <td>{{ $v->departemen->nama_departemen ?? $v->kode_dept }}</td>
                            <td>{{ $v->jabatan->nama_jabatan ?? $v->kode_jabatan }}</td>
                            <td>
                                <span class="badge bg-info">{{ $v->kuota }} orang</span>
                            </td>
                            <td>{{ $v->deadline ? $v->deadline->format('d M Y') : '-' }}</td>
                            <td>{!! $v->status_badge !!}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-warning btn-sm" onclick="editVacancy({{ $v->id }})"
                                        title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form action="{{ route('recruitment.vacancy.toggle', $v->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $v->status === 'buka' ? 'btn-secondary' : 'btn-success' }}"
                                            title="{{ $v->status === 'buka' ? 'Tutup Lowongan' : 'Buka Lowongan' }}"
                                            onclick="return confirm('Ubah status lowongan ini?')">
                                            <i class="ti {{ $v->status === 'buka' ? 'ti-lock' : 'ti-lock-open' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('recruitment.vacancy.destroy', $v->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus"
                                            onclick="return confirm('Hapus lowongan ini?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Belum ada data lowongan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($vacancies->hasPages())
        <div class="card-footer">
            {{ $vacancies->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('recruitment.vacancy.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-plus me-2"></i>Tambah Lowongan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('recruitment.vacancy._form', ['v' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-edit me-2"></i>Edit Lowongan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('recruitment.vacancy._form', ['v' => null, 'edit' => true])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('myscript')
@php
$_vacancyData = [];
foreach($vacancies as $v) {
    $_vacancyData[$v->id] = [
        'id'                  => $v->id,
        'kode_cabang'         => $v->kode_cabang,
        'kode_dept'           => $v->kode_dept,
        'kode_jabatan'        => $v->kode_jabatan,
        'posisi'              => $v->posisi,
        'kuota'               => $v->kuota,
        'deadline'            => $v->deadline ? $v->deadline->format('Y-m-d') : '',
        'deskripsi_pekerjaan' => $v->deskripsi_pekerjaan,
        'kualifikasi'         => $v->kualifikasi,
        'status'              => $v->status,
    ];
}
@endphp
<script>
const vacancyData = @json($_vacancyData);

// Salin link cabang
document.querySelectorAll('.btnCopyLink').forEach(btn => {
    btn.addEventListener('click', function() {
        navigator.clipboard.writeText(this.dataset.link).then(() => {
            this.innerHTML = '<i class="ti ti-check"></i>';
            this.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(() => {
                this.innerHTML = '<i class="ti ti-copy"></i>';
                this.classList.replace('btn-success', 'btn-outline-secondary');
            }, 2000);
        });
    });
});

function editVacancy(id) {
    const v = vacancyData[id];
    if (!v) {
        alert('Data tidak ditemukan untuk id: ' + id);
        return;
    }

    const form = document.getElementById('formEdit');
    form.action = `/recruitment/vacancy/${id}`;

    const setVal = (name, val) => {
        const el = form.querySelector(`[name="${name}"]`);
        if (!el) return;
        el.value = val ?? '';
    };

    setVal('kode_cabang', v.kode_cabang);
    setVal('kode_dept', v.kode_dept);
    setVal('kode_jabatan', v.kode_jabatan);
    setVal('posisi', v.posisi);
    setVal('kuota', v.kuota);
    setVal('deadline', v.deadline);
    setVal('deskripsi_pekerjaan', v.deskripsi_pekerjaan);
    setVal('kualifikasi', v.kualifikasi);
    setVal('status', v.status);

    const modalEl = document.getElementById('modalEdit');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}
</script>
@endpush
