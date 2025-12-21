@extends('layouts.admin')

@section('content')

{{-- Dephnie Ong Yan Yee --}}
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payroll & Attendance Service Integration Audit</h6>
        </div>
        <div class="card-body">
            <p>Testing the RESTful exposure for <strong>{{ $payroll->staff->full_name }}</strong></p>
            
            <div class="mb-3">
                <span class="badge bg-success">Status: {{ $status }} OK</span>
                <span class="badge bg-info">Module: Attendance Service</span>
            </div>

            <div class="bg-dark text-success p-4 rounded shadow" style="max-height: 500px; overflow-y: auto;">
                <pre class="mb-0"><code>{{ json_encode($rawData, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
            
            <div class="mt-4">
                <h5>Data Verification Logic:</h5>
                <ul>
                    <li><strong>Service URL:</strong> 127.0.0.1:8000/api/attendance/user/{{ $payroll->staff->user_id }}</li>
                    <li><strong>Records Found:</strong> {{ count($rawData['data'] ?? []) }}</li>
                    <li><strong>Days Present:</strong> {{ collect($rawData['data'] ?? [])->where('status', 'Present')->count() }}</li>
                </ul>
                <a href="{{ route('admin.payroll.batch_view', $payroll->batch_id) }}" class="btn btn-primary">Return to Ledger</a>
            </div>
        </div>
    </div>
</div>
@endsection