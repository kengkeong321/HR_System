@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
  <div class="card">
    <div class="card-body">
      <h3>Admin Dashboard</h3>
      <p>Welcome, <strong>{{ $user?->user_name ?? 'Admin' }}</strong></p>

      <p>Quick links:</p>
      <ul>
        @if(($user?->role ?? null) === 'Admin')
          <li><a href="{{ route('admin.users.index') }}">Manage Users</a></li>
        @endif
        <li><a href="{{ route('admin.faculties.index') }}">Manage Faculties</a></li>
        <li><a href="{{ route('admin.departments.index') }}">Manage Departments</a></li>
        <li><a href="{{ route('admin.courses.index') }}">Manage Courses</a></li>
         <li><a href="{{ route('training.index') }}">Manage Training Programs</a></li>
      </ul>
    </div>
  </div>
@endsection
