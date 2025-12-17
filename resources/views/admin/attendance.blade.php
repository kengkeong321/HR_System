@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Admin: Mark Staff Attendance</h3>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.attendance.create') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search username..." value="{{ $search }}">
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="submit">Search</button>
                    </div>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(count($users) > 0)
            <form action="{{ route('admin.attendance.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Select Staff Member</label>
                    <select name="user_id" class="form-control" required>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}">{{ $user->user_name }} (ID: {{ $user->user_id }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Submit Attendance</button>
            </form>
            @elseif($search)
                <p class="text-danger">No staff member found with that username.</p>
            @endif
        </div>
    </div>
</div>

@endsection