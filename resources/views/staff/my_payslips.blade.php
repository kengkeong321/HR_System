@extends('layouts.app') 

@section('content')
<div class="container mt-4">
    <h2>My Payslips</h2>
    <div class="card shadow-sm mt-3">
        <div class="card-body">
            @if($payrolls->isEmpty())
                <div class="alert alert-info">No payslips found.</div>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month/Year</th>
                            <th>Basic Salary</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $slip)
                        <tr>
                            <td>{{ $slip->month }} {{ $slip->year }}</td>
                            <td>RM {{ number_format($slip->basic_salary, 2) }}</td>
                            <td class="fw-bold text-success">RM {{ number_format($slip->net_salary, 2) }}</td>
                            <td><span class="badge bg-success">{{ $slip->status }}</span></td>
                            <td>
                                {{-- Link to Download PDF (Reuse your existing export route) --}}
                                <a href="{{ route('admin.payroll.export_slip', $slip->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="bi bi-download"></i> PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection