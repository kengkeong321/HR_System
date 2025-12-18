@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">Leave Management</h3>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 text-secondary fw-bold">
                <i class="bi bi-list-check me-2"></i>Staff Leave Applications
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">Staff Name</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Leave Type</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Duration</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Status</th>
                            <th class="pe-4 py-3 text-uppercase small fw-bold text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    @foreach($leaves as $leave)
    <tr>
        <td class="ps-4">
            <div class="fw-bold text-dark">{{ $leave->staff_name }}</div>
        </td>
        <td>
            <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1">
                {{ $leave->leave_type }}
            </span>
        </td>
        <td>
            <div class="small fw-bold text-dark">{{ date('d M Y', strtotime($leave->start_date)) }}</div>
            <div class="text-muted" style="font-size: 0.75rem;">Until {{ date('d M Y', strtotime($leave->end_date)) }}</div>
        </td>
        <td>
            @if($leave->status == 'Approved')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i>Approved
                </span>
            @elseif($leave->status == 'Rejected')
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                    <i class="bi bi-x-circle me-1"></i>Rejected
                </span>
            @else
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 text-dark">
                    <i class="bi bi-clock-history me-1"></i>Pending
                </span>
            @endif
        </td>
        <td class="pe-4 text-end">
            <div class="btn-group shadow-sm">
                <button type="button" class="btn btn-white btn-sm border" 
                        data-bs-toggle="modal" 
                        data-bs-target="#viewLeave{{ $leave->id }}">
                    <i class="bi bi-eye text-primary"></i>
                </button>
                
                @if($leave->status == 'Pending')
                <form action="{{ route('leave.update', $leave->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button name="status" value="Approved" class="btn btn-white btn-sm border border-start-0" title="Approve">
                        <i class="bi bi-check2 text-success"></i>
                    </button>
                    <button name="status" value="Rejected" class="btn btn-white btn-sm border border-start-0" title="Reject">
                        <i class="bi bi-trash text-danger"></i>
                    </button>
                </form>
                @endif
            </div>

            <div class="modal fade" id="viewLeave{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg text-start">
                        <div class="modal-header bg-primary text-white border-0">
                            <h5 class="modal-title fw-bold">Leave Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">Staff Member</label>
                                <p class="mb-0 fw-bold fs-5">{{ $leave->staff_name }}</p>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase fw-bold">Type</label>
                                    <p class="mb-0">{{ $leave->leave_type }}</p>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase fw-bold">Status</label>
                                    <div><span class="badge bg-light text-dark border">{{ $leave->status }}</span></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">Reason</label>
                                <div class="p-3 bg-light rounded border mt-1">
                                    <p class="mb-0" style="white-space: pre-wrap;">{{ $leave->reason ?? 'No reason provided.' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            </td>
    </tr>
    @endforeach
</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection