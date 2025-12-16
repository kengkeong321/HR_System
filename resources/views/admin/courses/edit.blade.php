@extends('layouts.admin')

@section('title', 'Edit Course')

@section('content')
  <h4>Edit Course</h4>
  <form method="POST" action="{{ route('admin.courses.update', $course) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">ID</label>
      <input name="course_id" class="form-control @error('course_id') is-invalid @enderror" value="{{ old('course_id', $course->course_id) }}" required>
      @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input name="course_name" class="form-control @error('course_name') is-invalid @enderror" value="{{ old('course_name', $course->course_name) }}" required>
      @error('course_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>



    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select @error('status') is-invalid @enderror">
        <option value="Active" {{ old('status', $course->status) === 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Inactive" {{ old('status', $course->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
      </select>
      @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button class="btn btn-primary">Save</button>
  </form>
@endsection
