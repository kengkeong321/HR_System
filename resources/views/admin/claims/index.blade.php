@extends('layouts.admin')

@section('title', 'Claims Verification Queue')

@section('content')
<div class="container-fluid">

    {{-- 4. Financial Oversight Summary --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved Claims (This Month)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">RM {{ number_format($totalClaimed, 2) }}</div>
                </div>
            </div>
        </div>
        @if($highValueClaims > 0)
        <div class="col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Fraud Alert: High Value Claims</div>
                    <div class="h5 mb-0 font-weight-bold text-danger">{{ $highValueClaims }} Pending > RM 1,000</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- 1. Status Filtering --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Claims Management</h6>
            <div class="btn-group">
                <a href="{{ route('admin.claims.index', ['status' => 'Pending']) }}" class="btn btn-sm {{ $status == 'Pending' ? 'btn-primary' : 'btn-outline-primary' }}">Pending</a>
                <a href="{{ route('admin.claims.index', ['status' => 'Approved']) }}" class="btn btn-sm {{ $status == 'Approved' ? 'btn-success' : 'btn-outline-success' }}">Approved</a>
                <a href="{{ route('admin.claims.index', ['status' => 'Rejected']) }}" class="btn btn-sm {{ $status == 'Rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Staff</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Receipt / OCR Check</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claims as $claim)
                        <tr>
                            <td>{{ $claim->created_at->format('Y-m-d') }}</td>
                            <td>{{ $claim->staff->user->name ?? 'Unknown' }}</td>
                            <td>{{ $claim->description }}</td>
                            <td class="font-weight-bold">RM {{ number_format($claim->amount, 2) }}</td>
                            
                            {{-- OCR Verification Button --}}
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $claim->id }}">
                                    <i class="bi bi-eye"></i> Verify Receipt
                                </button>
                            </td>

                            {{-- 2. Approval/Rejection Workflow --}}
                            <td>
                                @if($claim->status === 'Pending' && auth()->user()->role === 'HR')
                                    <div class="btn-group">
                                        {{-- One-Click Approval --}}
                                        <form action="{{ route('admin.claims.approve', $claim->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve & Sync to Payroll">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>

                                        {{-- Rejection Trigger --}}
                                        <button type="button" class="btn btn-sm btn-danger ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $claim->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="badge bg-secondary">{{ $claim->status }}</span>
                                @endif
                            </td>
                        </tr>

                        {{-- MODAL: Side-by-Side Review --}}
                        <div class="modal fade" id="verifyModal{{ $claim->id }}" tabindex="-1">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Integrity Check: Claim #{{ $claim->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body row">
                                        {{-- Left: Receipt Image --}}
                                        <div class="col-md-6 border-end text-center bg-dark">
                                            @if($claim->receipt_path)
                                                <img src="{{ asset('storage/' . $claim->receipt_path) }}" class="img-fluid" style="max-height: 500px;">
                                            @else
                                                <div class="text-white py-5">No Receipt Uploaded</div>
                                            @endif
                                        </div>
                                        {{-- Right: OCR Data & Comparison --}}
                                        <div class="col-md-6">
                                            <h6 class="fw-bold">System Data</h6>
                                            <ul class="list-group mb-3">
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Claimed Amount:</span>
                                                    <strong class="text-primary">RM {{ $claim->amount }}</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Date:</span>
                                                    <strong>{{ $claim->created_at->format('d M Y') }}</strong>
                                                </li>
                                            </ul>
                                            
                                            <div class="alert alert-warning">
                                                <small><i class="bi bi-shield-exclamation"></i> Ensure the receipt amount matches the claimed amount exactly.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL: Rejection Reason --}}
                        <div class="modal fade" id="rejectModal{{ $claim->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.claims.reject', $claim->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Reject Claim</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Reason for Rejection (Required):</label>
                                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="e.g., Receipt blurry, Duplicate claim..."></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
                {{ $claims->appends(['status' => $status])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection