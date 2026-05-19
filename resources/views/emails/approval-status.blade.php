<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #4f46e5; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 32px; color: #374151; }
        .badge-approved { background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
        .badge-rejected { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
        .badge-pending  { background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
        .detail-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .detail-box p { margin: 6px 0; font-size: 14px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff !important; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 16px; }
        .footer { padding: 16px 32px; background: #f9fafb; color: #9ca3af; font-size: 12px; text-align: center; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 {{ $typeText }} — {{ $statusText }}</h1>
    </div>
    <div class="body">
        <p>Halo <strong>{{ $notifiable->name }}</strong>,</p>
        <p>Pengajuan <strong>{{ $typeText }}</strong> Anda telah diproses.</p>

        <div class="detail-box">
            <p><strong>Kode Pengajuan:</strong> {{ $approvalCode }}</p>
            <p><strong>Status:</strong>
                @if($status == 1)
                    <span class="badge-approved">✅ Disetujui</span>
                @elseif($status == 2)
                    <span class="badge-rejected">❌ Ditolak</span>
                @else
                    <span class="badge-pending">⏳ Diperbarui</span>
                @endif
            </p>
            <p><strong>Diproses oleh:</strong> {{ $approverName }}</p>
            @if($notes)
            <p><strong>Catatan:</strong> {{ $notes }}</p>
            @endif
        </div>

        <a href="{{ route('dashboard.index') }}" class="btn">Lihat Dashboard</a>
    </div>
    <div class="footer">
        Pesan ini dikirim otomatis oleh sistem {{ config('app.name') }}. Mohon tidak membalas email ini.
    </div>
</div>
</body>
</html>
