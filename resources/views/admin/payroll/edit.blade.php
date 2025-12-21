@extends('layouts.admin')

@section('title', 'Edit Payroll Record')

@section('content')
<div id="payroll-rates"
    data-epf="{{ $configs['staff_epf_rate'] ?? 11.00 }}"
    data-eis="{{ $configs['eis_rate'] ?? 0.20 }}"
    data-hours="{{ $configs['standard_work_hours'] ?? 9 }}"
    style="display:none;">
</div>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-gray-800">Edit Payroll: {{ $payroll->staff->full_name }}</h2>
        <a href="{{ route('admin.payroll.batch_view', $payroll->batch_id) }}" class="btn btn-secondary shadow-sm">
            <i class="bi bi-arrow-left"></i> Back to Batch
        </a>
    </div>

    @if($payroll->staff->employment_type == 'Part-Time' || $daysPresent > 0)
    <div class="alert alert-info shadow-sm d-flex justify-content-between align-items-center border-left-info mb-4">
        <div>
            <i class="bi bi-clock-history fs-4 me-2"></i>
            <strong>Attendance Verification:</strong>
            Detected <strong>{{ $daysPresent }}</strong> Present days.
            <br><small class="text-muted">Rate: RM {{ number_format($payroll->staff->hourly_rate ?? 0, 2) }}/hour</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btn-sync-attendance"
            data-days="{{ $daysPresent }}"
            data-rate="{{ $payroll->staff->hourly_rate ?? 0 }}"
            onclick="syncAttendance()">
            <i class="bi bi-arrow-repeat"></i> Auto-Calculate Basic
        </button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Adjust Salary Components</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payroll.update', $payroll->id) }}" method="POST" id="payroll-form">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Basic Salary (RM)</label>
                                <input type="number" step="0.01" id="basic_salary" name="basic_salary"
                                    class="form-control form-control-lg calc-trigger"
                                    value="{{ old('basic_salary', $payroll->basic_salary) }}"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-danger">EPF (Employee)</label>
                                <input type="number" id="epf_employee" value="0.00"
                                    class="form-control form-control-lg bg-light fw-bold text-danger"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-danger">SOCSO + EIS</label>
                                <input type="number" id="socso_eis_display" value="0.00"
                                    class="form-control form-control-lg bg-light fw-bold text-danger"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success">Net Salary (RM)</label>
                                <input type="number" id="net_salary" name="net_salary" value="0.00"
                                    class="form-control form-control-lg bg-light fw-bold text-success"
                                    readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- ALLOWANCES SECTION --}}
                        <div class="card mb-3 border-success border-opacity-25">
                            <div class="card-header bg-success bg-opacity-10 py-2">
                                <h6 class="mb-0 text-success small fw-bold text-uppercase">Allowances</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Amount (RM)</label>
                                        <input type="number" step="0.01" min="0"
                                            name="total_allowances"
                                            id="total_allowances"
                                            class="form-control calc-trigger"
                                            value="{{ old('total_allowances', $payroll->allowances ?? 0) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">Remarks / Category</label>
                                        <input list="allowance_options"
                                            name="allowance_remark"
                                            class="form-control"
                                            value="{{ old('allowance_remark', $payroll->allowance_remark) }}"
                                            placeholder="Select or type reason...">
                                        <datalist id="allowance_options">
                                            @foreach($allowanceCategories as $cat)
                                            <option value="{{ $cat->name }}">
                                                @endforeach
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DEDUCTIONS SECTION --}}
                        <div class="card mb-4 border-danger border-opacity-25">
                            <div class="card-header bg-danger bg-opacity-10 py-2">
                                <h6 class="mb-0 text-danger small fw-bold text-uppercase">Manual Deductions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                   <div class="col-md-4">
                                        <label class="form-label small fw-bold">Amount (RM)</label>
                                        <input type="number" step="0.01" min="0"
                                            name="manual_deduction" {{-- Match your new column name --}}
                                            id="total_deductions"
                                            class="form-control calc-trigger"
                                            value="{{ old('manual_deduction', $manualAmount) }}">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Enter only non-statutory deductions.
                                        </small>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">Remarks / Category</label>
                                        <input list="deduction_options"
                                            name="deduction_remark"
                                            class="form-control"
                                            value="{{ old('deduction_remark', $payroll->deduction_remark ?? '') }}"
                                            placeholder="Select or type reason...">
                                        <datalist id="deduction_options">
                                            @foreach($deductionCategories as $cat)
                                            <option value="{{ $cat->name }}">
                                                @endforeach
                                        </datalist>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Note: Statutory deductions (EPF, SOCSO, EIS) are calculated automatically.
                                </small>
                            </div>
                        </div>

                        {{-- FORM ACTIONS --}}
                        <div class="pt-3 border-top d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light px-4 border" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Reset Changes
                            </button>
                            <button type="submit" id="save-btn" class="btn btn-success px-5 shadow-sm">
                                <i class="bi bi-check-lg me-2"></i>Save Adjustments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow border-left-info mb-4">
                <div class="card-header py-3 bg-info bg-opacity-10">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-person-badge me-2"></i>Staff Information
                    </h6>
                </div>
                <div class="card-body small">
                    <div class="text-center mb-3">
                        <h5 class="mb-0 fw-bold">{{ $payroll->staff->full_name }}</h5>
                        <span class="badge bg-secondary text-uppercase">{{ $payroll->staff->employment_type }}</span>
                    </div>
                    <hr>
                    <p class="mb-2">
                        <strong>Staff ID:</strong>
                        <span class="float-end">{{ $payroll->staff->staff_id }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Position:</strong>
                        <span class="float-end">{{ $payroll->staff->position ?? 'N/A' }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Bank:</strong>
                        <span class="float-end">{{ $payroll->staff->bank_name ?? 'N/A' }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Account:</strong>
                        <span class="float-end">{{ $payroll->staff->bank_account_no ?? 'N/A' }}</span>
                    </p>
                    <hr>
                    <p class="mb-0">
                        <strong>Status:</strong>
                        <span class="float-end badge bg-{{ $payroll->status == 'Paid' ? 'success' : 'warning' }}">
                            {{ $payroll->status }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="card shadow border-left-primary">
                <div class="card-header py-3 bg-primary bg-opacity-10">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-calculator me-2"></i>Calculation Breakdown
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-1 text-muted">
                        <strong>EPF Cap:</strong> No limit
                    </p>
                    <p class="mb-1 text-muted">
                        <strong>SOCSO/EIS Cap:</strong> RM 5,000
                    </p>
                    <hr>
                    <p class="mb-0">
                        <small>All statutory rates are automatically applied based on current configurations.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        'use strict';

        const ratesEl = document.getElementById('payroll-rates');
        if (!ratesEl) {
            console.error('Payroll rates element not found');
            return;
        }

        // Get configuration rates
        const STAFF_EPF_RATE = parseFloat(ratesEl.dataset.epf || 11) / 100;
        const EIS_RATE = parseFloat(ratesEl.dataset.eis || 0.2) / 100;
        const SOCSO_RATE = 0.005; 

        /**
         * Sync attendance-based basic salary calculation
         */
        function syncAttendance() {
            const btn = document.getElementById('btn-sync-attendance');
            const days = parseFloat(btn.dataset.days);
            const hourlyRate = parseFloat(btn.dataset.rate);
            const standardHours = parseFloat(ratesEl.dataset.hours || 9); 
            const calculatedBasic = days * standardHours * hourlyRate;

            document.getElementById('basic_salary').value = calculatedBasic.toFixed(2);
            calculateNet();
        }

        /**
        * Calculate net salary and statutory deductions
        */
       function calculateNet() {
        const basic = parseFloat(document.getElementById('basic_salary').value) || 0;
        const allowances = parseFloat(document.getElementById('total_allowances').value) || 0;
        const manualDeductions = parseFloat(document.getElementById('total_deductions').value) || 0;

        // 1. Calculate EPF (usually round numbers if salary is round)
        const epf = basic * STAFF_EPF_RATE;

        // 2. Official SOCSO Bracket Logic (Matches the .75 in your Ledger)
        let socso = 0;
        if (basic > 0) {
            if (basic <= 30) socso = 0.10;
            else if (basic <= 3000) socso = 14.75; // Matches your RM 3,000 staff
            else if (basic <= 3100) socso = 15.25;
            else if (basic <= 3500) socso = 17.25; // Matches your RM 3,500 staff
            else if (basic > 6000)  socso = 29.75; // Capped at ceiling
            else {
                // Approximation for other brackets
                socso = Math.floor(basic / 100) * 0.5 + 0.25; 
            }
        }

        // 3. EIS Calculation (0.2%)
        const cappedSalary = Math.min(basic, 6000);
        const eis = cappedSalary * EIS_RATE;

        // 4. Update the View
        const statutoryTotal = epf + socso + eis;
        const net = (basic + allowances) - (manualDeductions + statutoryTotal);

        document.getElementById('epf_employee').value = epf.toFixed(2);
        // This will now show the decimals like .75 or .25 correctly
        document.getElementById('socso_eis_display').value = (socso + eis).toFixed(2);
        document.getElementById('net_salary').value = net.toFixed(2);
    }
        document.querySelectorAll('.calc-trigger').forEach(input => {
            input.addEventListener('input', calculateNet);
        });

        window.syncAttendance = syncAttendance;

        calculateNet();
    })();
</script>
@endpush