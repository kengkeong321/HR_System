<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Faculty</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($departments as $d)
    <tr>
      <td>{{ $d->depart_id }}</td>
      <td>{{ $d->depart_name }}</td>
      <td>{{ $d->faculty?->faculty_name }}</td>
      <td><span class="badge bg-{{ $d->status === 'Active' ? 'success' : 'danger' }}">{{ $d->status }}</span></td>
      <td>
        <a href="{{ route('admin.departments.edit', $d) }}" class="btn btn-sm btn-secondary">Edit</a>
        <form action="{{ route('admin.departments.toggleStatus', $d) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Change status?')">
          @csrf
          @method('PATCH')
          <button class="btn btn-sm btn-outline-secondary">{{ $d->status === 'Active' ? 'Set Inactive' : 'Activate' }}</button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="d-flex justify-content-center mt-3 pagination-wrap">
  {{ $departments->links('pagination::bootstrap-5') }}
</div>

<div class="small text-muted text-center" style="margin-top: -1rem;">
  Showing <span class="fw-semibold">{{ $departments->firstItem() ?? 0 }}</span>
  to <span class="fw-semibold">{{ $departments->lastItem() ?? 0 }}</span>
  of <span class="fw-semibold">{{ $departments->total() }}</span>
  results
</div>

