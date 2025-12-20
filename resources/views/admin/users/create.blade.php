@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
  <h4>Create User</h4>
  <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    <div class="card p-3 mb-3 shadow-sm">
        <h5 class="text-primary">Account Information</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Username</label>
              <input name="user_name" class="form-control @error('user_name') is-invalid @enderror" value="{{ old('user_name') }}" required>
              @error('user_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Role</label>
              <select name="role" id="roleSelect" class="form-select">
                <option value="Admin" {{ old('role') === 'Admin' ? 'selected' : '' }}>Admin</option>
                <option value="Staff" {{ old('role', 'Staff') === 'Staff' ? 'selected' : '' }}>Staff</option>
                <option value="HR" {{ old('role') === 'HR' ? 'selected' : '' }}>HR</option>
                <option value="Finance" {{ old('role') === 'Finance' ? 'selected' : '' }}>Finance</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
        </div>
    </div>

    <div id="staffFields" class="card p-3 mb-3 shadow-sm" style="border-left: 5px solid #0d6efd;">
        <h5 class="text-primary">Personal & Employment Details</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input id="full_name" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
                @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" readonly>
                <div id="email-feedback" class="form-text text-warning" style="display:none"></div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Employment Type</label>
                <select name="employment_type" id="employment_type" class="form-select">
                    <option value="Full-Time">Full-Time</option>
                    <option value="Contract">Contract</option>
                    <option value="Part-Time">Part-Time</option>
                    <option value="Intern">Intern</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" id="salary_label">Basic Salary (Monthly)</label>
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input name="basic_salary" id="salary_input" type="number" step="0.01" class="form-control" value="{{ old('basic_salary', '0.00') }}">
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary px-5">Create Account</button>
  </form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('full_name');
    const emailInput = document.getElementById('email');
    const feedback = document.getElementById('email-feedback');
    const empTypeSelect = document.getElementById('employment_type');
    const salaryLabel = document.getElementById('salary_label');
    const salaryInput = document.getElementById('salary_input');

    // Toggle Salary vs Hourly Label
    function updateSalaryUI() {
        const type = empTypeSelect.value;
        if (type === 'Part-Time' || type === 'Intern') {
            salaryLabel.innerText = "Hourly Rate (RM)";
        } else {
            salaryLabel.innerText = "Basic Salary (Monthly)";
        }
    }

    // Auto-Generate Email logic
    nameInput.addEventListener('input', async function() {
        let nameValue = this.value.trim();
        if (nameValue.length < 3) return;

        let parts = nameValue.split(/\s+/);
        let baseHandle = parts[0].toLowerCase() + (parts[1] ? parts[1][0].toLowerCase() : '');
        let domain = "@tarc.edu.my";

        let counter = 1;
        let isUnique = false;
        while (!isUnique) {
            let emailToCheck = (counter === 1 ? baseHandle : baseHandle + counter) + domain;
            try {
                let res = await fetch(`{{ route('admin.staff.checkEmail') }}?email=${emailToCheck}`);
                let data = await res.json();
                if (!data.exists) {
                    emailInput.value = emailToCheck;
                    isUnique = true;
                    feedback.style.display = counter > 1 ? 'block' : 'none';
                    if (counter > 1) feedback.innerText = `Suggested: ${emailToCheck}`;
                } else { counter++; }
            } catch (e) { break; }
        }
    });

    empTypeSelect.addEventListener('change', updateSalaryUI);
    updateSalaryUI(); 
});
</script>
@endsection