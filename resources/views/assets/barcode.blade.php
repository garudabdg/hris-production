<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode — {{ $asset->kode_asset }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            gap: 20px;
        }

        /* ── TOOLBAR ── */
        .toolbar { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .btn {
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn:active { transform: scale(0.97); }
        .btn-print { background: var(--accent); color: #fff; box-shadow: 0 4px 14px rgba(59,130,246,0.35); }
        .btn-download { background: #10b981; color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,0.35); }
        .btn-back  { background: #fff; color: var(--primary); border: 1px solid #e2e8f0; }

        /* ── LABEL CARD ── */
        .label-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.10), 0 1px 4px rgba(0,0,0,0.06);
            width: auto;
            min-width: 340px;
            max-width: 600px;
            overflow: hidden;
        }
        .label-header {
            background: var(--primary);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .label-header .company {
            font-size: 11px; font-weight: 700;
            color: rgba(255,255,255,0.75);
            text-transform: uppercase; letter-spacing: 1.2px;
        }
        .label-header .tag {
            font-size: 9px; font-weight: 700;
            color: rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.1);
            padding: 3px 8px; border-radius: 20px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .label-body { padding: 20px 22px 18px; text-align: center; }
        .asset-name {
            font-size: 16px; font-weight: 800;
            color: var(--primary); line-height: 1.2; margin-bottom: 4px;
        }
        .asset-sub {
            font-size: 11px; color: #94a3b8; font-weight: 500; margin-bottom: 18px;
        }
        .barcode-box {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 14px 16px 10px;
            display: block; width: 100%;
            overflow-x: auto; margin-bottom: 16px;
        }
        .barcode-box svg, .barcode-box img {
            display: block; margin: 0 auto;
            max-width: 100%;
            height: auto;
        }
        .kode-label {
            font-size: 12px; font-weight: 700;
            letter-spacing: 2px; color: #475569; margin-top: 6px;
            word-break: break-all;
        }
        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 8px; text-align: left; margin-top: 4px;
        }
        .info-item {
            background: #f8fafc; border-radius: 8px;
            padding: 8px 10px; border: 1px solid #f1f5f9;
        }
        .info-item.full { grid-column: 1 / -1; }
        .info-label {
            font-size: 9px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .info-val {
            font-size: 12px; font-weight: 600; color: #334155;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            max-width: 100%;
        }
        .status-badge {
            display: inline-block; padding: 2px 10px; border-radius: 20px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .status-tersedia   { background: #d1fae5; color: #065f46; }
        .status-dipinjam   { background: #fef3c7; color: #92400e; }
        .status-tidak_aktif { background: #fee2e2; color: #991b1b; }
        .label-footer { height: 5px; background: linear-gradient(90deg, var(--accent), #6366f1); }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .label-card { box-shadow: none; border: 1px solid #cbd5e1; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn btn-print" onclick="window.print()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
            Print
        </button>
        <button class="btn btn-download" id="btnDownload">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download JPG
        </button>
        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Detail Aset
        </a>
        <a href="{{ route('assets.index') }}" class="btn btn-back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Daftar Aset
        </a>
    </div>

    <div class="label-card">
        <div class="label-header">
            <div class="company">{{ config('app.name') }}</div>
            <div class="tag">Asset Label</div>
        </div>

        <div class="label-body">
            <div class="asset-name">{{ $asset->nama_asset }}</div>
            <div class="asset-sub">
                {{ $asset->category->nama_kategori ?? 'Tanpa Kategori' }}
                @if (optional($asset->cabang)->nama_cabang)
                    &nbsp;·&nbsp;{{ $asset->cabang->nama_cabang }}
                @endif
            </div>

            <div class="barcode-box">
                {!! DNS2D::getBarcodeSVG(route('assets.public_checklist', $asset->kode_asset), 'QRCODE', 5, 5, '#1e293b') !!}
                <div class="kode-label" style="margin-top:12px;">{{ $asset->kode_asset }}</div>
            </div>

            <div class="info-grid">
                @if ($asset->merk)
                <div class="info-item">
                    <div class="info-label">Merk</div>
                    <div class="info-val">{{ $asset->merk }}</div>
                </div>
                @endif
                @if ($asset->no_seri)
                <div class="info-item">
                    <div class="info-label">No. Seri</div>
                    <div class="info-val">{{ $asset->no_seri }}</div>
                </div>
                @endif
                @if ($asset->lokasi)
                <div class="info-item full">
                    <div class="info-label">Lokasi</div>
                    <div class="info-val">{{ $asset->lokasi }}</div>
                </div>
                @endif
                <div class="info-item full">
                    <div class="info-label">Kondisi</div>
                    <div class="info-val">{{ ucfirst(str_replace('_', ' ', $asset->kondisi)) }}</div>
                </div>
            </div>
        </div>

        <div class="label-footer"></div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
(function () {
    var btn  = document.getElementById('btnDownload');
    var card = document.querySelector('.label-card');
    if (!btn || !card) return;

    btn.addEventListener('click', function () {
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/></svg> Memproses...';

        html2canvas(card, {
            backgroundColor: '#ffffff',
            scale: 3,
            useCORS: true,
            logging: false,
            width: card.offsetWidth,
            height: card.offsetHeight,
            windowWidth: card.offsetWidth,
            windowHeight: card.offsetHeight
        }).then(function (canvas) {
            var link = document.createElement('a');
            link.download = 'Barcode_{{ $asset->kode_asset }}.jpg';
            link.href = canvas.toDataURL('image/jpeg', 0.95);
            link.click();
            btn.innerHTML = orig;
            btn.disabled = false;
        }).catch(function (e) {
            alert('Gagal download: ' + e.message);
            btn.innerHTML = orig;
            btn.disabled = false;
        });
    });
})();
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

</body>
</html>
