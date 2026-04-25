<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran Lamaran Kerja</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}">
    <style>
        body { background: #f4f5fb; }
        .form-section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
            padding-bottom: 6px;
            margin-bottom: 16px;
        }
        .foto-preview-wrap {
            width: 140px; height: 175px;
            border: 2px dashed #adb5bd;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            background: #f8f9fa;
            transition: border-color 0.2s;
        }
        .foto-preview-wrap:hover { border-color: var(--bs-primary); }
        .foto-preview-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .foto-preview-wrap .placeholder-text { color: #adb5bd; font-size: 12px; text-align: center; padding: 10px; }
        .cv-drop-zone {
            border: 2px dashed #adb5bd;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            background: #f8f9fa;
            transition: border-color 0.2s, background 0.2s;
        }
        .cv-drop-zone:hover, .cv-drop-zone.dragover { border-color: var(--bs-primary); background: #e8f0fe; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                {{-- Header --}}
                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-1"><i class="ti ti-briefcase me-2 text-primary"></i>Form Lamaran Kerja</h3>
                    <p class="text-muted">Isi data dengan lengkap dan benar. Semua field bertanda <span class="text-danger">*</span> wajib diisi.</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="ti ti-alert-circle me-2"></i><strong>Mohon perbaiki data berikut:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('recruitment.store') }}" method="POST" enctype="multipart/form-data" id="formLamaran" novalidate>
                    @csrf

                    <div class="card mb-4">
                        <div class="card-body">
                            {{-- ═══ PAS FOTO ═══ --}}
                            <div class="form-section-title"><i class="ti ti-camera me-1"></i>Pas Foto</div>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-start gap-4">
                                        <div>
                                            <div class="foto-preview-wrap" id="fotoWrap" onclick="document.getElementById('foto').click()">
                                                <img id="fotoPreview" src="" alt="" style="display:none;">
                                                <div class="placeholder-text" id="fotoPlaceholder">
                                                    <i class="ti ti-camera" style="font-size:28px;display:block;margin-bottom:6px;"></i>
                                                    Klik untuk upload<br>pas foto
                                                </div>
                                            </div>
                                            <input type="file" name="foto" id="foto" accept="image/*" class="d-none" required>
                                            @error('foto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="text-muted small mt-2">
                                            <ul class="ps-3">
                                                <li>Format: JPG, JPEG, PNG</li>
                                                <li>Ukuran maks: 2 MB</li>
                                                <li>Pas foto formal terbaru</li>
                                                <li>Wajah terlihat jelas</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ═══ DATA DIRI ═══ --}}
                            <div class="form-section-title"><i class="ti ti-user me-1"></i>Data Diri</div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror"
                                        value="{{ old('nama_lengkap') }}" placeholder="Sesuai KTP" required>
                                    @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                        <option value="">Pilih</option>
                                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Agama</label>
                                    <select name="agama" class="form-select">
                                        <option value="">Pilih</option>
                                        @foreach(['Islam','Kristen','Katolik','Hindu','Budha','Konghucu'] as $agama)
                                            <option value="{{ $agama }}" {{ old('agama') == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}" placeholder="Kota lahir">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="text" name="tanggal_lahir" id="tanggal_lahir" class="form-control flatpickr-date"
                                        value="{{ old('tanggal_lahir') }}" placeholder="dd/mm/yyyy" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status Perkawinan</label>
                                    <select name="status_kawin" class="form-select">
                                        <option value="">Pilih</option>
                                        <option value="Belum Kawin" {{ old('status_kawin') == 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                                        <option value="Kawin" {{ old('status_kawin') == 'Kawin' ? 'selected' : '' }}>Kawin</option>
                                        <option value="Cerai Hidup" {{ old('status_kawin') == 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                                        <option value="Cerai Mati" {{ old('status_kawin') == 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. KTP</label>
                                    <input type="text" name="no_ktp" class="form-control" value="{{ old('no_ktp') }}"
                                        placeholder="16 digit nomor KTP" maxlength="20">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                        <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                                            value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
                                        @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" placeholder="email@contoh.com">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea name="alamat" class="form-control" rows="2"
                                        placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota">{{ old('alamat') }}</textarea>
                                </div>
                            </div>

                            {{-- ═══ PENDIDIKAN ═══ --}}
                            <div class="form-section-title"><i class="ti ti-school me-1"></i>Riwayat Pendidikan</div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Pendidikan Terakhir</label>
                                    <select name="pendidikan_terakhir" class="form-select">
                                        <option value="">Pilih</option>
                                        @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'] as $p)
                                            <option value="{{ $p }}" {{ old('pendidikan_terakhir') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jurusan / Program Studi</label>
                                    <input type="text" name="jurusan" class="form-control" value="{{ old('jurusan') }}"
                                        placeholder="Contoh: Teknik Informatika">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Nama Sekolah / Universitas</label>
                                    <input type="text" name="nama_institusi" class="form-control" value="{{ old('nama_institusi') }}"
                                        placeholder="Nama institusi">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tahun Lulus</label>
                                    <input type="number" name="tahun_lulus" class="form-control" value="{{ old('tahun_lulus') }}"
                                        placeholder="{{ date('Y') }}" min="1990" max="{{ date('Y') }}">
                                </div>
                            </div>

                            {{-- ═══ PENGALAMAN & KEAHLIAN ═══ --}}
                            <div class="form-section-title"><i class="ti ti-award me-1"></i>Pengalaman & Keahlian</div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Pengalaman Kerja</label>
                                    <textarea name="pengalaman_kerja" class="form-control" rows="4"
                                        placeholder="Uraikan pengalaman kerja Anda (nama perusahaan, posisi, lama bekerja)">{{ old('pengalaman_kerja') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Keahlian / Skill</label>
                                    <textarea name="keahlian" class="form-control" rows="4"
                                        placeholder="Contoh: Microsoft Office, Bahasa Inggris, Mengemudi, dll">{{ old('keahlian') }}</textarea>
                                </div>
                            </div>

                            {{-- ═══ LAMARAN ═══ --}}
                            <div class="form-section-title"><i class="ti ti-briefcase me-1"></i>Informasi Lamaran</div>
                            @if($cabang->isEmpty())
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Belum ada lowongan yang tersedia saat ini.</strong> Silakan cek kembali di lain waktu.
                            </div>
                            @else
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Cabang Tujuan <span class="text-danger">*</span></label>
                                    <select name="kode_cabang" id="kode_cabang" class="form-select select2" required>
                                        <option value="">-- Pilih Cabang --</option>
                                        @foreach ($cabang as $c)
                                            <option value="{{ $c->kode_cabang }}" {{ old('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                                {{ textUpperCase($c->nama_cabang) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Posisi yang Dilamar <span class="text-danger">*</span></label>
                                    <select name="posisi_dilamar" id="posisi_dilamar" class="form-select @error('posisi_dilamar') is-invalid @enderror" required>
                                        <option value="">-- Pilih Cabang dulu --</option>
                                    </select>
                                    <input type="hidden" name="kode_dept" id="hidden_kode_dept">
                                    <input type="hidden" name="kode_jabatan" id="hidden_kode_jabatan">
                                    @error('posisi_dilamar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bisa Mulai Bekerja</label>
                                    <input type="text" name="tanggal_tersedia" id="tanggal_tersedia" class="form-control flatpickr-date"
                                        value="{{ old('tanggal_tersedia') }}" placeholder="dd/mm/yyyy" autocomplete="off">
                                </div>
                            </div>
                            @endif

                            {{-- ═══ DOKUMEN ═══ --}}
                            <div class="form-section-title"><i class="ti ti-paperclip me-1"></i>Upload Dokumen</div>
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Curriculum Vitae (CV)</label>
                                    <div class="cv-drop-zone" id="cvDropZone" onclick="document.getElementById('cv').click()">
                                        <i class="ti ti-file-text" style="font-size:32px;" id="cvIcon"></i>
                                        <div id="cvText" class="mt-2 text-muted">
                                            <strong>Klik untuk upload CV</strong><br>
                                            <small>PDF, DOC, DOCX — Maks. 5 MB</small>
                                        </div>
                                        <div id="cvFileName" class="mt-2 text-primary fw-semibold" style="display:none;"></div>
                                    </div>
                                    <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx" class="d-none">
                                    @error('cv')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pas Foto Terbaru</label>
                                    <div class="cv-drop-zone" id="ijazahDropZone" onclick="document.getElementById('ijazah').click()">
                                        <i class="ti ti-photo" style="font-size:32px;" id="ijazahIcon"></i>
                                        <div id="ijazahText" class="mt-2 text-muted">
                                            <strong>Klik untuk upload Pas Foto</strong><br>
                                            <small>JPG, JPEG, PNG — Maks. 2 MB</small>
                                        </div>
                                        <div id="ijazahFileName" class="mt-2 text-primary fw-semibold" style="display:none;"></div>
                                    </div>
                                    <input type="file" name="ijazah" id="ijazah" accept=".jpg,.jpeg,.png" class="d-none">
                                    @error('ijazah')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex justify-content-center gap-3 mb-5">
                        <button type="reset" class="btn btn-outline-secondary btn-lg px-5">
                            <i class="ti ti-refresh me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="btnSubmit">
                            <i class="ti ti-send me-1"></i> Kirim Lamaran
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
    <script>
        $(function () {
            // Flatpickr
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                locale: { firstDayOfWeek: 1 }
            });

            // Select2
            $('.select2').select2({ width: '100%' });

            // Foto preview
            $('#foto').change(function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $('#fotoPreview').attr('src', e.target.result).show();
                        $('#fotoPlaceholder').hide();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // CV file name
            $('#cv').change(function () {
                const name = this.files[0]?.name;
                if (name) {
                    $('#cvText').hide();
                    $('#cvFileName').text(name).show();
                    $('#cvIcon').css('color', 'var(--bs-primary)');
                }
            });

            // Ijazah file name
            $('#ijazah').change(function () {
                const name = this.files[0]?.name;
                if (name) {
                    $('#ijazahText').hide();
                    $('#ijazahFileName').text(name).show();
                    $('#ijazahIcon').css('color', 'var(--bs-primary)');
                }
            });

            // Vacancies data per cabang
            const vacancies = @json($vacancies ?? collect());

            $('#kode_cabang').on('change', function () {
                const kode = $(this).val();
                const $posisi = $('#posisi_dilamar');
                $posisi.empty().append('<option value="">-- Pilih Posisi --</option>');
                $('#hidden_kode_dept').val('');
                $('#hidden_kode_jabatan').val('');

                if (kode && vacancies[kode]) {
                    vacancies[kode].forEach(function (v) {
                        $posisi.append(`<option value="${v.posisi}" data-dept="${v.kode_dept}" data-jabatan="${v.kode_jabatan}">${v.posisi}</option>`);
                    });
                } else if (!kode) {
                    $posisi.empty().append('<option value="">-- Pilih Cabang dulu --</option>');
                } else {
                    $posisi.append('<option value="" disabled>Tidak ada lowongan tersedia</option>');
                }
            });

            $('#posisi_dilamar').on('change', function () {
                const opt = $(this).find(':selected');
                $('#hidden_kode_dept').val(opt.data('dept') || '');
                $('#hidden_kode_jabatan').val(opt.data('jabatan') || '');
            });

            // Submit confirm
            $('#formLamaran').submit(function (e) {
                const foto = $('#foto').val();
                if (!foto) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Pas Foto Wajib!', text: 'Silakan upload pas foto terlebih dahulu.' });
                    return false;
                }

                $('#btnSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...');
            });
        });
    </script>
</body>
</html>
