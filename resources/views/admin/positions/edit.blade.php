@extends('layouts.admin')
{{-- Woo Keng Keong --}}
@section('title', 'Edit Position')

@section('content')
  <div class="mb-3">
    <h4>Edit Position</h4>
  </div>

  <form action="{{ route('admin.positions.update', $position) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.positions._form')

    <div>
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('admin.positions.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
