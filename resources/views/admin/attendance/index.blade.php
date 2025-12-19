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
                        <th>Clock Out</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $record)
                    <tr>
                        <td>{{ $record->attendance_date }}</td>
                        <td>{{ $record->user->user_name ?? 'N/A' }}
                            <span class="badge bg-secondary small">
                                <i class="bi bi-briefcase me-1"></i>
                                {{ $record->user?->staff?->position ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $record->status == 'Present' ? 'bg-success' : 'bg-danger' }}">
                                {{ $record->status }}
                            </span>
                        </td>
                        <td>{{ date('h:i A', strtotime($record->clock_in_time)) }}</td>
                        <td>{{ $record->clock_out_time ? date('h:i A', strtotime($record->clock_out_time)) : '-' }}</td>
                        <td>{{ $record->remarks }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.edit', $record->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection