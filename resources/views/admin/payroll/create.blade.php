@extends('layouts.admin')

@section('title', 'Create Payroll')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    {{-- Error Display Section --}}
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Generate Payroll</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payroll.store') }}" method="POST">
                @csrf

                {{-- 1. Staff Selection --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="select-staff" class="form-label">Select Staff <span class="text-danger">*</span></label>
                        <select name="staff_id" id="select-staff" class="form-select" required>
                            <option value="">Search Staff...</option>
                            @foreach($staffMembers as $staff)
                            <option value="{{ $staff->staff_id }}"
                                data-salary="{{ $staff->basic_salary }}"
                                data-type="{{ $staff->employment_type }}"
                                data-rate="{{ $staff->hourly_rate }}"
                                data-expiry="{{ $staff->contract_expiry_date }}">
                                [{{ $staff->employment_type }}] {{ $staff->full_name }} ({{ $staff->staff_id }})
                            </option>
                            @endforeach
                        </select>
                        <div id="contract-alert" class="contract-warning">
                            <i class="bi bi-exclamation-triangle"></i> Warning: This contract has expired!
                        </div>
                    </div>
                </div>

                {{-- 2. Date Selection --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Month <span class="text-danger">*</span></label>
                        <select name="month" class="form-select" required>
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                            <option value="{{ $m }}" {{ date('F') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Year <span class="text-danger">*</span></label>
                        <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                    </div>
                </div>

                <hr>

                {{-- 3. Dynamic Section for Part-Timers --}}
                <div id="part-time-box" class="part-time-section">
                    <h6 class="text-primary fw-bold">Part-Time Calculation</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Hourly Rate (RM)</label>
                            <input type="number" step="0.01" id="hourly_rate" name="hourly_rate" class="form-control calc-trigger" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Hours Worked <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" id="hours_worked" name="hours_worked" class="form-control calc-trigger" placeholder="Enter hours...">
                        </div>
                    </div>
                </div>

                {{-- 4. Financials with Standard Library --}}
                <h6 class="text-muted mb-3">Salary Breakdown & Adjustments</h6>

                {{-- Basic Salary --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Basic Salary (RM)</label>
                        <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control calc-trigger" required>
                        <div class="form-text">Fixed monthly base.</div>
                    </div>
                </div>

                {{-- Allowance Section --}}
                <div class="row mb-3 p-3 bg-light rounded">
                    <div class="col-md-4">
                        <label class="form-label text-success"><i class="bi bi-plus-circle"></i> Allowance Amount (RM)</label>
                        <input type="number" step="0.01" name="allowance" id="allowance" class="form-control calc-trigger" value="0" min="0">
                    </div>

                    {{-- STANDARD LIBRARY DROPDOWN --}}
                    <div class="col-md-4">
                        <label class="form-label">Allowance Type</label>
                        <select id="allowance_select" class="form-select remark-autofill" data-target="allowance_remark">
                            <option value="">-- Custom / None --</option>
                            @foreach($allowanceTypes as $type)
                            <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Remark / Detail</label>
                        {{-- Allows manual typing OR auto-filled from dropdown --}}
                        <input type="text" name="allowance_remark" id="allowance_remark" class="form-control" placeholder="Description...">
                    </div>
                </div>

                {{-- Deduction Section --}}
                <div class="row mb-3 p-3 bg-light rounded">
                    <div class="col-md-4">
                        <label class="form-label text-danger"><i class="bi bi-dash-circle"></i> Deduction Amount (RM)</label>
                        <input type="number" step="0.01" name="deduction" id="deduction" class="form-control calc-trigger" value="0" min="0">
                    </div>

                    {{-- STANDARD LIBRARY DROPDOWN --}}
                    <div class="col-md-4">
                        <label class="form-label">Deduction Type</label>
                        <select id="deduction_select" class="form-select remark-autofill" data-target="deduction_remark">
                            <option value="">-- Custom / None --</option>
                            @foreach($deductionTypes as $type)
                            <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Remark / Detail</label>
                        <input type="text" name="deduction_remark" id="deduction_remark" class="form-control" placeholder="Description...">
                    </div>
                </div>

                {{-- 5. Totals --}}
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Net Salary (Calculated)</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="text" id="net_salary" class="form-control fw-bold bg-light" readonly value="0.00">
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-5"><i class="bi bi-save"></i> Generate Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#select-staff').select2({
            theme: 'bootstrap-5',
            placeholder: "Search staff...",
            width: '100%'
        });

        // Logic when Staff is selected
        $('#select-staff').on('change', function() {
            var selected = $(this).find(':selected');
            var type = selected.data('type'); // Full-Time, Part-Time, Contract
            var salary = parseFloat(selected.data('salary')) || 0;
            var rate = parseFloat(selected.data('rate')) || 0;
            var expiry = selected.data('expiry');

            // 1. Reset Warnings
            $('#contract-alert').hide();

            // 2. Handle Employment Types
            if (type === 'Part-Time') {
                $('#part-time-box').slideDown(); // Show hourly inputs
                $('#basic_salary').prop('readonly', true).val(0); // Lock basic salary
                $('#hourly_rate').val(rate);
                $('#hours_worked').val('').focus();
            } else {
                $('#part-time-box').slideUp(); // Hide hourly inputs
                $('#basic_salary').prop('readonly', false).val(salary); // Auto-fill Fixed Salary
            }

            // 3. Handle Contract Expiry Check
            if (type === 'Contract' && expiry) {
                var today = new Date().toISOString().split('T')[0];
                if (expiry < today) {
                    $('#contract-alert').show().html('<i class="bi bi-exclamation-octagon"></i> BLOCK: Contract expired on ' + expiry);
                }
            }

            calculateNet();
        });

        // SMART REMARK LOGIC
        $('.remark-autofill').on('change', function() {
            var selectedText = $(this).val();
            var targetId = $(this).data('target');

            // 1. Auto-Fill: Copy selection to the text box
            if (selectedText) {
                $('#' + targetId).val(selectedText);
            } else {
                $('#' + targetId).val('');
            }
        });

        // SHORT-CODE SYSTEM (Optional Power User Feature)
        // If admin types "OT" in the remark box and hits space, it expands.
        $('input[name$="_remark"]').on('keyup', function(e) {
            if (e.code === 'Space') {
                var val = $(this).val();

                // Define Short-Codes
                var codes = {
                    "OT ": "Overtime Payment ",
                    "UL ": "Unpaid Leave Deduction ",
                    "PH ": "Public Holiday Pay "
                };

                // Check if value matches a short code
                Object.keys(codes).forEach(function(code) {
                    if (val.endsWith(code)) {
                        // Replace the short code with the full text
                        var newVal = val.slice(0, -code.length) + codes[code];
                        $(this).val(newVal);
                    }
                }.bind(this));
            }
        });

        // PRO-RATA AUTO DETECT (System Generated Remark)
        $('#select-staff').on('change', function() {
            var selected = $(this).find(':selected');
            var joinDate = new Date(selected.data('join')); // Assuming you passed data-join in the option
            var selectedMonth = $('#month').val();
            var selectedYear = $('#year').val();

            // Check if joined in selected month/year
            // (Simplified logic for demonstration)
            // If detected, auto-fill the remark:
            // $('#allowance_remark').val('Pro-rata salary for Join Date: ' + joinDate);
        });

        // Live Calculation
        $('.calc-trigger').on('input', calculateNet);

        function calculateNet() {
            var basic = parseFloat($('#basic_salary').val()) || 0;

            // If Part-Time, override basic with (Hours * Rate)
            if ($('#part-time-box').is(':visible')) {
                var hours = parseFloat($('#hours_worked').val()) || 0;
                var rate = parseFloat($('#hourly_rate').val()) || 0;
                basic = hours * rate;
                $('#basic_salary').val(basic.toFixed(2)); // Update the hidden basic field
            }

            var allowance = parseFloat($('#allowance').val()) || 0;
            var deduction = parseFloat($('#deduction').val()) || 0;
            var net = (basic + allowance) - deduction;

            $('#net_salary').val(net.toFixed(2));
        }
    });
</script>
@endpush