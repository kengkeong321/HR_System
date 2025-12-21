{{-- Woo Keng Keong --}}
@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Assign Staff to Department: {{ $department->depart_name }} ({{ $department->depart_id }})</h3>
        </div>

        <div class="card-body">
            <div class="mb-4">
                <h5>Department Details</h5>
                <p><strong>Faculty:</strong> {{ $department->faculty->faculty_name ?? 'N/A' }}</p>
                <p><strong>Assigned Courses:</strong>
                    @if($department->courses->isEmpty())
                        <span class="text-muted">No courses assigned</span>
                    @else
                        @foreach($department->courses as $c)
                            <span class="badge bg-secondary me-1">{{ $c->course_name }}</span>
                        @endforeach
                    @endif
                </p>
            </div>

            <div class="mb-3">
                <h5>Assign Staff to this Department</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label for="staff_select" class="form-label">Select Staff</label>
                        <select id="staff_select" class="form-select" aria-label="Select staff">
                            <option value="">-- choose staff --</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Staff Details</label>
                        <div id="staff_details" class="border rounded p-2 bg-light">
                            <div id="staff_name">Full name: -</div>
                            <div id="staff_email">Email: -</div>
                            <div id="staff_depart">Current Department: -</div>
                        </div>
                    </div>
                </div>

                <form id="assignStaffForm" action="{{ route('admin.departments.assign.staff.store', $department) }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="staff_id" id="assign_staff_id" value="">
                    <button type="submit" class="btn btn-success" id="assignStaffBtn" disabled>Assign to {{ $department->depart_name }}</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-default ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Load staff list for dropdown
    async function loadStaffList() {
        const sel = document.getElementById('staff_select');
        sel.innerHTML = '<option>Loading...</option>';
        try {
            const res = await fetch('/api/staff', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Failed to fetch staff');
            const json = await res.json();
            const data = Array.isArray(json.data) ? json.data : [];
            sel.innerHTML = '<option value="">-- choose staff --</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.staff_id;
                opt.textContent = s.full_name + ' (' + (s.email ?? '') + ')';
                sel.appendChild(opt);
            });
        } catch (err) {
            sel.innerHTML = '<option value="">Unable to load staff</option>';
            console.error(err);
        }
    }

    // When staff selected, fetch details and populate
    document.addEventListener('DOMContentLoaded', function() {
        loadStaffList();

        const sel = document.getElementById('staff_select');
        const nameEl = document.getElementById('staff_name');
        const emailEl = document.getElementById('staff_email');
        const departEl = document.getElementById('staff_depart');
        const assignInput = document.getElementById('assign_staff_id');
        const assignBtn = document.getElementById('assignStaffBtn');

        sel.addEventListener('change', async function() {
            const id = this.value;
            if (!id) {
                nameEl.textContent = 'Full name: -';
                emailEl.textContent = 'Email: -';
                departEl.textContent = 'Current Department: -';
                assignInput.value = '';
                assignBtn.disabled = true;
                return;
            }

            try {
                const res = await fetch('/api/staff/' + id, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Staff not found');
                const { data } = await res.json();
                nameEl.textContent = 'Full name: ' + (data.full_name ?? '-');
                emailEl.textContent = 'Email: ' + (data.email ?? '-');
                departEl.textContent = 'Current Department: ' + (data.depart_id ?? '-');
                assignInput.value = id;
                assignBtn.disabled = false;
            } catch (err) {
                console.error(err);
                nameEl.textContent = 'Full name: -';
                emailEl.textContent = 'Email: -';
                departEl.textContent = 'Current Department: -';
                assignInput.value = '';
                assignBtn.disabled = true;
            }
        });
    });
</script>
@endpush

@endsection