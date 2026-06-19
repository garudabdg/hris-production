@if(auth()->user()->hasRole('karyawan'))
{{-- ======================================================== --}}
{{-- KARYAWAN VIEW (Tailwind CSS, Desktop Optimized)          --}}
{{-- ======================================================== --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Daily Report Business</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .form-input { 
            width: 100%; border: 1px solid #d1d5db; border-radius: 0.375rem; 
            padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: border-color 0.15s;
        }
        .form-input:focus { outline: none; border-color: #3b82f6; ring: 2px; ring-color: #93c5fd; }
        .number-input::-webkit-inner-spin-button, .number-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .number-input { text-align: center; font-weight: 600; }
    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto pt-24 pb-8 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8 flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <ion-icon name="create" class="text-yellow-600"></ion-icon> Edit Daily Report Business
                </h1>
                <p class="text-sm text-gray-500 mt-1">Perbarui laporan aktivitas Anda untuk tanggal {{ \Carbon\Carbon::parse($report->tanggal)->format('d F Y') }}.</p>
            </div>
            <a href="{{ route('dashboard.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors flex items-center gap-1">
                <ion-icon name="arrow-back"></ion-icon> Kembali
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <ion-icon name="warning" class="text-red-500 text-xl"></ion-icon>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pengisian:</h3>
                        <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('dailyreportbu.update', $report->id) }}" method="POST" id="formReport">
            @csrf
            @method('PUT')
            
            {{-- Personal Info (Auto filled for Karyawan) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Nama</label>
                    <div class="font-bold text-gray-900">{{ $karyawan->nama_karyawan }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tanggal</label>
                    <div class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($report->tanggal)->format('d M Y') }}</div>
                    <input type="hidden" name="tanggal" value="{{ $report->tanggal->format('Y-m-d') }}">
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Team</label>
                    <div class="font-bold text-gray-900">{{ $report->sub_departemen ?? '-' }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Divisi</label>
                    <div class="font-bold text-gray-900">BUSINESS (BU)</div>
                </div>
            </div>

            {{-- Section 1: Online --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <ion-icon name="globe-outline"></ion-icon> SECTION 1: AKTIVITAS ONLINE
                    </h2>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-600 uppercase bg-gray-50 rounded-lg">
                            <tr>
                                <th class="px-4 py-3 font-semibold rounded-l-lg">Platform</th>
                                <th class="px-2 py-3 text-center font-semibold">Posting</th>
                                <th class="px-2 py-3 text-center font-semibold">Share Group</th>
                                <th class="px-2 py-3 text-center font-semibold">Add Group</th>
                                <th class="px-2 py-3 text-center font-semibold">Add Friend</th>
                                <th class="px-2 py-3 text-center font-semibold">Inbox</th>
                                <th class="px-2 py-3 text-center font-semibold">Story</th>
                                <th class="px-2 py-3 text-center font-semibold">Broadcast</th>
                                <th class="px-2 py-3 text-center font-semibold">Fanspage</th>
                                <th class="px-2 py-3 text-center font-semibold rounded-r-lg">Link Postingan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($platforms as $platform)
                            @php
                                $act = $report->onlineActivities->where('platform', $platform)->first();
                            @endphp
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 font-bold text-gray-800 capitalize flex items-center gap-2">
                                    <ion-icon name="logo-{{ $platform }}" class="text-xl"></ion-icon> {{ $platform }}
                                </td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][posting]" value="{{ $act->posting ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][share_group]" value="{{ $act->share_group ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][add_group]" value="{{ $act->add_group ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][add_friend]" value="{{ $act->add_friend ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][inbox]" value="{{ $act->inbox ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][story]" value="{{ $act->story ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][broadcast]" value="{{ $act->broadcast ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="number" name="online[{{ $platform }}][fanspage]" value="{{ $act->fanspage ?? 0 }}" min="0" class="form-input number-input online-input" onclick="this.select()"></td>
                                <td class="px-2 py-2"><input type="text" name="online[{{ $platform }}][link_postingan]" value="{{ $act->link_postingan ?? '' }}" class="form-input" placeholder="http://..."></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-3 font-bold text-gray-800 text-right">TOTAL</td>
                                <td colspan="9" class="px-4 py-3 font-bold text-blue-600 text-xl text-center" id="total_online">{{ $report->total_online }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Section 2: Offline --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-yellow-500 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <ion-icon name="people-outline"></ion-icon> SECTION 2: AKTIVITAS OFFLINE
                    </h2>
                    <button type="button" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded text-white text-sm font-semibold transition-colors" onclick="addOfflineRow()">
                        + Tambah Baris
                    </button>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-sm text-left" id="tableOffline">
                        <thead class="text-xs text-gray-600 uppercase bg-gray-50 rounded-lg">
                            <tr>
                                <th class="px-4 py-3 font-semibold rounded-l-lg w-[200px]">Tipe Kegiatan</th>
                                <th class="px-4 py-3 font-semibold">Nama Prospek</th>
                                <th class="px-4 py-3 font-semibold">No WhatsApp</th>
                                <th class="px-4 py-3 font-semibold">Alamat / Keterangan</th>
                                <th class="px-4 py-3 font-semibold text-center rounded-r-lg w-[80px]">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="offlineBody">
                            @php $offIdx = 0; @endphp
                            @forelse($report->offlineActivities as $offline)
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2">
                                    <select name="offline[{{ $offIdx }}][tipe]" class="form-input">
                                        @foreach($tipeOffline as $tipe)
                                            <option value="{{ $tipe }}" class="capitalize" {{ $offline->tipe == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-2"><input type="text" name="offline[{{ $offIdx }}][nama_prospek]" value="{{ $offline->nama_prospek }}" class="form-input" placeholder="Nama Prospek"></td>
                                <td class="px-2 py-2"><input type="text" name="offline[{{ $offIdx }}][whatsapp]" value="{{ $offline->whatsapp }}" class="form-input" placeholder="No WhatsApp"></td>
                                <td class="px-2 py-2"><input type="text" name="offline[{{ $offIdx }}][alamat]" value="{{ $offline->alamat }}" class="form-input" placeholder="Alamat/Keterangan"></td>
                                <td class="px-2 py-2 text-center">
                                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
                                </td>
                            </tr>
                            @php $offIdx++; @endphp
                            @empty
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2">
                                    <select name="offline[0][tipe]" class="form-input">
                                        @foreach($tipeOffline as $tipe)
                                            <option value="{{ $tipe }}" class="capitalize">{{ $tipe }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-2"><input type="text" name="offline[0][nama_prospek]" class="form-input" placeholder="Nama Prospek"></td>
                                <td class="px-2 py-2"><input type="text" name="offline[0][whatsapp]" class="form-input" placeholder="No WhatsApp"></td>
                                <td class="px-2 py-2"><input type="text" name="offline[0][alamat]" class="form-input" placeholder="Alamat/Keterangan"></td>
                                <td class="px-2 py-2 text-center">
                                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Section 3: Nasabah --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-green-600 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <ion-icon name="book-outline"></ion-icon> SECTION 3: PENGOLAHAN DATA CALON NASABAH
                    </h2>
                    <button type="button" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded text-white text-sm font-semibold transition-colors" onclick="addNasabahRow()">
                        + Tambah Baris
                    </button>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-sm text-left" id="tableNasabah">
                        <thead class="text-xs text-gray-600 uppercase bg-gray-50 rounded-lg">
                            <tr>
                                <th class="px-4 py-3 font-semibold rounded-l-lg">Nama Prospek</th>
                                <th class="px-4 py-3 font-semibold">Akun Sosial Media</th>
                                <th class="px-4 py-3 font-semibold">No WhatsApp</th>
                                <th class="px-4 py-3 font-semibold text-center w-[250px]">Status Lead</th>
                                <th class="px-4 py-3 font-semibold">Keterangan</th>
                                <th class="px-4 py-3 font-semibold text-center rounded-r-lg w-[80px]">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="nasabahBody">
                            @php $nasIdx = 0; @endphp
                            @forelse($report->nasabahData as $nasabah)
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2"><input type="text" name="nasabah[{{ $nasIdx }}][nama]" value="{{ $nasabah->nama }}" class="form-input" placeholder="Nama Prospek"></td>
                                <td class="px-2 py-2"><input type="text" name="nasabah[{{ $nasIdx }}][akun_sosial_media]" value="{{ $nasabah->akun_sosial_media }}" class="form-input" placeholder="IG/FB/TikTok dll"></td>
                                <td class="px-2 py-2"><input type="text" name="nasabah[{{ $nasIdx }}][no_whatsapp]" value="{{ $nasabah->no_whatsapp }}" class="form-input" placeholder="No WhatsApp"></td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2 justify-center">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="nasabah[{{ $nasIdx }}][status_lead]" value="cold" {{ $nasabah->status_lead == 'cold' ? 'checked' : '' }} class="peer sr-only">
                                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-blue-500 peer-checked:text-white transition-colors">Cold</span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="nasabah[{{ $nasIdx }}][status_lead]" value="warm" {{ $nasabah->status_lead == 'warm' ? 'checked' : '' }} class="peer sr-only">
                                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-yellow-500 peer-checked:text-white transition-colors">Warm</span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="nasabah[{{ $nasIdx }}][status_lead]" value="hot" {{ $nasabah->status_lead == 'hot' ? 'checked' : '' }} class="peer sr-only">
                                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-red-500 peer-checked:text-white transition-colors">Hot</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 py-2"><input type="text" name="nasabah[{{ $nasIdx }}][keterangan]" value="{{ $nasabah->keterangan }}" class="form-input" placeholder="Keterangan"></td>
                                <td class="px-2 py-2 text-center">
                                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
                                </td>
                            </tr>
                            @php $nasIdx++; @endphp
                            @empty
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2"><input type="text" name="nasabah[0][nama]" class="form-input" placeholder="Nama Prospek"></td>
                                <td class="px-2 py-2"><input type="text" name="nasabah[0][akun_sosial_media]" class="form-input" placeholder="IG/FB/TikTok dll"></td>
                                <td class="px-2 py-2"><input type="text" name="nasabah[0][no_whatsapp]" class="form-input" placeholder="No WhatsApp"></td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2 justify-center">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="nasabah[0][status_lead]" value="cold" checked class="peer sr-only">
                                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-blue-500 peer-checked:text-white transition-colors">Cold</span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="nasabah[0][status_lead]" value="warm" class="peer sr-only">
                                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-yellow-500 peer-checked:text-white transition-colors">Warm</span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="nasabah[0][status_lead]" value="hot" class="peer sr-only">
                                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-red-500 peer-checked:text-white transition-colors">Hot</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 py-2"><input type="text" name="nasabah[0][keterangan]" class="form-input" placeholder="Keterangan"></td>
                                <td class="px-2 py-2 text-center">
                                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3 mb-10">
                <a href="{{ route('dashboard.index') }}" class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">Batal</a>
                <button type="button" onclick="submitForm()" class="px-8 py-3 bg-yellow-500 text-white rounded-lg font-bold hover:bg-yellow-600 transition-colors shadow-sm flex items-center gap-2">
                    <ion-icon name="save"></ion-icon> Perbarui Report
                </button>
            </div>
        </form>
    </div>

    <script>
        let offlineIndex = {{ max($offIdx ?? 1, 1) * 100 }}; // Make sure it's unique
        function addOfflineRow() {
            let row = `
            <tr class="border-b border-gray-100">
                <td class="px-2 py-2">
                    <select name="offline[${offlineIndex}][tipe]" class="form-input">
                        @foreach($tipeOffline as $tipe)
                            <option value="{{ $tipe }}" class="capitalize">{{ $tipe }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-2 py-2"><input type="text" name="offline[${offlineIndex}][nama_prospek]" class="form-input" placeholder="Nama Prospek"></td>
                <td class="px-2 py-2"><input type="text" name="offline[${offlineIndex}][whatsapp]" class="form-input" placeholder="No WhatsApp"></td>
                <td class="px-2 py-2"><input type="text" name="offline[${offlineIndex}][alamat]" class="form-input" placeholder="Alamat/Keterangan"></td>
                <td class="px-2 py-2 text-center">
                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
                </td>
            </tr>`;
            $('#offlineBody').append(row);
            offlineIndex++;
        }

        let nasabahIndex = {{ max($nasIdx ?? 1, 1) * 100 }};
        function addNasabahRow() {
            let row = `
            <tr class="border-b border-gray-100">
                <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][nama]" class="form-input" placeholder="Nama Prospek"></td>
                <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][akun_sosial_media]" class="form-input" placeholder="IG/FB/TikTok dll"></td>
                <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][no_whatsapp]" class="form-input" placeholder="No WhatsApp"></td>
                <td class="px-2 py-2">
                    <div class="flex gap-2 justify-center">
                        <label class="cursor-pointer">
                            <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" value="cold" checked class="peer sr-only">
                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-blue-500 peer-checked:text-white transition-colors">Cold</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" value="warm" class="peer sr-only">
                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-yellow-500 peer-checked:text-white transition-colors">Warm</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" value="hot" class="peer sr-only">
                            <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 peer-checked:bg-red-500 peer-checked:text-white transition-colors">Hot</span>
                        </label>
                    </div>
                </td>
                <td class="px-2 py-2"><input type="text" name="nasabah[${nasabahIndex}][keterangan]" class="form-input" placeholder="Keterangan"></td>
                <td class="px-2 py-2 text-center">
                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><ion-icon name="trash"></ion-icon></button>
                </td>
            </tr>`;
            $('#nasabahBody').append(row);
            nasabahIndex++;
        }

        $(document).on('input', '.online-input', function() {
            let total = 0;
            $('.online-input').each(function() {
                let val = parseInt($(this).val()) || 0;
                total += val;
            });
            $('#total_online').text(total);
        });

        function submitForm() {
            Swal.fire({
                title: 'Perbarui Daily Report?',
                text: "Pastikan data yang diisi sudah benar",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#eab308',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Perbarui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#formReport').submit();
                }
            })
        }
    </script>
</body>
</html>
@else
{{-- ======================================================== --}}
{{-- ADMIN VIEW (Bootstrap, layouts.app)                        --}}
{{-- ======================================================== --}}
@extends('layouts.app')
@section('titlepage', 'Edit Daily Report Business')

@section('content')
@section('navigasi')
    <span>Daily Report Business</span> / <span>Edit</span>
@endsection

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Form Edit Daily Report Business (Manual by Admin)</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('dailyreportbu.update', $report->id) }}" method="POST" id="formReportAdmin">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nik" class="form-label fw-bold">Karyawan BU</label>
                                <input type="text" class="form-control bg-light" value="{{ $report->karyawan->nik }} - {{ $report->karyawan->nama_karyawan }}" readonly>
                                <input type="hidden" name="nik" value="{{ $report->nik }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="tanggal" class="form-label fw-bold">Tanggal Report</label>
                                <input type="text" class="form-control bg-light" value="{{ $report->tanggal->format('d F Y') }}" readonly>
                                <input type="hidden" name="tanggal" value="{{ $report->tanggal->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Section 1 --}}
                    <h6 class="fw-bold mt-4 mb-3 text-primary"><i class="ti ti-world me-2"></i>SECTION 1: AKTIVITAS ONLINE</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Platform</th>
                                    <th>Posting</th>
                                    <th>Share Group</th>
                                    <th>Add Group</th>
                                    <th>Add Friend</th>
                                    <th>Inbox</th>
                                    <th>Story</th>
                                    <th>Broadcast</th>
                                    <th>Fanspage</th>
                                    <th>Link Postingan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($platforms as $platform)
                                @php
                                    $act = $report->onlineActivities->where('platform', $platform)->first();
                                @endphp
                                <tr>
                                    <td class="text-start text-capitalize fw-bold"><i class="ti ti-brand-{{ $platform }} me-1"></i> {{ $platform }}</td>
                                    <td><input type="number" name="online[{{ $platform }}][posting]" value="{{ $act->posting ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="number" name="online[{{ $platform }}][share_group]" value="{{ $act->share_group ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="number" name="online[{{ $platform }}][add_group]" value="{{ $act->add_group ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="number" name="online[{{ $platform }}][add_friend]" value="{{ $act->add_friend ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="number" name="online[{{ $platform }}][inbox]" value="{{ $act->inbox ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="number" name="online[{{ $platform }}][story]" value="{{ $act->story ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="number" name="online[{{ $platform }}][broadcast]" value="{{ $act->broadcast ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="number" name="online[{{ $platform }}][fanspage]" value="{{ $act->fanspage ?? 0 }}" min="0" class="form-control text-center online-input-admin" onclick="this.select()"></td>
                                    <td><input type="text" name="online[{{ $platform }}][link_postingan]" value="{{ $act->link_postingan ?? '' }}" class="form-control" placeholder="http://..."></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-end fw-bold">TOTAL</td>
                                    <td colspan="9" class="text-center text-primary fs-5 fw-bold" id="total_online_admin">{{ $report->total_online }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Section 2 --}}
                    <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                        <h6 class="fw-bold mb-0 text-warning"><i class="ti ti-users me-2"></i>SECTION 2: AKTIVITAS OFFLINE</h6>
                        <button type="button" class="btn btn-warning btn-sm" onclick="addOfflineRowAdmin()"><i class="ti ti-plus me-1"></i> Tambah Baris</button>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="tableOfflineAdmin">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 200px;">Tipe Kegiatan</th>
                                    <th>Nama Prospek</th>
                                    <th>No WhatsApp</th>
                                    <th>Alamat / Keterangan</th>
                                    <th style="width: 80px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="offlineBodyAdmin">
                                @php $offIdxAdm = 0; @endphp
                                @forelse($report->offlineActivities as $offline)
                                <tr>
                                    <td>
                                        <select name="offline[{{ $offIdxAdm }}][tipe]" class="form-select">
                                            @foreach($tipeOffline as $tipe)
                                                <option value="{{ $tipe }}" class="text-capitalize" {{ $offline->tipe == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="offline[{{ $offIdxAdm }}][nama_prospek]" value="{{ $offline->nama_prospek }}" class="form-control" placeholder="Nama Prospek"></td>
                                    <td><input type="text" name="offline[{{ $offIdxAdm }}][whatsapp]" value="{{ $offline->whatsapp }}" class="form-control" placeholder="No WhatsApp"></td>
                                    <td><input type="text" name="offline[{{ $offIdxAdm }}][alamat]" value="{{ $offline->alamat }}" class="form-control" placeholder="Alamat/Keterangan"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                @php $offIdxAdm++; @endphp
                                @empty
                                <tr>
                                    <td>
                                        <select name="offline[0][tipe]" class="form-select">
                                            @foreach($tipeOffline as $tipe)
                                                <option value="{{ $tipe }}" class="text-capitalize">{{ $tipe }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="offline[0][nama_prospek]" class="form-control" placeholder="Nama Prospek"></td>
                                    <td><input type="text" name="offline[0][whatsapp]" class="form-control" placeholder="No WhatsApp"></td>
                                    <td><input type="text" name="offline[0][alamat]" class="form-control" placeholder="Alamat/Keterangan"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Section 3 --}}
                    <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                        <h6 class="fw-bold mb-0 text-success"><i class="ti ti-address-book me-2"></i>SECTION 3: DATA CALON NASABAH</h6>
                        <button type="button" class="btn btn-success btn-sm" onclick="addNasabahRowAdmin()"><i class="ti ti-plus me-1"></i> Tambah Baris</button>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="tableNasabahAdmin">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama Prospek</th>
                                    <th>Akun Sosmed</th>
                                    <th>No WhatsApp</th>
                                    <th style="width: 200px;" class="text-center">Status Lead</th>
                                    <th>Keterangan</th>
                                    <th style="width: 80px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="nasabahBodyAdmin">
                                @php $nasIdxAdm = 0; @endphp
                                @forelse($report->nasabahData as $nasabah)
                                <tr>
                                    <td><input type="text" name="nasabah[{{ $nasIdxAdm }}][nama]" value="{{ $nasabah->nama }}" class="form-control" placeholder="Nama Prospek"></td>
                                    <td><input type="text" name="nasabah[{{ $nasIdxAdm }}][akun_sosial_media]" value="{{ $nasabah->akun_sosial_media }}" class="form-control" placeholder="Sosmed"></td>
                                    <td><input type="text" name="nasabah[{{ $nasIdxAdm }}][no_whatsapp]" value="{{ $nasabah->no_whatsapp }}" class="form-control" placeholder="No WhatsApp"></td>
                                    <td class="text-center">
                                        <select name="nasabah[{{ $nasIdxAdm }}][status_lead]" class="form-select">
                                            <option value="cold" {{ $nasabah->status_lead == 'cold' ? 'selected' : '' }}>Cold</option>
                                            <option value="warm" {{ $nasabah->status_lead == 'warm' ? 'selected' : '' }}>Warm</option>
                                            <option value="hot" {{ $nasabah->status_lead == 'hot' ? 'selected' : '' }}>Hot</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="nasabah[{{ $nasIdxAdm }}][keterangan]" value="{{ $nasabah->keterangan }}" class="form-control" placeholder="Keterangan"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                @php $nasIdxAdm++; @endphp
                                @empty
                                <tr>
                                    <td><input type="text" name="nasabah[0][nama]" class="form-control" placeholder="Nama Prospek"></td>
                                    <td><input type="text" name="nasabah[0][akun_sosial_media]" class="form-control" placeholder="Sosmed"></td>
                                    <td><input type="text" name="nasabah[0][no_whatsapp]" class="form-control" placeholder="No WhatsApp"></td>
                                    <td class="text-center">
                                        <select name="nasabah[0][status_lead]" class="form-select">
                                            <option value="cold">Cold</option>
                                            <option value="warm">Warm</option>
                                            <option value="hot">Hot</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="nasabah[0][keterangan]" class="form-control" placeholder="Keterangan"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label for="catatan" class="form-label fw-bold">Catatan Laporan</label>
                        <textarea name="catatan" id="catatan" rows="3" class="form-control" placeholder="Opsional">{{ $report->catatan }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('dailyreportbu.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning"><i class="ti ti-pencil me-1"></i> Perbarui Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
    let offlineIdxAdmin = {{ max($offIdxAdm ?? 1, 1) * 100 }};
    function addOfflineRowAdmin() {
        let row = `
        <tr>
            <td>
                <select name="offline[${offlineIdxAdmin}][tipe]" class="form-select">
                    @foreach($tipeOffline as $tipe)
                        <option value="{{ $tipe }}" class="text-capitalize">{{ $tipe }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="offline[${offlineIdxAdmin}][nama_prospek]" class="form-control" placeholder="Nama Prospek"></td>
            <td><input type="text" name="offline[${offlineIdxAdmin}][whatsapp]" class="form-control" placeholder="No WhatsApp"></td>
            <td><input type="text" name="offline[${offlineIdxAdmin}][alamat]" class="form-control" placeholder="Alamat/Keterangan"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
            </td>
        </tr>`;
        $('#offlineBodyAdmin').append(row);
        offlineIdxAdmin++;
    }

    let nasabahIdxAdmin = {{ max($nasIdxAdm ?? 1, 1) * 100 }};
    function addNasabahRowAdmin() {
        let row = `
        <tr>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][nama]" class="form-control" placeholder="Nama Prospek"></td>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][akun_sosial_media]" class="form-control" placeholder="Sosmed"></td>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][no_whatsapp]" class="form-control" placeholder="No WhatsApp"></td>
            <td class="text-center">
                <select name="nasabah[${nasabahIdxAdmin}][status_lead]" class="form-select">
                    <option value="cold">Cold</option>
                    <option value="warm">Warm</option>
                    <option value="hot">Hot</option>
                </select>
            </td>
            <td><input type="text" name="nasabah[${nasabahIdxAdmin}][keterangan]" class="form-control" placeholder="Keterangan"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin anda hapus baris ini?')) $(this).closest('tr').remove()"><i class="ti ti-trash"></i></button>
            </td>
        </tr>`;
        $('#nasabahBodyAdmin').append(row);
        nasabahIdxAdmin++;
    }

    $(document).on('input', '.online-input-admin', function() {
        let total = 0;
        $('.online-input-admin').each(function() {
            let val = parseInt($(this).val()) || 0;
            total += val;
        });
        $('#total_online_admin').text(total);
    });

    $(function() {
        $('.select2').select2({ width: '100%' });
        $('.flatpickr-date').flatpickr({ dateFormat: "Y-m-d" });
    });
</script>
@endpush
@endif
