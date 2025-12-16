@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
  <h4>Edit User</h4>
  <form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Username</label>
      <input name="user_name" class="form-control @error('user_name') is-invalid @enderror" value="{{ old('user_name', $user->user_name) }}" required>
      @error('user_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Password (leave blank to keep)</label>
      <input name="password" type="password" class="form-control @error('password') is-invalid @enderror">
      @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Role</label>
      <select name="role" class="form-select @error('role') is-invalid @enderror">
        <option value="Admin" {{ $user->role === 'Admin' ? 'selected' : '' }}>Admin</option>
        <option value="Staff" {{ $user->role === 'Staff' ? 'selected' : '' }}>Staff</option>
      </select>
      @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select @error('status') is-invalid @enderror">
        <option value="Active" {{ $user->status === 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Inactive" {{ $user->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
      </select>
      @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button class="btn btn-primary">Save</button>
  </form>
@endsection
