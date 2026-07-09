@extends('layouts.app')
@section('titlepage', 'Edit Data Calon Nasabah')

@section('content')
@section('navigasi')
    <span>Edit Data Calon Nasabah</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('data-calon-nasabah.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Kembali
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('data-calon-nasabah.update', $nasabah->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal', $nasabah->tanggal) }}">
                            @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nama Nasabah <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required value="{{ old('nama', $nasabah->nama) }}">
                            @error('nama') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Lead <span class="text-danger">*</span></label>
                            <select name="status_lead" class="form-select" required>
                                <option value="cold" {{ old('status_lead', $nasabah->status_lead) == 'cold' ? 'selected' : '' }}>Cold</option>
                                <option value="warm" {{ old('status_lead', $nasabah->status_lead) == 'warm' ? 'selected' : '' }}>Warm</option>
                                <option value="hot" {{ old('status_lead', $nasabah->status_lead) == 'hot' ? 'selected' : '' }}>Hot</option>
                            </select>
                            @error('status_lead') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No WhatsApp</label>
                            <input type="text" name="no_whatsapp" class="form-control" value="{{ old('no_whatsapp', $nasabah->no_whatsapp) }}">
                            @error('no_whatsapp') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Akun Sosial Media (IG/FB/TikTok)</label>
                            <input type="text" name="akun_sosial_media" class="form-control" value="{{ old('akun_sosial_media', $nasabah->akun_sosial_media) }}">
                            @error('akun_sosial_media') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Keterangan / Catatan</label>
                            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $nasabah->keterangan) }}</textarea>
                            @error('keterangan') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-save me-2"></i>Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
