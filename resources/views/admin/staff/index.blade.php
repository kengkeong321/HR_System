@extends('layouts.admin')

@section('title', 'Staff Directory')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Staff Management</h4>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Staff
    </a>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div id="staff-list" class="ajax-paginate" data-url="{{ route('admin.staff.page') }}">
        @include('admin.staff._list', ['staffs' => $staffs])
      </div>
    </div>
  </div>
@endsection