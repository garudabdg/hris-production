@component('mail::message')
# 📋 {{ $typeText }} - {{ $statusText }}

Halo {{ $notifiable->name }},

Pengajuan **{{ $typeText }}** Anda (Kode: **{{ $approvalCode }}**) telah **{{ $statusText }}**.

@if($status == 1)
**Status:** ✅ Disetujui
@elseif($status == 2)
**Status:** ❌ Ditolak
@else
**Status:** ⏳ Diperbarui
@endif

**Oleh:** {{ $approverName }}

@if($notes)
**Catatan:** {{ $notes }}
@endif

@component('mail::button', ['url' => route('dashboard.index')])
Lihat Dashboard
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent
