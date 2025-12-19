@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Assign Courses to Department: {{ $department->depart_name }} ({{ $department->depart_id }})</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.departments.assign.store', $department) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Select Active Courses</label>
                    <div class="row">
                        @foreach($courses as $course)
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="courses[]" value="{{ $course->course_id }}" id="course_{{ $course->course_id }}" {{ in_array($course->course_id, $selected) ? 'checked' : '' }}>
                                <label class="form-check-label" for="course_{{ $course->course_id }}">{{ $course->course_name }} ({{ $course->course_id }})</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-default ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
