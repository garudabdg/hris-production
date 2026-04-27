@extends('layouts.app')
@section('titlepage', 'Detail Pelamar')

@section('content')
@section('navigasi')
    <a href="{{ route('recruitment.index') }}">Recruitment</a>
    <span> / Detail Pelamar</span>
@endsection

@php
    $r = $recruitment;
    $statusConfig = [
        'pending'   => ['color' => 'secondary', 'label' => 'Pending',    'icon' => 'ti-clock'],
        'review'    => ['color' => 'info',      'label' => 'Review',     'icon' => 'ti-eye'],
        'interview' => ['color' => 'warning',   'label' => 'Interview',  'icon' => 'ti-microphone'],
        'offering'  => ['color' => 'primary',   'label' => 'Penawaran',  'icon' => 'ti-file-dollar'],
        'diterima'  => ['color' => 'success',   'label' => 'Diterima',   'icon' => 'ti-circle-check'],
        'ditolak'   => ['color' => 'danger',    'label' => 'Ditolak',    'icon' => 'ti-circle-x'],
    ];
    $sc = $statusConfig[$r->status] ?? $statusConfig['pending'];
@endphp

<div class="row g-4">

    {{-- KOLOM KIRI --}}
    <div class="col-xl-3 col-lg-4 col-md-5 col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center pt-4 pb-3 px-3">
                {{-- Foto --}}
                <div class="position-relative d-inline-block mb-3">
                    @if ($r->foto && Storage::disk('public')->exists('recruitment/foto/' . $r->foto))
                        <img src="{{ asset('storage/recruitment/foto/' . $r->foto) }}"
                            class="rounded-circle border border-3 shadow"
                            style="width:110px;height:110px;object-fit:cover;" alt="foto">
                    @else
                        <div class="rounded-circle bg-label-primary mx-auto d-flex align-items-center justify-content-center shadow"
                            style="width:110px;height:110px;font-size:42px;">
                            <i class="ti ti-user"></i>
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 badge bg-{{ $sc['color'] }} rounded-pill border border-2 border-white p-1">
                        <i class="ti {{ $sc['icon'] }}"></i>
                    </span>
                </div>
                <h6 class="fw-bold mb-0">{{ $r->nama_lengkap }}</h6>
                <small class="text-muted d-block mb-2">{{ $r->posisi_dilamar }}</small>
                <span class="badge bg-{{ $sc['color'] }} mb-2 px-3 py-1" style="font-size:12px;">
                    <i class="ti {{ $sc['icon'] }} me-1"></i>{{ $sc['label'] }}
                </span>
                <div><span class="badge bg-label-secondary" style="font-size:11px;">{{ $r->kode_recruitment }}</span></div>
            </div>

            {{-- Kontak --}}
            <div class="border-top px-3 py-3">
                <p class="text-muted fw-semibold mb-2" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Kontak</p>
                <div class="d-flex align-items-start mb-2 gap-2">
                    <i class="ti ti-phone text-primary mt-1" style="font-size:15px;min-width:16px;"></i>
                    <span class="small">{{ $r->no_hp ?? '-' }}</span>
                </div>
                <div class="d-flex align-items-start mb-2 gap-2">
                    <i class="ti ti-mail text-primary mt-1" style="font-size:15px;min-width:16px;"></i>
                    <span class="small">{{ $r->email ?? '-' }}</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-map-pin text-primary mt-1" style="font-size:15px;min-width:16px;"></i>
                    <span class="small">{{ $r->alamat ?? '-' }}</span>
                </div>
            </div>

            {{-- Personal --}}
            <div class="border-top px-3 py-3">
                <p class="text-muted fw-semibold mb-2" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Personal</p>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted ps-0" width="80">TTL</td>
                        <td class="fw-medium">{{ $r->tempat_lahir ?? '' }}{{ ($r->tempat_lahir && $r->tanggal_lahir) ? ', ' : '' }}{{ $r->tanggal_lahir?->format('d M Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Kelamin</td>
                        <td class="fw-medium">{{ $r->jenis_kelamin == 'L' ? 'Laki-laki' : ($r->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Agama</td>
                        <td class="fw-medium">{{ $r->agama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Kawin</td>
                        <td class="fw-medium">{{ $r->status_kawin ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">No. KTP</td>
                        <td class="fw-medium">{{ $r->no_ktp ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            {{-- Dokumen --}}
            <div class="border-top px-3 py-3">
                <p class="text-muted fw-semibold mb-2" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Dokumen</p>
                @if ($r->cv)
                    <a href="{{ asset('storage/recruitment/cv/' . $r->cv) }}" target="_blank"
                        class="btn btn-sm btn-outline-primary w-100 mb-2">
                        <i class="ti ti-file-text me-1"></i> Download CV
                    </a>
                @else
                    <div class="text-muted small mb-2"><i class="ti ti-file-off me-1"></i>CV tidak diupload</div>
                @endif
                @if ($r->ijazah)
                    <a href="{{ asset('storage/recruitment/ijazah/' . $r->ijazah) }}" target="_blank"
                        class="btn btn-sm btn-outline-success w-100">
                        <i class="ti ti-photo me-1"></i> Lihat Pas Foto Terbaru
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN --}}
    <div class="col-xl-9 col-lg-8 col-md-7 col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-bottom-0 bg-white pt-3 pb-0 px-4">
                <ul class="nav nav-tabs border-bottom-0" id="tabDetail" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-flex align-items-center gap-1 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-biodata" type="button">
                            <i class="ti ti-user"></i> Biodata
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-1 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-lamaran" type="button">
                            <i class="ti ti-briefcase"></i> Lamaran
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-1 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-proses" type="button">
                            <i class="ti ti-adjustments"></i> Proses HR
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body tab-content px-4 pt-4 pb-4">

                {{-- TAB BIODATA --}}
                <div class="tab-pane fade show active" id="tab-biodata" role="tabpanel">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle bg-label-primary d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;">
                            <i class="ti ti-school text-primary"></i>
                        </div>
                        <span class="fw-bold">Pendidikan Terakhir</span>
                    </div>
                    <div class="row g-3 mb-4 ps-1">
                        <div class="col-sm-3">
                            <div class="text-muted small mb-1">Jenjang</div>
                            <div class="fw-semibold">{{ $r->pendidikan_terakhir ?? '-' }}</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="text-muted small mb-1">Jurusan</div>
                            <div class="fw-semibold">{{ $r->jurusan ?? '-' }}</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-muted small mb-1">Institusi</div>
                            <div class="fw-semibold">{{ $r->nama_institusi ?? '-' }}</div>
                        </div>
                        <div class="col-sm-2">
                            <div class="text-muted small mb-1">Tahun Lulus</div>
                            <div class="fw-semibold">{{ $r->tahun_lulus ?? '-' }}</div>
                        </div>
                    </div>

                    @if ($r->pengalaman_kerja)
                        <hr class="my-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-label-warning d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;">
                                <i class="ti ti-building-skyscraper text-warning"></i>
                            </div>
                            <span class="fw-bold">Pengalaman Kerja</span>
                        </div>
                        <div class="bg-light rounded p-3 mb-3" style="white-space:pre-line;font-size:14px;">{{ $r->pengalaman_kerja }}</div>
                    @endif

                    @if ($r->keahlian)
                        <hr class="my-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-label-success d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;">
                                <i class="ti ti-award text-success"></i>
                            </div>
                            <span class="fw-bold">Keahlian / Skill</span>
                        </div>
                        <div class="bg-light rounded p-3" style="white-space:pre-line;font-size:14px;">{{ $r->keahlian }}</div>
                    @endif
                </div>

                {{-- TAB LAMARAN --}}
                <div class="tab-pane fade" id="tab-lamaran" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light h-100">
                                <div class="text-muted small mb-1"><i class="ti ti-briefcase me-1"></i>Posisi Dilamar</div>
                                <div class="fw-bold fs-6">{{ $r->posisi_dilamar }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light h-100">
                                <div class="text-muted small mb-1"><i class="ti ti-calendar me-1"></i>Tanggal Melamar</div>
                                <div class="fw-bold">{{ $r->tanggal_melamar?->format('d F Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light h-100">
                                <div class="text-muted small mb-1"><i class="ti ti-building me-1"></i>Cabang Tujuan</div>
                                <div class="fw-semibold">{{ $r->cabang->nama_cabang ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light h-100">
                                <div class="text-muted small mb-1"><i class="ti ti-layout-grid me-1"></i>Departemen</div>
                                <div class="fw-semibold">{{ $r->departemen->nama_dept ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded bg-light h-100">
                                <div class="text-muted small mb-1"><i class="ti ti-id-badge me-1"></i>Jabatan</div>
                                <div class="fw-semibold">{{ $r->jabatan->nama_jabatan ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light h-100">
                                <div class="text-muted small mb-1"><i class="ti ti-calendar-check me-1"></i>Bisa Mulai Bekerja</div>
                                <div class="fw-semibold">{{ $r->tanggal_tersedia?->format('d F Y') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB PROSES HR --}}
                <div class="tab-pane fade" id="tab-proses" role="tabpanel">
                    <form action="{{ route('recruitment.updateStatus', $r->id) }}" method="POST" id="formUpdateStatus">
                        @csrf
                        @method('PATCH')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Lamaran</label>
                                <select name="status" id="statusSelect" class="form-select">
                                    @foreach(['pending' => 'Pending','review' => 'Review','interview' => 'Interview','offering' => 'Penawaran','diterima' => 'Diterima','ditolak' => 'Ditolak'] as $key => $lbl)
                                        <option value="{{ $key }}" {{ $r->status == $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="rowInterview">
                                <label class="form-label fw-semibold">Tanggal Interview</label>
                                <input type="text" name="tanggal_interview" id="tanggal_interview"
                                    class="form-control flatpickr-date"
                                    value="{{ $r->tanggal_interview?->format('Y-m-d') }}"
                                    placeholder="Pilih tanggal" autocomplete="off">
                            </div>
                            <div class="col-md-6" id="rowJamInterview">
                                <label class="form-label fw-semibold">Jam Interview</label>
                                <input type="time" name="jam_interview" id="jam_interview"
                                    class="form-control"
                                    value="{{ $r->jam_interview ? \Carbon\Carbon::parse($r->jam_interview)->format('H:i') : '' }}">
                            </div>

                            {{-- Status Konfirmasi Kehadiran --}}
                            @if($r->status === 'interview' && $r->token_konfirmasi)
                            <div class="col-12" id="rowKonfirmasi">
                                <label class="form-label fw-semibold">Status Konfirmasi Kehadiran</label>
                                @if($r->konfirmasi_interview === 'hadir')
                                    <div class="alert alert-success py-2 mb-0">
                                        <i class="ti ti-circle-check me-1"></i>
                                        <strong>Hadir</strong> — dikonfirmasi pada {{ $r->konfirmasi_at?->format('d/m/Y H:i') }}
                                    </div>
                                @elseif($r->konfirmasi_interview === 'tidak_hadir')
                                    <div class="alert alert-danger py-2 mb-0">
                                        <i class="ti ti-circle-x me-1"></i>
                                        <strong>Tidak Hadir</strong> — dikonfirmasi pada {{ $r->konfirmasi_at?->format('d/m/Y H:i') }}
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 mb-0">
                                        <i class="ti ti-clock me-1"></i> Belum ada konfirmasi dari pelamar
                                    </div>
                                @endif
                            </div>
                            @endif
                            <div class="col-12" id="rowCatatanInterview">
                                <label class="form-label fw-semibold">Catatan Interview</label>
                                <textarea name="catatan_interview" class="form-control" rows="3"
                                    placeholder="Hasil/catatan selama sesi interview">{{ $r->catatan_interview }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan HR <small class="text-muted fw-normal">(Internal)</small></label>
                                <textarea name="catatan_hr" class="form-control" rows="3"
                                    placeholder="Catatan internal HR, tidak terlihat oleh pelamar">{{ $r->catatan_hr }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($r->prosesOleh)
                        <div class="mt-4 pt-3 border-top d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-label-secondary d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;">
                                <i class="ti ti-user-check text-secondary"></i>
                            </div>
                            <small class="text-muted">
                                Terakhir diproses oleh <strong>{{ $r->prosesOleh->name }}</strong>
                                &mdash; {{ $r->updated_at->format('d M Y, H:i') }} WIB
                            </small>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
            <div class="d-flex gap-2">
                @can('recruitment.edit')
                    <a href="{{ route('recruitment.edit', $r->id) }}" class="btn btn-outline-primary">
                        <i class="ti ti-pencil me-1"></i> Edit Data
                    </a>
                @endcan
                @can('recruitment.delete')
                    <button type="button" class="btn btn-outline-danger btnHapus"
                        data-id="{{ $r->id }}" data-nama="{{ $r->nama_lengkap }}">
                        <i class="ti ti-trash me-1"></i> Hapus Lamaran
                    </button>
                @endcan
            </div>
        </div>
    </div>

</div>

<form id="formHapus" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection
@push('myscript')
<script>
    $(function () {
        flatpickr('#tanggal_interview', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
        });

        function toggleInterviewFields() {
            const val = $('#statusSelect').val();
            const showInterview = ['interview', 'offering', 'diterima'].includes(val);
            const showJam       = val === 'interview';
            const showCatatan   = ['interview', 'offering', 'diterima', 'ditolak'].includes(val);
            $('#rowInterview').toggle(showInterview);
            $('#rowJamInterview').toggle(showJam);
            $('#rowCatatanInterview').toggle(showCatatan);
        }

        toggleInterviewFields();
        $('#statusSelect').on('change', toggleInterviewFields);

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 2500,
                showConfirmButton: false,
            });
        @endif

        $(document).on('click', '.btnHapus', function () {
            const id   = $(this).data('id');
            const nama = $(this).data('nama');
            Swal.fire({
                title: 'Hapus Lamaran?',
                html: `Data lamaran <strong>${nama}</strong> akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ti ti-trash me-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ea5455',
                cancelButtonColor: '#6c757d',
            }).then(r => {
                if (r.isConfirmed) {
                    $('#formHapus').attr('action', `/recruitment/${id}`).submit();
                }
            });
        });
    });
</script>
@endpush
