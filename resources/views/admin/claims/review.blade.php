<div class="row">
    <div class="col-md-6 border-right">
        <h6 class="fw-bold">Original Receipt</h6>
        <div class="bg-light p-2 text-center" style="height: 500px; overflow-y: auto;">
            @if(Str::endsWith($claim->receipt_path, '.pdf'))
            <embed src="{{ asset('storage/' . $claim->receipt_path) }}" width="100%" height="100%" type="application/pdf">
            @else
            <img src="{{ asset('storage/' . $claim->receipt_path) }}" class="img-fluid rounded shadow-sm">
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <h6 class="fw-bold">Claim Verification</h6>
        <div class="p-3 bg-white shadow-sm border rounded">
            <div class="mb-3">
                <label class="small text-muted">Merchant (Extracted)</label>
                <input type="text" class="form-control bg-light" value="{{ $claim->ocr_merchant }}" readonly>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small text-muted">OCR Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="text" class="form-control bg-light" value="{{ number_format($claim->ocr_amount, 2) }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted">Staff Claimed Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="text" class="form-control {{ $claim->is_flagged ? 'is-invalid' : 'is-valid' }}"
                            value="{{ number_format($claim->amount, 2) }}" readonly>
                    </div>
                    @if($claim->is_flagged)
                    <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Amount Mismatch!</small>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <label class="small text-muted">Proposed Allowance Remark</label>
                <textarea class="form-control" id="final_remark" rows="2">{{ $claim->claim_type }}: {{ $claim->ocr_merchant }} (Ref #{{ $claim->id }})</textarea>
            </div>

            <div class="d-flex gap-2">
                <button onclick="approveClaim('{{ $claim->id }}')" class="btn btn-success flex-grow-1">
                    Approve & Push to Payroll
                </button>
                <button onclick="showRejectModal('{{ $claim->id }}')" class="btn btn-outline-danger">
                    Reject
                </button>
            </div>
            <div class="modal fade" id="rejectionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('admin.claims.reject') }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Reject Claim</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="claim_id" id="reject_claim_id">
                                <div class="mb-3">
                                    <label class="form-label">Reason for Rejection</label>
                                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="e.g. Receipt is blurry or invalid expense category."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function approveClaim(claimId) {
        if (!confirm('Confirm approval? This will instantly adjust the monthly payroll draft.')) return;

        // Use Fetch API to talk to the Bounded Context of Payroll
        fetch(`/admin/claims/${claimId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Strategic UI Update: Remove the row or refresh to show snapshot
                    alert('Success: Payroll draft updated with OCR verified data.');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Audit Error:', error);
                alert('Critical: Failed to sync claim with payroll engine.');
            });
    }

    function showRejectModal(claimId) {
        // Logic to show a Bootstrap modal to input the rejection_reason
        $('#rejectionModal').modal('show');
        document.getElementById('reject_claim_id').value = claimId;
    }
</script>