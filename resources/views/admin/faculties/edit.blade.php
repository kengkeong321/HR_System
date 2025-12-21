@extends('layouts.admin')
{{-- Woo Keng Keong --}}
@section('title', 'Edit Faculty')

@section('content')
  <h4>Edit Faculty</h4>
  <form method="POST" action="{{ route('admin.faculties.update', $faculty) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">ID</label>
      <input name="faculty_id" class="form-control @error('faculty_id') is-invalid @enderror" value="{{ old('faculty_id', $faculty->faculty_id) }}" required>
      @error('faculty_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input name="faculty_name" class="form-control @error('faculty_name') is-invalid @enderror" value="{{ old('faculty_name', $faculty->faculty_name) }}" required>
      @error('faculty_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select @error('status') is-invalid @enderror">
        <option value="Active" {{ old('status', $faculty->status) === 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Inactive" {{ old('status', $faculty->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
      </select>
      @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button class="btn btn-primary">Save</button>
  </form>
@endsection
