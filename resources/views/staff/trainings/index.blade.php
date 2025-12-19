@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <h2 class="my-4">My Training Programs</h2>

    @forelse($trainings as $item)
        @php 
            $prog = $item->trainingProgram; 
            $isEnded = (isset($prog->status) && $prog->status === 'Ended');
            $status = strtolower(trim($item->status));
        @endphp
        
        <div class="card mb-4 shadow-sm border-0 {{ $isEnded ? 'opacity-75' : '' }}">
            <div class="card-body">
                <h4 class="card-title text-primary font-weight-bold">
                    {{ $prog->title ?? 'N/A' }}
                    @if($isEnded)
                        <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">Ended</span>
                    @endif
                </h4>
                <hr>
                
                <div class="row">
                    <div class="col-md-8">
                        <p><strong>Venue:</strong> {{ $prog->venue ?? 'N/A' }}</p>
                        <p><strong>Time:</strong> {{ $prog->start_time }} to {{ $prog->end_time }}</p>
                        <p><strong>Description:</strong><br>
                        <span class="text-muted">{{ $prog->description ?? 'No description provided.' }}</span></p>
                    </div>

                    <div class="col-md-4 d-flex flex-column align-items-end justify-content-center">
                      
                        <div class="mb-3">
                            @php
                                $badgeClass = match($status) {
                                    'attended' => 'bg-success',
                                    'missed'   => 'bg-danger',
                                    default    => 'bg-secondary'
                                };
                                
                                $statusLabel = match($status) {
                                    'attended' => 'Attended',
                                    'missed'   => 'Missed (Absent)',
                                    default    => 'Pending Confirmation' 
                                };
                            @endphp
                            <span class="badge rounded-pill p-2 {{ $badgeClass }}" style="min-width: 120px; font-size: 0.9rem;">
                                {{ $statusLabel }}
                            </span>
                        </div>

                       
                        @if($status == 'attended')
                        
                            @if(!$isEnded)
                                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $item->id }}">
                                    <i class="fas fa-comment-dots me-1"></i> Give Feedback
                                </button>
                            @else
                                <button class="btn btn-outline-secondary shadow-sm" disabled>
                                    <i class="fas fa-lock me-1"></i> Feedback Closed
                                </button>
                            @endif
                        @elseif($status == 'missed')
                          
                            <span class="text-danger small"><i>No feedback required for absence</i></span>
                        @else
                       
                            <span class="text-muted small"><i>Wait for Admin to confirm attendance</i></span>
                        @endif

                        @if($isEnded)
                            <div class="mt-2 text-muted small text-end"><i>This training session has concluded.</i></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        @if(!$isEnded && $status == 'attended')
            <div class="modal fade" id="feedbackModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('staff.feedback.store') }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Feedback for {{ $prog->title }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="training_program_id" value="{{ $prog->id }}">
                                <div class="mb-3">
                                    <label class="form-label">Overall Rating</label>
                                    <select name="rating" class="form-select" required>
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="3">3 - Good</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="1">1 - Poor</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Comments & Suggestions</label>
                                    <textarea name="comment" class="form-control" rows="4" placeholder="What did you think of the training?" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    @empty
        <div class="alert alert-info border-0 shadow-sm text-center py-4">
            <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i>
            <p class="mb-0">You are not currently assigned to any training programs.</p>
        </div>
    @endforelse
</div>
@endsection