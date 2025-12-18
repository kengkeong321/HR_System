@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Edit Attendance: {{ $attendance->user->user_name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PATCH') <div class="form-group mb-3">
                    <label>Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="{{ $attendance->attendance_date }}">
                </div>

                <div class="form-group mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Present" {{ $attendance->status == 'Present' ? 'selected' : '' }}>Present</option>
                        <option value="Absent" {{ $attendance->status == 'Absent' ? 'selected' : '' }}>Absent</option>
                        <option value="Late" {{ $attendance->status == 'Late' ? 'selected' : '' }}>Late</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3">{{ $attendance->remarks }}</textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">Update Record</button>
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-default ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection