<!DOCTYPE html>
<html>

<head>
    <title>Payroll Disbursement Report - {{ $batch->month_year }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0; font-size: 12px; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 10px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 10px; color: #444; }
        
        /* Alternating row colors for better readability */
        tbody tr:nth-child(even) { background-color: #fafafa; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        .totals { margin-top: 30px; text-align: right; padding-right: 10px; }
        .totals-box { 
            display: inline-block; 
            border: 2px solid #333; 
            padding: 10px 20px; 
            background-color: #f8f9fa;
        }
        .totals h3 { margin: 0; font-size: 14px; }

        .remarks-text { font-style: italic; font-size: 9px; color: #555; }

        .signatures { margin-top: 60px; width: 100%; page-break-inside: avoid; }
        .sig-box { 
            float: left; 
            width: 28%; 
            margin-right: 4%; 
            text-align: center; 
        }
        .sig-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        .clear { clear: both; }
    </style>
</head>

<body>
    <div class="header">
        <h1>Payroll Disbursement Report</h1>
        <p><strong>Payment Batch:</strong> {{ $batch->month_year }}</p>
        <p><strong>Generated On:</strong> {{ now()->toDayDateTimeString() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30px" class="text-center">#</th>
                <th width="120px">Staff Name</th>
                <th width="70px" class="text-center">Attendance</th>
                <th width="80px">Bank Info</th>
                <th width="100px">Account No.</th>
                <th width="120px">Remarks</th>
                <th width="90px" class="text-right">Net Salary (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="fw-bold">{{ $row->staff->full_name }}</td>
                <td class="text-center">{{ $row->attendance_count ?? 0 }} days</td>
                <td>{{ $row->staff->bank_name ?? 'Maybank' }}</td>
                <td>{{ $row->staff->bank_account_no ?? 'N/A' }}</td>
                <td class="remarks-text">{{ $row->allowance_remark ?? '-' }}</td>
                <td class="text-right fw-bold">{{ number_format($row->net_salary, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-box">
            <h3>GRAND TOTAL: RM {{ number_format($batch->total_amount, 2) }}</h3>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line">
                <strong>Prepared By:</strong><br>
                Human Resources Department
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-line">
                <strong>Approved By:</strong><br>
                Finance Director
            </div>
        </div>
        <div class="sig-box" style="margin-right: 0;">
            <div class="sig-line">
                <strong>Verified By:</strong><br>
                Bursary Office
            </div>
        </div>
    </div>
    <div class="clear"></div>
</body>

</html>