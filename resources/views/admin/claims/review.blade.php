{{-- Dephnie Ong Yan Yee --}}

<div class="row g-0 h-100">
    {{-- View Receipt Section --}}
    @php $secureUrl = route('claims.view_receipt', $claim->id); @endphp

    <div class="col-lg-7 bg-dark d-flex align-items-center justify-content-center position-relative" style="min-height: 500px;">
        @if($claim->receipt_path)
            @if(Str::endsWith($claim->receipt_path, '.pdf'))
                <iframe src="{{ $secureUrl }}" class="w-100 h-100 border-0" style="min-height: 500px;"></iframe>
            @else
                <img src="{{ $secureUrl }}" class="img-fluid" style="max-height: 100%; max-width: 100%;">
            @endif
        @else
            <div class="text-white opacity-50 text-center">
                <i class="bi bi-file-x display-1"></i>
                <p>No Receipt File Found</p>
            </div>
        @endif
    </div>

    {{-- Verification Form Section --}}
    <div class="col-lg-5 bg-white border-start p-0">
        <div class="p-4 h-100 d-flex flex-column">
            
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-1">Verify Claim #{{ $claim->id }}</h5>
                <span class="badge bg-light text-dark border">{{ $claim->claim_type }}</span>
                <small class="text-muted ms-2">{{ $claim->created_at->format('d M Y, h:i A') }}</small>
            </div>

            {{-- Staff Submission Data --}}
            <div class="p-3 bg-light rounded mb-3 border">
                <label class="small text-uppercase text-muted fw-bold">Staff Submission</label>
                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        <small class="d-block text-muted">Merchant/Desc</small>
                        <strong>{{ $claim->description }}</strong>
                    </div>
                    <div class="text-end">
                        <small class="d-block text-muted">Claimed</small>
                        <span class="h5 fw-bold text-dark mb-0">RM {{ number_format($claim->amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Admin Edit  --}}
            <div class="mb-3">
                <label class="form-label fw-bold text-success">
                    <i class="bi bi-check-circle-fill me-1"></i>Verified Amount (RM)
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-success fw-bold">RM</span>
                    
                    <input type="number" id="final_approved_amount" class="form-control form-control-lg fw-bold text-success" 
                           step="0.01" value="{{ $claim->amount }}" 
                           {{ Auth::user()->role === 'Finance' ? 'disabled' : '' }}>
                </div>
                
                @if(Auth::user()->role !== 'Finance')
                    <div class="form-text">If the receipt amount differs, update this value before approving.</div>
                @endif
            </div>

            <div class="mb-auto">
                <label class="form-label small text-muted">Approval Remarks (Optional)</label>
 
                <textarea id="approval_remark" class="form-control" rows="2" placeholder="e.g., Approved partial amount..." 
                {{ Auth::user()->role === 'Finance' ? 'disabled' : '' }}></textarea>
            </div>

            <hr>
            
            @if(Auth::user()->role === 'Finance')
                <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                    <i class="bi bi-lock-fill fs-4 me-3"></i>
                    <div>
                        <strong>View Only Mode</strong>
                        <div class="small">Finance users cannot modify claim status.</div>
                    </div>
                </div>
            @else
                <div class="d-grid gap-2">
                    <button type="button" onclick="submitApproval('{{ $claim->id }}')" class="btn btn-success btn-lg">
                        <i class="bi bi-check-lg me-2"></i>Confirm & Approve
                    </button>
                    <button type="button" onclick="openRejectModal('{{ $claim->id }}')" class="btn btn-outline-danger">
                        Reject Claim
                    </button>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Rejection Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.claims.reject', $claim->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Claim #{{ $claim->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="claim_id" id="modal_claim_id" value="{{ $claim->id }}">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason for Rejection</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    function openRejectModal(claimId) {
        var myModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        document.getElementById('modal_claim_id').value = claimId; 
        myModal.show();
    }

    function submitApproval(claimId) {
        const finalAmount = document.getElementById('final_approved_amount').value;
        const remark = document.getElementById('approval_remark').value;

        if(!finalAmount || finalAmount <= 0) {
            alert("Please enter a valid approved amount.");
            return;
        }

        if (!confirm(`Confirm approval of RM ${finalAmount}? This will be synced to payroll.`)) return;

        fetch(`/admin/claims/${claimId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                approved_amount: finalAmount, 
                remark: remark
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Claim successfully approved.');
                window.location.reload(); 
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('System error occurred.');
        });
    }
</script>