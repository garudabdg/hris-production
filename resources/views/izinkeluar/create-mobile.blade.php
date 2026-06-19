@extends('layouts.mobile.modern')

@section('title', 'Buat Izin Keluar')

@section('header_left')
    <a href="{{ route('pengajuanizin.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background: #e6fcf5 !important;
        }

        .form-container {
            padding: 10px 5px;
        }

        .form-label-group {
            position: relative;
            margin-bottom: 12px;
            background: transparent !important;
            border: 1px solid #32745e;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .form-label-group .input-icon {
            position: absolute;
            left: 14px;
            top: 11px;
            font-size: 20px;
            color: #32745e;
            z-index: 10;
            pointer-events: none;
        }

        .form-label-group input,
        .form-label-group textarea {
            width: 100% !important;
            height: 44px;
            padding: 18px 14px 2px 42px !important;
            font-size: 14px;
            font-weight: 500;
            color: #616eb5;
            background: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            display: block !important;
        }

        .form-label-group textarea {
            height: 80px !important;
            padding-top: 22px !important;
            resize: none;
        }

        .form-label-group label {
            position: absolute;
            top: 11px;
            left: 42px;
            font-size: 14px;
            color: #32745e;
            opacity: 0.8;
            pointer-events: none;
            transition: all 0.2s ease-in-out;
            margin-bottom: 0;
            z-index: 5;
        }

        .form-label-group input:focus ~ label,
        .form-label-group input:not(:placeholder-shown) ~ label,
        .form-label-group textarea:focus ~ label,
        .form-label-group textarea:not(:placeholder-shown) ~ label {
            top: 2px;
            left: 42px;
            font-size: 10px;
            font-weight: 600;
            color: #32745e;
        }

        .btn-submit-modern {
            width: 100%;
            height: 48px;
            background: #32745e;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 5px;
            transition: all 0.3s;
        }

        .btn-submit-modern:active {
            transform: scale(0.97);
            background: #616eb5;
        }
    </style>
@endpush

@section('content')
    <div class="fade-up form-container">
        <form action="{{ route('izinkeluar.store') }}" method="POST" id="formIzin" autocomplete="off">
            @csrf
            
            <div class="form-label-group">
                <ion-icon name="calendar-outline" class="input-icon"></ion-icon>
                <input type="text" name="tanggal" id="tanggal" placeholder=" " required readonly>
                <label for="tanggal">Tanggal</label>
            </div>

            <div class="form-label-group">
                <ion-icon name="time-outline" class="input-icon"></ion-icon>
                <input type="time" name="jam_keluar" id="jam_keluar" placeholder=" " required>
                <label for="jam_keluar">Jam Keluar</label>
            </div>

            <div class="form-label-group">
                <ion-icon name="time-outline" class="input-icon"></ion-icon>
                <input type="time" name="jam_kembali" id="jam_kembali" placeholder=" ">
                <label for="jam_kembali">Jam Kembali (Opsional)</label>
            </div>

            <div class="form-label-group">
                <ion-icon name="document-text-outline" class="input-icon"></ion-icon>
                <textarea name="keperluan" id="keperluan" placeholder=" " required></textarea>
                <label for="keperluan">Keperluan</label>
            </div>

            <button type="submit" class="btn-submit-modern" id="btnSimpan">
                <ion-icon name="paper-plane-outline"></ion-icon>
                <span>Kirim Izin Keluar</span>
            </button>
        </form>
    </div>
@endsection

@push('myscript')
    <script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.5.0/air-datepicker.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const localeIndo = {
                days: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                daysShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                daysMin: ['Mg', 'Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb'],
                months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                today: 'Hari ini',
                clear: 'Hapus',
                dateFormat: 'yyyy-MM-dd',
                timeFormat: 'HH:mm',
                firstDay: 1
            };

            const dpTanggal = new AirDatepicker('#tanggal', {
                locale: localeIndo,
                autoClose: true,
                isMobile: true,
                buttons: ['today', 'clear']
            });

            const form = document.getElementById('formIzin');
            form.addEventListener('submit', function(e) {
                let tanggal = document.getElementById('tanggal').value;
                let jam_keluar = document.getElementById('jam_keluar').value;
                let keperluan = document.getElementById('keperluan').value;

                if (!tanggal) {
                    e.preventDefault();
                    Swal.fire({ title: "Oops!", text: 'Tanggal Harus Diisi !', icon: "warning" });
                    return;
                }

                if (!jam_keluar) {
                    e.preventDefault();
                    Swal.fire({ title: "Oops!", text: 'Jam Keluar Harus Diisi !', icon: "warning" });
                    return;
                }

                if (!keperluan.trim()) {
                    e.preventDefault();
                    Swal.fire({ title: "Oops!", text: 'Keperluan Harus Diisi !', icon: "warning" });
                    return;
                }

                const btn = document.getElementById('btnSimpan');
                btn.disabled = true;
                btn.innerHTML = `<ion-icon name="sync-outline" class="animate-spin"></ion-icon><span>Memproses...</span>`;
            });
        });
    </script>
@endpush
