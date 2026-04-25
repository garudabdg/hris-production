@extends('layouts.app')
@section('titlepage', 'Edit Pelamar')

@section('content')
@section('navigasi')
    <a href="{{ route('recruitment.index') }}">Recruitment</a>
    <span> / Edit Pelamar</span>
@endsection

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
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

                <form action="{{ route('recruitment.update', $recruitment->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $recruitment->nama_lengkap) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $recruitment->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $recruitment->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="text" name="tanggal_lahir" class="form-control flatpickr-date" value="{{ old('tanggal_lahir', $recruitment->tanggal_lahir?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $recruitment->no_hp) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $recruitment->email) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $recruitment->alamat) }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cabang</label>
                            <select name="kode_cabang" class="form-select select2">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($cabang as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ old('kode_cabang', $recruitment->kode_cabang) == $c->kode_cabang ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Departemen</label>
                            <select name="kode_dept" class="form-select select2">
                                <option value="">-- Pilih Dept --</option>
                                @foreach($departemen as $d)
                                    <option value="{{ $d->kode_dept }}" {{ old('kode_dept', $recruitment->kode_dept) == $d->kode_dept ? 'selected' : '' }}>{{ $d->nama_dept }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jabatan</label>
                            <select name="kode_jabatan" class="form-select select2">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($jabatan as $j)
                                    <option value="{{ $j->kode_jabatan }}" {{ old('kode_jabatan', $recruitment->kode_jabatan) == $j->kode_jabatan ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Posisi Dilamar</label>
                            <input type="text" name="posisi_dilamar" class="form-control" value="{{ old('posisi_dilamar', $recruitment->posisi_dilamar) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tanggal Tersedia</label>
                            <input type="text" name="tanggal_tersedia" class="form-control flatpickr-date" value="{{ old('tanggal_tersedia', $recruitment->tanggal_tersedia?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ekspektasi Gaji</label>
                            <input type="text" name="ekspektasi_gaji" class="form-control" value="{{ old('ekspektasi_gaji', $recruitment->ekspektasi_gaji) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <select name="pendidikan_terakhir" class="form-select">
                                <option value="">Pilih</option>
                                @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'] as $p)
                                    <option value="{{ $p }}" {{ old('pendidikan_terakhir', $recruitment->pendidikan_terakhir) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Pengalaman Kerja</label>
                            <textarea name="pengalaman_kerja" class="form-control" rows="4">{{ old('pengalaman_kerja', $recruitment->pengalaman_kerja) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Keahlian</label>
                            <textarea name="keahlian" class="form-control" rows="3">{{ old('keahlian', $recruitment->keahlian) }}</textarea>
                        </div>

                        {{-- Files --}}
                        <div class="col-md-4">
                            <label class="form-label">Ganti Foto (opsional)</label>
                            <input type="file" name="foto" class="form-control">
                            @if($recruitment->foto)
                                <small class="text-muted">Saat ini: <a href="{{ asset('storage/recruitment/foto/' . $recruitment->foto) }}" target="_blank">Lihat</a></small>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ganti CV (opsional)</label>
                            <input type="file" name="cv" class="form-control">
                            @if($recruitment->cv)
                                <small class="text-muted">Saat ini: <a href="{{ asset('storage/recruitment/cv/' . $recruitment->cv) }}" target="_blank">Download</a></small>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ganti Pas Foto Terbaru (opsional)</label>
                            <input type="file" name="ijazah" class="form-control">
                            @if($recruitment->ijazah)
                                <small class="text-muted">Saat ini: <a href="{{ asset('storage/recruitment/ijazah/' . $recruitment->ijazah) }}" target="_blank">Lihat</a></small>
                            @endif
                        </div>

                        <div class="col-12 text-end mt-3">
                            <a href="{{ route('recruitment.show', $recruitment->id) }}" class="btn btn-outline-secondary">Batal</a>
                            <button class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('myscript')
<script>
    $(function () {
        flatpickr('.flatpickr-date', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y' });
        $('.select2').select2({ width: '100%' });
    });
</script>
@endpush

@endsection
