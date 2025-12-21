@extends('layouts.staff')

@section('content')

{{-- Dephnie Ong Yan Yee --}}
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
                                    <span class="badge rounded-pill bg-info text-dark">{{ $claim->claim_type }}</span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;">{{ $claim->description }}</span>
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
                                    <span class="badge bg-danger mb-1"><i class="bi bi-x-circle"></i> Rejected</span>
                                    @if($claim->rejection_reason)
                                    <i class="bi bi-info-circle text-danger ms-1" title="{{ $claim->rejection_reason }}"></i>
                                    @endif
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    @if($claim->receipt_path)
    
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $claim->id }}">
                                        <i class="bi bi-eye"></i> View Receipt
                                    </button>
                                    @else
                                    <span class="text-muted small">No Receipt</span>
                                    @endif
                                </td>
                            </tr>

                            @if($claim->receipt_path)
                            <div class="modal fade" id="receiptModal{{ $claim->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content h-100">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Receipt for {{ $claim->claim_type }} (RM {{ $claim->amount }})</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body bg-dark p-0 d-flex justify-content-center align-items-center" style="min-height: 500px;">
                                            @php $secureViewUrl = route('claims.view_receipt', $claim->id); @endphp 

                                            @if(Str::endsWith($claim->receipt_path, '.pdf'))
                                            <iframe src="{{ $secureViewUrl }}" class="w-100 h-100 border-0" style="min-height: 500px;"></iframe>
                                            @else
                                            <img src="{{ $secureViewUrl }}" class="img-fluid" style="max-height: 100%; max-width: 100%;">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No claims history found.</td>
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