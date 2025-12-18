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
                    <input type="text" name="title" class="form-control" value="{{ old('title', $training->title) }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Venue / Location</label>
                        <input type="text" name="venue" class="form-control" value="{{ old('venue', $training->venue) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Max Participants (Capacity)</label>
                        <input type="number" name="capacity" 
                               class="form-control @error('capacity') is-invalid @enderror" 
                               value="{{ old('capacity', $training->capacity) }}" 
                               min="{{ $training->participants->count() }}" 
                               required>
                        <small class="form-text text-muted">Currently assigned: {{ $training->participants->count() }} staff.</small>
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" 
                               value="{{ old('start_time', date('Y-m-d\TH:i', strtotime($training->start_time))) }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" 
                               value="{{ old('end_time', date('Y-m-d\TH:i', strtotime($training->end_time))) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $training->description) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Training</button>
               <a href="{{ route('training.index') }}" class="btn btn-secondary">Cancel</a>
            </form>

        </div>
    </div>

</div>
@endsection