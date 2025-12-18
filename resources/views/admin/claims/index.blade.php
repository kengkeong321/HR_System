@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-gray-800">My Claim History</h2>
        <a href="{{ route('staff.claims.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Claim
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Type / Merchant</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                            <th class="text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $claim->claim_type }}</div>
                                <small class="text-muted">{{ $claim->ocr_merchant ?? 'Manual Entry' }}</small>
                            </td>
                            <td>
                                <span class="fw-bold">RM {{ number_format($claim->amount, 2) }}</span>
                                @if($claim->is_flagged)
                                <i class="bi bi-exclamation-triangle-fill text-warning" title="Amount variance detected"></i>
                                @endif
                            </td>
                            <td>
                                @if($claim->status == 'Pending')
                                <span class="badge bg-warning text-dark">Processing</span>
                                @elseif($claim->status == 'Approved')
                                <span class="badge bg-success">Approved</span>
                                @else
                                <span class="badge bg-danger" title="{{ $claim->rejection_reason }}" data-bs-toggle="tooltip">Rejected</span>
                                @endif
                            </td>
                            <td class="small">{{ $claim->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                @if($claim->receipt_path)
                                {{-- Generate the public URL using the storage link --}}
                                <a href="{{ asset('storage/' . $claim->receipt_path) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-info shadow-sm">
                                    <i class="bi bi-eye"></i> View Receipt
                                </a>
                                @else
                                <span class="text-muted small">No Receipt</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    @if($claim->receipt_path)
                                    <img src="{{ asset('storage/' . $claim->receipt_path) }}"
                                        class="rounded me-2 shadow-sm"
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $claim->claim_type }}</div>
                                        <small class="text-muted">{{ $claim->ocr_merchant ?? 'Manual Entry' }}</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No claims submitted yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection