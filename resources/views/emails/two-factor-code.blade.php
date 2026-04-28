<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #053b22; padding: 30px 24px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body { padding: 32px 24px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 16px; }
        .code-box { text-align: center; margin: 24px 0; }
        .code { display: inline-block; font-size: 40px; font-weight: bold; letter-spacing: 10px; color: #053b22; background: #f0f7f3; border: 2px dashed #053b22; border-radius: 8px; padding: 16px 32px; }
        .info { font-size: 14px; color: #666; margin-top: 16px; }
        .footer { background: #f9f9f9; padding: 16px 24px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Verifikasi Login HRIS</h1>
        </div>
        <div class="body">
            <p class="greeting">Halo, <strong>{{ $userName }}</strong>!</p>
            <p>Anda mencoba login ke sistem HRIS. Masukkan kode verifikasi berikut untuk melanjutkan:</p>
            <div class="code-box">
                <span class="code">{{ $code }}</span>
            </div>
            <p class="info">⏰ Kode ini berlaku selama <strong>10 menit</strong>.</p>
            <p class="info">Jika Anda tidak melakukan percobaan login, abaikan email ini dan segera hubungi administrator.</p>
        </div>
        <div class="footer">
            Email ini dikirim otomatis oleh sistem HRIS. Jangan balas email ini.
        </div>
    </div>
</body>
</html>
