@extends('layouts.admin')

@section('title', 'Payroll Management')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payroll Batches</h1>

        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#generateBatchModal">
            <i class="bi bi-plus-circle me-1"></i> Generate New Payroll
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Monthly Records</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-secondary">
                            <th>Batch ID</th>
                            <th>Month / Year</th>
                            <th>Total Staff</th>
                            <th>Total Payout</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr class="{{ $batch->status == 'Draft' && $batch->remark ? 'table-danger' : '' }}">
                            <td>
                                #{{ $batch->id }}
                                @if($batch->status == 'Draft' && $batch->remark)
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Rejected by Finance"></i>
                                @endif
                            </td>
                            <td class="fw-bold text-primary">{{ $batch->month_year }}</td>
                            <td>{{ $batch->total_staff }}</td>
                            <td class="fw-bold">RM {{ number_format($batch->total_amount, 2) }}</td>
                            <td>
                                @if($batch->status == 'Paid')
                                    <span class="badge bg-success rounded-pill">Paid</span>
                                @elseif($batch->status == 'Draft' && $batch->remark)
                                    <span class="badge bg-danger rounded-pill">
                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                    </span>
                                @elseif($batch->status == 'Draft')
                                    <span class="badge bg-warning text-dark rounded-pill">Draft</span>
                                @elseif($batch->status == 'L1_Approved')
                                    <span class="badge bg-info rounded-pill">Pending Finance Review</span>
                                @else
                                    <span class="badge bg-info text-dark rounded-pill">{{ $batch->status }}</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($batch->updated_at)->diffForHumans() }}
                                @if($batch->status == 'Draft' && $batch->remark)
                                    <br><small class="text-danger fw-bold">Needs attention</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.payroll.batch_view', $batch->id) }}" 
                                   class="btn btn-sm {{ $batch->status == 'Draft' && $batch->remark ? 'btn-danger' : 'btn-outline-primary' }} px-3">
                                    <i class="bi bi-{{ $batch->status == 'Draft' && $batch->remark ? 'exclamation-circle' : 'gear' }}-fill me-1"></i> 
                                    {{ $batch->status == 'Draft' && $batch->remark ? 'View Rejection' : 'Manage' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-folder-x fs-1 d-block mb-3 opacity-25"></i>
                                No payroll batches generated yet.<br>
                                <small>Click "Generate New Payroll" to create your first monthly batch.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="generateBatchModal" tabindex="-1" aria-labelledby="generateBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.payroll.generateBatch') }}" method="POST">
                @csrf
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold" id="generateBatchModalLabel">
                        <i class="bi bi-cpu text-primary me-2"></i>Generate Monthly Payroll
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="batch_month" class="form-label fw-bold small text-muted text-uppercase">Select Month</label>
                        <select name="month" id="batch_month" class="form-select form-select-lg" required title="Please select a month">
                             @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $m)
                                <option value="{{ $index + 1 }}" {{ date('n') == ($index + 1) ? 'selected' : '' }}>
                                    {{ $m }}
                                </option>
                             @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="batch_year" class="form-label fw-bold small text-muted text-uppercase">Select Year</label>
                        <input type="number" name="year" id="batch_year" class="form-control form-control-lg" value="{{ date('Y') }}" min="2020" max="{{ date('Y') + 1 }}" required title="Please enter a year">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-play-fill me-1"></i> Run Generation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection