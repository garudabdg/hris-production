@extends('layouts.app')
@section('titlepage', 'Tambah Threat Intelligence Report')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Threat Intelligence Report</h1>
        <a href="{{ route('threat-intelligence-reports.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tir-card shadow mb-4">
        <div class="tir-header">
            <h6 class="m-0 font-weight-bold text-white">Form Input Report</h6>
        </div>
        <div class="tir-body">
            <form action="{{ route('threat-intelligence-reports.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select form-control" id="status" name="status" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Tidak ada masalah" {{ old('status') == 'Tidak ada masalah' ? 'selected' : '' }}>Tidak ada masalah</option>
                            <option value="Ada masalah" {{ old('status') == 'Ada masalah' ? 'selected' : '' }}>Ada masalah</option>
                            <option value="Investigating" {{ old('status') == 'Investigating' ? 'selected' : '' }}>Investigating</option>
                            <option value="Resolved" {{ old('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jenis_ancaman" class="form-label">Jenis Ancaman</label>
                        <input type="text" class="form-control" id="jenis_ancaman" name="jenis_ancaman" value="{{ old('jenis_ancaman', request('judul')) }}" placeholder="Contoh: Malware, Phishing, Blue Screen..." required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sumber_ancaman" class="form-label">Sumber Ancaman</label>
                        <input type="text" class="form-control" id="sumber_ancaman" name="sumber_ancaman" value="{{ old('sumber_ancaman') }}" placeholder="Contoh: Email, Sistem Operasi, User..." required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi_insiden" class="form-label">Deskripsi Insiden</label>
                    <textarea class="form-control" id="deskripsi_insiden" name="deskripsi_insiden" rows="3" required>{{ old('deskripsi_insiden', request('deskripsi')) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="dampak" class="form-label">Dampak</label>
                    <textarea class="form-control" id="dampak" name="dampak" rows="3" required>{{ old('dampak') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="tindakan_yang_diambil" class="form-label">Tindakan yang diambil</label>
                    <textarea class="form-control" id="tindakan_yang_diambil" name="tindakan_yang_diambil" rows="3" required>{{ old('tindakan_yang_diambil') }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<style>
    <?php include public_path('assets/css/threat-intelligence-report.css'); ?>
</style>
@endpush
