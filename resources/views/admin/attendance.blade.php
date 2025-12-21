@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-primary font-weight-bold">
                <i class="fas fa-user-check mr-2"></i> Mark Staff Attendance
            </h3>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.attendance.create') }}" method="GET" class="mb-4">
                <label class="text-muted small text-uppercase font-weight-bold">Step 1: Search Staff</label>
                <div class="input-group shadow-sm">
                    <input type="text" name="search" class="form-control form-control-lg" placeholder="Enter username (e.g. staff01)..." value="{{ $search }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary px-4" type="submit">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </div>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm animate__animated animate__fadeIn">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
            @endif

            @if(!empty($users) && count($users) > 0)
            <hr class="my-4">
            <label class="text-muted small text-uppercase font-weight-bold">Step 2: Complete Attendance Details</label>
            
            <form action="{{ route('admin.attendance.store') }}" method="POST" class="bg-light p-4 rounded border shadow-sm">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Select Staff Member</label>
                        <select name="user_id" class="form-control select2" required>
                            @foreach($users as $user)
                                <option value="{{ $user->user_id }}">
                                    {{ $user->user_name }} (ID: {{ $user->user_id }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Please verify the correct staff name and ID.</small>
                    </div>

                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Present">Present</option>
                            <option value="Late">Late</option>
                            <option value="Absent">Absent</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label class="font-weight-bold">Remarks (Optional)</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Medical leave, traffic jam..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <button type="submit" name="action_type" value="in" class="btn btn-success btn-lg px-5 shadow">
                        <i class="fas fa-sign-in-alt mr-2"></i> Clock In
                    </button>
                    
                    <button type="submit" name="action_type" value="out" class="btn btn-danger btn-lg px-5 shadow">
                        <i class="fas fa-sign-out-alt mr-2"></i> Clock Out
                    </button>
                </div>
            </form>
            @elseif($search)
                <div class="alert alert-warning border-0 shadow-sm text-center py-4">
                    <i class="fas fa-user-slash fa-3x mb-3 text-muted"></i>
                    <p class="mb-0">No active staff found with username "<strong>{{ $search }}</strong>".</p>
                    <small>Only users with the 'Staff', 'HR', and 'Finance' role and 'Active' status can be marked.</small>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection