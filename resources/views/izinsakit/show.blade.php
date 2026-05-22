<div class="row">
    <div class="col">
        <table class="table">
            <tr>
                <th>Kode Izin Sakit</th>
                <td class="text-end">{{ $izinsakit->kode_izin_sakit }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td class="text-end">{{ DateToIndo($izinsakit->tanggal) }}</td>
            </tr>
            <tr>
                <th>NIK</th>
                <td class="text-end">{{ $izinsakit->nik }}</td>
            </tr>
            <tr>
                <th>Nama Karyawan</th>
                <td class="text-end">{{ $izinsakit->nama_karyawan }}</td>
            </tr>
            <tr>
                <th>Jabatan</th>
                <td class="text-end">{{ $izinsakit->nama_jabatan }}</td>
            </tr>
            <tr>
                <th>Dept</th>
                <td class="text-end">{{ $izinsakit->nama_dept }}</td>
            </tr>
            <tr>
                <th>Cabang</th>
                <td class="text-end">{{ $izinsakit->nama_cabang }}</td>
            </tr>
            <tr>
                <th>Lama</th>
                <td class="text-end">
                    @php
                        $lama = hitungHari($izinsakit->dari, $izinsakit->sampai);
                    @endphp
                    {{ $lama }} Hari / {{ DateToIndo($izinsakit->dari) }} - {{ DateToIndo($izinsakit->sampai) }}
                </td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td class="text-end">{{ $izinsakit->keterangan }}</td>
            </tr>

            {{-- Histori Approval --}}
            @if (isset($approvals) && $approvals->count() > 0)
                <tr>
                    <td colspan="2" class="p-0">
                        <div class="bg-light px-3 py-1" style="font-size: 11px; font-weight: 600; letter-spacing: 0.5px; color: #6c757d;">
                            <i class="ti ti-history me-1"></i> HISTORI APPROVAL
                        </div>
                    </td>
                </tr>
                @foreach ($approvals as $approval)
                    <tr>
                        <th>
                            @if ($approval->status == 'approved')
                                <i class="ti ti-user-check text-success me-1"></i>
                            @else
                                <i class="ti ti-user-x text-danger me-1"></i>
                            @endif
                            Level {{ $approval->level }}
                            <br>
                            <small class="text-muted fw-normal">{{ $approval->user?->name ?? '-' }}</small>
                        </th>
                        <td class="text-end">
                            @if ($approval->status == 'approved')
                                <span class="badge bg-success">Disetujui</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                            <br>
                            <small class="text-muted">
                                📅 {{ $approval->created_at->format('d/m/Y') }}
                                🕐 {{ $approval->created_at->format('H:i:s') }}
                            </small>
                            @if ($approval->keterangan)
                                <br><small class="text-muted fst-italic">"{{ $approval->keterangan }}"</small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        </table>

        {{-- Action buttons when REJECTED --}}
        @if($izinsakit->status == 2)
        <div class="alert alert-danger d-flex align-items-start gap-3 rounded-3 mt-3 p-3">
            <i class="ti ti-circle-x fs-4 mt-1 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">Pengajuan Ditolak</div>
                <div class="small text-muted mb-3">
                    Pengajuan ini telah ditolak. Anda dapat membatalkan penolakan (reset ke Pending) agar dapat di-approve ulang, atau langsung edit datanya terlebih dahulu.
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @can('izinsakit.edit')
                    <button type="button" class="btn btn-primary btn-sm" id="btnEditFromShow"
                        data-encrypted="{{ $encryptedKode }}"
                        title="Buka form edit">
                        <i class="ti ti-edit me-1"></i>
                        Edit Pengajuan
                    </button>
                    @endcan
                    <!-- Cancel rejection button visible to all users -->
                    <form method="POST" action="{{ route('izinsakit.cancelapprove', $encryptedKode) }}" id="formResetCutiVisible" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-warning btn-sm" id="btnResetCutiVisible" title="Batalkan penolakan dan reset ke pending">
                            <i class="ti ti-arrow-back-up me-1"></i>
                            Batalkan Penolakan
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@if($izinsakit->status == 2)
<script>
$(function() {
    // Confirm before resetting to pending
    $('#btnResetCutiVisible').on('click', function() {
        Swal.fire({
            title: 'Reset ke Pending?',
            text: 'Penolakan akan dibatalkan dan status pengajuan kembali ke Pending. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formResetCutiVisible').submit();
            }
        });
    });

    // Edit button: swap modal content to the edit form
    $('#btnEditFromShow').on('click', function() {
        const encryptedKode = $(this).data('encrypted');
        // Target the parent modal elements
        $('#modal').find('.modal-title').text('Edit Izin Sakit');
        $('#loadmodal').html(
            `<div class="sk-wave sk-primary" style="margin:auto">
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
            </div>`
        );
        $('#loadmodal').load('/izinsakit/' + encryptedKode + '/edit');
    });
});
</script>
@endif
