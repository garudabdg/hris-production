@extends('layouts.app')
@section('title', 'Edit ID Control List')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit ID Control List</h1>
        <a href="{{ route('id-control-list.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="id-control-card shadow mb-4">
        <div class="id-control-header">
            <h6 class="m-0 font-weight-bold">Form Edit ID Control List</h6>
        </div>
        <div class="id-control-body">
            <form action="{{ route('id-control-list.update', $idControlList->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Period <span class="text-danger">*</span></label>
                            <input type="date" name="period" class="form-control" value="{{ $idControlList->period }}" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Aplikasi (System Name) <span class="text-danger">*</span></label>
                            <select name="nama_aplikasi" class="form-select select2Aplikasi" required>
                                <option value="">Pilih Aplikasi...</option>
                                @foreach($aplikasis as $app)
                                    <option value="{{ $app->nama_aplikasi }}" {{ $idControlList->nama_aplikasi == $app->nama_aplikasi ? 'selected' : '' }}>{{ $app->kode_aplikasi }} - {{ $app->nama_aplikasi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Role / ID Name <span class="text-danger">*</span></label>
                            <input type="text" name="role" class="form-control" required value="{{ $idControlList->role }}">
                        </div>
                        <div class="form-group">
                            <label>Pilih Cabang / Lokasi</label>
                            <select name="location" id="kode_cabang" class="form-select select2Cabang">
                                <option value="">Semua Cabang</option>
                                @foreach($cabangs as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ $idControlList->location == $c->kode_cabang ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Pengguna / ID User <span class="text-danger">*</span></label>
                            <select name="nama_pengguna" id="nama_pengguna" class="form-select select2NamaPengguna" required>
                                <option value="">Pilih Karyawan...</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->nik }}" 
                                        data-divisi="{{ optional($k->departemen)->nama_dept }}"
                                        {{ $idControlList->nama_pengguna == $k->nik ? 'selected' : '' }}>
                                        {{ $k->nama_karyawan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Divisi</label>
                            <input type="text" name="division" id="division" class="form-control" value="{{ $idControlList->division }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Type ID</label>
                            <select name="type_id" class="form-control">
                                <option value="">Pilih Type ID...</option>
                                <option value="Operation User" {{ $idControlList->type_id == 'Operation User' ? 'selected' : '' }}>Operation User</option>
                                <option value="Admin" {{ $idControlList->type_id == 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="Guest" {{ $idControlList->type_id == 'Guest' ? 'selected' : '' }}>Guest</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Remarks</label>
                            <select name="remarks" class="form-control">
                                <option value="1" {{ $idControlList->remarks == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $idControlList->remarks == '0' ? 'selected' : '' }}>non-Active</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-custom">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<style>
    <?php include public_path('assets/css/id_control_list.css'); ?>
</style>
<script>
    window.IdControlConfig = {
        csrfToken: '{{ csrf_token() }}'
    };

    const allKaryawans = @json($karyawans);

    document.addEventListener('DOMContentLoaded', function() {
        const selectCabang = document.getElementById('kode_cabang');
        const selectPengguna = $('#nama_pengguna');
        const inputDivision = document.getElementById('division');

        if ($.fn.select2) {
            $('.select2Cabang').wrap('<div class="position-relative"></div>').select2({
                placeholder: 'Semua Cabang',
                allowClear: true,
                width: '100%'
            });
            $('.select2Aplikasi').wrap('<div class="position-relative"></div>').select2({
                placeholder: 'Pilih Aplikasi...',
                allowClear: true,
                width: '100%'
            });
            $('.select2NamaPengguna').wrap('<div class="position-relative"></div>').select2({
                placeholder: 'Pilih Karyawan...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('.select2NamaPengguna').parent(),
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    if (typeof data.text === 'undefined') return null;
                    if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) return data;
                    if (data.id && data.id.toLowerCase().indexOf(params.term.toLowerCase()) > -1) return data;
                    return null;
                }
            });
        }

        $(selectCabang).on('change', function() {
            let selectedCabang = $(this).val();
            
            // Simpan nilai lama sebelum dikosongkan
            let oldVal = selectPengguna.val();
            
            selectPengguna.empty();
            selectPengguna.append('<option value="">Pilih Karyawan...</option>');

            let filtered = selectedCabang ? allKaryawans.filter(k => k.kode_cabang == selectedCabang) : allKaryawans;

            filtered.forEach(k => {
                let divisi = k.departemen ? k.departemen.nama_dept : '';
                
                let opt = $('<option></option>').val(k.nik).text(k.nama_karyawan);
                opt.data('divisi', divisi);
                
                if(oldVal == k.nik) opt.prop('selected', true);
                selectPengguna.append(opt);
            });

            selectPengguna.trigger('change');
        });

        if (selectPengguna.length) {
            selectPengguna.on('change', function() {
                let selectedOption = $(this).find('option:selected');
                let divisi = selectedOption.data('divisi');
                
                inputDivision.value = divisi || '';
            });
        }
    });
</script>
<script src="{{ asset('assets/js/id_control_list.js') }}?v={{ time() }}"></script>
@endpush
