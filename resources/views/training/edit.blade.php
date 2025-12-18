@extends('layouts.admin')

@section('title', 'Edit Training Program')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Edit Training Program</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Update Details</h6>
        </div>
        <div class="card-body">
            
            <form action="{{ route('training.update', $training->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Training Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $training->title }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Venue / Location</label>
                        <input type="text" name="venue" class="form-control" value="{{ $training->venue }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Max Participants (Capacity)</label>
                        <input type="number" name="capacity" class="form-control" 
                               value="{{ $training->max_participants }}" min="1" placeholder="e.g. 50">
                        <small class="form-text text-muted">Limit the number of staff allowed.</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" 
                               value="{{ date('Y-m-d\TH:i', strtotime($training->start_time)) }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" 
                               value="{{ date('Y-m-d\TH:i', strtotime($training->end_time)) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ $training->description }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Training</button>
                <a href="{{ route('training.show', $training->id) }}" class="btn btn-secondary">Cancel</a>
            </form>

        </div>
    </div>

</div>
@endsection