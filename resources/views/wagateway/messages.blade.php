@extends('layouts.app')
@section('titlepage', 'Riwayat Pesan WhatsApp')
@section('navigasi')
    <a href="{{ route('wagateway.index') }}" class="text-muted">WhatsApp Gateway</a>
    <span class="mx-1">/</span>
    <span>Riwayat Pesan</span>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="card text-center border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total Pesan</div>
                <div class="fs-3 fw-bold text-dark">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card text-center border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Berhasil</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['success']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card text-center border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Gagal</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($stats['failed']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card text-center border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Hari Ini</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($stats['today']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <a href="{{ route('wagateway.messages', ['kategori' => 'birthday']) }}" class="text-decoration-none">
            <div class="card text-center border-0 shadow-sm h-100 {{ request('kategori') === 'birthday' ? 'border border-warning' : '' }}">
                <div class="card-body py-3">
                    <div class="text-muted small mb-1">🎂 Ulang Tahun</div>
                    <div class="fs-3 fw-bold text-warning">{{ number_format($stats['birthday']) }}</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0">
            <i class="ti ti-message-2 me-2 text-success"></i>Riwayat Pesan WhatsApp
        </h5>
        <a href="{{ route('wagateway.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Kembali
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('wagateway.messages') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Cari</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nomor / pesan..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="success" @selected(request('status') === 'success')>✓ Berhasil</option>
                        <option value="failed" @selected(request('status') === 'failed')>✗ Gagal</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Kategori</label>
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="birthday"    @selected(request('kategori') === 'birthday')>🎂 Ulang Tahun</option>
                        <option value="presensi"    @selected(request('kategori') === 'presensi')>📋 Presensi</option>
                        <option value="recruitment" @selected(request('kategori') === 'recruitment')>💼 Recruitment</option>
                        <option value="lainnya"     @selected(request('kategori') === 'lainnya')>📌 Lainnya</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="ti ti-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('wagateway.messages') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($messages->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ti ti-message-off" style="font-size:3rem"></i>
                <p class="mt-2 mb-0">Belum ada pesan yang tercatat</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="50">No</th>
                        <th>Penerima</th>
                        <th width="120">Kategori</th>
                        <th>Pesan</th>
                        <th width="110">Status</th>
                        <th width="140">Waktu</th>
                        <th width="60" class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $index => $msg)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $messages->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:14px">
                                    <i class="ti ti-device-mobile"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $msg->penerima }}</div>
                                    @if($msg->pengirim && $msg->pengirim !== 'local')
                                    <div class="text-muted" style="font-size:11px">via {{ $msg->pengirim }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $kategoriMap = [
                                    'birthday'    => ['label' => '🎂 Ulang Tahun', 'class' => 'bg-warning-subtle text-warning border-warning-subtle'],
                                    'presensi'    => ['label' => '📋 Presensi',    'class' => 'bg-info-subtle text-info border-info-subtle'],
                                    'recruitment' => ['label' => '💼 Recruitment', 'class' => 'bg-primary-subtle text-primary border-primary-subtle'],
                                    'lainnya'     => ['label' => '📌 Lainnya',     'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle'],
                                ];
                                $kat = $kategoriMap[$msg->kategori] ?? $kategoriMap['lainnya'];
                            @endphp
                            <span class="badge border px-2 py-1 {{ $kat['class'] }}" style="font-size:11px">
                                {{ $kat['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width:320px" title="{{ $msg->pesan }}">
                                {{ $msg->pesan }}
                            </div>
                            @if($msg->status === 'failed' && $msg->error_message)
                            <div class="text-danger" style="font-size:11px">
                                <i class="ti ti-alert-circle me-1"></i>{{ Str::limit($msg->error_message, 60) }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($msg->status === 'success')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="ti ti-check me-1"></i>Terkirim
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="ti ti-x me-1"></i>Gagal
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">{{ $msg->created_at->format('d/m/Y') }}</div>
                            <div class="small fw-semibold">{{ $msg->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-secondary px-2 py-1"
                                onclick="showDetail({{ json_encode(['penerima' => $msg->penerima, 'pengirim' => $msg->pengirim, 'pesan' => $msg->pesan, 'status' => $msg->status, 'message_id' => $msg->message_id, 'error_message' => $msg->error_message, 'waktu' => $msg->created_at->format('d/m/Y H:i:s')]) }})">
                                <i class="ti ti-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top flex-wrap gap-2">
            <div class="small text-muted">
                Menampilkan {{ $messages->firstItem() }}–{{ $messages->lastItem() }} dari {{ number_format($messages->total()) }} pesan
            </div>
            {{ $messages->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="ti ti-message-2 me-2"></i>Detail Pesan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Pengirim</div>
                        <div class="fw-semibold" id="d-pengirim">-</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Penerima</div>
                        <div class="fw-semibold" id="d-penerima">-</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Status</div>
                        <div id="d-status"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Waktu</div>
                        <div class="fw-semibold small" id="d-waktu">-</div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small mb-1">Pesan</div>
                    <div class="bg-light rounded p-3 small" id="d-pesan" style="white-space:pre-wrap;max-height:200px;overflow-y:auto"></div>
                </div>
                <div id="d-message-id-wrap" class="mb-2 d-none">
                    <div class="text-muted small mb-1">Message ID</div>
                    <code id="d-message-id" class="text-success"></code>
                </div>
                <div id="d-error-wrap" class="d-none">
                    <div class="text-muted small mb-1">Error</div>
                    <div class="alert alert-danger small py-2 mb-0" id="d-error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
function showDetail(data) {
    document.getElementById('d-pengirim').textContent = data.pengirim || 'local';
    document.getElementById('d-penerima').textContent = data.penerima;
    document.getElementById('d-pesan').textContent    = data.pesan;
    document.getElementById('d-waktu').textContent    = data.waktu;

    const statusEl = document.getElementById('d-status');
    if (data.status === 'success') {
        statusEl.innerHTML = '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Terkirim</span>';
    } else {
        statusEl.innerHTML = '<span class="badge bg-danger"><i class="ti ti-x me-1"></i>Gagal</span>';
    }

    const midWrap = document.getElementById('d-message-id-wrap');
    if (data.message_id) {
        document.getElementById('d-message-id').textContent = data.message_id;
        midWrap.classList.remove('d-none');
    } else {
        midWrap.classList.add('d-none');
    }

    const errWrap = document.getElementById('d-error-wrap');
    if (data.error_message) {
        document.getElementById('d-error').textContent = data.error_message;
        errWrap.classList.remove('d-none');
    } else {
        errWrap.classList.add('d-none');
    }

    new bootstrap.Modal(document.getElementById('detailModal')).show();
}
</script>
@endpush
