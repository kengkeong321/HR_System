@extends('layouts.admin')

@section('title', 'Edit Payroll Record')

@section('content')
{{-- Hidden data for JavaScript calculations fetched from payroll_configs --}}
<div id="payroll-rates"
    data-epf="{{ $configs['staff_epf_rate'] ?? 11.00 }}"
    data-eis="{{ $configs['eis_rate'] ?? 0.20 }}"
    style="display:none;">
</div>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-gray-800">Edit Payroll: {{ $payroll->staff->full_name }}</h2>
        <a href="{{ route('admin.payroll.batch_view', $payroll->batch_id) }}" class="btn btn-secondary shadow-sm">
            <i class="bi bi-arrow-left"></i> Back to Batch
        </a>
    </div>

    {{-- ATTENDANCE INTEGRATION --}}
    @if($payroll->staff->employment_type == 'Part-Time' || $daysPresent > 0)
    <div class="alert alert-info shadow-sm d-flex justify-content-between align-items-center border-left-info mb-4">
        <div>
            <i class="bi bi-clock-history fs-4 me-2"></i>
            <strong>Attendance Verification:</strong>
            Detected <strong>{{ $daysPresent }}</strong> Present days.
            <br><small class="text-muted">Rate: RM {{ number_format($payroll->staff->hourly_rate ?? 0, 2) }}/day</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btn-sync-attendance"
            data-days="{{ $daysPresent }}" data-rate="{{ $payroll->staff->hourly_rate ?? 0 }}" onclick="syncAttendance()">
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

                        <input type="hidden" name="attendance_count_hidden" id="attendance_count_hidden"
                            value="{{ old('attendance_count_hidden', $payroll->attendance_count ?? $daysPresent) }}">

                        {{-- SECTION B: PRIMARY EARNINGS (Fixed monthly or daily rate) --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Basic Salary (RM)</label>
                                <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-control form-control-lg calc-trigger" value="{{ $payroll->basic_salary }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-danger">EPF</label>
                                <input type="number" id="epf_employee" class="form-control form-control-lg bg-light" readonly>
                            </div>
                            {{-- NEW STATUTORY DISPLAY FIELDS --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-danger">SOCSO + EIS</label>
                                <input type="number" id="socso_eis_display" class="form-control form-control-lg bg-light" readonly>

                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success">Net Salary (RM)</label>
                                <input type="number" id="net_salary" name="net_salary" class="form-control form-control-lg bg-light fw-bold text-success" readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- SECTION C: MANUAL ADJUSTMENTS WITH DYNAMIC REMARKS --}}
                        <div class="card mb-3 border-success border-opacity-25">
                            <div class="card-header bg-success bg-opacity-10 py-2">
                                <h6 class="mb-0 text-success small fw-bold text-uppercase">Allowances</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Amount (RM)</label>
                                        <input type="number" step="0.01" min="0" name="total_allowances" id="total_allowances"
                                            class="form-control calc-trigger" {{-- Added calc-trigger here --}}
                                            value="{{ old('total_allowances', $payroll->allowances ?? 0) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">Remarks / Category </label>
                                        <input list="allowance_options" name="allowance_remark" class="form-control"
                                            value="{{ old('allowance_remark', $payroll->allowance_remark) }}" placeholder="Select or type reason...">
                                        <datalist id="allowance_options">
                                            @foreach($allowanceCategories as $cat) <option value="{{ $cat->name }}"> @endforeach
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 border-danger border-opacity-25">
                            <div class="card-header bg-danger bg-opacity-10 py-2">
                                <h6 class="mb-0 text-danger small fw-bold text-uppercase">Deductions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Amount (RM)</label>
                                        <input type="number" step="0.01" min="0" name="total_deductions" id="total_deductions"
                                            class="form-control calc-trigger" {{-- Added calc-trigger here --}}
                                            value="{{ old('total_deductions', $payroll->deduction ?? 0) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">Remarks / Category</label>
                                        <input list="deduction_options" name="deduction_remark" class="form-control"
                                            value="{{ old('deduction_remark', $payroll->deduction_remark) }}" placeholder="Select or type reason...">
                                        <datalist id="deduction_options">
                                            @foreach($deductionCategories as $cat) <option value="{{ $cat->name }}"> @endforeach
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light px-4 border" onclick="location.reload()">Reset Changes</button>
                            <button type="submit" id="save-btn" class="btn btn-success px-5 shadow-sm">
                                <i class="bi bi-check-lg me-2"></i>Save Adjustments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SIDEBAR: STAFF INFO --}}
        <div class="col-md-4">
            <div class="card shadow border-left-info mb-4">
                <div class="card-header py-3 bg-info bg-opacity-10">
                    <h6 class="m-0 font-weight-bold text-info"><i class="bi bi-person-badge me-2"></i>Staff Information</h6>
                </div>
                <div class="card-body small">
                    <div class="text-center mb-3">
                        <h5 class="mb-0 fw-bold">{{ $payroll->staff->full_name }}</h5>
                        <span class="badge bg-secondary text-uppercase">{{ $payroll->staff->employment_type }}</span>
                    </div>
                    <hr>
                    <p class="mb-2"><strong>Staff ID:</strong> <span class="float-end">{{ $payroll->staff->staff_id }}</span></p>
                    <p class="mb-2"><strong>Bank:</strong> <span class="float-end">{{ $payroll->staff->bank_name ?? 'N/A' }}</span></p>
                    <p class="mb-2"><strong>Account:</strong> <span class="float-end">{{ $payroll->staff->bank_account_no ?? 'N/A' }}</span></p>
                    <hr>
                    <p class="mb-0"><strong>Status:</strong>
                        <span class="float-end badge bg-{{ $payroll->status == 'Paid' ? 'success' : 'warning' }}">
                            {{ $payroll->status }}
                        </span>
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

        // 1. Fetch dynamic rates passed from the controller
        const ratesEl = document.getElementById('payroll-rates');
        const STAFF_EPF_RATE = parseFloat(ratesEl.dataset.epf || 11) / 100;
        const EIS_RATE = parseFloat(ratesEl.dataset.eis || 0.2) / 100;

        /**
         * MAIN MATH ENGINE: Updates values instantly as you type
         */
        function calculateNet() {
            const getVal = (id) => {
                const el = document.getElementById(id);
                return el ? (parseFloat(el.value) || 0) : 0;
            };

            const basic = parseFloat(document.getElementById('basic_salary').value) || 0;
            const allowances = parseFloat(document.getElementById('total_allowances').value) || 0;
            const deductions = parseFloat(document.getElementById('total_deductions').value) || 0;

            // Statutory Calculations (EPF, SOCSO, EIS)
            const epf = basic * STAFF_EPF_RATE;
            const capped = Math.min(basic, 6000);
            const socso = capped * 0.005;
            const eis = capped * EIS_RATE;

            const statutoryTotal = epf + socso + eis;

            // Final Net Calculation
            const net = (basic + allowances) - (deductions + statutoryTotal);

            document.getElementById('epf_employee').value = epf.toFixed(2);
            document.getElementById('socso_eis_display').value = (socso + eis).toFixed(2);
            document.getElementById('net_salary').value = net.toFixed(2);

            if (net < 0) {
                document.getElementById('net_salary').classList.add('text-danger');
                document.getElementById('save-btn').disabled = true;
            }

            const saveBtn = document.getElementById('save-btn');
            if (saveBtn) saveBtn.disabled = net < 0;
        }

        /**
         * Attendance Sync Function
         */
        window.syncAttendance = function() {
            const btn = document.getElementById('btn-sync-attendance');
            const days = parseFloat(btn.getAttribute('data-days')) || 0;
            const rate = parseFloat(btn.getAttribute('data-rate')) || 0;
            const basicInput = document.getElementById('basic_salary');

            if (basicInput) {
                basicInput.value = (days * rate).toFixed(2);
                calculateNet();
            }
        };

        // 2. Initialize Listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.calc-trigger').forEach(input => {
                input.addEventListener('input', calculateNet);
            });

            calculateNet();
        });
    })();
</script>
@endpush