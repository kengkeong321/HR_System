@extends('layouts.admin')

@section('title', 'Create Staff')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Create New Staff Member</h4>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
    </div>
    @if ($errors->any())
    <div class="alert alert-danger shadow-sm border-start border-danger border-4">
        <h6 class="alert-heading fw-bold">Please fix the following errors:</h6>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form method="POST" action="{{ route('admin.staff.store') }}" class="card shadow-sm">
        @csrf
        <div class="card-body">
            <div class="row">
                <h5 class="text-primary mb-3">Personal Information</h5>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <input id="full_name" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Position</label>
                    <input name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position') }}" disabled>
                    @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Department</label>
                    <select name="depart_id" class="form-select @error('depart_id') is-invalid @enderror" disabled>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->depart_id }}" {{ old('depart_id') == $dept->depart_id ? 'selected' : '' }}>
                                {{ $dept->depart_name ?? $dept->depart_id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">

                <h5 class="text-primary mb-3">Employment & Salary</h5>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Employment Type</label>
                        <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                            <option value="Full-Time" {{ old('employment_type') == 'Full-Time' ? 'selected' : '' }}>Full-Time</option>
                            <option value="Part-Time" {{ old('employment_type') == 'Part-Time' ? 'selected' : '' }}>Part-Time</option>
                            <option value="Contract" {{ old('employment_type') == 'Contract' ? 'selected' : '' }}>Contract</option>
                            <option value="Intern" {{ old('employment_type') == 'Intern' ? 'selected' : '' }}>Intern</option>
                        </select>
                </div>

                <div class="col-md-4 mb-3" id="basic_salary_group">
                    <label class="form-label">Basic Salary</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary', '0.00') }}">
                    </div>
                </div>

                <div class="col-md-4 mb-3 d-none" id="hourly_rate_group">
                    <label class="form-label">Hourly Rate</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" step="0.01" name="hourly_rate" class="form-control" value="{{ old('hourly_rate', '0.00') }}">
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Join Date</label>
                    <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date', date('Y-m-d')) }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Contract Expiry (Optional)</label>
                    <input type="date" name="contract_expiry_date" class="form-control" value="{{ old('contract_expiry_date') }}">
                </div>

                <hr class="my-4">

                <h5 class="text-primary mb-3">Banking Details</h5>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Name</label>
                    <input name="bank_name" class="form-control" value="{{ old('bank_name') }}" placeholder="e.g. Maybank">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Account No.</label>
                    <input name="bank_account_no" class="form-control" value="{{ old('bank_account_no') }}">
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary px-5">Save Staff Record</button>
        </div>
    </form>
</div>

<script>

const nameInput = document.getElementById('full_name');
    const emailInput = document.getElementById('email');
    const feedback = document.getElementById('email-feedback');

    nameInput.addEventListener('input', async function() {
        let nameValue = this.value.trim();
        if (nameValue.length < 3) return;

        let parts = nameValue.split(/\s+/);
        let firstName = parts[0].toLowerCase();
        let initials = parts.slice(1).map(p => p[0].toLowerCase()).join('');
        let baseHandle = firstName + initials;
        let domain = "@tarc.edu.my";

        let currentHandle = baseHandle;
        let counter = 1;
        let isUnique = false;

        while (!isUnique) {
            let emailToCheck = (counter === 1 ? currentHandle : currentHandle + counter) + domain;
            
            let response = await fetch(`{{ route('admin.staff.checkEmail') }}?email=${emailToCheck}`);
            let data = await response.json();

            if (!data.exists) {
                emailInput.value = emailToCheck;
                isUnique = true;
                
                if (counter > 1) {
                    emailInput.classList.add('is-warning');
                    feedback.style.display = 'block';
                    feedback.innerText = `Handle '${currentHandle}' was taken. Suggested: ${emailToCheck}`;
                } else {
                    emailInput.classList.remove('is-warning');
                    feedback.style.display = 'none';
                }
            } else {
                counter++; 
            }
        }
    });
    
    document.getElementById('employment_type').addEventListener('change', function() {
        const salaryGroup = document.getElementById('basic_salary_group');
        const hourlyGroup = document.getElementById('hourly_rate_group');
        
        if (this.value === 'Part-Time') {
            salaryGroup.classList.add('d-none');
            hourlyGroup.classList.remove('d-none');
        } else {
            salaryGroup.classList.remove('d-none');
            hourlyGroup.classList.add('d-none');
        }
    });
</script>
@endsection