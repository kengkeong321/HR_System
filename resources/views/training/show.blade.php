@extends('layouts.admin')
@section('title', 'Training Details')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <a href="{{ route('training.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ $training->title }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Venue:</strong> {{ $training->venue }}</p>
                    <p><strong>Time:</strong> {{ $training->start_time }} to {{ $training->end_time }}</p>
                </div>
            </div>
            <hr>
            <h5 class="font-weight-bold">Description</h5>
            <p>{{ $training->description ?? 'No description provided.' }}</p>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Feedbacks & Reviews</h6>
        </div>
        <div class="card-body">
            
            @forelse($training->feedbacks as $feedback)
                <div class="media mb-3 border-bottom pb-3">
                    <div class="media-body">
                        <h5 class="mt-0">{{ $feedback->user->user_name }} 
                            <small class="text-warning">{{ str_repeat('★', $feedback->rating) }}</small>
                            <small class="text-muted">({{ $feedback->created_at->format('Y-m-d') }})</small>
                        </h5>
                        {{ $feedback->comments }}
                    </div>
                </div>
            @empty
                <p class="text-muted">No reviews yet.</p>
            @endforelse

            @if(session('role') !== 'Admin')
                <hr>
                <h5 class="font-weight-bold mt-4">Leave Feedback</h5>
                <form action="{{ route('training.feedback', $training->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
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
@endsection