@extends('layouts.app')
@section('titlepage', 'Karyawan')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Karyawan
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen data master karyawan, unit kerja, jabatan, dan konfigurasi akses operasional.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="ti ti-home-2 ti-xs"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);">
                        <i class="ti ti-database ti-xs me-1"></i> Data Master
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-users ti-xs me-1"></i> Karyawan
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                @can('karyawan.create')
                    <a href="#" class="btn btn-primary" id="btnCreate"><i class="fa fa-plus me-2"></i> Tambah
                        Karyawan</a>
                    <a href="{{ route('karyawan.export', request()->query()) }}" class="btn btn-success"><i class="ti ti-file-export me-2"></i> Export Excel</a>
                    <a href="#" class="btn btn-success" id="btnImport"><i class="ti ti-file-import me-2"></i> Import Excel</a>
                    @can('users.create')
                        <a href="{{ route('karyawan.generatealluser') }}" class="btn btn-warning"><i class="ti ti-user-plus me-2"></i> Buat User (All)</a>
                    @endcan
                    @can('karyawan.edit')
                        <div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-settings me-2"></i> Pengaturan Presensi (All)
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item btn-bulk-action" href="#" data-action="lock_location"><i class="ti ti-map-pin me-2 text-danger"></i> Lock Location All</a></li>
                                <li><a class="dropdown-item btn-bulk-action" href="#" data-action="unlock_location"><i class="ti ti-map-pin-off me-2 text-success"></i> Unlock Location All</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item btn-bulk-action" href="#" data-action="lock_jam_kerja"><i class="ti ti-clock me-2 text-danger"></i> Lock Jam Kerja All</a></li>
                                <li><a class="dropdown-item btn-bulk-action" href="#" data-action="unlock_jam_kerja"><i class="ti ti-clock-pause me-2 text-success"></i> Unlock Jam Kerja All</a></li>
                            </ul>
                        </div>
                        <form id="formBulkAction" action="{{ route('karyawan.bulklockunlock') }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="bulk_action" id="bulk_action_input">
                            <input type="hidden" name="nama_karyawan" value="{{ Request('nama_karyawan') }}">
                            <input type="hidden" name="kode_cabang" value="{{ Request('kode_cabang') }}">
                            <input type="hidden" name="kode_dept" value="{{ Request('kode_dept') }}">
                            <input type="hidden" name="sub_departemen" value="{{ Request('sub_departemen') }}">
                            <input type="hidden" name="status_aktif" value="{{ Request('status_aktif') }}">
                        </form>
                    @endcan
                @endcan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <form id="formSearch" action="{{ route('karyawan.index') }}" method="GET">
                            <div class="row g-2">
                                <div class="col-lg-3 col-sm-12 col-md-12">
                                    <x-input-with-icon label="Cari Nama / NIK" value="{{ Request('nama_karyawan') }}" name="nama_karyawan"
                                        icon="ti ti-search" hideLabel placeholder="Cari Nama / NIK..." />
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <x-select label="Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang"
                                        selected="{{ Request('kode_cabang') }}" hideLabel />
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <x-select label="Departemen" name="kode_dept" :data="$departemen" key="kode_dept" textShow="nama_dept"
                                        selected="{{ Request('kode_dept') }}" upperCase="true" hideLabel />
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="form-group mb-0">
                                        <select name="sub_departemen" id="sub_departemen" class="form-select" style="border-radius: 0.375rem;">
                                            <option value="">Pilih Team</option>
                                            @php
                                                // Get unique sub_departemen from filtered karyawan
                                                $subDepartemens = collect($karyawan->items())
                                                    ->pluck('sub_departemen')
                                                    ->filter()
                                                    ->unique()
                                                    ->sort()
                                                    ->values();
                                            @endphp
                                            @foreach ($subDepartemens as $sub)
                                                <option value="{{ $sub }}" {{ Request('sub_departemen') == $sub ? 'selected' : '' }}>
                                                    {{ $sub }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <div class="form-group mb-0">
                                        <select name="status_aktif" class="form-select" style="border-radius: 0.375rem;">
                                            <option value="">Status Karyawan</option>
                                            <option value="1" {{ Request('status_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ Request('status_aktif') === '0' ? 'selected' : '' }}>Non Aktif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-1 col-sm-12 col-md-12">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ti ti-search"></i></button>
                                        <a href="{{ route('karyawan.index') }}" class="btn btn-secondary"><i class="ti ti-x"></i></a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">

                        <div class="row">
                            <div class="col-12">
                                @foreach ($karyawan as $d)
                                    <div class="card mb-2 shadow-sm border">
                                        <div class="card-body p-2">
                                            <div class="row align-items-center">
                                                <!-- Avatar -->
                                                <div class="col-md-1 text-center">
                                                    @if (!empty($d->foto) && Storage::disk('public')->exists('/karyawan/' . $d->foto))
                                                        <img src="{{ getfotoKaryawan($d->foto) }}" alt="Avatar"
                                                            class="rounded-circle"
                                                            style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e9ecef;">
                                                    @else
                                                        <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}"
                                                            alt="No Image" class="rounded-circle"
                                                            style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e9ecef;">
                                                    @endif
                                                </div>
                                                <!-- Identity -->
                                                <div class="col-md-4">
                                                    <div class="fw-bold text-dark" style="font-size: 14px;">
                                                        {{ $d->nama_karyawan }}
                                                        <span class="text-muted fw-normal" style="font-size: 12px;">({{ $d->nik_show ?? $d->nik }})</span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-label-primary" style="font-size: 10px;">{{ $d->nama_jabatan }}</span>
                                                        <span class="badge bg-label-info" style="font-size: 10px;">{{ $d->nama_dept }}</span>
                                                        <span class="badge bg-label-warning" style="font-size: 10px;">{{ $d->nama_cabang }}</span>
                                                        @if ($d->status_karyawan)
                                                  @php
    $status_karyawan_text = $d->status_karyawan == 'K' ? 'Kontrak' 
        : ($d->status_karyawan == 'T' ? 'Tetap' 
        : ($d->status_karyawan == 'M' ? 'Mitra' 
        : ($d->status_karyawan == 'O' ? 'Outsourcing' 
        : $d->status_karyawan)));
    $badge_class = $d->status_karyawan == 'T' ? 'bg-label-success' : 'bg-label-primary';
@endphp
                                                            <span class="badge {{ $badge_class }}" style="font-size: 10px;">{{ $status_karyawan_text }}</span>
                                                        @endif
                                                        @if ($d->jenis_upah)
                                                            @php
                                                                $upah_class = $d->jenis_upah == 'Harian' ? 'bg-label-info' : 'bg-label-success';
                                                            @endphp
                                                            <span class="badge {{ $upah_class }}" style="font-size: 10px;">{{ $d->jenis_upah }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <!-- Status & Date -->
                                                <div class="col-md-3 border-start border-end d-none d-md-block text-center">
                                                    <div class="mb-1">
                                                        @if ($d->status_aktif_karyawan == '1')
                                                            <span class="badge bg-success py-1 px-2" style="font-size: 10px;">Aktif</span>
                                                        @else
                                                            <span class="badge bg-danger py-1 px-2" style="font-size: 10px;">Non Aktif</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted" style="font-size: 11px;">
                                                        Masuk: {{ date('d-m-Y', strtotime($d->tanggal_masuk)) }}
                                                    </div>
                                                    <div class="text-muted" style="font-size: 10px;">
                                                        @php
                                                            $awal = new DateTime($d->tanggal_masuk);
                                                            $akhir = new DateTime();
                                                            $masa_kerja = $akhir->diff($awal);
                                                        @endphp
                                                        {{ $masa_kerja->y . ' Th ' . $masa_kerja->m . ' Bln' }}
                                                    </div>
                                                </div>
                                                <!-- Locks -->
                                                <div class="col-md-2 text-center d-flex justify-content-center gap-3">
                                                    <div class="text-center">
                                                        @if ($d->lock_location == '1')
                                                            <a href="{{ route('karyawan.lockunlocklocation', Crypt::encrypt($d->nik)) }}"
                                                                data-bs-toggle="tooltip" title="Unlock Location">
                                                                <i class="ti ti-lock text-danger fs-5"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('karyawan.lockunlocklocation', Crypt::encrypt($d->nik)) }}"
                                                                data-bs-toggle="tooltip" title="Lock Location">
                                                                <i class="ti ti-lock-open text-success fs-5"></i>
                                                            </a>
                                                        @endif
                                                        <div class="d-block text-muted" style="font-size: 9px;">Location</div>
                                                    </div>
                                                    <div class="text-center">
                                                        @if ($d->lock_jam_kerja == '1')
                                                            <a href="{{ route('karyawan.lockunlockjamkerja', Crypt::encrypt($d->nik)) }}"
                                                                data-bs-toggle="tooltip" title="Unlock Jam Kerja">
                                                                <i class="ti ti-lock text-danger fs-5"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('karyawan.lockunlockjamkerja', Crypt::encrypt($d->nik)) }}"
                                                                data-bs-toggle="tooltip" title="Lock Jam Kerja">
                                                                <i class="ti ti-lock-open text-success fs-5"></i>
                                                            </a>
                                                        @endif
                                                        <div class="d-block text-muted" style="font-size: 9px;">Jam Kerja</div>
                                                    </div>
                                                </div>
                                                <!-- Actions -->
                                                <div class="col-md-2 text-end">
                                                    <div class="d-flex flex-column align-items-end gap-1">
                                                        <div class="btn-group shadow-sm" role="group">
                                                            @can('karyawan.setjamkerja')
                                                                <a href="#" class="btn btn-sm btn-outline-secondary btnSetJamkerja py-1 px-2"
                                                                    nik="{{ Crypt::encrypt($d->nik) }}" title="Set Jam Kerja">
                                                                    <i class="ti ti-device-watch"></i>
                                                                </a>
                                                            @endcan
                                                            @can('karyawan.setcabang')
                                                                <a href="#" class="btn btn-sm btn-outline-secondary btnSetCabang py-1 px-2"
                                                                    nik="{{ Crypt::encrypt($d->nik) }}" title="Set Cabang">
                                                                    <i class="ti ti-map"></i>
                                                                </a>
                                                            @endcan
                                                            @can('karyawan.edit')
                                                                <a href="#" class="btn btn-sm btn-outline-primary btnEdit py-1 px-2"
                                                                    nik="{{ Crypt::encrypt($d->nik) }}" title="Edit">
                                                                    <i class="ti ti-edit"></i>
                                                                </a>
                                                            @endcan
                                                            @can('karyawan.show')
                                                                <a href="{{ route('karyawan.show', Crypt::encrypt($d->nik)) }}"
                                                                    class="btn btn-sm btn-outline-info py-1 px-2" title="Detail">
                                                                    <i class="ti ti-file-description"></i>
                                                                </a>
                                                            @endcan
                                                            @can('karyawan.delete')
                                                                <form method="POST" name="deleteform" class="deleteform d-inline"
                                                                    action="{{ route('karyawan.delete', Crypt::encrypt($d->nik)) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger delete-confirm rounded-0 rounded-end py-1 px-2"
                                                                        title="Delete">
                                                                        <i class="ti ti-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                        @can('users.create')
                                                            @if (empty($d->id_user))
                                                                <a href="{{ route('karyawan.createuser', Crypt::encrypt($d->nik)) }}"
                                                                    class="btn btn-sm btn-danger py-0 px-2" style="font-size: 10px;">
                                                                    <i class="ti ti-user-plus me-1"></i> Buat User
                                                                </a>
                                                            @else
                                                                <a href="{{ route('karyawan.deleteuser', Crypt::encrypt($d->nik)) }}"
                                                                    class="btn btn-sm btn-success py-0 px-2" style="font-size: 10px;">
                                                                    <i class="ti ti-user me-1"></i> Hapus User
                                                                </a>
                                                            @endif
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div style="float: right;">
                            {{ $karyawan->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<x-modal-form id="modal" show="loadmodal" />
<x-modal-form id="modalSetJamkerja" show="loadmodalSetJamkerja" size="modal-lg" title="Set Jam Kerja" />
<x-modal-form id="modalSetCabang" show="loadmodalSetCabang" size="modal-lg" title="Set Cabang Karyawan" />
<x-modal-form id="modalImport" show="loadmodalImport" size="modal-lg" title="Import Data Karyawan" />
@endsection
@push('myscript')
<script>
    $(function() {

        // ========== FORM SEARCH ==========
        // Use native HTML form submit - no JS override needed
        // ========== END FORM SEARCH ==========

        // ========== DYNAMIC TEAM DROPDOWN - INDEX KARYAWAN ==========
        $('#kode_dept').change(function() {
            var kodeDept = $(this).val();
            var teamSelect = $('#sub_departemen');
            var currentSelected = "{{ Request('sub_departemen') }}";
            
            teamSelect.html('<option value="">Pilih Team</option>');
            
            if (!kodeDept) {
                return;
            }
            
            // Fetch team dari API
            $.ajax({
                url: `/api/departemen/${kodeDept}/sub-departemen`,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success && response.sub_departemen && Array.isArray(response.sub_departemen) && response.sub_departemen.length > 0) {
                        response.sub_departemen.forEach(function(team) {
                            var selected = (currentSelected === team) ? 'selected' : '';
                            teamSelect.append(`<option value="${team}" ${selected}>${team}</option>`);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading teams:', error);
                }
            });
        });
        
        // Trigger on page load if department is already selected
        if ($('#kode_dept').val()) {
            $('#kode_dept').trigger('change');
        }
        // ========== END DYNAMIC TEAM DROPDOWN ==========

        // ========== DYNAMIC TEAM DROPDOWN FOR MODAL CREATE/EDIT ==========
        // Menggunakan event delegation karena form di-load via AJAX
        $(document).on('change', '#modal #kode_dept, #modalSetCabang #kode_dept', function() {
            var kodeDept = $(this).val();
            var modal = $(this).closest('.modal');
            var subDeptContainer = modal.find('#subDepartemenContainer');
            var subDeptSelect = modal.find('#sub_departemen');
            
            subDeptSelect.html('<option value="">Pilih Team</option>');
            
            if (!kodeDept) {
                subDeptContainer.hide();
                return;
            }
            
            subDeptContainer.show();
            
            // Fetch teams dari API
            $.ajax({
                url: `/api/departemen/${kodeDept}/sub-departemen`,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success && response.sub_departemen && Array.isArray(response.sub_departemen) && response.sub_departemen.length > 0) {
                        // Get current sub_departemen for edit form
                        var currentSubDept = window.currentSubDept || '';
                        
                        response.sub_departemen.forEach(function(team) {
                            var isSelected = currentSubDept === team ? 'selected' : '';
                            subDeptSelect.append(`<option value="${team}" ${isSelected}>${team}</option>`);
                        });
                        
                        subDeptContainer.show();
                    } else {
                        subDeptContainer.hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading teams:', error);
                    subDeptContainer.hide();
                }
            });
        });
        // ========== END MODAL DYNAMIC TEAM DROPDOWN ==========

        function loading() {
            $("#loadmodal").html(`<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            </div>`);
        };
        loading();
        $("#btnCreate").click(function() {
            $("#modal").modal("show");
            $(".modal-title").text("Tambah Data Karyawan");
            $("#loadmodal").load("{{ route('karyawan.create') }}", function() {
                console.log('✓ Create form loaded');
                // Tidak perlu trigger karena belum ada departemen terpilih
            });
        });

        $("#btnImport").click(function() {
            $("#modalImport").modal("show");
            $("#loadmodalImport").html(`<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            </div>`);
            $("#loadmodalImport").load("{{ route('karyawan.import') }}");
        });

        $(".btnEdit").click(function() {
            loading();
            const nik = $(this).attr("nik");
            $("#modal").modal("show");
            $(".modal-title").text("Edit Data Karyawan");
            $("#loadmodal").load(`/karyawan/${nik}/edit`, function() {
                console.log('✓ Edit form loaded');
                // Trigger change untuk load teams jika ada departemen terpilih
                setTimeout(function() {
                    var kodeDept = $('#modal #kode_dept').val();
                    if (kodeDept) {
                        console.log('Triggering change for pre-selected dept:', kodeDept);
                        $('#modal #kode_dept').trigger('change');
                    }
                }, 500); // Delay untuk memastikan DOM ready
            });
        });

        $(".btnSetJamkerja").click(function() {
            const nik = $(this).attr("nik");
            $("#modalSetJamkerja").modal("show");
            $("#loadmodalSetJamkerja").html(`<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            </div>`);

            $("#loadmodalSetJamkerja").load(`/karyawan/${nik}/setjamkerja`);
        });

        $(".btnSetCabang").click(function() {
            const nik = $(this).attr("nik");
            $("#modalSetCabang").modal("show");
            $("#loadmodalSetCabang").html(`<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            </div>`);

            $("#loadmodalSetCabang").load(`/karyawan/${nik}/setcabang`);
        });

        $('.btn-bulk-action').on('click', function(e) {
            e.preventDefault();
            let action = $(this).data('action');
            let title = $(this).text().trim();
            
            Swal.fire({
                title: "Apakah Anda Yakin?",
                text: "Anda akan mengeksekusi: " + title + " untuk seluruh karyawan yang difilter.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Lanjutkan!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#bulk_action_input').val(action);
                    $('#formBulkAction').submit();
                }
            });
        });

    });
</script>
@endpush
