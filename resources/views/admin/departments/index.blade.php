@extends('layouts.admin')

@section('title', 'Departments')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Departments</h4>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">Create Department</a>
  </div>

  <div id="departments-list" class="ajax-paginate" data-url="{{ route('admin.departments.page') }}">
    @include('admin.departments._list', ['departments' => $departments])
  </div>
@endsection
