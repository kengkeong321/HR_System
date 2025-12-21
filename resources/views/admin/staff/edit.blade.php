{{-- Loong Wei Lim --}}
@extends('layouts.admin')

@section('title', 'Edit Staff Member')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Edit Staff Member: {{ $staff->full_name }}</h4>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
    </div>

    <form method="POST" action="{{ route('admin.staff.update', $staff->staff_id) }}" class="card shadow-sm">
        @csrf
        @method('PUT')
        
        <div class="card-body">
            <div class="row">
                <h5 class="text-primary mb-3">Identity (Read-Only)</h5>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control bg-light" value="{{ $staff->full_name }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control bg-light" value="{{ $staff->email }}" readonly>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $staff->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr class="my-4">

                <h5 class="text-primary mb-3">Employment & Salary</h5>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Employment Type</label>
                    <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                        @foreach(['Full-Time', 'Part-Time', 'Contract', 'Intern'] as $type)
                            <option value="{{ $type }}" {{ old('employment_type', $staff->employment_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3" id="basic_salary_group">
                    <label class="form-label">Basic Salary (RM)</label>
                    <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary', $staff->basic_salary) }}">
                </div>

                <div class="col-md-4 mb-3" id="hourly_rate_group">
                    <label class="form-label">Hourly Rate (RM)</label>
                    <input type="number" step="0.01" name="hourly_rate" class="form-control" value="{{ old('hourly_rate', $staff->hourly_rate) }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Position</label>
                    <select name="position" class="form-select @error('position') is-invalid @enderror">
                        <option value="">-- Select Position --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->name }}" 
                                {{ old('position', $staff->position ?? '') == $pos->name ? 'selected' : '' }}>
                                {{ $pos->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Contract Expiry (Optional)</label>
                    <input type="date" name="contract_expiry_date" class="form-control" value="{{ old('contract_expiry_date', $staff->contract_expiry_date) }}">
                </div>

                <hr class="my-4">

                <h5 class="text-primary mb-3">Account Status & Banking</h5>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Login Status</label>
                    <select name="status" class="form-select">
                        <option value="Active" {{ ($staff->user?->status == 'Active') ? 'selected' : '' }}>
                            Active (Allow Login)
                        </option>
                        <option value="Inactive" {{ ($staff->user?->status == 'Inactive' || !$staff->user) ? 'selected' : '' }}>
                            Inactive (Block Login)
                        </option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Name</label>
                    <select name="bank_name" class="form-select @error('bank_name') is-invalid @enderror">
                        <option value="">-- Select Bank --</option>
                        @php
                            $malaysiaBanks = [
                                'Maybank' => 'Malayan Banking Berhad (Maybank)',
                                'CIMB' => 'CIMB Bank Berhad',
                                'Public Bank' => 'Public Bank Berhad',
                                'RHB' => 'RHB Bank Berhad',
                                'Hong Leong' => 'Hong Leong Bank Berhad',
                                'AmBank' => 'AmBank Group',
                                'UOB' => 'United Overseas Bank (Malaysia)',
                                'OCBC' => 'OCBC Bank (Malaysia)',
                                'HSBC' => 'HSBC Bank Malaysia',
                                'Standard Chartered' => 'Standard Chartered Bank Malaysia',
                                'Alliance Bank' => 'Alliance Bank Malaysia Berhad',
                                'Affim Bank' => 'Affin Bank Berhad',
                                'Bank Islam' => 'Bank Islam Malaysia Berhad',
                                'Bank Muamalat' => 'Bank Muamalat Malaysia Berhad',
                                'Agrobank' => 'Pertanian Bank Malaysia Berhad (Agrobank)',
                                'BSN' => 'Bank Simpanan Nasional (BSN)'
                            ];
                        @endphp
                        @foreach($malaysiaBanks as $code => $name)
                        <option value="{{ $code }}" {{ old('bank_name', $staff->bank_name) == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Bank Account No.</label>
                    <input name="bank_account_no" class="form-control" value="{{ old('bank_account_no', $staff->bank_account_no) }}">
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success px-5">Update Staff Record</button>
        </div>
    </form>
</div>

<script>
    function toggleRates() {
        const type = document.getElementById('employment_type').value;
        document.getElementById('basic_salary_group').style.display = (type === 'Part-Time') ? 'none' : 'block';
        document.getElementById('hourly_rate_group').style.display = (type === 'Part-Time') ? 'block' : 'none';
    }
    document.getElementById('employment_type').addEventListener('change', toggleRates);
    window.onload = toggleRates;
</script>
@endsection