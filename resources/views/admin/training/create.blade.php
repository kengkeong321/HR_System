@extends('layouts.admin')
@section('title', 'Create Training')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Training Program</h1>
        <a href="{{ route('training.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Back to List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Fill in Training Details
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('training.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Training Title</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Java Training at Tarumt KL" value="{{ old('title') }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Venue / Location</label>
                            <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror" placeholder="e.g. Block B Room A" value="{{ old('venue') }}" required>
                            @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Capacity</label>
                            <div class="input-group" style="max-width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                </div>
                                <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" placeholder="1" min="1" value="{{ old('capacity') }}" required>
                            </div>
                            @error('capacity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-dark">Start Time</label>
                                <input type="datetime-local" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                                @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold text-dark">End Time</label>
                                <input type="datetime-local" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                                @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="e.g. Please bring your pencil box and A4 papers">{{ old('description') }}</textarea>
                        </div>

                        <hr>
                        
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="{{ route('training.index') }}" class="btn btn-light border mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save mr-1"></i> Create Training
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection