<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📢 Pengumuman Baru - HRIS PT Didimax Berjangka</title>
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
            background-color: #f0f7ff;
            padding: 40px 20px;
        }

        .wrapper {
            max-width: 620px;
            margin: 0 auto;
        }

        /* Top Brand Bar */
        .brand-bar {
            background: linear-gradient(90deg, #32745e 0%, #3ab58c 50%, #4ac9a8 100%);
            border-radius: 16px 16px 0 0;
            padding: 8px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-bar-text {
            color: rgba(255,255,255,0.9);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .brand-bar-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,0.7);
        }

        /* Main Container */
        .container {
            background: #ffffff;
            border-radius: 0 0 16px 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(50, 116, 94, 0.15), 0 4px 16px rgba(0,0,0,0.08);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #32745e 0%, #3ab58c 60%, #4ac9a8 100%);
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
            background: rgba(255,255,255,0.08);
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -30px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .header-inner {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .announcement-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .announcement-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .header-title {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .header-subtitle {
            color: rgba(255,255,255,0.85);
            font-size: 16px;
            font-weight: 500;
        }

        /* Wave divider */
        .wave-divider {
            display: block;
            width: 100%;
            background: #32745e;
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
            font-size: 22px;
            font-weight: 700;
            color: #32745e;
            margin-bottom: 16px;
        }

        .greeting span {
            color: #3ab58c;
        }

        .announcement-card {
            background: linear-gradient(135deg, #f0f9f5 0%, #e8f5f0 100%);
            border: 2px solid #c2e8d8;
            border-radius: 14px;
            padding: 28px 32px;
            margin: 24px 0;
            position: relative;
            overflow: hidden;
        }

        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #32745e, #3ab58c, #32745e);
        }

        .announcement-title {
            font-size: 24px;
            font-weight: 800;
            color: #32745e;
            margin-bottom: 16px;
            line-height: 1.3;
        }

        .announcement-content {
            color: #2d3748;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .announcement-content p {
            margin-bottom: 12px;
        }

        .announcement-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(50, 116, 94, 0.1);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4a5568;
            font-size: 13px;
        }

        .meta-icon {
            width: 20px;
            height: 20px;
            background: #e8f5f0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .meta-icon svg {
            width: 12px;
            height: 12px;
            fill: #32745e;
        }

        /* CTA Button */
        .cta-section {
            text-align: center;
            margin: 32px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #32745e 0%, #3ab58c 100%);
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            padding: 16px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(50, 116, 94, 0.3);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(50, 116, 94, 0.4);
        }

        .cta-note {
            font-size: 13px;
            color: #718096;
            margin-top: 12px;
        }

        /* Info Box */
        .info-box {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 10px;
            padding: 20px;
            margin: 24px 0;
        }

        .info-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .info-icon {
            width: 24px;
            height: 24px;
            background: #ffd54f;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-icon svg {
            width: 14px;
            height: 14px;
            fill: #7a5200;
        }

        .info-title-text {
            font-weight: 700;
            color: #7a5200;
            font-size: 15px;
        }

        .info-content {
            color: #7a5200;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e8f0fe;
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
            background: linear-gradient(135deg, #32745e, #3ab58c);
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
            color: #32745e;
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
                <div class="announcement-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 9h-2V5h2v6zm0 4h-2v-2h2v2z"/>
                    </svg>
                </div>
                <div class="header-title">📢 Pengumuman Baru</div>
                <div class="header-subtitle">HRIS Didimax - Informasi Penting Perusahaan</div>
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
            <p class="greeting">Halo <span>{{ $notifiable->name ?? 'Karyawan' }}</span>! 👋</p>
            <p>Ada pengumuman penting dari perusahaan yang perlu Anda ketahui. Silakan baca informasi berikut dengan seksama.</p>

            <!-- Announcement Card -->
            <div class="announcement-card">
                <div class="announcement-title">{{ $pengumuman->judul }}</div>
                <div class="announcement-content">
                    {!! nl2br(e($pengumuman->isi)) !!}
                </div>
                <div class="announcement-meta">
                    <div class="meta-item">
                        <div class="meta-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                            </svg>
                        </div>
                        <span>Dibuat: {{ $pengumuman->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <span>Untuk: Semua Karyawan</span>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="cta-section">
                <a href="{{ $url }}" class="cta-button">📖 Baca Selengkapnya di HRIS</a>
                <p class="cta-note">Klik tombol di atas untuk melihat pengumuman lengkap di sistem HRIS</p>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-title">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                    </div>
                    <div class="info-title-text">Informasi Penting</div>
                </div>
                <div class="info-content">
                    • Pastikan Anda membaca pengumuman dengan teliti<br>
                    • Jika ada pertanyaan, hubungi atasan langsung atau tim HR<br>
                    • Pengumuman ini bersifat resmi dan mengikat
                </div>
            </div>

            <p>Terima kasih atas perhatiannya. Mari kita terus bekerja sama untuk kemajuan perusahaan.</p>

            <hr class="divider">
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo-row">
                <div class="footer-logo-badge">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6h-2.18c.07-.44.18-.89.18-1.36C18 2.07 15.93 0 13.36 0c-1.31 0-2.48.52-3.36 1.36C9.12.52 7.95 0 6.64 0 4.07 0 2 2.07 2 4.64c0 .47.11.92.18 1.36H0v14h20V6zm-9-3.91C11.43 1.42 12.37 1 13.36 1c1.93 0 3.5 1.57 3.5 3.5 0 .48-.1.93-.18 1.36H11V2.09zM6.64 1c.99 0 1.93.42 2.36 1.09V5.86H3.32C3.24 5.43 3.14 4.98 3.14 4.5c0-1.93 1.57-3.5