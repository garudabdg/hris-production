<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode Verifikasi Setup Akun</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 32px; }
        .content p { font-size: 16px; line-height: 1.5; margin-bottom: 16px; color: #4b5563; }
        .otp-box { background: #f8fafc; border: 2px dashed #93c5fd; border-radius: 8px; padding: 20px; text-align: center; margin: 24px 0; }
        .otp-code { font-size: 36px; font-weight: 700; color: #1e40af; letter-spacing: 4px; }
        .footer { background: #f1f5f9; padding: 16px; text-align: center; font-size: 14px; color: #64748b; }
        .btn { display: inline-block; background-color: #1e40af; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Kode Verifikasi Setup Akun</h1>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $name }}</strong></p>
            <p>Anda sedang melakukan proses Setup Akun di sistem HRIS. Gunakan kode verifikasi (OTP) berikut untuk menyelesaikan setup dan mengkonfirmasi alamat email Anda:</p>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <div style="text-align: center; margin-bottom: 24px;">
                <a href="{{ route('account.setup.otp') }}" class="btn" style="color: #ffffff !important; text-decoration: none;">Buka Halaman Verifikasi</a>
            </div>
            
            <p>Kode ini hanya berlaku selama <strong>2 menit</strong>. Jangan bagikan kode ini kepada siapapun.</p>
            <p>Jika Anda tidak merasa melakukan proses setup akun, abaikan email ini.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} HRIS System. All rights reserved.
        </div>
    </div>
</body>
</html>
