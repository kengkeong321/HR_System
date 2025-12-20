@extends('layouts.admin')
@section('title', 'Staff Training Records')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staff Training Records</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Select Staff to View History</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('training.records') }}" method="POST" class="row g-3 align-items-center">
                @csrf
                <div class="col-auto">
                    <label class="font-weight-bold text-dark">Staff Name:</label>
                </div>
                <div class="col-md-4">
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Choose a Staff Member --</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->user_id }}" {{ (isset($selectedUser) && $selectedUser->user_id == $staff->user_id) ? 'selected' : '' }}>
        
                                {{ $staff->user_name }} 
                                ({{ $staff->staffRecord->email ?? 'No Email' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-search fa-sm text-white-50"></i> View Records
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedUser)
        <div class="card shadow mb-4 border-left-success">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-history mr-1"></i> Training History: {{ $selectedUser->user_name }}
                </h6>
                <span class="badge bg-dark">Total: {{ $selectedUser->trainings->count() }} Programs</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light text-dark">
                            <tr class="text-center">
                                <th>Training Title</th>
                                <th>Date & Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-dark">
                            @forelse($selectedUser->trainings as $training)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $training->title }}</td>
                                    <td class="align-middle text-center small">
                                        {{ $training->start_time }} <br>
                                        <span class="text-muted">to {{ $training->end_time }}</span>
                                    </td>
                                    <td class="align-middle">{{ $training->venue }}</td>
                                    <td class="align-middle text-center">
                                        @php
                                            $status = $training->pivot->status;
                                            $badgeClass = match($status) {
                                                'Completed' => 'bg-success',
                                                'Attended'  => 'bg-info',
                                                'Missed'    => 'bg-danger',
                                                'Assigned'  => 'bg-warning text-dark',
                                                default     => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2" style="font-size: 0.85rem;">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i><br>
                                        No training records found for this staff member.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection