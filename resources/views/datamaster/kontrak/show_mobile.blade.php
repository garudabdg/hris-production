@extends('layouts.mobile.modern')
@section('title', 'Detail Kontrak')

@section('header_left')
    <a href="{{ route('kontrak.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background: #f8fafc !important; /* light slate background */
        }
        
        .letter-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            padding: 24px 15px; /* Matched the padding from Pelanggaran */
            font-family: 'Inter', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #334155;
            position: relative;
            overflow: hidden;
        }

        /* Decorative top accent for the document */
        .letter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #32745e, #4b9b82); /* Green theme for contract */
        }

        .contract-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f1f5f9;
            text-transform: uppercase;
        }

        .contract-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .contract-nomor {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        .section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .section-table td {
            vertical-align: top;
            padding: 3px 0;
        }

        .section-table .label {
            width: 110px;
            color: #64748b;
        }

        .section-table .colon {
            width: 10px;
            color: #64748b;
        }

        .section-table strong {
            color: #0f172a;
        }

        .paragraph {
            text-align: justify;
            margin-bottom: 12px;
        }

        .pasal-title {
            text-align: center;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 24px;
            margin-bottom: 12px;
            font-size: 14px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 16px;
        }

        .letter-list {
            padding-left: 20px;
            margin-top: 5px;
            margin-bottom: 12px;
        }
        
        .letter-list li {
            margin-bottom: 6px;
            text-align: justify;
        }

        .comp-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 16px;
            background: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .comp-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .comp-table tr:last-child td {
            border-bottom: none;
        }

        .comp-table td.label {
            width: 60%;
            color: #475569;
            font-weight: 500;
        }

        .comp-table td.value {
            text-align: right;
            font-weight: 600;
            color: #0f172a;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signature-box {
            width: 48%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-title-text {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .signature-space {
            height: 70px;
            width: 100%;
            border-bottom: 1px dashed #cbd5e1;
            margin: 10px 0;
        }

        .signature-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
        }

        /* Animations */
        .fade-up {
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush

@section('content')
@php
    use Carbon\Carbon;
    $startDate = $kontrak->dari ? Carbon::parse($kontrak->dari) : null;
    $endDate = $kontrak->sampai ? Carbon::parse($kontrak->sampai) : null;
    $birthDate = $kontrak->tanggal_lahir ? Carbon::parse($kontrak->tanggal_lahir) : null;
@endphp
    <div class="px-1 pt-2 pb-24">
        <div class="letter-card fade-up">
            <div class="dynamic-konten" style="width: 100%; overflow-x: auto;">
                {!! $konten !!}
            </div>

            <div class="mt-8 text-center px-2">
                <a href="{{ route('kontrak.print', Crypt::encrypt($kontrak->id)) }}" class="flex items-center justify-center gap-2 w-full py-3 bg-[#32745e] text-white rounded-xl font-bold text-sm shadow-lg shadow-[#32745e]/20 active:scale-95 transition-all">
                    <ion-icon name="print-outline" class="text-lg"></ion-icon> Download / Cetak PDF
                </a>
            </div>

        </div>
    </div>
@endsection
