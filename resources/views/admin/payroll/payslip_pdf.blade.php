<!DOCTYPE html>
<html>

<head>
    <title>Payslip - {{ $payroll->staff->full_name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #003366;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .uni-name {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
        }

        .section-title {
            background: #f4f4f4;
            padding: 5px;
            font-weight: bold;
            margin-top: 15px;
            border-left: 4px solid #003366;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        td {
            padding: 5px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }

        .total-row {
            border-top: 1px solid #ddd;
            font-weight: bold;
            background: #fafafa;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="uni-name">TAR UMT PAYROLL SERVICE</div>
        <div>Itemized Salary Statement: {{ $payroll->month }} {{ $payroll->year }}</div>
    </div>

    {{-- STAFF INFO (Bounded Context) --}}
    <table>
        <tr>
            <td><strong>Staff ID:</strong> {{ $payroll->staff->staff_id }}</td>
            <td class="text-right"><strong>Bank:</strong> {{ $payroll->staff->bank_name }}</td>
        </tr>
        <tr>
            <td><strong>Name:</strong> {{ $payroll->staff->full_name }}</td>
            <td class="text-right"><strong>Account:</strong> {{ $payroll->staff->bank_account_no }}</td>
        </tr>
    </table>

    {{-- EARNINGS SECTION --}}
    <div class="section-title">EARNINGS</div>
    <table>
        <tr>
            <td>Basic Salary</td>
            <td class="text-right">RM {{ number_format($payroll->basic_salary, 2) }}</td>
        </tr>
        @if($payroll->allowances > 0)
        <tr>
            <td>Allowances: {{ $payroll->allowance_remark }}</td>
            <td class="text-right">RM {{ number_format($payroll->allowances, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Total Gross Earnings</td>
            <td class="text-right">RM {{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</td>
        </tr>
    </table>

    {{-- DEDUCTIONS SECTION (Snapshot Pattern) --}}
    @php
    $breakdown = is_string($payroll->breakdown) ? json_decode($payroll->breakdown, true) : $payroll->breakdown;
    @endphp
    <div class="section-title">DEDUCTIONS (STATUTORY & MANUAL)</div>
    <table>
        <tr>
            <td>Employee EPF ({{ $data['statutory_rates']['epf_employee_percent'] ?? 11 }}%)</td>
            <td class="text-right">-RM {{ number_format($data['calculated_amounts']['epf_employee_rm'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>SOCSO Contribution</td>
            <td class="text-right">-RM {{ number_format($data['calculated_amounts']['socso_employee_rm'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>EIS Contribution</td>
            <td class="text-right">-RM {{ number_format($data['calculated_amounts']['eis_rm'] ?? 0, 2) }}</td>
        </tr>
        @if($payroll->deduction > 0)
        <tr>
            <td>Manual Deduction: {{ $payroll->deduction_remark }}</td>
            <td class="text-right">-RM {{ number_format($payroll->deduction, 2) }}</td>
        </tr>
        @endif
    </table>

    {{-- NET PAYOUT --}}
    <div style="margin-top: 20px; border: 2px solid #003366; padding: 10px; text-align: center;">
        <span style="font-size: 14px; font-weight: bold;">NET PAYOUT: RM {{ number_format($payroll->net_salary, 2) }}</span>
    </div>

    {{-- EMPLOYER CONTRIBUTIONS (Informational Only) --}}
    <div class="section-title" style="color: #666; font-size: 10px;">UNIVERSITY CONTRIBUTIONS (FOR REFERENCE)</div>
    <table style="color: #666; font-size: 10px;">
        <tr>
            <td>Employer EPF</td>
            <td class="text-right">RM {{ number_format($data['calculated_amounts']['epf_employer_rm'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Employer SOCSO</td>
            <td class="text-right">RM {{ number_format($data['calculated_amounts']['socso_employer_rm'] ?? 0, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        This is a computer-generated payslip and does not require a signature.<br>
        Generated on: {{ date('d-M-Y H:i:s') }} | Transaction ID: {{ strtoupper(uniqid('TAR-')) }}
    </div>
</body>

</html>