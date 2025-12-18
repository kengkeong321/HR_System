@extends('layouts.admin')
@section('title', 'Assign Staff')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Participants</h1>
        <a href="{{ route('training.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Training: {{ $training->title }}</h6>
        </div>
        <div class="card-body">
            <p><strong>Venue:</strong> {{ $training->venue }} | <strong>Time:</strong> {{ $training->start_time }}</p>
        </div>
    </div>

    <div class="card shadow mb-4 border-left-success">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Assign Staff & Track Status</h6>
        </div>
        <div class="card-body">
            
            <form action="{{ route('training.assign', $training->id) }}" method="POST" class="form-inline mb-4">
                @csrf
                <label class="mr-2 font-weight-bold">Assign Staff:</label>
                <select name="user_id" class="form-control mr-2" required>
                    <option value="">-- Select Staff --</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->user_id }}">{{ $staff->user_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-success">Assign</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
                            <th width="10%">Action</th> </tr>
                    </thead>
                    <tbody>
                        @forelse($training->participants as $participant)
                        <tr>
                            <td class="align-middle">{{ $participant->user_name }}</td>
                            <td class="align-middle">
                                <span class="badge badge-secondary">{{ $participant->pivot->status }}</span>
                            </td>
                            <td class="align-middle">
                                <form action="{{ route('training.status', ['id'=>$training->id, 'userId'=>$participant->user_id]) }}" method="POST" class="form-inline">
                                    @csrf
                                    <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                        <option value="Assigned" {{ $participant->pivot->status == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="Attended" {{ $participant->pivot->status == 'Attended' ? 'selected' : '' }}>Attended</option>
                                        <option value="Completed" {{ $participant->pivot->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Missed" {{ $participant->pivot->status == 'Missed' ? 'selected' : '' }}>Missed</option>
                                    </select>
                                </form>
                            </td>
                            <td class="align-middle text-center">
                                <form action="{{ route('training.detach', ['id' => $training->id, 'userId' => $participant->user_id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE') <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this staff from the training?')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No staff assigned yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection