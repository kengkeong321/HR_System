@extends('layouts.admin')

@section('title', 'Payroll Batch Review')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Batch Statutory Liability (RM)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Total Disbursement: RM {{ number_format($totals['net_salary'] + $totals['epf_employee'] + $totals['epf_employer'] + $totals['socso_employee'] + $totals['socso_employer'] + $totals['eis_total'], 2) }}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center small">
                        <div class="col-md-3 border-right">
                            <strong class="d-block">Total EPF</strong>
                            RM {{ number_format($totals['epf_employee'] + $totals['epf_employer'], 2) }}
                        </div>
                        <div class="col-md-3 border-right">
                            <strong class="d-block">Total SOCSO</strong>
                            RM {{ number_format($totals['socso_employee'] + $totals['socso_employer'], 2) }}
                        </div>
                        <div class="col-md-3 border-right">
                            <strong class="d-block">Total EIS</strong>
                            RM {{ number_format($totals['eis_total'], 2) }}
                        </div>
                        <div class="col-md-3 text-success">
                            <strong class="d-block">Total Net Pay</strong>
                            RM {{ number_format($totals['net_salary'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 1. HEADER & APPROVAL/REJECTION BUTTONS --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Batch: {{ $batch->month_year }}</h1>
            <span class="badge bg-{{ 
            $batch->status == 'Paid' ? 'success' : 
            ($batch->status == 'Draft' ? 'secondary' : 'info') 
        }} fs-6">
                Status: {{ str_replace('_', ' ', $batch->status) }}
            </span>
        </div>

        <div class="btn-group">
            {{-- LEVEL 1: HR APPROVAL (Only visible to HR when in Draft) --}}
            @if(auth()->user()->role === 'HR' && $batch->status == 'Draft')
            <form action="{{ route('admin.payroll.approve_l1', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm L1 Approval? This will LOCK the batch and send it to Finance.');">
                @csrf
                <button type="submit" class="btn btn-info text-white">
                    <i class="bi bi-check-circle"></i> HR Approval (L1)
                </button>
            </form>
            @endif

            {{-- LEVEL 2: FINANCE APPROVAL (Only visible to Finance when L1 is Approved) --}}
            @if(auth()->user()->role === 'Finance' && $batch->status == 'L1_Approved')
            <form action="{{ route('admin.payroll.approve_l2', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm L2 Approval? This authorizes the payout.');">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-shield-lock"></i> Finance Approval (L2)
                </button>
            </form>
            @endif

            {{-- REJECT / UNLOCK (Visible to Finance to send back to HR) --}}
            @if(auth()->user()->role === 'Finance' && $batch->status == 'L1_Approved')
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-arrow-counterclockwise"></i> Reject & Unlock
            </button>
            @endif

            {{-- DISBURSEMENT (Visible to Finance after final approval) --}}
            @if(auth()->user()->role === 'Finance' && $batch->status == 'L2_Approved')
            <a href="{{ route('admin.payroll.export', $batch->id) }}" class="btn btn-danger" onclick="return confirm('Download Final Report? This will mark the batch as PAID.');">
                <i class="bi bi-file-earmark-pdf"></i> Download & Mark Paid
            </a>
            @endif

            {{-- VIEW ONLY (Re-download) --}}
            @if($batch->status == 'Paid')
            <a href="{{ route('admin.payroll.export', $batch->id) }}" class="btn btn-secondary">
                <i class="bi bi-file-earmark-pdf"></i> Re-Download Report
            </a>
            @endif
        </div>
    </div>

    {{-- 2. STATS CARDS --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Staff</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $batch->total_staff }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Payout</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">RM {{ number_format($batch->total_amount, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card border-left-{{ $varianceColor ?? 'primary' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-{{ $varianceColor ?? 'primary' }} text-uppercase mb-1">Variance Check</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $varianceMsg }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. REJECTION FEEDBACK ALERT (Shows only if rejected) --}}
    @if($batch->status == 'Draft' && !empty($batch->rejection_reason))
    <div class="alert alert-warning border-start border-danger border-4 shadow-sm mb-4">
        <h5 class="alert-heading text-danger fw-bold">
            <i class="bi bi-exclamation-octagon-fill"></i> Batch Rejected by Finance
        </h5>
        <p class="mb-0"><strong>Feedback:</strong> {{ $batch->rejection_reason }}</p>
        <hr>
        <small class="text-muted">Please correct the issues above and re-submit for Level 1 Approval.</small>
    </div>
    @endif

    {{-- 4. LIST OF SLIPS --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Individual Slips Management</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Basic Salary</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $slip)
                        <tr class="{{ $slip->allowance_remark ? 'table-warning' : '' }}">
                            <td>{{ $slip->staff->staff_id }}</td>
                            <td>
                                {{ $slip->staff->full_name }}<br>
                                <small class="text-muted">{{ $slip->staff->employment_type }}</small>
                            </td>
                            <td>RM {{ number_format($slip->basic_salary, 2) }}</td>
                            <td class="text-danger">RM {{ number_format($slip->deduction, 2) }}</td>
                            <td class="fw-bold text-success">RM {{ number_format($slip->net_salary, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ $slip->status }}</span></td>
                            <td>
                                @if($slip->id)
                                @if($batch->status === 'Draft')
                                <a href="{{ route('admin.payroll.edit', $slip->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Check
                                </a>
                                @else
                                <button class="btn btn-sm btn-secondary" disabled title="Locked: Under review">
                                    <i class="bi bi-lock-fill"></i> Locked
                                </button>
                                @endif
                                @else
                                <span class="badge bg-danger">ID Missing</span>
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

{{-- 5. REJECTION MODAL --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg">
            <form action="{{ route('admin.payroll.reject', $batch->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise"></i> Reject Payroll Batch</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Quick Select Reason:</label>
                        <select class="form-select border-primary" onchange="document.getElementById('reason_text').value = this.value">
                            <option value="">-- Choose from standard list --</option>
                            @foreach($rejectionReasons as $r)
                            <option value="{{ $r->reason_template }}">{{ $r->reason_label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Detailed Feedback for HR:</label>
                        <textarea name="rejection_reason" id="reason_text" class="form-control" rows="5" required
                            placeholder="HR will see this message and use it to fix the data..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Confirm & Send to HR</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection