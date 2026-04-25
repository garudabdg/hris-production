@if(isset($approvals) && $approvals->count() > 0)
<div class="row mt-3">
    <div class="col">
        <h6 class="mb-3"><i class="ti ti-file-check me-2"></i>{{ $title ?? 'Riwayat Approval' }}</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="10%">Level</th>
                        <th width="25%">Approver</th>
                        <th width="15%">Status</th>
                        <th width="25%">Tanggal & Waktu</th>
                        <th width="25%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvals as $approval)
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">Level {{ $approval->level }}</span>
                        </td>
                        <td>
                            @if($approval->user)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2 bg-primary-subtle text-primary rounded">
                                        <i class="ti ti-user"></i>
                                    </div>
                                    <span class="fw-medium">{{ $approval->user->name }}</span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($approval->status == 'approved')
                                <span class="badge bg-success"><i class="ti ti-check me-1"></i>Disetujui</span>
                            @else
                                <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Ditolak</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="ti ti-calendar me-1"></i>{{ $approval->created_at->format('d/m/Y') }}
                                <br>
                                <i class="ti ti-clock me-1"></i>{{ $approval->created_at->format('H:i:s') }}
                            </small>
                        </td>
                        <td>
                            <small>{{ $approval->keterangan ?? '-' }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
