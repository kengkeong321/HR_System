@extends('layouts.admin')
@section('title', 'Training List')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Training & Development</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Training Programs List</h6>
            
            @if(session('role') === 'Admin' || (isset($isAdmin) && $isAdmin))
                <a href="{{ route('training.create') }}" class="btn btn-sm btn-success shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Create New Training
                </a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Title</th>
                            <th>Venue</th>
                            <th>Start Date</th>
                            <th>End Date</th> <th>Participants</th> <th style="min-width: 280px;">Action</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trainings as $training)
                        <tr>
                            <td class="align-middle">{{ $training->title }}</td>
                            <td class="align-middle">{{ $training->venue }}</td>
                            <td class="align-middle">{{ $training->start_time }}</td>
                            <td class="align-middle">{{ $training->end_time }}</td>
                            
                            <td class="align-middle text-center">
                                @php
                                    $currentCount = $training->participants->count();
                                    $isFull = $training->capacity && $currentCount >= $training->capacity;
                                @endphp
                                <span class="badge {{ $isFull ? 'badge-danger' : 'badge-info' }}" style="font-size: 0.9rem;">
                                    <i class="fas fa-users"></i> {{ $currentCount }} / {{ $training->capacity ?? '∞' }}
                                </span>
                            </td>

                            <td class="align-middle">
                                <a href="{{ route('training.show', $training->id) }}" class="btn btn-info btn-sm mb-1" title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </a>

                                @if(session('role') === 'Admin' || (isset($isAdmin) && $isAdmin))
                                    <a href="{{ route('training.assignPage', $training->id) }}" class="btn btn-success btn-sm mb-1" title="Assign Staff">
                                        <i class="fas fa-user-plus"></i> Assign
                                    </a>

                                    <a href="{{ route('training.edit', $training->id) }}" class="btn btn-warning btn-sm mb-1" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('training.destroy', $training->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Confirm Delete?')" title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No training programs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection