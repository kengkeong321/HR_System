{{-- Dephnie Ong Yan Yee --}}

@extends('layouts.admin')

@section('title', 'Payroll Batch Review')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Finance Batch Audit Summary ({{ $batch->month_year }})
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                Batch Total: RM {{ number_format($batch->total_amount, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $batch->status == 'Paid' ? 'success' : ($batch->status == 'Draft' ? 'secondary' : 'info') }} p-2">
                                State: {{ str_replace('_', ' ', $batch->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Financial Component</th>
                                    <th class="text-end">Subtotal (RM)</th>
                                    <th>Audit Verification</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Staff Basic Salaries</td>
                                    <td class="text-end font-monospace">{{ number_format($totals['basic_salary'] ?? 0, 2) }}</td>
                                    <td class="small text-muted"><i class="bi bi-shield-check"></i> Cross-checked with Staff Master</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Claims/Allowances</strong></td>
                                    <td class="text-end font-monospace text-success"><strong>{{ number_format($totals['allowances'] ?? 0, 2) }}</strong></td>
                                    <td class="small"><a href="#details-table" class="text-primary text-decoration-none"><i class="bi bi-search"></i> Spot-check Itemized Claims</a></td>
                                </tr>
                                <tr>
                                    <td>Statutory Liabilities (EPF/SOCSO/EIS)</td>
                                    <td class="text-end font-monospace">{{ number_format(($totals['epf_total'] ?? 0) + ($totals['socso_total'] ?? 0) + ($totals['eis_total'] ?? 0), 2) }}</td>
                                    <td class="small text-muted"><i class="bi bi-calculator"></i> System-calculated Snapshot</td>
                                </tr>
                                <tr>
                                    <td>Manual Deductions (Adjustments)</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format($totals['manual_deduction'] ?? 0, 2) }}</td>
                                    <td class="small text-muted"><i class="bi bi-person-gear"></i> Manual User Adjustments</td>
                                </tr>
                                <tr class="table-light border-top">
                                    <td class="fw-bold">NET UNIVERSITY OUTFLOW</td>
                                    <td class="text-end font-monospace fw-bold text-primary">RM {{ number_format($batch->total_amount, 2) }}</td>
                                    <td class="small fw-bold text-primary"><i class="bi bi-check-all"></i> Ready for Bank Transfer</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(strtolower($batch->status) === 'draft' && $batch->remark)
    <div class="alert alert-danger border-start border-4 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1">Payroll Rejected by Finance</h6>
                <p class="mb-0 text-dark">{{ $batch->remark }}</p>
                <small class="text-muted mt-2 d-block">
                    Rejected by: {{ $batch->rejectedBy->user_name ?? 'Finance Dept' }}
                    on {{ $batch->rejected_at ? $batch->rejected_at->format('d M Y, h:i A') : 'N/A' }}
                </small>
            </div>
        </div>
    </div>
    @endif

    <div class="d-flex justify-content-end mb-4 bg-white p-3 rounded shadow-sm gap-2">
        @if(auth()->user()->role === 'HR' || auth()->user()->role === 'Admin')
        <form action="{{ route('admin.payroll.approve_l1', $batch->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-info text-white"
                {{ $batch->status !== 'Draft' ? 'disabled' : '' }}
                title="{{ $batch->status !== 'Draft' ? 'Record locked after submission' : 'Approve for Finance Review' }}">
                <i class="bi bi-send-check me-1"></i> Submit for Review (L1)
            </button>
        </form>
        @endif

        @if(auth()->user()->role === 'Finance' || auth()->user()->role === 'Admin')
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"
            {{ $batch->status !== 'L1_Approved' ? 'disabled' : '' }}
            title="{{ $batch->status !== 'L1_Approved' ? 'Must be approved by HR first' : 'Veto Batch & Return to HR' }}">
            <i class="bi bi-x-circle me-1"></i> Reject & Return to HR
        </button>

        <form action="{{ route('admin.payroll.approve_l2', $batch->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success"
                {{ $batch->status !== 'L1_Approved' ? 'disabled' : '' }}
                title="{{ $batch->status !== 'L1_Approved' ? 'Awaiting HR Submission' : 'Confirm Audit & Authorize Disbursement' }}">
                <i class="bi bi-check-all me-1"></i> Authorize Payment (L2)
            </button>
        </form>
        @endif

        @if($batch->status == 'Paid')
        <a href="{{ route('admin.payroll.export', $batch->id) }}" class="btn btn-primary" title="Export Bank File">
            <i class="bi bi-download me-1"></i> Download Bank File
        </a>
        @endif
    </div>

    <div class="card shadow mb-4" id="details-table">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Consolidated Ledger for Audit</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Staff Detail</th>
                            <th class="text-end">Basic</th>
                            <th class="text-end">Claims/Allowances</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Net Salary</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $slip)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $slip->staff->full_name }}</div>
                                <small class="text-muted">{{ $slip->staff->staff_id }} | {{ $slip->staff->employment_type }}</small>
                            </td>
                            <td class="text-end font-monospace">RM {{ number_format($slip->basic_salary, 2) }}</td>
                            <td class="text-end font-monospace text-success">
                                RM {{ number_format($slip->allowances, 2) }}
                                @if($slip->allowance_remark)
                                <i class="bi bi-info-circle ms-1" title="{{ $slip->allowance_remark }}"></i>
                                @endif
                            </td>
                            <td class="text-end font-monospace text-danger">RM {{ number_format($slip->deduction, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-primary">RM {{ number_format($slip->net_salary, 2) }}</td>
                            <td class="text-center">
                                @if(in_array(auth()->user()->role, ['HR', 'Admin']) && $batch->status === 'Draft')
                                <a href="{{ route('admin.payroll.edit', $slip->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                @else
                                <button class="btn btn-sm btn-light border" disabled title="Finance: Audit Mode Only">
                                    <i class="bi bi-lock-fill text-muted"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="{{ route('admin.payroll.reject', $batch->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Batch Veto (Reject)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-bold">Audit Correction Notes for HR:</label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Explain discrepancies (e.g., Claim amount mismatch for Staff ID 10)..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel Audit</button>
                    <button type="submit" class="btn btn-danger">Confirm Veto & Return to HR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection