<form action="{{ route('lembur.storeapprove', Crypt::encrypt($lembur->id)) }}" method="POST" id="formApprovelembur">
    @csrf
    <div class="row">
        <div class="col">
            <table class="table">
                <tr>
                    <th>Tanggal</th>
                    <td class="text-end">{{ DateToIndo($lembur->tanggal) }}</td>
                </tr>
                <tr>
                    <th>NIK</th>
                    <td class="text-end">{{ $lembur->nik }}</td>
                </tr>
                <tr>
                    <th>Nama Karyawan</th>
                    <td class="text-end">{{ $lembur->nama_karyawan }}</td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td class="text-end">{{ $lembur->nama_jabatan }}</td>
                </tr>
                <tr>
                    <th>Dept</th>
                    <td class="text-end">{{ $lembur->nama_dept }}</td>
                </tr>
                <tr>
                    <th>Cabang</th>
                    <td class="text-end">{{ $lembur->nama_cabang }}</td>
                </tr>
                <tr>
                    <th>Waktu Lembur</th>
                    <td class="text-end">
                        {{ date('d-m-Y H:i:s', strtotime($lembur->lembur_mulai)) }} -
                        {{ date('d-m-Y H:i:s', strtotime($lembur->lembur_selesai)) }}
                    </td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td class="text-end">{{ $lembur->keterangan }}</td>
                </tr>

                {{-- Histori Approval --}}
                @if ($approvals->count() > 0)
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

    <div class="row mt-3">
        <div class="col">
            <label class="form-label fw-bold" style="font-size: 13px;">Catatan / Keterangan <small class="text-muted fw-normal">(opsional, wajib jika menolak)</small></label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Tulis catatan untuk approval ini..."></textarea>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col">
            <button class="btn btn-primary w-100" name="approve" type="submit" value="approve"><i
                    class="ti ti-thumb-up me-1"></i> Approve </button>
        </div>
        <div class="col">
            <button class="btn btn-danger w-100" name="tolak" type="submit" value="tolak"><i
                    class="ti ti-thumb-down me-1"></i> Tolak </button>
        </div>
    </div>

</form>

<script>
    $(document).on('click', '[name="approve"]', function() {
        $('#formApprovelembur').submit();
        $(this).prop('readonly', true);
        $('button[name="tolak"]').prop('disabled', true);
        $(this).html("<i class='fa fa-spin fa-spinner me-1'></i> Processing...");
    })
</script>
