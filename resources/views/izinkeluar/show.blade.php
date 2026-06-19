<div class="row">
    <div class="col-12">
        <table class="table">
            <tr>
                <th>Kode Izin</th>
                <td>{{ $izinkeluar->kode_izin_keluar }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td>{{ date('d-m-Y', strtotime($izinkeluar->tanggal)) }}</td>
            </tr>
            <tr>
                <th>Karyawan</th>
                <td>{{ $izinkeluar->nama_karyawan }}</td>
            </tr>
            <tr>
                <th>Jabatan</th>
                <td>{{ $izinkeluar->nama_jabatan }}</td>
            </tr>
            <tr>
                <th>Departemen</th>
                <td>{{ $izinkeluar->nama_dept }}</td>
            </tr>
            <tr>
                <th>Cabang</th>
                <td>{{ $izinkeluar->nama_cabang }}</td>
            </tr>
            <tr>
                <th>Jam Keluar</th>
                <td>{{ date('H:i', strtotime($izinkeluar->jam_keluar)) }}</td>
            </tr>
            <tr>
                <th>Jam Kembali</th>
                <td>{{ $izinkeluar->jam_kembali ? date('H:i', strtotime($izinkeluar->jam_kembali)) : 'Selesai / Tidak Kembali' }}</td>
            </tr>
            <tr>
                <th>Keperluan</th>
                <td>{{ $izinkeluar->keperluan }}</td>
            </tr>
            @if($izinkeluar->driver)
            <tr>
                <th>Driver</th>
                <td>{{ $izinkeluar->driver->nama_karyawan }}</td>
            </tr>
            @endif
            @if($izinkeluar->kendaraan)
            <tr>
                <th>Kendaraan</th>
                <td>{{ $izinkeluar->kendaraan->nama_asset }}</td>
            </tr>
            @endif
            <tr>
                <th>Status</th>
                <td>
                    @if ($izinkeluar->status == 0)
                        <span class="badge bg-warning">Pending</span>
                    @elseif ($izinkeluar->status == 1)
                        <span class="badge bg-success">Disetujui</span>
                    @elseif ($izinkeluar->status == 2)
                        <span class="badge bg-danger">Ditolak</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="row mt-3">
    <div class="col-12">
        <h5 class="mb-2">Riwayat Approval</h5>
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>Tahap</th>
                    <th>Approver</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($izinkeluar->approvals()->orderBy('level', 'asc')->get() as $approval)
                    <tr>
                        <td>Level {{ $approval->level }}</td>
                        <td>{{ $approval->user->name ?? 'Unknown' }}</td>
                        <td>
                            @if($approval->status == 'approved')
                                <span class="badge bg-success"><i class="ti ti-check me-1"></i>Approved</span>
                            @else
                                <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Rejected</span>
                            @endif
                        </td>
                        <td>{{ $approval->created_at->format('d-m-Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada riwayat approval</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
