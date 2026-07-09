<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Aset HRIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        #reader { width: 100%; border: none !important; border-radius: 12px; overflow: hidden; background: #000; }
        #reader video { object-fit: cover; border-radius: 12px; }
        #reader__dashboard_section_csr span { color: white !important; }
        #reader__dashboard_section_swaplink { color: #60a5fa !important; text-decoration: none; font-weight: bold; }
        #reader button { background-color: #2563eb !important; color: white !important; border: none !important; padding: 8px 16px !important; border-radius: 8px !important; font-weight: 600 !important; cursor: pointer; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen pb-10 flex flex-col">

    <div class="bg-blue-600 text-white shadow-md">
        <div class="max-w-md mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold leading-tight">{{ config('app.name', 'HRIS') }}</h1>
                <p class="text-xs text-blue-200">Asset Scanner</p>
            </div>
            <div class="bg-white/20 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="max-w-md mx-auto mt-4 px-4 flex-1 w-full flex flex-col">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-5 flex-1 flex flex-col">
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Scan Barcode / QR Code</span>
            </div>
            <div class="p-4 flex-1 flex flex-col">
                <p class="text-sm text-slate-500 mb-4 text-center">Arahkan kamera ke Barcode (1D) atau QR Code aset untuk melaporkan perawatannya.</p>
                <div id="reader" class="flex-1 min-h-[300px]"></div>
                
                <div class="mt-6">
                    <div class="relative flex items-center py-2">
                        <div class="flex-grow border-t border-slate-200"></div>
                        <span class="flex-shrink-0 mx-4 text-slate-400 text-xs font-medium uppercase">Atau ketik manual</span>
                        <div class="flex-grow border-t border-slate-200"></div>
                    </div>
                    
                    <form onsubmit="handleManual(event)" class="mt-3 flex gap-2">
                        <input type="text" id="manual_code" placeholder="Cth: AST-SFW-001" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm">Cari</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`);
            // Check if it's a URL
            if(decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
                window.location.href = decodedText;
            } else {
                // Assuming it's just the code e.g. AST-123
                window.location.href = "{{ url('/public/assets') }}/" + decodedText;
            }
        }

        function onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning.
        }

        // Use Html5Qrcode directly to force environment (back) camera automatically
        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error("Gagal memulai kamera: ", err);
            document.getElementById('reader').innerHTML = '<div class="p-4 text-center text-red-500 font-semibold">Tidak dapat mengakses kamera. Pastikan Anda telah memberikan izin akses kamera.</div>';
        });

        function handleManual(e) {
            e.preventDefault();
            const code = document.getElementById('manual_code').value.trim();
            if(code) {
                window.location.href = "{{ url('/public/assets') }}/" + code;
            }
        }
    </script>
</body>
</html>
