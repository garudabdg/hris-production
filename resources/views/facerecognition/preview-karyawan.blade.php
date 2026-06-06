@extends('layouts.mobile.app')
@section('content')
    @push('mystyle')
        <link rel="stylesheet" href="{{ asset('assets/css/preview_karyawan.css') }}">
    @endpush

    <div class="preview-container">
        <div class="header-info">
            <h2>{{ $karyawan->nama_karyawan ?? 'Karyawan' }}</h2>
            <p><strong>NIK:</strong> {{ $nik }}</p>
            <span class="info-badge">
                <ion-icon name="images-outline"></ion-icon>
                {{ $wajahList->count() }} Foto Wajah Tersimpan
            </span>
        </div>

        @if($wajahList->count() > 0)
            <div class="wajah-grid">
                @foreach($wajahList as $index => $wajah)
                    <div class="wajah-item">
                        @if($wajah->file_exists && $wajah->image_url)
                            <img src="{{ $wajah->image_url }}" 
                                 alt="Wajah {{ $index + 1 }}" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EGambar tidak ditemukan%3C/text%3E%3C/svg%3E';">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f0f0f0; color: #999;">
                                <div style="text-align: center; padding: 20px;">
                                    <ion-icon name="image-outline" style="font-size: 48px; margin-bottom: 10px;"></ion-icon>
                                    <div style="font-size: 12px;">File tidak ditemukan</div>
                                </div>
                            </div>
                        @endif
                        <div class="wajah-label">
                            Foto {{ $index + 1 }}<br>
                            <small>{{ \Carbon\Carbon::parse($wajah->created_at)->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="action-buttons">
                <form id="formRekamUlang" action="{{ route('facerecognition.karyawan.destroyAll') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Mulai perekaman ulang? Data wajah yang sudah tersimpan akan dihapus secara permanen.');">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn-action btn-rekam" style="width: 100%;">
                        <ion-icon name="camera-outline"></ion-icon>
                        Mulai Perekaman Ulang
                    </button>
                </form>
            </div>
        @else
            <div class="empty-state">
                <ion-icon name="images-outline"></ion-icon>
                <p>Belum ada data wajah yang tersimpan</p>
                <a href="{{ route('facerecognition.karyawan.create') }}" class="btn-action btn-rekam" style="text-decoration: none; display: inline-block; margin-top: 20px;">
                    <ion-icon name="camera-outline"></ion-icon>
                    Mulai Perekaman
                </a>
            </div>
        @endif
    </div>

@endsection

@push('myscript')
    <script src="{{ asset('assets/external/js/sweetalert2@11.js') }}"></script>
    <script>
        window.PreviewKaryawanConfig = {
            successMsg: {!! json_encode(session('success')) !!},
            errorMsg: {!! json_encode(session('error')) !!}
        };
    </script>
    <script src="{{ asset('assets/js/preview_karyawan.js') }}?v=1"></script>
@endpush
