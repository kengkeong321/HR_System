@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-5 text-center">
                    <h1 class="display-4 text-primary">Hello, {{ session('user_name') }}!</h1>
                    <p class="lead text-muted">Welcome to your personal HR portal.</p>
                    <hr class="my-4">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('staff.attendance.create') }}" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-clock me-2"></i>Mark Attendance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection