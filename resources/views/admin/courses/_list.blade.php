<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($courses as $c)
    <tr>
      <td>{{ $c->course_id }}</td>
      <td>{{ $c->course_name }}</td>
      <td><span class="badge bg-{{ $c->status === 'Active' ? 'success' : 'danger' }}">{{ $c->status }}</span></td>
      <td>
        <a href="{{ route('admin.courses.edit', $c) }}" class="btn btn-sm btn-secondary">Edit</a>
        <form action="{{ route('admin.courses.toggleStatus', $c) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Change status?')">
          @csrf
          @method('PATCH')
          <button class="btn btn-sm btn-outline-secondary">{{ $c->status === 'Active' ? 'Set Inactive' : 'Activate' }}</button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="d-flex justify-content-center mt-3 pagination-wrap">
  {{ $courses->links('pagination::bootstrap-5') }}
</div>

<div class="small text-muted text-center" style="margin-top: -1rem;">
  Showing <span class="fw-semibold">{{ $courses->firstItem() ?? 0 }}</span>
  to <span class="fw-semibold">{{ $courses->lastItem() ?? 0 }}</span>
  of <span class="fw-semibold">{{ $courses->total() }}</span>
  results
</div>
