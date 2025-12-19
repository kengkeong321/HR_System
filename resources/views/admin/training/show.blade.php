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
                    <p class="mb-2" style="color: #000;"><strong>Venue:</strong> {{ $training->venue }}</p>
                    <p class="mb-2" style="color: #000;"><strong>Time:</strong> {{ $training->start_time }} to {{ $training->end_time }}</p>
                </div>
                <div class="col-md-6 text-md-right">
                    @php
                        $currentCount = $training->participants->count();
                        $capacity = $training->capacity;
                        $isFull = $capacity > 0 && $currentCount >= $capacity;
                    @endphp
                    <p class="mb-2" style="color: #000;">
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
            <p class="text-dark" style="color: #000 !important;">{{ $training->description ?? 'No description provided.' }}</p>
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
                    <thead class="thead-light">
                        <tr style="background-color: #f8f9fc;">
                            <th style="color: #000; font-weight: bold;">Name</th>
                            <th style="color: #000; font-weight: bold;">Email / Dept</th>
                            <th style="width: 180px; color: #000; font-weight: bold;" class="text-center">Current Status</th>
                            <th style="width: 200px; color: #000; font-weight: bold;">Update Status</th>
                            <th style="width: 80px; color: #000; font-weight: bold;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($training->participants as $participant)
                            @php 
                                $status = $participant->pivot->status;
                              
                                $staffData = \DB::table('staff')->where('user_id', $participant->user_id)->first();
                            @endphp
                            <tr>
                             
                                <td class="align-middle font-weight-bold" style="color: #000 !important;">
                                    {{ $staffData->full_name ?? ($participant->user_name ?? 'Unknown') }}
                                </td>
                                
                              
                                <td class="align-middle small" style="color: #000 !important;">
                                    @if($staffData)
                                        <i class="fas fa-envelope mr-1"></i> {{ $staffData->email }}<br>
                                        <i class="fas fa-building mr-1 text-muted"></i> <span class="text-muted">{{ $staffData->depart_id }}</span>
                                    @else
                                        <span class="text-danger">Record Missing</span>
                                    @endif
                                </td>
                                
                                <td class="align-middle text-center">
                                    @if($status == 'Missed')
                                        <span style="color: #e74a3b; font-weight: 800;">Absent</span>
                                    @else
                                        <span style="color: #1cc88a; font-weight: 800;">{{ $status }}</span>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    <form action="{{ route('training.updateStatus', [$training->id, $participant->user_id]) }}" method="POST" id="status-form-{{ $participant->user_id }}">
                                        @csrf
                                        <select name="status" class="form-control form-control-sm" style="color: #000 !important; font-weight: 600; background: #fff !important;">
                                            <option value="Assigned" {{ $status == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                            <option value="Attended" {{ $status == 'Attended' ? 'selected' : '' }}>Attended</option>
                                            <option value="Missed" {{ $status == 'Missed' ? 'selected' : '' }}>Missed (Absent)</option>
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
                                <td colspan="5" class="text-center py-4" style="color: #000 !important;">No staff assigned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                        <h6 class="mt-0 font-weight-bold" style="color: #000;">
                            {{ $feedback->user->user_name ?? 'Unknown User' }} 
                            <small class="text-warning ml-2">{{ str_repeat('★', $feedback->rating) }}</small>
                            <small class="text-muted ml-2">({{ $feedback->created_at->format('Y-m-d') }})</small>
                        </h6>
                        <p class="mb-0" style="color: #000;">{{ $feedback->comments }}</p>
                    </div>
                </div>
            @empty
                <p class="text-muted">No reviews yet.</p>
            @endforelse
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection