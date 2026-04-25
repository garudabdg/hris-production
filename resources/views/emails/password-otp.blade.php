<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset OTP - HRIS PT Didimax Berjangka</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #1e2a3a;
            background-color: #e8f0fe;
            padding: 40px 20px;
        }

        .wrapper {
            max-width: 620px;
            margin: 0 auto;
        }

        /* Top Brand Bar */
        .brand-bar {
            background: linear-gradient(90deg, #0a3fa8 0%, #1565d8 50%, #1a7fe8 100%);
            border-radius: 16px 16px 0 0;
            padding: 8px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-bar-text {
            color: rgba(255,255,255,0.7);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .brand-bar-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
        }

        /* Main Container */
        .container {
            background: #ffffff;
            border-radius: 0 0 16px 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(10, 63, 168, 0.15), 0 4px 16px rgba(0,0,0,0.08);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #0a3fa8 0%, #1565d8 60%, #1a7fe8 100%);
            padding: 40px 40px 50px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -30px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }

        .header-inner {
            position: relative;
            z-index: 1;
        }

        .company-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 30px;
            padding: 6px 14px 6px 8px;
            margin-bottom: 20px;
        }

        .badge-icon {
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-icon svg {
            width: 14px;
            height: 14px;
            fill: #1565d8;
        }

        .badge-label {
            color: rgba(255,255,255,0.9);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .header-title {
            font-size: 30px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .header-title span {
            color: rgba(255,255,255,0.65);
            font-weight: 400;
            font-size: 22px;
        }

        .header-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            font-weight: 500;
        }

        /* Wave divider */
        .wave-divider {
            display: block;
            width: 100%;
            background: #0a3fa8;
            line-height: 0;
        }

        .wave-divider svg {
            display: block;
            width: 100%;
        }

        /* Body */
        .body {
            padding: 40px 40px 32px;
        }

        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0a3fa8;
            margin-bottom: 12px;
        }

        .body p {
            color: #4a5568;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        /* OTP Section */
        .otp-section {
            margin: 32px 0;
        }

        .otp-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #1565d8;
            margin-bottom: 12px;
        }

        .otp-card {
            background: linear-gradient(135deg, #f0f5ff 0%, #e8f0ff 100%);
            border: 2px solid #c2d4ff;
            border-radius: 14px;
            padding: 28px 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .otp-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0a3fa8, #1a7fe8, #0a3fa8);
        }

        .otp-digits {
            font-size: 44px;
            font-weight: 800;
            letter-spacing: 14px;
            color: #0a3fa8;
            font-variant-numeric: tabular-nums;
            margin-bottom: 4px;
            text-indent: 14px; /* offset letter-spacing */
        }

        .otp-underline {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .otp-underline span {
            width: 28px;
            height: 3px;
            border-radius: 2px;
            background: #1565d8;
            opacity: 0.4;
        }

        /* Expiry info */
        .expiry-row {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 24px 0;
        }

        .expiry-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            background: #fff3cd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .expiry-icon svg {
            width: 18px;
            height: 18px;
            stroke: #e68900;
        }

        .expiry-text {
            color: #7a5200;
            font-size: 13.5px;
            line-height: 1.5;
        }

        .expiry-text strong {
            color: #e68900;
        }

        /* Security tip */
        .security-tip {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            background: #f0f5ff;
            border-left: 4px solid #1565d8;
            border-radius: 0 10px 10px 0;
            padding: 16px 18px;
            margin-top: 24px;
        }

        .tip-icon {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            background: #1565d8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }

        .tip-icon svg {
            width: 15px;
            height: 15px;
            stroke: white;
            fill: none;
        }

        .tip-text {
            font-size: 13px;
            color: #2d3748;
            line-height: 1.6;
        }

        .tip-text strong {
            color: #0a3fa8;
            font-weight: 700;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e8eef8;
            margin: 32px 0 0;
        }

        /* Footer */
        .footer {
            background: #f7f9ff;
            padding: 24px 40px;
            border-top: 1px solid #e2eafc;
        }

        .footer-logo-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .footer-logo-badge {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0a3fa8, #1565d8);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-logo-badge svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .footer-company {
            font-size: 14px;
            font-weight: 700;
            color: #0a3fa8;
            line-height: 1.2;
        }

        .footer-company-sub {
            font-size: 11px;
            color: #8a9ab5;
            font-weight: 500;
        }

        .footer-note {
            font-size: 12px;
            color: #8a9ab5;
            line-height: 1.6;
            margin-bottom: 4px;
        }

        .footer-copyright {
            font-size: 11.5px;
            color: #b0bcc8;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2eafc;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Top brand bar -->
    <div class="brand-bar">
        <span class="brand-bar-text">PT Didimax Berjangka</span>
        <div class="brand-bar-dot"></div>
        <span class="brand-bar-text">Human Resource Information System</span>
    </div>

    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-inner">
                <div class="company-badge">
                    <div class="badge-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                    </div>
                    <span class="badge-label">HRIS System</span>
                </div>
                <div class="header-title">
                    HRIS <span>Didimax</span>
                </div>
                <div class="header-subtitle">Password Reset Verification</div>
            </div>
        </div>

        <!-- Wave Divider -->
        <div class="wave-divider">
            <svg viewBox="0 0 620 40" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,0 C155,40 465,0 620,30 L620,0 Z" fill="#ffffff"/>
            </svg>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">Halo! 👋</p>
            <p>Kami menerima permintaan untuk mereset password akun <strong>HRIS Didimax</strong> Anda. Gunakan kode OTP berikut untuk memverifikasi identitas Anda.</p>

            <!-- OTP Box -->
            <div class="otp-section">
                <div class="otp-label">🔐 Kode Verifikasi OTP</div>
                <div class="otp-card">
                    <div class="otp-digits">{{ $otp }}</div>
                    <div class="otp-underline">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>
            </div>

            <!-- Expiry -->
            <div class="expiry-row">
                <div class="expiry-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="expiry-text">
                    Kode ini berlaku hingga <strong>{{ $expires_at }}</strong> &nbsp;·&nbsp; <strong>10 menit</strong> dari sekarang
                </div>
            </div>

            <p>Jika Anda tidak merasa meminta reset password, abaikan email ini. Akun Anda tetap aman dan tidak ada perubahan yang dilakukan.</p>

            <!-- Security Tip -->
            <div class="security-tip">
                <div class="tip-icon">
                    <svg viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div class="tip-text">
                    <strong>Tips Keamanan:</strong> Jangan pernah bagikan kode OTP Anda kepada siapapun. Tim HRIS Didimax tidak akan pernah meminta kode OTP Anda melalui telepon, chat, atau email.
                </div>
            </div>

            <hr class="divider">
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo-row">
                <div class="footer-logo-badge">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 6h-2.18c.07-.44.18-.89.18-1.36C18 2.07 15.93 0 13.36 0c-1.31 0-2.48.52-3.36 1.36C9.12.52 7.95 0 6.64 0 4.07 0 2 2.07 2 4.64c0 .47.11.92.18 1.36H0v14h20V6zm-9-3.91C11.43 1.42 12.37 1 13.36 1c1.93 0 3.5 1.57 3.5 3.5 0 .48-.1.93-.18 1.36H11V2.09zM6.64 1c.99 0 1.93.42 2.36 1.09V5.86H3.32C3.24 5.43 3.14 4.98 3.14 4.5c0-1.93 1.57-3.5 3.5-3.5zM1 19V7h8v12H1zm9 0V7h9v12h-9z"/></svg>
                </div>
                <div>
                    <div class="footer-company">HRIS Didimax</div>
                    <div class="footer-company-sub">PT Didimax Berjangka</div>
                </div>
            </div>
            <p class="footer-note">Ini adalah pesan otomatis dari sistem HRIS. Mohon jangan membalas email ini.</p>
            <p class="footer-note">Jika Anda memiliki pertanyaan, silakan hubungi tim HR kami melalui kanal resmi.</p>
            <div class="footer-copyright">
                &copy; {{ date('Y') }} PT Didimax Berjangka &mdash; HRIS Didimax. All rights reserved.
            </div>
        </div>

    </div>
</div>
</body>
</html>