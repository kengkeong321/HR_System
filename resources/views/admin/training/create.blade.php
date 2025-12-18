@extends('layouts.admin')
@section('title', 'Training List')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Training Program</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Training Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('training.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Java Workshop" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label>Venue <span class="text-danger">*</span></label>
                                <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror" placeholder="e.g. Meeting Room A" required>
                                @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Max Participants <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" placeholder="e.g. 20" min="1" required>
                                @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Start Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_time" class="form-control @error('start_time') is-invalid @enderror" required>
                                @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>End Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_time" class="form-control @error('end_time') is-invalid @enderror" required>
                                @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="5"></textarea>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-success btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-check"></i>
                            </span>
                            <span class="text">Create Training</span>
                        </button>
                        
                        <a href="{{ route('training.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                            <span class="text">Cancel</span>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection