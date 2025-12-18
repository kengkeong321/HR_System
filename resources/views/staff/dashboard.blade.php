@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Staff Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Claims</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                RM {{ number_format($claimStats['total_pending_rm'] ?? 0, 2) }}
                            </div>
                            <div class="text-muted small mt-1">{{ $claimStats['pending_count'] ?? 0 }} requests awaiting HR review</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Approved (Next Payroll)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                RM {{ number_format($claimStats['total_approved_rm'] ?? 0, 2) }}
                            </div>
                            <div class="text-muted small mt-1">Automatically pushed to your draft payslip</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle-fill fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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