@extends('layouts.mobile.modern')

@section('title', 'Ajukan Lembur')

@section('header_left')
    <a href="{{ route('lembur.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background: #f0f8ff !important;
        }

        .form-container {
            padding: 10px 5px;
        }

        .form-label-group {
            position: relative;
            margin-bottom: 12px;
            background: transparent !important;
            border: 1px solid #024a75;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .form-label-group .input-icon {
            position: absolute;
            left: 15px;
            top: 15px;
            font-size: 24px;
            color: #024a75;
            z-index: 10;
            pointer-events: none;
        }

        .form-label-group input,
        .form-label-group textarea {
            width: 100% !important;
            height: 54px;
            padding: 22px 15px 5px 52px !important;
            font-size: 16px;
            font-weight: 500;
            color: #1e293b;
            background: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            display: block !important;
        }

        .form-label-group textarea {
            height: 120px !important;
            padding-top: 30px !important;
            resize: none;
        }

        .form-label-group label {
            position: absolute;
            top: 15px;
            left: 52px;
            font-size: 16px;
            color: #024a75;
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
            top: 5px;
            font-size: 11px;
            font-weight: 600;
            color: #024a75;
        }

        .btn-submit-modern {
            width: 100%;
            height: 54px;
            background: #024a75;
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
            transform: scale(0.98);
            background: #013352;
        }
    </style>
@endpush

@section('content')
    <div class="fade-up form-container">
        <form action="{{ route('lembur.store') }}" method="POST" id="formLembur" autocomplete="off">
            @csrf

            <div class="form-label-group">
                <ion-icon name="time-outline" class="input-icon"></ion-icon>
                <input type="datetime-local" name="dari" id="dari" placeholder=" " required>
                <label for="dari">Dari Waktu</label>
            </div>

            <div class="form-label-group">
                <ion-icon name="time-outline" class="input-icon"></ion-icon>
                <input type="datetime-local" name="sampai" id="sampai" placeholder=" " required>
                <label for="sampai">Sampai Waktu</label>
            </div>

            <div class="form-label-group">
                <ion-icon name="calculator-outline" class="input-icon"></ion-icon>
                <input type="text" name="jml_jam" id="jml_jam" placeholder=" " readonly>
                <label for="jml_jam">Jumlah Jam</label>
            </div>

            <div class="form-label-group">
                <ion-icon name="document-text-outline" class="input-icon"></ion-icon>
                <textarea name="keterangan" id="keterangan" placeholder=" " required></textarea>
                <label for="keterangan">Keterangan</label>
            </div>

            <button type="submit" class="btn-submit-modern" id="btnSimpan">
                <ion-icon name="paper-plane-outline"></ion-icon>
                <span>Ajukan Lembur</span>
            </button>
        </form>
    </div>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function hitungJam(startDate, endDate) {
                if (startDate && endDate) {
                    var start = new Date(startDate);
                    var end = new Date(endDate);
                    
                    // Tambahkan 1 detik agar penghitungan inklusif (opsional, disesuaikan dengan logic lama)
                    var timeDifference = end - start + 1000;
                    var hourDifference = timeDifference / (1000 * 3600);
                    return hourDifference > 0 ? hourDifference.toFixed(2) : 0;
                }
                return 0;
            }

            document.getElementById('dari').addEventListener('change', function() {
                let sampai = document.getElementById('sampai').value;
                document.getElementById('jml_jam').value = hitungJam(this.value, sampai);
            });

            document.getElementById('sampai').addEventListener('change', function() {
                let dari = document.getElementById('dari').value;
                document.getElementById('jml_jam').value = hitungJam(dari, this.value);
            });

            const form = document.getElementById('formLembur');
            form.addEventListener('submit', function(e) {
                let dari = document.getElementById('dari').value;
                let sampai = document.getElementById('sampai').value;
                let keterangan = document.getElementById('keterangan').value;

                if (!dari || !sampai) {
                    e.preventDefault();
                    Swal.fire({ title: "Oops!", text: 'Periode Lembur Harus Diisi !', icon: "warning" });
                    return;
                }

                if (new Date(sampai) < new Date(dari)) {
                    e.preventDefault();
                    Swal.fire({ title: "Oops!", text: 'Waktu Selesai tidak boleh kurang dari Waktu Mulai !', icon: "warning" });
                    return;
                }

                if (!keterangan.trim()) {
                    e.preventDefault();
                    Swal.fire({ title: "Oops!", text: 'Keterangan Harus Diisi !', icon: "warning" });
                    return;
                }

                const btn = document.getElementById('btnSimpan');
                btn.disabled = true;
                btn.innerHTML = `<ion-icon name="sync-outline" class="animate-spin"></ion-icon><span>Memproses...</span>`;
            });
        });
    </script>
@endpush

