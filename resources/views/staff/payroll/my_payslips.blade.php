@extends('layouts.staff') 

@section('title', 'My Financial History')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">My Financial History</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payslips</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Alert for empty records --}}
    @if($payrolls->isEmpty())
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i> No finalized payslips are available for viewing at this time.
        </div>
    @else
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Released Payslips</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Period</th>
                                <th>Net Payout</th>
                                <th>Adjustments & Remarks</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payrolls as $payroll)
                            <tr>
                                <td class="fw-bold text-dark">
                                    {{ $payroll->month }} {{ $payroll->year }}
                                </td>
                                <td class="fw-bold text-primary">
                                    RM {{ number_format($payroll->net_salary, 2) }}
                                </td>
                                <td>
                                    @if($payroll->allowance_remark)
                                        <small class="text-success d-block">
                                            <i class="bi bi-plus-circle small me-1"></i> {{ $payroll->allowance_remark }}
                                        </small>
                                    @endif
                                    @if($payroll->deduction_remark)
                                        <small class="text-danger d-block">
                                            <i class="bi bi-dash-circle small me-1"></i> {{ $payroll->deduction_remark }}
                                        </small>
                                    @endif
                                    @if(!$payroll->allowance_remark && !$payroll->deduction_remark)
                                        <span class="text-muted small italic">No adjustments</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success px-3">
                                        Paid
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('staff.payroll.export', $payroll->id) }}" 
                                       class="btn btn-sm btn-outline-danger shadow-sm px-3"
                                       target="_blank">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Download
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small py-3">
                <i class="bi bi-shield-lock me-1"></i> These records are frozen snapshots of your payroll at the time of disbursement.
            </div>
        </div>
    @endif
</div>
@endsection