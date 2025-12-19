@extends('layouts.admin')

@section('title', 'Create Position')

@section('content')
  <div class="mb-3">
    <h4>Create Position</h4>
  </div>

  <form action="{{ route('admin.positions.store') }}" method="POST">
    @csrf
    @include('admin.positions._form')
    <div>
      <button class="btn btn-primary">Create</button>
      <a href="{{ route('admin.positions.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
