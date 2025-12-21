<!DOCTYPE html>
<html>
<head>
    <title>Payslip - {{ $payroll->staff->full_name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #003366; padding-bottom: 10px; margin-bottom: 15px; }
        .uni-name { font-size: 18px; font-weight: bold; color: #003366; }
        .statement-title { font-size: 12px; font-weight: bold; margin-top: 5px; text-transform: uppercase; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; }
        
        .section-title { background: #003366; color: white; padding: 5px 10px; font-weight: bold; margin-top: 15px; text-transform: uppercase; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data-table td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .font-monospace { font-family: 'Courier', monospace; }
        .bold { font-weight: bold; }
        
        .total-row { background: #f9f9f9; font-weight: bold; border-top: 2px solid #333 !important; }
        .net-payout-box { margin-top: 25px; border: 2px solid #003366; padding: 15px; text-align: center; background: #f0f4f8; }
        .net-amount { font-size: 20px; color: #003366; }
        
        .footer { margin-top: 40px; font-size: 9px; text-align: center; color: #777; border-top: 1px dashed #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    @php
        // Decode the snapshot breakdown
        $data = is_string($payroll->breakdown) ? json_decode($payroll->breakdown, true) : $payroll->breakdown;
        $calc = $data['calculated_amounts'] ?? [];
        $rates = $data['statutory_rates'] ?? [];
    @endphp

    <div class="header">
        <div class="uni-name">TAR UMT PAYROLL SERVICE</div>
        <div class="statement-title">Salary Statement: {{ date('F', mktime(0, 0, 0, $payroll->month, 1)) }} {{ $payroll->year }}</div>
    </div>

    {{-- STAFF DETAILS --}}
    <table class="info-table">
        <tr>
            <td width="15%"><strong>Staff ID</strong></td>
            <td width="35%">: {{ $payroll->staff->staff_id }}</td>
            <td width="15%"><strong>Bank Name</strong></td>
            <td width="35%">: {{ $payroll->staff->bank_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Staff Name</strong></td>
            <td>: {{ $payroll->staff->full_name }}</td>
            <td><strong>Account No</strong></td>
            <td>: {{ $payroll->staff->bank_account_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Department</strong></td>
            <td>: {{ $payroll->staff->department->depart_name ?? 'General' }}</td>
            <td><strong>Pay Type</strong></td>
            <td>: {{ $payroll->staff->employment_type }}</td>
        </tr>
    </table>

    {{-- EARNINGS SECTION --}}
    <div class="section-title">Earnings Breakdown</div>
    <table class="data-table">
        <tr>
            <td>
                Basic Salary 
                @if($payroll->staff->employment_type === 'Part-Time')
                    <small style="color:#666;">(Hourly Rate: RM {{ number_format($payroll->staff->hourly_rate, 2) }})</small>
                @endif
            </td>
            <td class="text-right font-monospace">RM {{ number_format($payroll->basic_salary, 2) }}</td>
        </tr>
        
        @if($payroll->allowances > 0)
        <tr>
            <td>
                Allowances & Claims
                @if($payroll->allowance_remark)
                    <br><small style="color:#666;">Note: {{ $payroll->allowance_remark }}</small>
                @endif
            </td>
            <td class="text-right font-monospace text-success">RM {{ number_format($payroll->allowances, 2) }}</td>
        </tr>
        @endif

        <tr class="total-row">
            <td><strong>TOTAL GROSS EARNINGS</strong></td>
            <td class="text-right font-monospace"><strong>RM {{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</strong></td>
        </tr>
    </table>

    {{-- DEDUCTIONS SECTION --}}
    <div class="section-title">Deductions (Statutory & Adjustments)</div>
    <table class="data-table">
        <tr>
            <td>Employee EPF Contribution ({{ $rates['epf_employee_percent'] ?? 11 }}%)</td>
            <td class="text-right font-monospace">-RM {{ number_format($calc['epf_employee_rm'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>SOCSO Contribution (Employee)</td>
            <td class="text-right font-monospace">-RM {{ number_format($calc['socso_employee_rm'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>EIS Contribution (Employee)</td>
            <td class="text-right font-monospace">-RM {{ number_format($calc['eis_employee_rm'] ?? 0, 2) }}</td>
        </tr>
        
        @if($payroll->manual_deduction > 0)
        <tr>
            <td>
                Manual Adjustments / Deductions
                @if($payroll->deduction_remark)
                    <br><small style="color:#666;">Reason: {{ $payroll->deduction_remark }}</small>
                @endif
            </td>
            <td class="text-right font-monospace">-RM {{ number_format($payroll->manual_deduction, 2) }}</td>
        </tr>
        @endif

        <tr class="total-row">
            <td><strong>TOTAL DEDUCTIONS</strong></td>
            <td class="text-right font-monospace"><strong>-RM {{ number_format($payroll->deduction + ($payroll->manual_deduction ?? 0), 2) }}</strong></td>
        </tr>
    </table>

    {{-- NET PAYOUT BOX --}}
    <div class="net-payout-box">
        <div style="text-transform: uppercase; font-weight: bold; letter-spacing: 1px; margin-bottom: 5px;">Total Net Payout</div>
        <div class="net-amount font-monospace">RM {{ number_format($payroll->net_salary, 2) }}</div>
    </div>

    {{-- UNIVERSITY CONTRIBUTIONS --}}
    <div class="section-title" style="background: #eee; color: #333; margin-top: 30px;">Employer Contributions (Informational)</div>
    <table class="data-table" style="color: #666;">
        <tr>
            <td>Employer EPF ({{ $rates['epf_employer_percent'] ?? 13 }}%)</td>
            <td class="text-right font-monospace">RM {{ number_format($calc['epf_employer_rm'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Employer SOCSO</td>
            <td class="text-right font-monospace">RM {{ number_format($calc['socso_employer_rm'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Employer EIS</td>
            <td class="text-right font-monospace">RM {{ number_format($calc['eis_employer_rm'] ?? 0, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        This is a computer-generated document. No signature is required.<br>
        <strong>TAR UMT University - Payroll Department</strong><br>
        Generated on: {{ date('d-M-Y H:i:s') }} | Ref ID: {{ strtoupper(uniqid('TAR-')) }}
    </div>
</body>
</html>