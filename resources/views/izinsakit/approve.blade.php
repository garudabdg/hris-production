<form action="{{ isset($isDelegation) && $isDelegation ? route('karyawan-approval.izinsakit.storeapprove', Crypt::encrypt($izinsakit->kode_izin_sakit)) : route('izinsakit.storeapprove', Crypt::encrypt($izinsakit->kode_izin_sakit)) }}" method="POST" id="formApproveizinsakit">
    @csrf
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
    $(document).on('click', '[name="approve"]', function(e) {
        e.preventDefault();
        $('#formApproveizinsakit').append('<input type="hidden" name="approve" value="approve">');
        $('#formApproveizinsakit').submit();
        $(this).prop('disabled', true);
        $('button[name="tolak"]').prop('disabled', true);
        $(this).html("<i class='fa fa-spin fa-spinner me-1'></i> Processing...");
    });

    $(document).on('click', '[name="tolak"]', function(e) {
        e.preventDefault();
        $('#formApproveizinsakit').append('<input type="hidden" name="tolak" value="tolak">');
        $('#formApproveizinsakit').submit();
        $(this).prop('disabled', true);
        $('button[name="approve"]').prop('disabled', true);
        $(this).html("<i class='fa fa-spin fa-spinner me-1'></i> Processing...");
    });
</script>
