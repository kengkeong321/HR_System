@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-gear-fill me-2 text-primary"></i>System Settings</h4>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="fw-bold">Standard Work Start Time</label>
                        <p class="text-muted small">Staff checking in after this time will be marked as "Late".</p>
                    </div>
                    <div class="col-md-4">
                        <input type="time" name="work_start_time" class="form-control form-control-lg" value="{{ $startTime }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-save me-2"></i>Update Policy
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection