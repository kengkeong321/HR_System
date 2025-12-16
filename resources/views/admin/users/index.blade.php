@extends('layouts.admin')

@section('title', 'Users')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Users</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create User</a>
  </div>

  <div id="users-list" class="ajax-paginate" data-url="{{ route('admin.users.page') }}">
    @include('admin.users._list', ['users' => $users])
  </div>
@endsection
