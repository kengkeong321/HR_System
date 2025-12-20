@extends('layouts.admin')
@section('title', 'Assign Staff')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Participants</h1>
        <a href="{{ route('training.show', $training->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Details
        </a>
    </div>

    @if(session('error'))
        <div id="error-alert" class="alert alert-danger shadow-sm" role="alert" style="display: none;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Training: {{ $training->title }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p style="color: #000;"><strong>Venue:</strong> {{ $training->venue }} | <strong>Time:</strong> {{ $training->start_time }}</p>
                </div>
                <div class="col-md-6 text-md-right">
                    @php
                        $currentCount = $training->participants->count();
                        $capacity = $training->capacity;
                    @endphp
                    <p style="color: #000;"><strong>Capacity:</strong> 
                        <span style="font-size: 1.1rem; color: #000 !important; font-weight: 700; margin-left: 5px;">
                            <i class="fas fa-user text-info"></i> {{ $currentCount }} / {{ $capacity ?? '∞' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4 border-left-success">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Assign Staff</h6>
        </div>
        <div class="card-body">
            
            <form action="{{ route('training.assign', $training->id) }}" method="POST" class="form-inline mb-4">
                @csrf
                <label class="mr-2 font-weight-bold" style="color: #000;">Assign Staff:</label>
           
                <select name="user_id" class="form-control mr-2" style="color: #000 !important; min-width: 300px;" required>
                    <option value="" style="color: #000;">-- Select Staff --</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->user_id }}" style="color: #000;">
                            {{ $staff->user_name }} 
                          
                            @if($staff->staffRecord) ({{ $staff->staffRecord->email }}) @endif
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-success">Assign</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th style="color: #000;">Name & Email</th>
                            <th style="color: #000;">Current Status</th>
                            <th width="15%" class="text-center" style="color: #000;">Action</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($training->participants as $participant)
                            <tr>
                                <td class="align-middle" style="color: #000 !important; font-weight: bold;">
                                    {{ $participant->user_name }}<br>
                                
                                    @if($participant->staffRecord)
                                        <small class="text-muted">{{ $participant->staffRecord->email }}</small>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($participant->pivot->status == 'Missed')
                                        <span style="color: #e74a3b !important; font-weight: 800; font-size: 1rem;">Absent</span>
                                    @else
                                        <span style="color: #1cc88a !important; font-weight: 800; font-size: 1rem;">{{ $participant->pivot->status }}</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <form action="{{ route('training.detach', ['id' => $training->id, 'userId' => $participant->user_id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE') 
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('Are you sure you want to remove this staff?')">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3" style="color: #000 !important;">No staff assigned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        var alert = $('#error-alert');
        if (alert.length > 0) {
            alert.slideDown(500);
            setTimeout(function() {
                alert.slideUp(500, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });
</script>

@endsection