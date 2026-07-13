@extends('layouts.mobile.modern')

@section('title', 'Daily Report Business')

@section('header_left')
    <a href="{{ route('aktivitaskaryawan.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background: #f8fafc !important;
        }
        .form-section-title {
            font-size: 14px;
            font-weight: 800;
            color: #32745e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .report-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 20px;
            border: 1px solid #f1f5f9;
        }
        
        .platform-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 16px;
            color: #1e293b;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .input-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .input-group-modern {
            position: relative;
        }
        
        .input-group-modern label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        
        .input-group-modern input[type="number"], .input-group-modern input[type="text"], .input-group-modern select {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 14px;
            color: #334155;
            transition: all 0.2s;
        }
        
        .input-group-modern input:focus, .input-group-modern select:focus {
            outline: none;
            border-color: #32745e;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(50, 116, 94, 0.1);
        }

        .btn-add-row {
            width: 100%;
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            color: #64748b;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: transparent;
            transition: all 0.2s;
        }

        .btn-add-row:active {
            background: #f1f5f9;
            transform: scale(0.98);
        }

        .dynamic-row {
            background: #f8fafc;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .btn-remove-row {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 28px;
            height: 28px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            border: 2px solid white;
            z-index: 10;
        }

        .btn-submit-report {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #32745e, #235a47);
            color: white;
            border-radius: 16px;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 20px rgba(50, 116, 94, 0.2);
            transition: all 0.3s;
            border: none;
            margin-top: 20px;
        }

        .btn-submit-report:active {
            transform: scale(0.97);
            box-shadow: 0 5px 10px rgba(50, 116, 94, 0.2);
        }

        .status-radio-group {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }

        .status-radio {
            flex: 1;
        }

        .status-radio input[type="radio"] {
            display: none;
        }

        .status-radio label {
            display: block;
            text-align: center;
            padding: 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            color: #64748b;
            transition: all 0.2s;
        }

        .status-radio input[value="cold"]:checked + label {
            background: #eff6ff; border-color: #3b82f6; color: #3b82f6;
        }
        .status-radio input[value="warm"]:checked + label {
            background: #fefce8; border-color: #eab308; color: #eab308;
        }
        .status-radio input[value="hot"]:checked + label {
            background: #fef2f2; border-color: #ef4444; color: #ef4444;
        }
    </style>
@endpush

@section('content')
    <div class="fade-up" style="padding: 15px 15px 100px 15px;">
        
        <form action="{{ route('dailyreportbu.store') }}" method="POST" id="formDailyReport">
            @csrf

            <!-- IDENTITAS REPORT -->
            <div class="report-card">
                <h3 class="form-section-title"><ion-icon name="person-outline"></ion-icon> Data Karyawan</h3>
                
                @if(auth()->user()->hasRole('admin'))
                    <div class="input-group-modern">
                        <label>Pilih Karyawan</label>
                        <select name="nik" required>
                            <option value="">-- Pilih --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->nik }}">{{ $k->nama_karyawan }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl">
                            {{ substr($karyawan->nama_karyawan ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">{{ $karyawan->nama_karyawan ?? auth()->user()->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $karyawan->jabatan->nama_jabatan ?? '-' }} | {{ date('d M Y') }}</p>
                        </div>
                    </div>
                    <input type="hidden" name="nik" value="{{ $karyawan->nik ?? '' }}">
                    <div class="input-group-modern mt-3">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                @endif
            </div>

            <!-- ONLINE ACTIVITIES -->
            <div class="report-card">
                <h3 class="form-section-title"><ion-icon name="earth-outline"></ion-icon> Aktivitas Online</h3>
                
                @foreach($platforms as $platform)
                <div class="mb-6 last:mb-0">
                    <div class="platform-header">
                        @if($platform == 'facebook')
                            <ion-icon name="logo-facebook" style="color: #1877f2;"></ion-icon> Facebook
                        @elseif($platform == 'instagram')
                            <ion-icon name="logo-instagram" style="color: #e4405f;"></ion-icon> Instagram
                        @elseif($platform == 'whatsapp')
                            <ion-icon name="logo-whatsapp" style="color: #25d366;"></ion-icon> WhatsApp
                        @else
                            <ion-icon name="logo-tiktok" style="color: #000000;"></ion-icon> TikTok
                        @endif
                    </div>
                    
                    <div class="input-grid mb-3">
                        <div class="input-group-modern">
                            <label>Posting</label>
                            <input type="number" name="online[{{ $platform }}][posting]" value="0" min="0" onclick="this.select()">
                        </div>
                        <div class="input-group-modern">
                            <label>Share Group</label>
                            <input type="number" name="online[{{ $platform }}][share_group]" value="0" min="0" onclick="this.select()">
                        </div>
                        <div class="input-group-modern">
                            <label>Add Group</label>
                            <input type="number" name="online[{{ $platform }}][add_group]" value="0" min="0" onclick="this.select()">
                        </div>
                        <div class="input-group-modern">
                            <label>Add Friend</label>
                            <input type="number" name="online[{{ $platform }}][add_friend]" value="0" min="0" onclick="this.select()">
                        </div>
                        <div class="input-group-modern">
                            <label>Inbox</label>
                            <input type="number" name="online[{{ $platform }}][inbox]" value="0" min="0" onclick="this.select()">
                        </div>
                        <div class="input-group-modern">
                            <label>Story</label>
                            <input type="number" name="online[{{ $platform }}][story]" value="0" min="0" onclick="this.select()">
                        </div>
                        @if($platform != 'tiktok' && $platform != 'instagram')
                        <div class="input-group-modern">
                            <label>Broadcast</label>
                            <input type="number" name="online[{{ $platform }}][broadcast]" value="0" min="0" onclick="this.select()">
                        </div>
                        <div class="input-group-modern">
                            <label>Fanspage</label>
                            <input type="number" name="online[{{ $platform }}][fanspage]" value="0" min="0" onclick="this.select()">
                        </div>
                        @endif
                    </div>
                    <div class="input-group-modern">
                        <label>Link Postingan</label>
                        <input type="text" name="online[{{ $platform }}][link_postingan]" placeholder="http://...">
                    </div>
                </div>
                @endforeach
            </div>

            <!-- OFFLINE ACTIVITIES -->
            <div class="report-card">
                <h3 class="form-section-title"><ion-icon name="walk-outline"></ion-icon> Aktivitas Offline</h3>
                <div id="offlineContainer">
                    <!-- Default 1 Row -->
                    <div class="dynamic-row">
                        <div class="input-group-modern mb-3">
                            <label>Tipe Kegiatan</label>
                            <select name="offline[0][tipe]">
                                @foreach($tipeOffline as $tipe)
                                    <option value="{{ $tipe }}">{{ ucfirst($tipe) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group-modern mb-3">
                            <label>Nama Prospek</label>
                            <input type="text" name="offline[0][nama_prospek]" placeholder="Masukkan nama">
                        </div>
                        <div class="input-grid mb-3">
                            <div class="input-group-modern">
                                <label>No WhatsApp</label>
                                <input type="text" name="offline[0][whatsapp]" placeholder="08...">
                            </div>
                            <div class="input-group-modern">
                                <label>Alamat / Ket</label>
                                <input type="text" name="offline[0][alamat]" placeholder="Keterangan">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn-add-row" onclick="addOfflineRow()">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Aktivitas Offline
                </button>
            </div>

            <!-- NASABAH PROSPECT -->
            <div class="report-card">
                <h3 class="form-section-title"><ion-icon name="people-outline"></ion-icon> Calon Nasabah</h3>
                <div id="nasabahContainer">
                    <div class="dynamic-row">
                        <div class="input-group-modern mb-3">
                            <label>Nama Prospek</label>
                            <input type="text" name="nasabah[0][nama]" placeholder="Masukkan nama">
                        </div>
                        <div class="input-grid mb-3">
                            <div class="input-group-modern">
                                <label>Sosial Media</label>
                                <input type="text" name="nasabah[0][akun_sosial_media]" placeholder="IG/FB/dll">
                            </div>
                            <div class="input-group-modern">
                                <label>No WhatsApp</label>
                                <input type="text" name="nasabah[0][no_whatsapp]" placeholder="08...">
                            </div>
                        </div>
                        <div class="input-group-modern mb-3">
                            <label>Status Lead</label>
                            <div class="status-radio-group">
                                <div class="status-radio">
                                    <input type="radio" name="nasabah[0][status_lead]" id="s_cold_0" value="cold" checked>
                                    <label for="s_cold_0">Cold</label>
                                </div>
                                <div class="status-radio">
                                    <input type="radio" name="nasabah[0][status_lead]" id="s_warm_0" value="warm">
                                    <label for="s_warm_0">Warm</label>
                                </div>
                                <div class="status-radio">
                                    <input type="radio" name="nasabah[0][status_lead]" id="s_hot_0" value="hot">
                                    <label for="s_hot_0">Hot</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-group-modern">
                            <label>Keterangan</label>
                            <input type="text" name="nasabah[0][keterangan]" placeholder="Catatan tambahan...">
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-add-row" onclick="addNasabahRow()">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Calon Nasabah
                </button>
            </div>

            <div class="report-card">
                <div class="input-group-modern">
                    <label>Catatan Keseluruhan (Opsional)</label>
                    <textarea name="catatan" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" rows="3" placeholder="Masukkan catatan..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit-report" id="btnSubmit">
                <ion-icon name="save-outline"></ion-icon> Simpan Daily Report
            </button>
        </form>

    </div>
@endsection

@push('myscript')
    <script>
        let offlineIndex = 1;
        let nasabahIndex = 1;

        const tipeOfflineOptions = {!! json_encode($tipeOffline ?? ['appointment', 'cto', 'canvasing']) !!};

        function addOfflineRow() {
            let optionsHtml = '';
            tipeOfflineOptions.forEach(tipe => {
                optionsHtml += `<option value="${tipe}">${tipe.charAt(0).toUpperCase() + tipe.slice(1)}</option>`;
            });

            const html = `
                <div class="dynamic-row" id="offlineRow_${offlineIndex}" style="display:none;">
                    <button type="button" class="btn-remove-row" onclick="removeRow('offlineRow_${offlineIndex}')">
                        <ion-icon name="close"></ion-icon>
                    </button>
                    <div class="input-group-modern mb-3">
                        <label>Tipe Kegiatan</label>
                        <select name="offline[${offlineIndex}][tipe]">
                            ${optionsHtml}
                        </select>
                    </div>
                    <div class="input-group-modern mb-3">
                        <label>Nama Prospek</label>
                        <input type="text" name="offline[${offlineIndex}][nama_prospek]" placeholder="Masukkan nama">
                    </div>
                    <div class="input-grid mb-3">
                        <div class="input-group-modern">
                            <label>No WhatsApp</label>
                            <input type="text" name="offline[${offlineIndex}][whatsapp]" placeholder="08...">
                        </div>
                        <div class="input-group-modern">
                            <label>Alamat / Ket</label>
                            <input type="text" name="offline[${offlineIndex}][alamat]" placeholder="Keterangan">
                        </div>
                    </div>
                </div>
            `;
            $('#offlineContainer').append(html);
            $(`#offlineRow_${offlineIndex}`).fadeIn(300);
            offlineIndex++;
        }

        function addNasabahRow() {
            const html = `
                <div class="dynamic-row" id="nasabahRow_${nasabahIndex}" style="display:none;">
                    <button type="button" class="btn-remove-row" onclick="removeRow('nasabahRow_${nasabahIndex}')">
                        <ion-icon name="close"></ion-icon>
                    </button>
                    <div class="input-group-modern mb-3">
                        <label>Nama Prospek</label>
                        <input type="text" name="nasabah[${nasabahIndex}][nama]" placeholder="Masukkan nama">
                    </div>
                    <div class="input-grid mb-3">
                        <div class="input-group-modern">
                            <label>Sosial Media</label>
                            <input type="text" name="nasabah[${nasabahIndex}][akun_sosial_media]" placeholder="IG/FB/dll">
                        </div>
                        <div class="input-group-modern">
                            <label>No WhatsApp</label>
                            <input type="text" name="nasabah[${nasabahIndex}][no_whatsapp]" placeholder="08...">
                        </div>
                    </div>
                    <div class="input-group-modern mb-3">
                        <label>Status Lead</label>
                        <div class="status-radio-group">
                            <div class="status-radio">
                                <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" id="s_cold_${nasabahIndex}" value="cold" checked>
                                <label for="s_cold_${nasabahIndex}">Cold</label>
                            </div>
                            <div class="status-radio">
                                <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" id="s_warm_${nasabahIndex}" value="warm">
                                <label for="s_warm_${nasabahIndex}">Warm</label>
                            </div>
                            <div class="status-radio">
                                <input type="radio" name="nasabah[${nasabahIndex}][status_lead]" id="s_hot_${nasabahIndex}" value="hot">
                                <label for="s_hot_${nasabahIndex}">Hot</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group-modern">
                        <label>Keterangan</label>
                        <input type="text" name="nasabah[${nasabahIndex}][keterangan]" placeholder="Catatan tambahan...">
                    </div>
                </div>
            `;
            $('#nasabahContainer').append(html);
            $(`#nasabahRow_${nasabahIndex}`).fadeIn(300);
            nasabahIndex++;
        }

        function removeRow(id) {
            $(`#${id}`).fadeOut(300, function() {
                $(this).remove();
            });
        }

        $('#formDailyReport').on('submit', function() {
            $('#btnSubmit').html('<ion-icon name="sync-outline" class="animate-spin text-xl"></ion-icon> Menyimpan...').prop('disabled', true).css('opacity', '0.7');
        });
    </script>
    <style>
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
@endpush
