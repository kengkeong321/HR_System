<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Contact</th>
                <th>Employment</th>
                <th>Salary/Rate</th>
                <th>Join Date</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffs as $staff)
            <tr>
                <td><small class="text-muted">#{{ $staff->staff_id }}</small></td>
                <td>
                    <strong>{{ $staff->full_name }}</strong><br>
                    <small class="text-muted">{{ $staff->email }}</small>
                </td>
                <td>{{ $staff->depart_id }}</td>
                <td><span class="badge bg-info text-dark">{{ $staff->position }}</span></td>
                <td>{{ $staff->phone ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $staff->employment_type == 'Full-Time' ? 'bg-success' : 'bg-warning' }}">
                        {{ $staff->employment_type }}
                    </span>
                </td>
                <td>
                    @if($staff->employment_type == 'Full-Time')
                        ${{ number_format($staff->basic_salary, 2) }} <small>(M)</small>
                    @else
                        ${{ number_format($staff->hourly_rate, 2) }} <small>(H)</small>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($staff->join_date)->format('d M Y') }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.staff.edit', $staff->staff_id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-4">No staff records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-center custom-pagination">
    {{ $staffs->links() }}
</div>