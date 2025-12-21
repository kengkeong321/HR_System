{{-- Loong Wei Lim --}}
<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Role</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($users as $user)
    <tr>
      <td>{{ $user->user_id }}</td>
      <td>{{ $user->user_name }}</td>
      <td>{{ $user->role }}</td>
      <td><span class="badge bg-{{ $user->status === 'Active' ? 'success' : 'danger' }}">{{ $user->status }}</span></td>
      <td>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-secondary">Edit</a>
        <form action="{{ route('admin.users.toggleStatus', $user) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Change status?')">
          @csrf
          @method('PATCH')
          <button class="btn btn-sm btn-outline-secondary">{{ $user->status === 'Active' ? 'Set Inactive' : 'Activate' }}</button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="d-flex justify-content-center mt-3 pagination-wrap">
  {{ $users->links('pagination::bootstrap-5') }}
</div>

<div class="small text-muted text-center" style="margin-top: -0.5rem;">
  Showing <span class="fw-semibold">{{ $users->firstItem() ?? 0 }}</span>
  to <span class="fw-semibold">{{ $users->lastItem() ?? 0 }}</span>
  of <span class="fw-semibold">{{ $users->total() }}</span>
  results
</div>