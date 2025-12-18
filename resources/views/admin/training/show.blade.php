@extends('layouts.admin')
@section('title', 'Training Details')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <a href="{{ route('training.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ $training->title }}</h6>
            @if(session('role') === 'Admin' || (isset($isAdmin) && $isAdmin))
                <a href="{{ route('training.edit', $training->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit Program
                </a>
            @endif
        </div>
        <div class="card-body">
            <div class="row text-dark">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Venue:</strong> {{ $training->venue }}</p>
                    <p class="mb-2"><strong>Time:</strong> {{ $training->start_time }} to {{ $training->end_time }}</p>
                </div>
                <div class="col-md-6 text-md-right">
                    @php
                        $currentCount = $training->participants->count();
                        $capacity = $training->capacity;
                        $isFull = $capacity > 0 && $currentCount >= $capacity;
                    @endphp
                    <p class="mb-2">
                        <strong>Capacity:</strong> 
                        <span style="font-size: 1.1rem; color: #000; font-weight: 700; margin-left: 10px;">
                            <i class="fas fa-user {{ $isFull ? 'text-danger' : 'text-info' }}"></i> 
                            {{ $currentCount }} / {{ $capacity ?? '∞' }}
                        </span>
                    </p>
                </div>
            </div>
            <hr>
            <h5 class="font-weight-bold text-dark">Description</h5>
            <p class="text-dark">{{ $training->description ?? 'No description provided.' }}</p>
        </div>
    </div>

    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">Participants List</h6>
            <a href="{{ route('training.assignPage', $training->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus"></i> Add/Remove Staff
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light text-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email / Dept</th>
                            <th style="width: 180px;" class="text-center">Current Status</th>
                            <th style="width: 200px;">Update Status</th>
                            <th style="width: 80px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @forelse($training->participants as $participant)
                            @php 
                                $status = $participant->pivot->status;
                            @endphp
                            <tr>
                                <td class="align-middle font-weight-bold text-dark">{{ $participant->user_name ?? $participant->name }}</td>
                                <td class="align-middle small">
                                    {{ $participant->email }}<br>
                                    <span class="text-muted">{{ $participant->department ?? 'N/A' }}</span>
                                </td>
                                
                                <td class="align-middle text-center">
                                    @if($status == 'Missed')
                                        <span style="color: #e74a3b; font-weight: 800; font-size: 1rem;">Absent</span>
                                    @else
                                        <span style="color: #1cc88a; font-weight: 800; font-size: 1rem;">{{ $status }}</span>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    <form action="{{ route('training.updateStatus', [$training->id, $participant->user_id]) }}" method="POST" id="status-form-{{ $participant->user_id }}">
                                        @csrf
                                        <select name="status" class="form-control form-control-sm" style="color: #000; font-weight: 600;">
                                            <option value="Assigned" {{ $status == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                            <option value="Attended" {{ $status == 'Attended' ? 'selected' : '' }}>Attended</option>
                                            <option value="Missed" {{ $status == 'Missed' ? 'selected' : '' }}>Missed (Absent)</option>
                                            <option value="Completed" {{ $status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="align-middle text-center">
                                    <button type="submit" form="status-form-{{ $participant->user_id }}" class="btn btn-success btn-sm shadow-sm">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No staff assigned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

   
    <div class="card shadow mb-4">
        <div class="card-header py-3 text-dark">
            <h6 class="m-0 font-weight-bold">Feedbacks & Reviews</h6>
        </div>
        <div class="card-body">
            @forelse($training->feedbacks as $feedback)
                <div class="media mb-3 border-bottom pb-3 text-dark">
                    <div class="media-body">
                        <h6 class="mt-0 font-weight-bold">
                            {{ $feedback->user->user_name ?? 'Unknown User' }} 
                            <small class="text-warning ml-2">{{ str_repeat('★', $feedback->rating) }}</small>
                            <small class="text-muted ml-2">({{ $feedback->created_at->format('Y-m-d') }})</small>
                        </h6>
                        <p class="mb-0">{{ $feedback->comments }}</p>
                    </div>
                </div>
            @empty
                <p class="text-muted">No reviews yet.</p>
            @endforelse

            
            @if(session('role') !== 'Admin' && !(isset($isAdmin) && $isAdmin))
                <hr>
                <h5 class="font-weight-bold mt-4 text-dark">Leave Feedback</h5>
                <form action="{{ route('training.feedback', $training->id) }}" method="POST">
                    @csrf
                    <div class="form-group text-dark">
                        <label>Rating</label>
                        <select name="rating" class="form-control" style="width: 200px;">
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Terrible</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea name="comments" class="form-control" rows="3" placeholder="Share experience..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Submit Feedback</button>
                </form>
            @endif
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection