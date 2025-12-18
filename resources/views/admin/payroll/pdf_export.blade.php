<!DOCTYPE html>
<html>
<head>
    <title>Payroll Disbursement Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; color: #555; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        
        .totals { margin-top: 20px; text-align: right; }
        .totals h3 { border-top: 2px solid #333; display: inline-block; padding-top: 5px; }

        .signatures { margin-top: 50px; width: 100%; }
        .sig-box { float: left; width: 30%; border-top: 1px solid #000; margin-right: 5%; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payroll Disbursement Report</h1>
        <p>Batch: {{ $batch->month_year }}</p>
        <p>Generated On: {{ now()->toDayDateTimeString() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Staff Name</th>
                <th>Bank Name</th>
                <th>Account No.</th>
                <th style="text-align: right;">Net Salary (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->staff->full_name }}</td>
                <td>{{ $row->staff->bank_name ?? 'N/A' }}</td>
                <td>{{ $row->staff->bank_account_no ?? 'Missing' }}</td>
                <td style="text-align: right;">{{ number_format($row->net_salary, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <h3>Total Amount: RM {{ number_format($batch->total_amount, 2) }}</h3>
    </div>

    {{-- Signature Section for Finance --}}
    <div class="signatures">
        <div class="sig-box">
            <strong>Prepared By:</strong><br>
            HR Department
        </div>
        <div class="sig-box">
            <strong>Approved By:</strong><br>
            Finance Director
        </div>
        <div class="sig-box" style="margin-right: 0;">
            <strong>Verified By:</strong><br>
            Bursary Office
        </div>
    </div>
</body>
</html>