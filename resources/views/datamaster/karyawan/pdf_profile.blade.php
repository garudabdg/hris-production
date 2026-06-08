<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Karyawan - {{ $karyawan->nama_karyawan }}</title>
    <style>
        @page { margin: 40px; }
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 11pt; color: #333; line-height: 1.4; }
        h1, h2, h3, h4, h5 { margin-top: 0; margin-bottom: 10px; color: #1a4971; }
        
        .letterhead { width: 100%; border-bottom: 3px solid #1a4971; padding-bottom: 10px; margin-bottom: 25px; }
        .letterhead td { vertical-align: middle; }
        .letterhead-logo { width: 120px; text-align: left; }
        .letterhead-logo img { max-width: 100px; max-height: 80px; }
        .letterhead-text { text-align: right; }
        .letterhead-text h2 { margin: 0; font-size: 16pt; color: #1a4971; text-transform: uppercase; }
        .letterhead-text p { margin: 3px 0 0; font-size: 10pt; color: #555; line-height: 1.3; }
        
        .section-title { background-color: #f4f6f9; color: #1a4971; padding: 6px 10px; font-weight: bold; border-left: 4px solid #1a4971; margin-top: 25px; margin-bottom: 10px; font-size: 12pt; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: avoid; }
        .table-profile td { padding: 6px 4px; vertical-align: top; }
        .table-profile td.label { width: 30%; font-weight: bold; color: #555; }
        .table-profile td.colon { width: 2%; text-align: center; }
        .table-profile td.value { width: 68%; }
        
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10pt; }
        .table-data th, .table-data td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table-data th { background-color: #f4f6f9; color: #1a4971; font-weight: bold; }
        
        .badge { display: inline-block; padding: 3px 6px; font-size: 9pt; font-weight: bold; border-radius: 3px; color: #fff; }
        .bg-success { background-color: #28a745; }
        .bg-danger { background-color: #dc3545; }
        .bg-primary { background-color: #0d6efd; }
        .bg-info { background-color: #0dcaf0; color: #000; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-secondary { background-color: #6c757d; }
        
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .mt-0 { margin-top: 0; }
        .mb-0 { margin-bottom: 0; }
        
        .photo-container { text-align: center; margin-bottom: 20px; }
        .photo { width: 120px; height: 140px; object-fit: cover; border: 3px solid #ddd; border-radius: 5px; }
        
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 8pt; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 5px; }
        
        /* Clearfix */
        .clearfix::after { content: ""; clear: both; display: table; }
        .col-left { float: left; width: 70%; }
        .col-right { float: right; width: 30%; text-align: center; }
    </style>
</head>
<body>

    @php
        $gs = \App\Models\Pengaturanumum::first();
        $logoPath = null;
        if (!empty($gs->logo) && \Storage::disk('public')->exists('logo/' . $gs->logo)) {
            $logoPath = storage_path('app/public/logo/' . $gs->logo);
        } else {
            $logoPath = public_path('assets/login/images/logoweb-1.png');
        }

        $base64Logo = null;
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    @endphp

    <table class="letterhead">
        <tr>
            <td class="letterhead-logo">
                @if($base64Logo)
                    <img src="{{ $base64Logo }}" alt="Logo">
                @endif
            </td>
            <td class="letterhead-text">
                <h2>{{ $gs->nama_perusahaan ?? 'PERUSAHAAN' }} - {{ strtoupper($karyawan->nama_cabang) }}</h2>
                <p>
                    {{ $karyawan->alamat_cabang ?? 'Alamat Cabang' }}<br>
                    Telp: {{ $karyawan->telepon_cabang ?? '-' }}
                </p>
            </td>
        </tr>
    </table>

    <div style="text-align: center; margin-bottom: 20px;">
        <h1 style="font-size: 16pt; margin: 0; text-decoration: underline; text-transform: uppercase;">PROFIL KARYAWAN</h1>
        <p style="margin: 5px 0 0; color: #666; font-size: 10pt;">Diunduh pada: {{ date('d F Y H:i') }}</p>
    </div>

    <div class="clearfix">
        <div class="col-left">
            <h2 style="margin-bottom: 5px;">{{ strtoupper($karyawan->nama_karyawan) }}</h2>
            <p style="margin-top:0; font-size:12pt; color:#666;">
                {{ $karyawan->nama_jabatan }} - {{ $karyawan->nama_dept }}
            </p>
            <div>
                @if ($karyawan->status_aktif_karyawan === '1')
                    <span class="badge bg-success">AKTIF</span>
                @else
                    <span class="badge bg-danger">NONAKTIF</span>
                @endif
                <span class="badge bg-primary">NIK: {{ $karyawan->nik_show ?? $karyawan->nik }}</span>
                <span class="badge bg-info">{{ $karyawan->nama_cabang }}</span>
            </div>
        </div>
        <div class="col-right">
            <div class="photo-container">
                @if ($karyawan->foto && \Storage::disk('public')->exists('karyawan/' . $karyawan->foto))
                    @php
                        // Encode image to base64 for reliable dompdf rendering
                        $path = storage_path('app/public/karyawan/' . $karyawan->foto);
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    @endphp
                    <img src="{{ $base64 }}" class="photo" alt="Foto">
                @else
                    <div style="width: 120px; height: 140px; border: 2px dashed #ccc; display: inline-block; line-height: 140px; color:#999; font-size: 10pt;">Tanpa Foto</div>
                @endif
            </div>
        </div>
    </div>

    <div style="page-break-inside: avoid;">
        <div class="section-title">Data Pribadi</div>
        <table class="table-profile">
            <tr>
                <td class="label">Nomor KTP</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->no_ktp ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tgl Lahir</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->tempat_lahir }}, {{ !empty($karyawan->tanggal_lahir) ? date('d-m-Y', strtotime($karyawan->tanggal_lahir)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kelamin</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td class="label">Status Kawin</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->keterangan_status_kawin ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Agama</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->agama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pendidikan Terakhir</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->pendidikan_terakhir ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. Telepon / HP</td>
                <td class="colon">:</td>
                <td class="value">{{ $karyawan->no_hp ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div style="page-break-inside: avoid;">
        <div class="section-title">Data Kepegawaian & Akun</div>
        <table class="table-profile">
            <tr>
                <td class="label">Tanggal Bergabung</td>
                <td class="colon">:</td>
                <td class="value">{{ !empty($karyawan->tanggal_masuk) ? date('d-m-Y', strtotime($karyawan->tanggal_masuk)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status Karyawan</td>
                <td class="colon">:</td>
                <td class="value">
                    @php
                        $sk = $karyawan->status_karyawan;
                        if($sk == 'T') echo 'Tetap';
                        elseif($sk == 'K') echo 'Kontrak';
                        elseif($sk == 'M') echo 'Mitra';
                        elseif($sk == 'O') echo 'Outsourcing';
                        else echo $sk ?? '-';
                    @endphp
                </td>
            </tr>
            @if ($kontrak)
            <tr>
                <td class="label">Nomor Kontrak</td>
                <td class="colon">:</td>
                <td class="value">
                    <strong>{{ $kontrak->no_kontrak }}</strong> 
                    <span class="text-muted" style="font-size: 9pt;">(s/d {{ date('d-m-Y', strtotime($kontrak->sampai)) }})</span>
                </td>
            </tr>
            @endif
            @if ($karyawan->status_aktif_karyawan === '0')
            <tr>
                <td class="label">Tanggal Nonaktif</td>
                <td class="colon">:</td>
                <td class="value" style="color:red;">{{ !empty($karyawan->tanggal_nonaktif) ? date('d-m-Y', strtotime($karyawan->tanggal_nonaktif)) : '-' }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Username HRIS</td>
                <td class="colon">:</td>
                <td class="value">{{ $user ? $user->username : 'Belum Dibuat' }}</td>
            </tr>
            <tr>
                <td class="label">Email HRIS</td>
                <td class="colon">:</td>
                <td class="value">{{ $user ? $user->email : '-' }}</td>
            </tr>
        </table>
    </div>

    <div style="page-break-before: always;"></div>

    <div style="page-break-inside: avoid;">
        <div class="section-title">Aset & Dukungan IT</div>
        <table class="table-profile">
            <tr>
                <td class="label">Total Tiket IT Dibuat</td>
                <td class="colon">:</td>
                <td class="value"><strong>{{ $total_tickets }}</strong> tiket</td>
            </tr>
            <tr>
                <td class="label">Aset Dipegang ({{ $assets->count() }})</td>
                <td class="colon">:</td>
                <td class="value">
                    @if($assets->count() > 0)
                        <ul style="margin:0; padding-left:15px;">
                            @foreach($assets as $asset)
                                <li><strong>{{ $asset->kode_asset }}</strong> - {{ $asset->nama_asset }} ({{ $asset->merk ?? '-' }})</li>
                            @endforeach
                        </ul>
                    @else
                        <span style="color:#888;">Tidak ada aset yang sedang dipinjam/dipegang.</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Riwayat Mutasi / Promosi / Demosi</div>
    @if($mutasi->count() > 0)
        <table class="table-data">
            <thead>
                <tr>
                    <th width="12%">Tanggal</th>
                    <th width="15%">Jenis</th>
                    <th width="35%">Unit Lama</th>
                    <th width="38%">Unit Baru</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mutasi as $m)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($m->tanggal_mutasi)) }}</td>
                    <td><strong>{{ $m->jenis_mutasi }}</strong></td>
                    <td>
                        <div style="font-size: 9pt;">
                            <strong>Cab:</strong> {{ $m->cabangLama->nama_cabang ?? '-' }}<br>
                            <strong>Dept:</strong> {{ $m->deptLama->nama_dept ?? '-' }}<br>
                            <strong>Jab:</strong> {{ $m->jabatanLama->nama_jabatan ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 9pt;">
                            <strong>Cab:</strong> {{ $m->cabangBaru->nama_cabang ?? '-' }}<br>
                            <strong>Dept:</strong> {{ $m->deptBaru->nama_dept ?? '-' }}<br>
                            <strong>Jab:</strong> {{ $m->jabatanBaru->nama_jabatan ?? '-' }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted text-center" style="padding: 10px; border: 1px dashed #ccc;">Belum ada riwayat mutasi/promosi.</p>
    @endif

    <div class="section-title">Pelatihan & Sertifikasi</div>
    @if($karyawan->pelatihan && $karyawan->pelatihan->count() > 0)
        <table class="table-data">
            <thead>
                <tr>
                    <th width="45%">Nama Pelatihan / Sertifikasi</th>
                    <th width="20%">Tanggal</th>
                    <th width="20%">Expired</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($karyawan->pelatihan as $p)
                <tr>
                    <td>{{ $p->nama_pelatihan }}</td>
                    <td>{{ $p->tanggal_pelatihan ? $p->tanggal_pelatihan->format('d/m/Y') : '-' }}</td>
                    <td>{{ $p->tanggal_expired ? $p->tanggal_expired->format('d/m/Y') : 'Seumur Hidup' }}</td>
                    <td>
                        @if($p->tanggal_expired)
                            @if(\Carbon\Carbon::now()->gt($p->tanggal_expired))
                                <span style="color:red; font-weight:bold;">Expired</span>
                            @else
                                <span style="color:green; font-weight:bold;">Active</span>
                            @endif
                        @else
                            <span style="color:green; font-weight:bold;">Active</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted text-center" style="padding: 10px; border: 1px dashed #ccc;">Belum ada riwayat pelatihan/sertifikasi.</p>
    @endif

    <div class="footer">
        Dicetak dari Sistem HRIS pada {{ date('d-m-Y H:i:s') }} - Dokumen ini valid tanpa tanda tangan.
    </div>

</body>
</html>
