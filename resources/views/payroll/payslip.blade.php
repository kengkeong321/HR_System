<div class="card">
    <div class="card-header">
        Payroll Period: {{ $slip->month }} {{ $slip->year }}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Earnings</h5>
                <p>Basic Salary: RM {{ number_format($slip->basic_salary, 2) }}</p>
                
                {{-- DISPLAY THE AUTO-REMARK --}}
                @if($slip->allowance_remark)
                    <div class="alert alert-info py-1 px-2 small">
                        <i class="bi bi-info-circle"></i> {{ $slip->allowance_remark }}
                    </div>
                @endif
            </div>
            
            <div class="col-md-6 text-end">
                <small class="text-muted">Disbursed On</small>
                {{-- Shows "Jan 5, 2025" even though title is "December" --}}
                <p class="fw-bold">{{ \Carbon\Carbon::parse($slip->payment_date)->toFormattedDateString() }}</p>
            </div>
        </div>
    </div>
</div>