@extends('layouts.staff')
@section('content')
{{-- Mu Jun Yi --}}
<div class="container py-4">
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Apply for Leave</h5>
                    <form action="{{ route('staff.leave.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small fw-bold">Leave Type</label>
                            <select name="leave_type" class="form-select">
                                <option value="Medical">Medical Leave</option>
                                <option value="Annual">Annual Leave</option>
                                <option value="Emergency">Emergency Leave</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col"><label class="small fw-bold">Start</label><input type="date" name="start_date" class="form-control"></div>
                            <div class="col"><label class="small fw-bold">End</label><input type="date" name="end_date" class="form-control"></div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Reason</label>
                            <textarea name="reason" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">My Leave Status</h5>
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $leave)
                            <tr>
                                <td>{{ $leave->leave_type }}</td>
                                <td class="small">{{ $leave->start_date }} to {{ $leave->end_date }}</td>
                                <td>
                                    <span class="badge {{ $leave->status == 'Approved' ? 'bg-success' : ($leave->status == 'Rejected' ? 'bg-danger' : 'bg-warning') }}">
                                        {{ $leave->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif