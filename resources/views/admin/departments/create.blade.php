@extends('layouts.admin')

@section('title', 'Create Department')

@section('content')
  <h4>Create Department</h4>
  <form method="POST" action="{{ route('admin.departments.store') }}">
    @csrf

    <div class="mb-3">
      <label class="form-label">ID</label>
      <input name="depart_id" class="form-control @error('depart_id') is-invalid @enderror" value="{{ old('depart_id') }}" required>
      @error('depart_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Faculty</label>
      <select name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror">
        @foreach($faculties as $f)
          <option value="{{ $f->faculty_id }}" {{ old('faculty_id') === $f->faculty_id ? 'selected' : '' }}>{{ $f->faculty_name }}</option>
        @endforeach
      </select>
      @error('faculty_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input name="depart_name" class="form-control @error('depart_name') is-invalid @enderror" value="{{ old('depart_name') }}" required>
      @error('depart_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select @error('status') is-invalid @enderror">
        <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
      </select>
      @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button class="btn btn-primary">Create</button>
  </form>
@endsection
