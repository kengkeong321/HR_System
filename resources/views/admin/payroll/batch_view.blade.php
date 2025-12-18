@extends('layouts.admin')

@section('title', 'Payroll Batch Review')

@section('content')
<div class="container-fluid">

    {{-- HEADER & APPROVAL BUTTONS --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Batch: {{ $batch->month_year }}</h1>
            <span class="badge bg-{{ 
                $batch->status == 'Paid' ? 'success' : 
                ($batch->status == 'Draft' ? 'secondary' : 'info') 
            }} fs-6">
                Status: {{ $batch->status }}
            </span>
        </div>

        {{-- STEP 5: APPROVAL ACTIONS --}}
        <div>
            {{-- LEVEL 1: HR APPROVAL --}}
            @if($batch->status == 'Draft')
            <form action="{{ route('admin.payroll.approve_l1', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm L1 Approval? This will LOCK the batch from further edits.');">
                @csrf
                <button class="btn btn-info text-white">
                    <i class="bi bi-check-circle"></i> Submit for Level 1 (HR)
                </button>
            </form>
            @endif

            {{-- LEVEL 2: FINANCE APPROVAL --}}
            @if($batch->status == 'L1_Approved')
            <form action="{{ route('admin.payroll.approve_l2', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm L2 Approval? This authorizes the payout.');">
                @csrf
                <button class="btn btn-primary">
                    <i class="bi bi-shield-lock"></i> Level 2 Approval (Finance)
                </button>
            </form>
            @endif

            {{-- STEP 6: DISBURSEMENT --}}
            {{-- STEP 6: DISBURSEMENT --}}
            @if($batch->status == 'L2_Approved')
            <a href="{{ route('admin.payroll.export', $batch->id) }}" class="btn btn-danger" onclick="return confirm('Download Final Report? This will mark the batch as PAID.');">
                <i class="bi bi-file-earmark-pdf"></i> Download Payment Report (PDF) & Mark Paid
            </a>
            @endif

            @if($batch->status == 'Paid')
            <a href="{{ route('admin.payroll.export', $batch->id) }}" class="btn btn-secondary">
                <i class="bi bi-file-earmark-pdf"></i> Re-Download Report
            </a>
            @endif
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row mb-4">
        {{-- Total Staff --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Staff</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $batch->total_staff }}</div>
                </div>
            </div>
        </div>

        {{-- Total Payout --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Payout</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">RM {{ number_format($batch->total_amount, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- VARIANCE CHECK (Dynamic Data from Controller) --}}
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card border-left-{{ $varianceColor ?? 'primary' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-{{ $varianceColor ?? 'primary' }} text-uppercase mb-1">Variance Check</div>
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $varianceMsg }}
                            </div>

                            {{-- Specific Alerts based on Percentage --}}
                            @if(isset($percentChange) && $percentChange > 20)
                            <small class="text-danger fw-bold">
                                <i class="bi bi-exclamation-triangle-fill"></i> High Variance: Requires Investigation.
                            </small>
                            @elseif(isset($percentChange) && $percentChange < -20)
                                <small class="text-warning fw-bold">
                                <i class="bi bi-arrow-down-right"></i> Note: Significant drop in payroll.
                                </small>
                                @elseif(isset($percentChange))
                                <small class="text-muted">Variance is within normal range.</small>
                                @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LIST OF DRAFT SLIPS --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Individual Slips (Drafts)</h6>
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
                        {{-- Highlight Row if there is a remark (Audit Trail) --}}
                        <tr class="{{ $slip->allowance_remark ? 'table-warning' : '' }}">

                            <td>{{ $slip->staff->staff_id }}</td>

                            <td>
                                {{ $slip->staff->full_name }}
                                <br>
                                <small class="text-muted">{{ $slip->staff->employment_type }}</small>
                            </td>

                            <td>
                                RM {{ number_format($slip->basic_salary, 2) }}

                                {{-- VISUAL FLAG: Pro-rated Badge --}}
                                @if(str_contains($slip->allowance_remark ?? '', 'Pro-rated'))
                                <span class="badge bg-warning text-dark ms-1" data-bs-toggle="tooltip" title="{{ $slip->allowance_remark }}">
                                    <i class="bi bi-scissors"></i> Pro-rated
                                </span>
                                @endif
                            </td>

                            <td class="text-danger">RM {{ number_format($slip->deduction, 2) }}</td>

                            <td class="fw-bold text-success">RM {{ number_format($slip->net_salary, 2) }}</td>

                            <td>
                                {{-- Alert if Part-Time has 0 salary (Missing Input) --}}
                                @if($slip->net_salary == 0 && $slip->staff->employment_type == 'Part-Time')
                                <span class="badge bg-danger">Needs Input</span>
                                @else
                                <span class="badge bg-secondary">{{ $slip->status }}</span>
                                @endif
                            </td>

                            <td>
                                @if($slip->id)
                                {{-- Valid ID: Show the button --}}
                                <a href="{{ route('admin.payroll.edit', $slip->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Check
                                </a>
                                @else
                                {{-- Invalid ID: Show Error Message (Prevents Crash) --}}
                                <span class="badge bg-danger">
                                    <i class="bi bi-bug"></i> Error: ID Missing
                                </span>
                                <small class="d-block text-danger">Delete this row in DB</small>
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
@endsection