@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Staff Attendance Logs</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Staff Name</th>
                        <th>Status</th>
                        <th>Clock In</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $record)
                    <tr>
                        <td>{{ $record->attendance_date }}</td>
                        <td>{{ $record->user->user_name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $record->status == 'Present' ? 'bg-success' : 'bg-danger' }}">
                                {{ $record->status }}
                            </span>
                        </td>
                        <td>{{ date('h:i A', strtotime($record->clock_in_time)) }}</td>
                        <td>{{ $record->remarks }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection