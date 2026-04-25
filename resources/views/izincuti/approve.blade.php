<form action="{{ isset($isDelegation) && $isDelegation ? route('karyawan-approval.izincuti.storeapprove', Crypt::encrypt($izincuti->kode_izin_cuti)) : route('izincuti.storeapprove', Crypt::encrypt($izincuti->kode_izin_cuti)) }}" method="POST" id="formApproveizincuti">
    @csrf
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
                        <i class="ti ti-file-check me-2"></i>Riwayat Approval
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

        </div>
    </div>
    
    <div class="row mt-2 mb-2">
        <div class="col">
            <x-textarea label="Catatan" name="catatan" />
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button class="btn btn-primary w-100" name="approve" type="submit" value="approve"><i class="ti ti-thumb-up me-1"></i> Approve </button>
        </div>
        <div class="col">
            <button class="btn btn-danger w-100" name="tolak" type="submit" value="tolak"><i class="ti ti-thumb-down me-1"></i> Tolak </button>
        </div>
    </div>

</form>

<script>
    $(document).on('click', '[name="approve"]', function() {
        $('#formApproveizincuti').submit();
        $(this).prop('readonly', true);
        $('button[name="tolak"]').prop('disabled', true);
        $(this).html("<i class='fa fa-spin fa-spinner me-1'></i> Processing...");
    })
</script>
