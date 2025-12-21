@extends('layouts.admin')
{{-- Woo Keng Keong --}}
@section('title', 'Positions')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Positions</h4>
    <a href="{{ route('admin.positions.create') }}" class="btn btn-primary">Create Position</a>
  </div>

  <div id="positions-list" class="ajax-paginate" data-url="{{ route('admin.positions.page') }}">
    @include('admin.positions._list', ['positions' => $positions])
  </div>
@endsection
