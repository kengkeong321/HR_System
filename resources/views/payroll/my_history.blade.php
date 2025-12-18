@extends('layout') {{-- Or whatever your main layout file is named --}}

@section('title', 'My Payslips')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">My Payment History</h5>
    </div>
    <div class="card-body">
        @if($payrolls->isEmpty())
            <div class="alert alert-info">No payment records found.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Month/Year</th>
                            <th>Basic Salary</th>
                            <th>Allowance</th>
                            <th>Deduction</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $row)
                        <tr>
                            <td>{{ $row->month }} {{ $row->year }}</td>
                            <td>{{ number_format($row->basic_salary, 2) }}</td>
                            <td class="text-success">+{{ number_format($row->allowance, 2) }}</td>
                            <td class="text-danger">-{{ number_format($row->deduction, 2) }}</td>
                            <td class="fw-bold">{{ number_format($row->net_salary, 2) }}</td>
                            <td>
                                @if($row->status == 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>{{ $row->payment_date ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection