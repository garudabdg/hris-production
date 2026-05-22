@extends('layouts.mobile.modern')

@section('title', 'Profile')

@section('header_left')
    <a href="{{ route('dashboard.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #e6fcf5 100%) !important;
            font-family: 'Inter', sans-serif;
        }

        .form-container {
            padding: 20px 15px;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-label-group {
            position: relative;
            margin-bottom: 20px;
            background: #ffffff;
            border: 1.5px solid transparent;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-label-group:focus-within {
            border-color: #32745e;
            box-shadow: 0 0 0 4px rgba(50, 116, 94, 0.15), 0 4px 15px rgba(0, 0, 0, 0.03);
            transform: translateY(-2px);
        }

        .form-label-group .icon-wrapper {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(50, 116, 94, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .form-label-group textarea ~ .icon-wrapper {
            top: 12px;
            transform: none;
        }

        .form-label-group:focus-within .icon-wrapper {
            background: #32745e;
            color: white;
        }

        .form-label-group:focus-within .icon-wrapper ion-icon {
            color: white;
        }

        .form-label-group .icon-wrapper ion-icon {
            font-size: 18px;
            color: #32745e;
            transition: all 0.3s ease;
        }

        .form-label-group input,
        .form-label-group textarea {
            width: 100% !important;
            height: 56px;
            padding: 24px 16px 8px 60px !important;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            background: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            display: block !important;
        }

        .form-label-group textarea {
            height: 100px !important;
            padding-top: 28px !important;
            resize: none;
        }

        .form-label-group label {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 60px;
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 0;
            z-index: 5;
            transform-origin: left top;
        }
        
        .form-label-group textarea ~ label {
            top: 28px;
            transform: none;
        }

        .form-label-group input:focus ~ label,
        .form-label-group input:not(:placeholder-shown) ~ label,
        .form-label-group textarea:focus ~ label,
        .form-label-group textarea:not(:placeholder-shown) ~ label {
            top: 14px;
            transform: translateY(-50%) scale(0.85);
            color: #32745e;
            font-weight: 700;
        }
        
        .form-label-group textarea:focus ~ label,
        .form-label-group textarea:not(:placeholder-shown) ~ label {
            top: 14px;
            transform: scale(0.85);
        }

        /* Foto Profil */
        .profile-photo-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
        }

        .profile-photo-box {
            position: relative;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, #32745e, #10b981, #32745e);
            background-size: 200% 200%;
            animation: gradientMove 3s ease infinite;
            box-shadow: 0 12px 25px rgba(50, 116, 94, 0.25);
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .profile-photo-box img,
        .profile-photo-box .photo-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            background-color: #f8fafc;
        }

        .profile-photo-box .photo-placeholder {
            background-size: cover;
            background-position: center;
        }
        
        .camera-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 36px;
            height: 36px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            color: #32745e;
            font-size: 18px;
            border: 2px solid #e6fcf5;
            z-index: 10;
        }

        /* Dashed Box File Upload */
        .custom-file-upload {
            border: 2px dashed rgba(50, 116, 94, 0.3);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 24px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 110px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .custom-file-upload:hover {
            border-color: #32745e;
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(50, 116, 94, 0.1);
        }

        .custom-file-upload:active {
            transform: scale(0.98);
        }

        .custom-file-upload input[type="file"] {
            display: none;
        }

        .custom-file-upload .upload-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(50, 116, 94, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .custom-file-upload:hover .upload-icon-wrapper {
            background: #32745e;
            color: white;
            transform: scale(1.1);
        }

        .custom-file-upload ion-icon {
            font-size: 24px;
            color: #32745e;
        }

        .custom-file-upload:hover .upload-icon-wrapper ion-icon {
            color: white;
        }

        .custom-file-upload span {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .custom-file-upload p.helper-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            margin-bottom: 0;
            font-weight: 500;
        }

        .file-name {
            font-size: 13px;
            color: #32745e;
            margin-top: 8px;
            font-weight: 600;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            background: rgba(50, 116, 94, 0.1);
            padding: 4px 12px;
            border-radius: 20px;
            display: none;
        }

        .btn-submit-modern {
            width: 100%;
            height: 54px;
            background: linear-gradient(135deg, #32745e 0%, #215c48 100%);
            color: #ffffff;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            box-shadow: 0 10px 20px rgba(50, 116, 94, 0.25);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit-modern::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.6s;
        }
        
        .btn-submit-modern:hover::after {
            left: 100%;
        }

        .btn-submit-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 25px rgba(50, 116, 94, 0.35);
        }

        .btn-submit-modern:active {
            transform: scale(0.97) translateY(0);
            box-shadow: 0 5px 10px rgba(50, 116, 94, 0.2);
        }
    </style>
@endpush

@section('content')
    <div class="fade-up form-container pb-24">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="formProfile" autocomplete="off">
            @csrf
            @method('PUT')

            {{-- Profile Photo --}}
            <div class="profile-photo-wrapper">
                <div class="profile-photo-box">
                    @if (!empty($karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $karyawan->foto))
                        <div class="photo-placeholder" style="background-image: url({{ getfotoKaryawan($karyawan->foto) }});"></div>
                    @else
                        <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}" alt="Profile Photo">
                    @endif
                    <div class="camera-badge">
                        <ion-icon name="camera"></ion-icon>
                    </div>
                </div>
            </div>

            {{-- Nama Lengkap --}}
            <div class="form-label-group">
                <div class="icon-wrapper">
                    <ion-icon name="person-outline"></ion-icon>
                </div>
                <input type="text" name="nama_karyawan" id="nama_karyawan" placeholder=" " value="{{ $karyawan->nama_karyawan ?? '' }}" required>
                <label for="nama_karyawan">Nama Lengkap</label>
            </div>

            {{-- No. KTP --}}
            <div class="form-label-group">
                <div class="icon-wrapper">
                    <ion-icon name="card-outline"></ion-icon>
                </div>
                <input type="text" name="no_ktp" id="no_ktp" placeholder=" " value="{{ $karyawan->no_ktp ?? '' }}" required>
                <label for="no_ktp">No. KTP</label>
            </div>

            {{-- No. HP --}}
            <div class="form-label-group">
                <div class="icon-wrapper">
                    <ion-icon name="call-outline"></ion-icon>
                </div>
                <input type="tel" name="no_hp" id="no_hp" placeholder=" " value="{{ $karyawan->no_hp ?? '' }}" required>
                <label for="no_hp">No. HP</label>
            </div>

            {{-- Alamat --}}
            <div class="form-label-group">
                <div class="icon-wrapper">
                    <ion-icon name="location-outline"></ion-icon>
                </div>
                <textarea name="alamat" id="alamat" placeholder=" " required>{{ $karyawan->alamat ?? '' }}</textarea>
                <label for="alamat">Alamat Lengkap</label>
            </div>

            {{-- Tanggal Lahir --}}
            <div class="form-label-group">
                <div class="icon-wrapper">
                    <ion-icon name="calendar-outline"></ion-icon>
                </div>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" placeholder=" " value="{{ $karyawan->tanggal_lahir ?? '' }}">
                <label for="tanggal_lahir">Tanggal Lahir</label>
            </div>

            {{-- Username --}}
            <div class="form-label-group">
                <div class="icon-wrapper">
                    <ion-icon name="at-outline"></ion-icon>
                </div>
                <input type="text" name="username" id="username" placeholder=" " value="{{ $user->username }}" required>
                <label for="username">Username</label>
            </div>

            {{-- Email --}}
            <div class="form-label-group">
                <div class="icon-wrapper">
                    <ion-icon name="mail-outline"></ion-icon>
                </div>
                <input type="email" name="email" id="email" placeholder=" " value="{{ $user->email }}" required>
                <label for="email">Alamat Email</label>
            </div>

            {{-- Upload Foto --}}
            <div class="custom-file-upload" onclick="document.getElementById('foto').click()">
                <input type="file" name="foto" id="foto" accept=".jpg, .jpeg, .png">
                <div class="upload-icon-wrapper">
                    <ion-icon name="cloud-upload-outline"></ion-icon>
                </div>
                <span>Unggah Foto Profil</span>
                <p class="helper-text">Format JPG, JPEG, PNG max 2MB</p>
                <div id="fileName" class="file-name"></div>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="btn-submit-modern" id="btnSimpan">
                <ion-icon name="save-outline"></ion-icon>
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
@endsection

@push('myscript')
    <script>
        // File Upload Handling
        document.getElementById('foto').addEventListener('change', function() {
            let file = this.files[0];
            const fileNameDisplay = document.getElementById('fileName');
            if (file) {
                fileNameDisplay.textContent = file.name;
            } else {
                fileNameDisplay.textContent = '';
            }
        });

        $(function() {
            $("#formProfile").submit(function(e) {
                let nama_karyawan = $('input[name="nama_karyawan"]').val();
                let no_ktp = $('input[name="no_ktp"]').val();
                let no_hp = $('input[name="no_hp"]').val();
                let alamat = $('textarea[name="alamat"]').val();
                let username = $('input[name="username"]').val();
               let email = $('input[name="email"]').val();

                if (nama_karyawan == "" || no_ktp == "" || no_hp == "" || alamat == "" || username == "" || email == "") {
                    e.preventDefault();
                    Swal.fire({title: "Oops!", text: 'Semua Bidang Harus Diisi !', icon: "warning"});
                    return false;
                }

                const btn = document.getElementById('btnSimpan');
                btn.disabled = true;
                btn.innerHTML = `<ion-icon name="sync-outline" class="animate-spin"></ion-icon><span>Menyimpan...</span>`;
            });
        });
    </script>
@endpush
