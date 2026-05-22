<div class="row">
    <div class="col">
        <table class="table">
            <tr>
                <th>Kode Izin Cuti</th>
                <td class="text-end">{{ $izincuti->kode_izin_cuti }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td class="text-end">{{ DateToIndo($izincuti->tanggal) }}</td>
            </tr>
            <tr>
                <th>NIK</th>
                <td class="text-end">{{ $izincuti->nik }}</td>
            </tr>
            <tr>
                <th>Nama Karyawan</th>
                <td class="text-end">{{ $izincuti->nama_karyawan }}</td>
            </tr>
            <tr>
                <th>Jabatan</th>
                <td class="text-end">{{ $izincuti->nama_jabatan }}</td>
            </tr>
            <tr>
                <th>Dept</th>
                <td class="text-end">{{ $izincuti->nama_dept }}</td>
            </tr>
            <tr>
                <th>Cabang</th>
                <td class="text-end">{{ $izincuti->nama_cabang }}</td>
            </tr>
            <tr>
                <th>Lama</th>
                <td class="text-end">
                    @php
                        $lama = hitungHari($izincuti->dari, $izincuti->sampai);
                    @endphp
                    {{ $lama }} Hari / {{ DateToIndo($izincuti->dari) }} - {{ DateToIndo($izincuti->sampai) }}
                </td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td class="text-end">{{ $izincuti->keterangan }}</td>
            </tr>
            @if(isset($approvals) && $approvals->count() > 0)
            <tr>
                <th colspan="2" class="table-secondary">
                    <i class="ti ti-file-check me-2"></i>Status Approval
                </th>
            </tr>
            @foreach($approvals as $approval)
            <tr>
                <th>
                    <span class="badge bg-label-secondary">Level {{ $approval->level }}</span>
                    @if($approval->user)
                        {{ $approval->user->name }}
                    @else
                        -
                    @endif
                </th>
                <td class="text-end">
                    @if($approval->status == 'approved')
                        <span class="badge bg-success"><i class="ti ti-check me-1"></i>Disetujui</span>
                    @else
                        <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Ditolak</span>
                    @endif
                    <br>
                    <small class="text-muted">
                        <i class="ti ti-calendar me-1"></i>{{ $approval->created_at->format('d/m/Y') }}
                        <i class="ti ti-clock me-1"></i>{{ $approval->created_at->format('H:i:s') }}
                    </small>
                    @if($approval->keterangan)
                    <br>
                    <small class="text-muted">{{ $approval->keterangan }}</small>
                    @endif
                </td>
            </tr>
            @endforeach
            @endif
        </table>

        {{-- Action buttons when REJECTED --}}
        @if($izincuti->status == 2)
        <div class="alert alert-danger d-flex align-items-start gap-3 rounded-3 mt-3 p-3">
            <i class="ti ti-circle-x fs-4 mt-1 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">Pengajuan Ditolak</div>
                <div class="small text-muted mb-3">
                    Pengajuan ini telah ditolak. Anda dapat membatalkan penolakan (reset ke Pending) agar dapat di-approve ulang, atau langsung edit datanya terlebih dahulu.
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @can('izincuti.edit')
                    <button type="button" class="btn btn-primary btn-sm" id="btnEditFromShow"
                        data-encrypted="{{ $encryptedKode }}"
                        title="Buka form edit">
                        <i class="ti ti-edit me-1"></i>
                        Edit Pengajuan
                    </button>
                    @endcan
                    <!-- Cancel rejection button visible to all users -->
                    <form method="POST" action="{{ route('izincuti.cancelapprove', $encryptedKode) }}" id="formResetCutiVisible" class="d-inline">
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

@if($izincuti->status == 2)
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
        $('#modal').find('.modal-title').text('Edit Izin Cuti');
        $('#loadmodal').html(
            `<div class="sk-wave sk-primary" style="margin:auto">
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
            </div>`
        );
        $('#loadmodal').load('/izincuti/' + encryptedKode + '/edit');
    });
});
</script>
@endif
