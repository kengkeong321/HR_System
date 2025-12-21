    {{-- Dephnie Ong Yan Yee --}}
    
@extends('layouts.admin')

@section('title', 'Generate Payroll')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .contract-warning { display: none; color: #dc3545; font-size: 0.875rem; margin-top: 5px; font-weight: bold; }
    .part-time-section { display: none; background: #f8f9fc; padding: 15px; border-radius: 5px; border-left: 5px solid #4e73df; margin-bottom: 20px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    @if($errors->any())
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-calculator-fill me-2"></i>Generate Payroll Batch / Individual</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payroll.store') }}" method="POST" id="payroll-form">
                @csrf

                <div class="row mb-4 p-3 bg-light rounded mx-1">
                    @php
                        $currentYear = date('Y');
                    @endphp
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Year <span class="text-danger">*</span></label>
                        <select name="year" id="payroll_year" class="form-select border-primary" onchange="filterMonths()" required>
                            @for ($y = 2024; $y <= $currentYear; $y++)
                                <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Month <span class="text-danger">*</span></label>
                        <select name="month" id="payroll_month" class="form-select border-primary" required>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="select-staff" class="form-label fw-bold">Select Staff <span class="text-danger">*</span></label>
                        <select name="staff_id" id="select-staff" class="form-select" required>
                            <option value="">Search Staff Name or ID...</option>
                            @foreach($staffMembers as $staff)
                            <option value="{{ $staff->staff_id }}"
                                data-salary="{{ $staff->basic_salary }}"
                                data-type="{{ $staff->employment_type }}"
                                data-rate="{{ $staff->hourly_rate }}"
                                data-expiry="{{ $staff->contract_expiry_date }}"
                                data-join="{{ $staff->join_date }}"> 
                                [{{ $staff->employment_type }}] {{ $staff->full_name }} ({{ $staff->staff_id }})
                            </option>
                            @endforeach
                        </select>
                        <div id="contract-alert" class="contract-warning mt-2"></div>
                    </div>
                </div>

                <div id="part-time-box" class="part-time-section shadow-sm">
                    <h6 class="text-primary fw-bold mb-3"><i class="bi bi-clock-history"></i> Part-Time / Hourly Calculation</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Hourly Rate (RM)</label>
                            <input type="number" step="0.01" id="hourly_rate" name="hourly_rate" class="form-control bg-white" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hours Worked <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" id="hours_worked" name="hours_worked" class="form-control calc-trigger border-primary" placeholder="Enter total hours...">
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Basic Salary (RM)</label>
                        <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control form-control-lg calc-trigger" required>
                        <div class="form-text">System defaults to staff master profile salary.</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card border-left-success h-100 py-2">
                            <div class="card-body">
                                <label class="form-label text-success fw-bold"><i class="bi bi-plus-circle me-1"></i> Add Allowance</label>
                                <input type="number" step="0.01" name="allowance" id="allowance" class="form-control calc-trigger mb-2" value="0">
                                <input type="text" name="allowance_remark" id="allowance_remark" class="form-control form-control-sm" placeholder="Reason (e.g. Mileage, Bonus)">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-left-danger h-100 py-2">
                            <div class="card-body">
                                <label class="form-label text-danger fw-bold"><i class="bi bi-dash-circle me-1"></i> Add Deduction</label>
                                <input type="number" step="0.01" name="deduction" id="deduction" class="form-control calc-trigger mb-2" value="0">
                                <input type="text" name="deduction_remark" id="deduction_remark" class="form-control form-control-sm" placeholder="Reason (e.g. Unpaid Leave, Advance)">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 p-3 bg-dark rounded mx-1 align-items-center">
                    <div class="col-md-6">
                        <label class="text-white">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6 text-end">
                        <label class="text-white-50 small d-block">ESTIMATED NET PAYOUT</label>
                        <h2 class="text-success fw-bold mb-0">RM <span id="net_salary_text">0.00</span></h2>
                        <input type="hidden" name="net_salary" id="net_salary_val">
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                        <i class="bi bi-check2-all me-2"></i>Generate & Save Payroll
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function filterMonths() {
    const yearInput = document.getElementById('payroll_year');
    const monthSelect = document.getElementById('payroll_month');
    
    const now = new Date();
    const currentMonth = now.getMonth() + 1; 

    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    monthSelect.innerHTML = '';

    for (let i = 1; i <= currentMonth; i++) {
        let option = document.createElement('option');
        
        option.value = i; 
        option.text = monthNames[i - 1];
        
        if (i === currentMonth) option.selected = true;
        
        monthSelect.appendChild(option);
    }
}

function calculateNet() {
    let basic = parseFloat($('#basic_salary').val()) || 0;
    let allowance = parseFloat($('#allowance').val()) || 0;
    let deduction = parseFloat($('#deduction').val()) || 0;
    let type = $('#select-staff').find(':selected').data('type');

    if (type === 'Part-Time') {
        let rate = parseFloat($('#hourly_rate').val()) || 0;
        let hours = parseFloat($('#hours_worked').val()) || 0;
        basic = rate * hours;
        $('#basic_salary').val(basic.toFixed(2));
    }

    let net = (basic + allowance) - deduction;
    $('#net_salary_text').text(net.toLocaleString(undefined, {minimumFractionDigits: 2}));
    $('#net_salary_val').val(net.toFixed(2));
}

$(document).ready(function() {
    filterMonths();
    
    $('#select-staff').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    $('#select-staff').on('change', function() {
        let selected = $(this).find(':selected');
        let type = selected.data('type');
        let salary = parseFloat(selected.data('salary')) || 0;
        let rate = parseFloat(selected.data('rate')) || 0;
        let expiry = selected.data('expiry');
        let joinDateStr = selected.data('join');

        $('#contract-alert').hide();

        if (type === 'Part-Time') {
            $('#part-time-box').slideDown();
            $('#basic_salary').prop('readonly', true).val(0);
            $('#hourly_rate').val(rate);
        } else {
            $('#part-time-box').slideUp();
            $('#basic_salary').prop('readonly', false).val(salary);
        }

        // Check Expiry
        if (type === 'Contract' && expiry) {
            let today = new Date().toISOString().split('T')[0];
            if (expiry < today) {
                $('#contract-alert').show().html('<i class="bi bi-exclamation-octagon"></i> BLOCK: Contract expired on ' + expiry);
            }
        }

        if (joinDateStr) {
            let joinDate = new Date(joinDateStr);
            let selectedMonth = $('#payroll_month').val();
            let selectedYear = parseInt($('#payroll_year').val());
            let months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            
            if (joinDate.getMonth() === months.indexOf(selectedMonth) && joinDate.getFullYear() === selectedYear) {
                if (joinDate.getDate() > 1) {
                    Swal.fire({
                        title: 'Pro-rata Required',
                        text: `Staff joined on ${joinDateStr}. Adjust Basic Salary for partial month.`,
                        icon: 'info'
                    });
                }
            }
        }
        calculateNet();
    });

    $('.calc-trigger').on('input', calculateNet);
});
</script>
@endpush