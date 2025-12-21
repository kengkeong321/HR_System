@extends('layouts.staff')

@section('title', 'Mark My Attendance')

@section('content')
{{-- Mu Jun Yi --}}
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>My Attendance</h3>
        <span class="badge bg-light text-dark border p-2">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, d F Y') }}
        </span>
    </div>

    @if(session('error'))
    <div id="flash-error" class="flash-message show">
        <div class="alert alert-danger shadow-sm border-0 mb-0">
            <i class="bi bi-exclamation-octagon me-2"></i> {{ session('error') }}
        </div>
    </div>
    @endif

    <div class="row justify-content-center mt-5">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    
                    <div class="mb-4">
                        @if(!$attendance)
                            <div class="bg-light d-inline-block rounded-circle p-4 mb-3">
                                <i class="bi bi-person-workspace h1 text-secondary"></i>
                            </div>
                            <h4 class="fw-bold">Ready to work?</h4>
                            <p class="text-muted small">Your attendance is not yet recorded for today.</p>
                        @elseif($attendance && !$attendance->clock_out_time)
                            <div class="bg-success-subtle d-inline-block rounded-circle p-4 mb-3">
                                <i class="bi bi-check2-circle h1 text-success"></i>
                            </div>
                            <h4 class="fw-bold">Currently Active</h4>
                            <p class="text-muted small">You clocked in at <strong>{{ date('h:i A', strtotime($attendance->clock_in_time)) }}</strong></p>
                        @else
                            <div class="bg-info-subtle d-inline-block rounded-circle p-4 mb-3">
                                <i class="bi bi-door-closed h1 text-info"></i>
                            </div>
                            <h4 class="fw-bold">Work Completed</h4>
                            <p class="text-muted small">Shift ended at <strong>{{ date('h:i A', strtotime($attendance->clock_out_time)) }}</strong></p>
                        @endif
                    </div>

                    <hr class="my-4">

                    <form action="{{ route('staff.attendance.store') }}" method="POST">
                        @csrf
                        <div class="d-grid gap-3">
                            @if(!$attendance)
                                <button type="submit" name="action_type" value="in" class="btn btn-primary btn-lg py-3 rounded-3 shadow-sm">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Clock In
                                </button>
                            @elseif($attendance && !$attendance->clock_out_time)
                                <button type="submit" name="action_type" value="out" class="btn btn-danger btn-lg py-3 rounded-3 shadow-sm">
                                    <i class="bi bi-box-arrow-right me-2"></i>Clock Out
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-lg py-3 rounded-3 shadow-sm" disabled>
                                    <i class="bi bi-calendar-check me-2"></i>Attendance Finished
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0" role="alert" style="background-color: #fff3cd; color: #856404;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> 
        <strong>Attention:</strong> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="mt-5">
    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Attendance History</h5>
    <div class="table-responsive">
        <table class="table table-hover bg-white rounded shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $record)
                <tr>
                    <td>{{ date('d M Y', strtotime($record->attendance_date)) }}</td>
                    <td>{{ $record->clock_in_time ? date('h:i A', strtotime($record->clock_in_time)) : '-' }}</td>
                    <td>{{ $record->clock_out_time ? date('h:i A', strtotime($record->clock_out_time)) : '-' }}</td>
                    <td>
                        @if($record->status == 'Late')
                            <span class="badge bg-danger text-white">Late</span>
                        @else
                            <span class="badge bg-success text-white">Present</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">No records found yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // Auto-hide the error flash message using your CSS transition logic
    const errorFlash = document.getElementById('flash-error');
    if (errorFlash) {
        setTimeout(() => {
            errorFlash.style.opacity = '0';
            errorFlash.style.top = '-80px';
            setTimeout(() => errorFlash.remove(), 500);
        }, 4000);
    }
</script>
@endsection