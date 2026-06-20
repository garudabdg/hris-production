<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Print Barcode Asset</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e2e8f0; }

        .toolbar {
            display: flex; gap: 10px; justify-content: center; padding: 15px;
            background: #fff; border-bottom: 1px solid #cbd5e1; margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-print { background: var(--accent); color: #fff; }
        .btn-back { background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; }

        /* A4 Page Styling */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px auto;
            background: #fff;
            padding: 12mm 10mm;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12mm 6mm; /* Row gap and Column gap */
        }

        /* Card Styling - Compact version of barcode.blade.php */
        .label-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* To make sure cards have consistent height */
            height: 100%;
        }

        .label-header {
            background: var(--primary);
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .label-header .company {
            font-size: 8px; font-weight: 700; color: rgba(255,255,255,0.9);
            text-transform: uppercase; letter-spacing: 1px;
        }
        .label-header .tag {
            font-size: 6px; font-weight: 700; color: rgba(255,255,255,0.7);
            background: rgba(255,255,255,0.15); padding: 3px 6px; border-radius: 10px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .label-body { padding: 12px 14px 10px; text-align: center; flex-grow: 1; display: flex; flex-direction: column; }
        .asset-name {
            font-size: 12px; font-weight: 800; color: var(--primary);
            line-height: 1.2; margin-bottom: 3px;
        }
        .asset-sub {
            font-size: 7px; color: #94a3b8; font-weight: 500; margin-bottom: 12px;
        }

        .barcode-box {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 6px; padding: 10px;
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .barcode-box svg, .barcode-box table { 
            display: block; margin: 0 auto; 
            max-width: 100%; 
        }
        .kode-label {
            font-size: 9px; font-weight: 700; letter-spacing: 1.5px;
            color: #475569; margin-top: 5px;
            word-break: break-all;
        }

        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 6px;
            text-align: left; margin-top: auto;
        }
        .info-item {
            background: #f8fafc; border-radius: 6px; padding: 6px 8px; border: 1px solid #f1f5f9;
        }
        .info-item.full { grid-column: 1 / -1; }
        .info-label {
            font-size: 6px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;
        }
        .info-val {
            font-size: 9px; font-weight: 600; color: #334155;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;
        }

        .label-footer { height: 4px; background: linear-gradient(90deg, var(--accent), #6366f1); }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            body { background: transparent; padding: 0; }
            .toolbar { display: none !important; }
            .a4-page { 
                box-shadow: none; margin: 0; padding: 12mm 10mm;
                page-break-after: always; width: 210mm; height: 297mm;
            }
            .label-card {
                /* Ensure borders print */
                border: 1px solid #cbd5e1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn btn-print" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
            Print Barcode (A4)
        </button>
        <button type="button" class="btn btn-back" onclick="window.close()">
            Tutup
        </button>
    </div>

    @foreach($assets->chunk(15) as $chunk)
    <div class="a4-page">
        <div class="grid-container">
            @foreach($chunk as $asset)
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
                        <!-- Ukuran DNS1D disesuaikan sedikit lebih kecil dari versi detail -->
                        {!! DNS1D::getBarcodeHTML($asset->kode_asset, 'C128', 1, 40, '#1e293b') !!}
                        <div class="kode-label">{{ $asset->kode_asset }}</div>
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
            @endforeach
        </div>
    </div>
    @endforeach

</body>
</html>
