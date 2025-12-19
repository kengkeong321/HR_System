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
    @foreach($positions as $p)
    <tr class="position-row" data-position-id="{{ $p->position_id }}" data-position-name="{{ $p->name }}" style="cursor:pointer">
      <td>{{ $p->position_id }}</td>
      <td>{{ $p->name }}</td>
      <td><span class="badge bg-{{ $p->status === 'Active' ? 'success' : 'danger' }}">{{ $p->status }}</span></td>
      <td>
        <a href="{{ route('admin.positions.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit mr-1"></i> Edit</a>
        <form action="{{ route('admin.positions.toggleStatus', $p) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Change status?')">
          @csrf
          @method('PATCH')
          <button class="btn btn-sm btn-outline-secondary">{{ $p->status === 'Active' ? 'Set Inactive' : 'Activate' }}</button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="d-flex justify-content-center mt-3 pagination-wrap">
  {{ $positions->links('pagination::bootstrap-5') }}
</div>

<div class="small text-muted text-center" style="margin-top: -1rem;">
  Showing <span class="fw-semibold">{{ $positions->firstItem() ?? 0 }}</span>
  to <span class="fw-semibold">{{ $positions->lastItem() ?? 0 }}</span>
  of <span class="fw-semibold">{{ $positions->total() }}</span>
  results
</div>
