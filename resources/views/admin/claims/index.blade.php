@extends('layouts.admin')

@section('title', 'Claims Verification Queue')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase fw-bold text-success small mb-1">Approved Claims (This Month)</div>
                            <div class="h3 mb-0 fw-bold text-dark">RM {{ number_format($totalClaimed, 2) }}</div>
                        </div>
                        <div class="fs-1 text-success opacity-25">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($highValueClaims > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase fw-bold text-danger small mb-1">Fraud Alert: High Value (> RM1k)</div>
                            <div class="h3 mb-0 fw-bold text-danger">{{ $highValueClaims }} Pending</div>
                        </div>
                        <div class="fs-1 text-danger opacity-25">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- claims management --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h5 class="m-0 fw-bold text-primary">
                <i class="bi bi-list-check me-2"></i>Claims Management
            </h5>

            {{-- filter status --}}
            <div class="btn-group shadow-sm">
                <a href="{{ route('admin.claims.index', ['status' => 'Pending']) }}"
                    class="btn btn-sm {{ $status == 'Pending' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Pending
                </a>
                <a href="{{ route('admin.claims.index', ['status' => 'Approved']) }}"
                    class="btn btn-sm {{ $status == 'Approved' ? 'btn-success' : 'btn-outline-secondary' }}">
                    Approved
                </a>
                <a href="{{ route('admin.claims.index', ['status' => 'Rejected']) }}"
                    class="btn btn-sm {{ $status == 'Rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    Rejected
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-4">Date</th>
                            <th>Staff Member</th>
                            <th>Claim Details</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Verification</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                        <tr>
                            <td class="ps-4 text-nowrap text-secondary">
                                {{ $claim->created_at->format('d M Y') }}<br>
                                <small>{{ $claim->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $claim->staff->user->user_name ?? 'Unknown' }}</div>
                                <small class="text-muted">{{ $claim->staff->department->depart_name ?? 'N/A' }}</small>
                            </td>
                            <td style="max-width: 250px;">
                                <span class="badge bg-light text-dark border mb-1">{{ $claim->claim_type }}</span>
                                <div class="text-truncate text-secondary" title="{{ $claim->description }}">
                                    {{ $claim->description }}
                                </div>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                RM {{ number_format($claim->amount, 2) }}
                            </td>

                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $claim->id }}">
                                    <i class="bi bi-receipt"></i> Review Receipt
                                </button>
                            </td>

                            {{-- Actions --}}
                            <td class="text-center pe-4">
                                @if($claim->status === 'Pending' && (auth()->user()->role === 'HR' || auth()->user()->role === 'Admin'))
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="{{ route('admin.claims.approve', $claim->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="approved_amount" value="{{ $claim->amount }}">
                                        <button type="submit" class="btn btn-sm btn-success shadow-sm" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-danger shadow-sm" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $claim->id }}">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                @else
                                @if($claim->status == 'Approved')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3">Approved</span>
                                @elseif($claim->status == 'Rejected')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3">Rejected</span>
                                @else
                                <span class="badge bg-secondary">Processing</span>
                                @endif
                                @endif
                            </td>
                        </tr>

                        <div class="modal fade" id="verifyModal{{ $claim->id }}" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title">
                                            <i class="bi bi-search text-primary me-2"></i>Verify Claim #{{ $claim->id }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <div class="row g-0" style="min-height: 500px;">
                                            {{-- Receipt Viewer --}}
                                            <div class="col-lg-7 bg-dark d-flex align-items-center justify-content-center p-3 position-relative">
                                                @if($claim->receipt_path)
                                                @if(Str::endsWith($claim->receipt_path, '.pdf'))
                                                <iframe src="{{ asset('storage/' . $claim->receipt_path) }}" class="w-100 h-100" style="min-height:450px;"></iframe>
                                                @else
                                                <img src="{{ asset('storage/' . $claim->receipt_path) }}" class="img-fluid rounded shadow" style="max-height: 80vh;">
                                                @endif
                                                @else
                                                <div class="text-white text-center opacity-50">
                                                    <i class="bi bi-file-earmark-x display-1"></i>
                                                    <p class="mt-2">No Receipt Found</p>
                                                </div>
                                                @endif
                                            </div>

                                            {{-- Details & Actions --}}
                                            <div class="col-lg-5 p-4 bg-white border-start">
                                                <h6 class="text-uppercase text-muted fw-bold small mb-3">Claim Summary</h6>

                                                <div class="mb-4">
                                                    <label class="small text-muted">Amount Claimed</label>
                                                    <div class="display-6 fw-bold text-primary">RM {{ number_format($claim->amount, 2) }}</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="small text-muted">Category</label>
                                                    <div class="fw-bold">{{ $claim->claim_type }}</div>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="small text-muted">Justification</label>
                                                    <div class="p-3 bg-light rounded text-secondary small">{{ $claim->description }}</div>
                                                </div>

                                                @if($claim->status === 'Pending')
                                                <hr class="my-4">

                                                @php
                                                $canAction = in_array(auth()->user()->role, ['HR', 'Admin']);
                                                @endphp

                                                @if($canAction)

                                                <div class="d-grid gap-2">
                                                    <form action="{{ route('admin.claims.approve', $claim->id) }}" method="POST">
                                                        @csrf
                                                        <button class="btn btn-success btn-lg w-100 shadow-sm">
                                                            <i class="bi bi-check-circle-fill me-2"></i>Verify & Approve
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $claim->id }}" data-bs-dismiss="modal">
                                                        Reject Claim
                                                    </button>
                                                </div>
                                                @else
                                                <div class="alert alert-secondary text-center py-3 mb-0 border-0 shadow-sm">
                                                    <i class="bi bi-shield-lock me-2"></i>
                                                    <strong>View-Only Audit Mode</strong><br>
                                                    <small class="text-muted">Finance users cannot modify claim status</small>
                                                </div>
                                                @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Rejection --}}
                        <div class="modal fade" id="rejectModal{{ $claim->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-danger border-top border-4">
                                    <form action="{{ route('admin.claims.reject', $claim->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Confirm Rejection</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-3">Why is this claim being rejected? This note will be sent to the staff member.</p>
                                            <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="E.g., Receipt date does not match claim date..."></textarea>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject Claim</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                No {{ strtolower($status) }} claims found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- pagination --}}
            @if($claims->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $claims->appends(['status' => $status])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
@endif
@endsection