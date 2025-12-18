@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">Intelligent Claim Submission</h5>
                    <div id="ocr_status" class="small fw-bold"></div>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.claims.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-5 border-end">
                                <div class="mb-4 text-center p-4 border border-dashed rounded bg-light">
                                    <label class="form-label d-block fw-bold">Step 1: Upload Receipt</label>
                                    <i class="bi bi-cloud-upload text-primary display-4"></i>
                                    <input type="file" name="receipt" id="receipt_input" class="form-control mt-3" accept="image/*,application/pdf" required>
                                    <small class="text-muted">PDF, JPG, or PNG (Max 2MB)</small>
                                </div>
                                
                                <div id="ocr_flag_notice" class="alert alert-warning d-none small shadow-sm">
                                    <i class="bi bi-shield-check"></i> <b>AI Verified:</b> Data has been extracted from your receipt. Please verify the amount below.
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Claim Category</label>
                                    <select name="claim_type" class="form-select" required>
                                        <option value="Travel">Travel/Mileage</option>
                                        <option value="Medical">Medical</option>
                                        <option value="Meal">Meal Allowance</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Amount (RM)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">RM</span>
                                        <input type="number" name="amount" id="amount_display" step="0.01" class="form-control form-control-lg" placeholder="0.00" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Justification</label>
                                    <textarea name="description" class="form-control" rows="3" required placeholder="Describe the purpose of this claim..."></textarea>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" class="btn btn-primary btn-lg">Submit for Approval</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Logic to handle OCR Visual Feedback
document.getElementById('receipt_input').addEventListener('change', function() {
    const statusEl = document.getElementById('ocr_status');
    const amountField = document.getElementById('amount_display');
    const flagNotice = document.getElementById('ocr_flag_notice');
    
    statusEl.innerHTML = `<div class="spinner-border spinner-border-sm text-primary"></div> AI is reading your receipt...`;
    
    // Simulate OCR processing time
    setTimeout(() => {
        statusEl.innerHTML = `<span class="text-success"><i class="bi bi-check-all"></i> Data Extracted</span>`;
        
        // AUTO-MAPPING: Simulate data found from OCR
        amountField.value = "150.00"; 
        
        amountField.classList.add('is-valid');
        flagNotice.classList.remove('d-none');
    }, 2000);
});
</script>
@endsection