@extends('layouts.admin')
@section('title', 'Staff Training Records')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staff Training Records</h1>
        <a href="{{ route('training.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Training List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Select Staff to View History</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('training.records') }}" method="POST" class="form-inline">
                @csrf
                <label class="mr-3">Select Staff:</label>
                <select name="user_id" class="form-control mr-3" required>
                    <option value="">-- Choose a Staff Member --</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->user_id }}" {{ (isset($selectedUser) && $selectedUser->user_id == $staff->user_id) ? 'selected' : '' }}>
                            {{ $staff->user_name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> View Records
                </button>
            </form>
        </div>
    </div>

    @if($selectedUser)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Training History for: {{ $selectedUser->user_name }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Training Title</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedUser->trainings as $training)
                                <tr>
                                    <td>{{ $training->title }}</td>
                                    <td>{{ $training->start_time }}</td>
                                    <td>{{ $training->venue }}</td>
                                    <td>
                                        <span class="badge {{ match($training->pivot->status) {
                                            'Completed' => 'badge-success',
                                            'Attended' => 'badge-warning',
                                            'Missed' => 'badge-danger',
                                            default => 'badge-info'
                                        } }}">
                                            {{ $training->pivot->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No training history found for this user.</td>
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