@extends('layouts.app')
@section('titlepage', 'Broadcast WhatsApp Excel')
@section('navigasi')
    <a href="{{ route('wagateway.index') }}" class="text-muted">WhatsApp Gateway</a>
    <span class="mx-1">/</span>
    <span>Broadcast Excel</span>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom pb-3">
                <h5 class="card-title mb-0">
                    <i class="ti ti-broadcast me-2 text-primary"></i>Broadcast Pesan via Excel
                </h5>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="ti ti-check me-2 fs-4"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="ti ti-alert-triangle me-2 fs-4"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('wagateway.broadcast.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="file_excel" class="form-label fw-semibold">File Excel (.xlsx, .xls, .csv)</label>
                        <input class="form-control" type="file" id="file_excel" name="file_excel" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text text-muted mt-2">
                            Pastikan format data di dalam excel sudah sesuai (baris pertama adalah header, no wa ada di kolom A).
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="file_media" class="form-label fw-semibold">Lampiran Foto (Opsional)</label>
                        <input class="form-control" type="file" id="file_media" name="file_media" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <div class="form-text text-muted mt-2">
                            Maksimal ukuran foto 2MB. Foto ini akan dilampirkan bersamaan dengan pesan di atas.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="delay" class="form-label fw-semibold">Jeda</label>
                        <select class="form-select" id="delay" name="delay" required>
                            <option value="15">15 Detik</option>
                            <option value="30" selected>30 Detik</option>
                            <option value="60">60 Detik</option>
                            <option value="120">2 Menit</option>
                        </select>
                        <div class="form-text text-muted mt-2">
                            Sistem akan memberikan waktu jeda pengiriman setiap pesan sesuai pilihan di atas.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="pesan" class="form-label fw-semibold">Template Pesan</label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="6" placeholder="Halo @{{B}}, tagihan Anda sebesar @{{C}}..." required></textarea>
                        <div class="form-text text-muted mt-2">
                            Gunakan tanda <strong>@{{kolom}}</strong> untuk menyisipkan data otomatis dari excel. Kolom menggunakan huruf kapital (A, B, C, dst).
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('wagateway.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Memproses...';">
                            <i class="ti ti-send me-1"></i> Mulai Broadcast
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 border-top border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-sm bg-info-subtle text-info rounded me-2 d-flex align-items-center justify-content-center">
                        <i class="ti ti-info-circle fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0 fw-bold text-info-emphasis">Panduan Import Excel</h5>
                </div>
                
                <div class="text-sm text-muted">
                    <p>Pada fitur ini, Anda bisa melakukan kustom pesan ke setiap nomor tujuannya. Misal dalam file excel, Anda mempunyai tabel berikut:</p>
                    
                    <div class="table-responsive my-3">
                        <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:12px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-muted" style="width:30px">#</th>
                                    <th>A (No. WA)</th>
                                    <th>B</th>
                                    <th>C</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-muted">1</td>
                                    <td><em>(Header)</em></td>
                                    <td><em>(Header)</em></td>
                                    <td><em>(Header)</em></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">2</td>
                                    <td>081234567890</td>
                                    <td>Budi</td>
                                    <td>Rp 200.000</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">3</td>
                                    <td>081234567891</td>
                                    <td>Ani</td>
                                    <td>Rp 100.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p>Lalu pada field <strong>Template Pesan</strong>, Anda menulis:</p>
                    <div class="bg-light p-2 rounded border mb-3 text-dark fst-italic" style="font-size:13px;">
                        "Hai <strong>@{{B}}</strong>, untuk tagihan kamu sebesar <strong>@{{C}}</strong> sudah jatuh tempo ya."
                    </div>
                    
                    <p>Maka pesan yang dikirim ke <strong class="text-dark">081234567890</strong> adalah:</p>
                    <div class="bg-light p-2 rounded border text-dark fst-italic" style="font-size:13px;">
                        "Hai <strong>Budi</strong>, untuk tagihan kamu sebesar <strong>Rp 200.000</strong> sudah jatuh tempo ya."
                    </div>

                    <hr>
                    <div class="text-danger" style="font-size: 12px;">
                        <i class="ti ti-alert-circle me-1"></i> <strong>Penting:</strong> 
                        <ul class="mb-0 ps-3 mt-1">
                            <li>Baris pertama (row 1) akan diabaikan karena dianggap sebagai judul kolom/header.</li>
                            <li>Sistem akan mengambil data hingga kolom Z. Penulisan kolom di template wajib huruf kapital (A, B, C...).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
