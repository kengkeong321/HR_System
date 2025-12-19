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
    @foreach($faculties as $f)
    <tr class="faculty-row" data-faculty-id="{{ $f->faculty_id }}" data-faculty-name="{{ $f->faculty_name }}" style="cursor:pointer">
      <td>{{ $f->faculty_id }}</td>
      <td>{{ $f->faculty_name }}</td>
      <td><span class="badge bg-{{ $f->status === 'Active' ? 'success' : 'danger' }}">{{ $f->status }}</span></td>
      <td>
        <a href="{{ route('admin.faculties.edit', $f) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit mr-1"></i> Edit</a>
        <form action="{{ route('admin.faculties.toggleStatus', $f) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Change status?')">
          @csrf
          @method('PATCH')
          <button class="btn btn-sm btn-outline-secondary">{{ $f->status === 'Active' ? 'Set Inactive' : 'Activate' }}</button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="d-flex justify-content-center mt-3 pagination-wrap">
  {{ $faculties->links('pagination::bootstrap-5') }}
</div>

<div class="small text-muted text-center" style="margin-top: -1rem;">
  Showing <span class="fw-semibold">{{ $faculties->firstItem() ?? 0 }}</span>
  to <span class="fw-semibold">{{ $faculties->lastItem() ?? 0 }}</span>
  of <span class="fw-semibold">{{ $faculties->total() }}</span>
  results
</div>

