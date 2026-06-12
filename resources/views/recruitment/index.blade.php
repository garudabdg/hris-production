@extends('layouts.app')
@section('titlepage', 'Recruitment')

@section('content')
@section('navigasi')
    <span>Recruitment</span>
@endsection

{{-- ─── FILTER CARD ─────────────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex gap-2">
                    @can('recruitment.create')
                        <a href="{{ route('recruitment.form') }}" class="btn btn-primary" target="_blank">
                            <i class="ti ti-external-link me-1"></i> Buka Form Pendaftaran
                        </a>
                    @endcan
                    <a href="{{ route('recruitment.form') }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="ti ti-link me-1"></i> Link Publik
                    </a>
                </div>
                <div class="text-muted small">
                    <i class="ti ti-users me-1"></i>
                    Total: <strong>{{ $recruitments->total() }}</strong> pelamar
                </div>
            </div>
            <div class="card-body pb-2">
                <form action="{{ route('recruitment.index') }}" method="GET" id="formFilter">
                    <div class="row g-2">
                        <div class="col-lg-3 col-sm-12">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama / kode / email..."
                                value="{{ Request('search') }}">
                        </div>
                        <div class="col-lg-2 col-sm-12">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ Request('status') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if (!$isHrdCabang)
                        <div class="col-lg-2 col-sm-12">
                            <select name="kode_cabang" class="form-select select2">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabang as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ Request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                        {{ textUpperCase($c->nama_cabang) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-lg-2 col-sm-12">
                            <select name="kode_dept" class="form-select select2">
                                <option value="">Semua Dept</option>
                                @foreach ($departemen as $d)
                                    <option value="{{ $d->kode_dept }}" {{ Request('kode_dept') == $d->kode_dept ? 'selected' : '' }}>
                                        {{ textUpperCase($d->nama_dept) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-sm-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i> Cari
                                </button>
                                <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if ($isHrdCabang && $recruitmentsByCabang)
    {{-- ─── HRD CABANG: Tabel per cabang ──────────────────────────────────── --}}
    @forelse ($recruitmentsByCabang as $kodeCabang => $items)
        @php $namaCabang = optional($items->first()->cabang)->nama_cabang ?? $kodeCabang; @endphp
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-label-primary d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-building-community fs-5"></i>
                            <span class="fw-bold">{{ textUpperCase($namaCabang) }}</span>
                        </div>
                        <span class="badge bg-primary">{{ $items->count() }} Pelamar</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">#</th>
                                        <th width="60">Foto</th>
                                        <th>Kode</th>
                                        <th>Nama Pelamar</th>
                                        <th>Posisi Dilamar</th>
                                        <th>Dept</th>
                                        <th>Kontak</th>
                                        <th>Tgl Melamar</th>
                                        <th>Status</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $idx => $item)
                                        <tr style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button') && !event.target.closest('form')) { window.location.href='{{ route('recruitment.show', $item->id) }}'; }">
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                @if ($item->foto && Storage::disk('public')->exists('recruitment/foto/' . $item->foto))
                                                    <img src="{{ asset('storage/recruitment/foto/' . $item->foto) }}"
                                                        class="rounded-circle object-fit-cover"
                                                        style="width:40px;height:40px;object-fit:cover;" alt="foto">
                                                @else
                                                    <div class="rounded-circle bg-label-primary d-flex align-items-center justify-content-center"
                                                        style="width:40px;height:40px;">
                                                        <i class="ti ti-user"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-label-primary">{{ $item->kode_recruitment }}</span></td>
                                            <td>
                                                <div class="fw-semibold">{{ $item->nama_lengkap }}</div>
                                                <small class="text-muted">
                                                    {{ $item->pendidikan_terakhir ?? '-' }}
                                                    @if ($item->jurusan) - {{ $item->jurusan }} @endif
                                                </small>
                                            </td>
                                            <td>{{ $item->posisi_dilamar }}</td>
                                            <td>
                                                <small>{{ $item->departemen->nama_dept ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <div><i class="ti ti-phone me-1 text-muted"></i>{{ $item->no_hp ?? '-' }}</div>
                                                <small class="text-muted"><i class="ti ti-mail me-1"></i>{{ $item->email ?? '-' }}</small>
                                            </td>
                                            <td>{{ $item->tanggal_melamar?->format('d/m/Y') }}</td>
                                            <td>
                                                {!! $item->status_badge !!}
                                                @if($item->status === 'interview')
                                                    @if($item->konfirmasi_interview === 'hadir')
                                                        <br><span class="badge bg-success" style="font-size:10px;">✅ Hadir</span>
                                                    @elseif($item->konfirmasi_interview === 'tidak_hadir')
                                                        <br><span class="badge bg-danger" style="font-size:10px;">❌ Tidak Hadir</span>
                                                    @else
                                                        <br><span class="badge bg-warning text-dark" style="font-size:10px;">⏳ Belum Konfirmasi</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('recruitment.show', $item->id) }}"
                                                        class="btn btn-sm btn-icon btn-outline-primary" title="Detail">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    @can('recruitment.edit')
                                                        <a href="{{ route('recruitment.edit', $item->id) }}"
                                                            class="btn btn-sm btn-icon btn-outline-info" title="Edit">
                                                            <i class="ti ti-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('recruitment.delete')
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon btn-outline-danger btnHapus"
                                                            data-id="{{ $item->id }}"
                                                            data-nama="{{ $item->nama_lengkap }}"
                                                            title="Hapus">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="ti ti-inbox" style="font-size:2rem;"></i>
                        <div class="mt-2">Belum ada data lamaran</div>
                    </div>
                </div>
            </div>
        </div>
    @endforelse

@else
    {{-- ─── SUPER ADMIN & ROLE LAIN: Tabel flat ────────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">#</th>
                                    <th width="60">Foto</th>
                                    <th>Kode</th>
                                    <th>Nama Pelamar</th>
                                    <th>Posisi Dilamar</th>
                                    <th>Cabang / Dept</th>
                                    <th>Kontak</th>
                                    <th>Tgl Melamar</th>
                                    <th>Status</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recruitments as $index => $item)
                                    <tr style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button') && !event.target.closest('form')) { window.location.href='{{ route('recruitment.show', $item->id) }}'; }">
                                        <td>{{ $recruitments->firstItem() + $index }}</td>
                                        <td>
                                            @if ($item->foto && Storage::disk('public')->exists('recruitment/foto/' . $item->foto))
                                                <img src="{{ asset('storage/recruitment/foto/' . $item->foto) }}"
                                                    class="rounded-circle object-fit-cover"
                                                    style="width:40px;height:40px;object-fit:cover;" alt="foto">
                                            @else
                                                <div class="rounded-circle bg-label-primary d-flex align-items-center justify-content-center"
                                                    style="width:40px;height:40px;">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $item->kode_recruitment }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $item->nama_lengkap }}</div>
                                            <small class="text-muted">
                                                {{ $item->pendidikan_terakhir ?? '-' }}
                                                @if ($item->jurusan) - {{ $item->jurusan }} @endif
                                            </small>
                                        </td>
                                        <td>{{ $item->posisi_dilamar }}</td>
                                        <td>
                                            <div>{{ $item->cabang->nama_cabang ?? '-' }}</div>
                                            <small class="text-muted">{{ $item->departemen->nama_dept ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <div><i class="ti ti-phone me-1 text-muted"></i>{{ $item->no_hp ?? '-' }}</div>
                                            <small class="text-muted"><i class="ti ti-mail me-1"></i>{{ $item->email ?? '-' }}</small>
                                        </td>
                                        <td>{{ $item->tanggal_melamar?->format('d/m/Y') }}</td>
                                        <td>{!! $item->status_badge !!}
                                            @if($item->status === 'interview')
                                                @if($item->konfirmasi_interview === 'hadir')
                                                    <br><span class="badge bg-success" style="font-size:10px;">✅ Hadir</span>
                                                @elseif($item->konfirmasi_interview === 'tidak_hadir')
                                                    <br><span class="badge bg-danger" style="font-size:10px;">❌ Tidak Hadir</span>
                                                @else
                                                    <br><span class="badge bg-warning text-dark" style="font-size:10px;">⏳ Belum Konfirmasi</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('recruitment.show', $item->id) }}"
                                                    class="btn btn-sm btn-icon btn-outline-primary" title="Detail">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                @can('recruitment.edit')
                                                    <a href="{{ route('recruitment.edit', $item->id) }}"
                                                        class="btn btn-sm btn-icon btn-outline-info" title="Edit">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('recruitment.delete')
                                                    <button type="button"
                                                        class="btn btn-sm btn-icon btn-outline-danger btnHapus"
                                                        data-id="{{ $item->id }}"
                                                        data-nama="{{ $item->nama_lengkap }}"
                                                        title="Hapus">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="ti ti-inbox" style="font-size:2rem;"></i>
                                            <div class="mt-2">Belum ada data lamaran</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $recruitments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Form hapus --}}
<form id="formHapus" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection
@push('myscript')
<script>
    $(function () {
        $(".select2").select2({ width: '100%' });

        $(document).on('click', '.btnHapus', function () {
            const id   = $(this).data('id');
            const nama = $(this).data('nama');
            Swal.fire({
                title: 'Hapus Lamaran?',
                text: `Data lamaran atas nama "${nama}" akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('#formHapus');
                    form.attr('action', `/recruitment/${id}`);
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
