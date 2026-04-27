<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Interview</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: #f2f6f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 35px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        .icon { font-size: 72px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .title.hadir { color: #2e7d32; }
        .title.tidak_hadir { color: #c62828; }
        .pesan { font-size: 15px; color: #555; line-height: 1.6; margin-bottom: 25px; }
        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px 20px;
            text-align: left;
            margin-bottom: 20px;
        }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; }
        .info-label { color: #888; }
        .info-value { font-weight: 600; color: #333; }
        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-hadir { background: #e8f5e9; color: #2e7d32; }
        .badge-tidak { background: #ffebee; color: #c62828; }
        .sudah-badge {
            background: #fff3cd;
            color: #856404;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="card">
        @if($jawaban === 'hadir')
            <div class="icon">✅</div>
            <div class="title hadir">Konfirmasi Diterima!</div>
        @else
            <div class="icon">😔</div>
            <div class="title tidak_hadir">Konfirmasi Diterima</div>
        @endif

        @if($sudah)
            <div class="sudah-badge">ℹ️ Anda sudah pernah mengkonfirmasi sebelumnya</div>
        @endif

        <p class="pesan">{{ $pesan }}</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Nama</span>
                <span class="info-value">{{ $recruitment->nama_lengkap }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Posisi</span>
                <span class="info-value">{{ $recruitment->posisi_dilamar }}</span>
            </div>
            @if($recruitment->tanggal_interview)
            <div class="info-row">
                <span class="info-label">Tanggal Interview</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($recruitment->tanggal_interview)->translatedFormat('d F Y') }}</span>
            </div>
            @endif
            @if($recruitment->jam_interview)
            <div class="info-row">
                <span class="info-label">Jam</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($recruitment->jam_interview)->format('H:i') }} WIB</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Status Konfirmasi</span>
                <span class="info-value">
                    @if($jawaban === 'hadir')
                        <span class="badge badge-hadir">✅ Hadir</span>
                    @else
                        <span class="badge badge-tidak">❌ Tidak Hadir</span>
                    @endif
                </span>
            </div>
        </div>

        <p style="font-size:12px; color:#aaa;">Jika ada pertanyaan, hubungi HR kami.</p>
    </div>
</body>
</html>
