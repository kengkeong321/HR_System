@extends('layouts.admin')

@section('title', 'Edit Training Program')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Training Program</h1>
         <a href="{{ route('training.show', $training->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Details
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Update Details for: {{ $training->title }}</h6>
                </div>
                <div class="card-body">
                    
                    <form action="{{ route('training.update', $training->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Training Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $training->title) }}" required>
                        </div>

                        <div class="form-row mb-4">
                            <div class="form-group col-md-8">
                                <label class="font-weight-bold">Venue / Location</label>
                                <input type="text" name="venue" class="form-control" value="{{ old('venue', $training->venue) }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Capacity</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                                    </div>
                                    <input type="number" name="capacity" 
                                           class="form-control @error('capacity') is-invalid @enderror" 
                                           value="{{ old('capacity', $training->capacity) }}" 
                                           min="{{ $training->participants->count() }}" 
                                           required>
                                </div>
                                <small class="text-muted">Min required: {{ $training->participants->count() }} (current staff)</small>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row mb-4">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Start Time</label>
                                <input type="datetime-local" name="start_time" class="form-control" 
                                       value="{{ old('start_time', date('Y-m-d\TH:i', strtotime($training->start_time))) }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">End Time</label>
                                <input type="datetime-local" name="end_time" class="form-control" 
                                       value="{{ old('end_time', date('Y-m-d\TH:i', strtotime($training->end_time))) }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Description</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Enter program details...">{{ old('description', $training->description) }}</textarea>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('training.index') }}" class="btn btn-light mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-1"></i> Update Training
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection