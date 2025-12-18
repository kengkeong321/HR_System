@extends('layouts.admin')

@section('title', 'Edit Payroll Record')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-gray-800">Edit Payroll: {{ $payroll->staff->full_name }}</h2>
        <a href="{{ route('admin.payroll.batch_view', $payroll->batch_id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Batch
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Adjust Salary Components</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payroll.update', $payroll->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Basic Salary (RM)</label>
                                <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ $payroll->basic_salary }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Net Salary (RM)</label>
                                <input type="number" step="0.01" name="net_salary" class="form-control" value="{{ $payroll->net_salary }}">
                                <small class="text-muted">Total after all adjustments.</small>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Total Allowances</label>
                                <input type="number" step="0.01" name="total_allowances" class="form-control" value="{{ $payroll->total_allowances }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Deductions</label>
                                <input type="number" step="0.01" name="total_deductions" class="form-control" value="{{ $payroll->total_deductions }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">EPF (Staff Contrib.)</label>
                                <input type="number" step="0.01" name="epf_employee" class="form-control" value="{{ $payroll->epf_employee }}">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-save"></i> Save Adjustments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow border-left-info">
                <div class="card-body">
                    <h5 class="font-weight-bold text-info">Staff Information</h5>
                    <p class="mb-1"><strong>ID:</strong> {{ $payroll->staff->staff_id }}</p>
                    <p class="mb-1"><strong>Bank:</strong> {{ $payroll->staff->bank_name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Account:</strong> {{ $payroll->staff->bank_account_no ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection