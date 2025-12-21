{{-- Dephnie Ong Yan Yee --}}
<!DOCTYPE html>
<html>
<head>
    <title>Payroll Disbursement Report - {{ $batch->month_year }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.2; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 6px 4px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .totals-box { margin-top: 20px; text-align: right; border: 1px solid #333; padding: 10px; background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>University Payroll Disbursement Audit</h1>
        <p>Batch ID: #{{ $batch->id }} | Period: {{ $batch->month_year }} | Status: {{ $batch->status }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="20">#</th>
                <th width="100">Staff Detail</th>
                <th width="60" class="text-right">Basic (RM)</th>
                <th width="60" class="text-right">Allowances</th>
                <th width="60" class="text-right">Deductions*</th>
                <th width="80" class="text-right">Net Salary</th>
                <th width="120">Bank Account</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $index => $row)
            @php
                $breakdown = is_string($row->breakdown) ? json_decode($row->breakdown, true) : $row->breakdown;
                $statutory = $breakdown['calculated_amounts'] ?? [];
                $totalDeductions = ($statutory['epf_employee_rm'] ?? 0) + 
                                   ($statutory['socso_employee_rm'] ?? 0) + 
                                   ($statutory['eis_employee_rm'] ?? 0) + 
                                   ($row->manual_deduction ?? 0);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $row->staff->full_name }}</strong><br>
                    <small>{{ $row->staff->staff_id }}</small>
                </td>
                <td class="text-right">{{ number_format($row->basic_salary, 2) }}</td>
                <td class="text-right">{{ number_format($row->allowances, 2) }}</td>
                <td class="text-right text-danger">-{{ number_format($totalDeductions, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($row->net_salary, 2) }}</td>
                <td>
                    {{ $row->staff->bank_name ?? 'N/A' }}<br>
                    {{ $row->staff->bank_account_no ?? 'N/A' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-box">
        <strong>TOTAL DISBURSEMENT: RM {{ number_format($batch->total_amount, 2) }}</strong>
    </div>

    <p style="font-size: 8px; margin-top: 10px;">*Deductions include EPF, SOCSO, EIS, and manual adjustments as calculated by the university strategy components.</p>
</body>
</html>