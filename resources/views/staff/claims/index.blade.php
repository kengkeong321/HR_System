@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">My Claims History</h4>
                    <p class="text-muted small">View and track your reimbursement requests</p>
                </div>
                <a href="{{ route('staff.claims.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> New Claim
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($claims as $claim)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold d-block">{{ $claim->created_at->format('d M Y') }}</span>
                                    <small class="text-muted">{{ $claim->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-info text-dark">
                                        {{ $claim->claim_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;">
                                        {{ $claim->description }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">RM {{ number_format($claim->amount, 2) }}</span>
                                </td>
                                <td>
                                    @if($claim->status == 'Pending')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history"></i> Pending</span>
                                    @elseif($claim->status == 'Approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved</span>
                                    @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    @if($claim->receipt_path)
                                    <a href="{{ asset('storage/' . $claim->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> View Receipt
                                    </a>
                                    @else
                                    <span class="text-muted small">No Receipt</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No claims found. Click "New Claim" to start.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection