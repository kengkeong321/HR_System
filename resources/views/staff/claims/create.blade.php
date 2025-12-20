@extends('layouts.staff')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">

            @if(session('error'))
            <div class="alert alert-danger shadow-sm mb-3 border-0 border-start border-danger border-4">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
            @endif

            @if ($errors->has('receipt'))
            <div class="alert alert-warning mb-3">
                {{ $errors->first('receipt') }}
            </div>
            @endif

            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-file-earmark-plus text-primary me-2"></i>Submit New Claim
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('staff.claims.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">

                            <div class="col-md-5 border-end">
                                <label class="form-label fw-bold">1. Attach Receipt</label>

                                <div class="mb-3">
                                    <input type="file" name="receipt" id="receipt_input" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <div class="form-text">Supported: JPG, PNG, PDF (Max 2MB)</div>
                                </div>

                                {{-- Preview Container --}}
                                <div class="text-center p-3 border rounded bg-light d-flex align-items-center justify-content-center" style="min-height: 300px; background-color: #f8f9fa;">

                                    <div id="preview_placeholder" class="text-muted">
                                        <i class="bi bi-image display-4 d-block mb-2 text-secondary opacity-50"></i>
                                        <small>Receipt preview will appear here</small>
                                    </div>

                                    <img id="image_preview" src="#" alt="Receipt Preview" class="img-fluid rounded shadow-sm d-none" style="max-height: 280px;">

                                    <div id="pdf_icon" class="d-none">
                                        <i class="bi bi-file-earmark-pdf text-danger display-1"></i>
                                        <p class="mt-2 fw-bold text-dark" id="pdf_filename">filename.pdf</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Claim Details --}}
                            <div class="col-md-7 ps-md-4">
                                <label class="form-label fw-bold mb-3">2. Claim Details</label>

                                <div class="mb-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Category</label>
                                    <select name="claim_type" class="form-select" required>
                                        <option value="" disabled selected>-- Select Category --</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Total Amount</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light fw-bold text-dark">RM</span>
                                        <input type="number" name="amount" step="0.01" class="form-control fw-bold" placeholder="0.00" required>
                                    </div>
                                    <div class="form-text">Please enter the exact total shown on the receipt.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Justification / Description</label>
                                    <textarea name="description" class="form-control" rows="4" required placeholder="E.g., Taxi fare for client meeting at KL Sentral..."></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary py-2 fw-bold">
                                        <i class="bi bi-send-fill me-2"></i>Submit Application
                                    </button>
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
    document.getElementById('receipt_input').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const previewPlaceholder = document.getElementById('preview_placeholder');
        const imagePreview = document.getElementById('image_preview');
        const pdfIcon = document.getElementById('pdf_icon');
        const pdfFilename = document.getElementById('pdf_filename');

        previewPlaceholder.classList.add('d-none');
        imagePreview.classList.add('d-none');
        pdfIcon.classList.add('d-none');

        if (file) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                pdfFilename.textContent = file.name;
                pdfIcon.classList.remove('d-none');
            } else {
                previewPlaceholder.classList.remove('d-none');
            }
        } else {
            previewPlaceholder.classList.remove('d-none');
        }
    });
</script>
@endsection