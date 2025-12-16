@extends('layouts.admin')

@section('title', 'Faculties')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Faculties</h4>
    <a href="{{ route('admin.faculties.create') }}" class="btn btn-primary">Create Faculty</a>
  </div>

  <div id="faculties-list" class="ajax-paginate" data-url="{{ route('admin.faculties.page') }}">
    @include('admin.faculties._list', ['faculties' => $faculties])
  </div>
@endsection
