@extends('layouts.admin')

@section('title', 'Payroll Management')

@section('content')
<div class="container-fluid">
    
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payroll Batches</h1>
        
        {{-- Trigger Modal --}}
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
            <i class="bi bi-plus-circle"></i> Generate New Payroll
        </button>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- BATCH LIST TABLE --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Monthly Records</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch ID</th>
                            <th>Month / Year</th>
                            <th>Total Staff</th>
                            <th>Total Payout</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>#{{ $batch->id }}</td>
                            <td class="fw-bold text-primary">{{ $batch->month_year }}</td>
                            <td>{{ $batch->total_staff }}</td>
                            <td>RM {{ number_format($batch->total_amount, 2) }}</td>
                            <td>
                                @if($batch->status == 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($batch->status == 'Draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $batch->status }}</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($batch->updated_at)->diffForHumans() }}</td>
                            <td>
                                {{-- Link to the Batch View Dashboard --}}
                                <a href="{{ route('admin.payroll.batch_view', $batch->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Manage
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x fs-3 d-block mb-2"></i>
                                No payroll batches generated yet. Click "Generate New Payroll" to start.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- GENERATE MODAL --}}
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.payroll.generateBatch') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Monthly Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> This will calculate salaries for all Active staff. You can review and edit before finalizing.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Month</label>
                        <select name="month" class="form-select" required>
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                                <option value="{{ $m }}" {{ date('F') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Year</label>
                        <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Generation</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection