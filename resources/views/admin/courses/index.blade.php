@extends('layouts.admin')

@section('title', 'Courses')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Courses</h4>
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">Create Course</a>
  </div>

  <div id="courses-list" class="ajax-paginate" data-url="{{ route('admin.courses.page') }}">
    @include('admin.courses._list', ['courses' => $courses])
  </div>
@endsection
