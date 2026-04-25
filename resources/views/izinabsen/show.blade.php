<div class="row">
    <div class="col">
        <table class="table">
            <tr>
                <th>Kode Izin</th>
                <td class="text-end">{{ $izinabsen->kode_izin }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td class="text-end">{{ DateToIndo($izinabsen->tanggal) }}</td>
            </tr>
            <tr>
                <th>NIK</th>
                <td class="text-end">{{ $izinabsen->nik }}</td>
            </tr>
            <tr>
                <th>Nama Karyawan</th>
                <td class="text-end">{{ $izinabsen->nama_karyawan }}</td>
            </tr>
            <tr>
                <th>Jabatan</th>
                <td class="text-end">{{ $izinabsen->nama_jabatan }}</td>
            </tr>
            <tr>
                <th>Dept</th>
                <td class="text-end">{{ $izinabsen->nama_dept }}</td>
            </tr>
            <tr>
                <th>Cabang</th>
                <td class="text-end">{{ $izinabsen->nama_cabang }}</td>
            </tr>
            <tr>
                <th>Lama</th>
                <td class="text-end">
                    @php
                        $lama = hitungHari($izinabsen->dari, $izinabsen->sampai);
                    @endphp
                    {{ $lama }} Hari / {{ DateToIndo($izinabsen->dari) }} - {{ DateToIndo($izinabsen->sampai) }}
                </td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td class="text-end">{{ $izinabsen->keterangan }}</td>
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
    </div>
</div>
