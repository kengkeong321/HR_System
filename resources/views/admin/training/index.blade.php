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
                        <tr class="text-center">
                            <th>Title</th>
                            <th>Venue</th>
                            <th>Start Date</th>
                            <th>End Date</th> 
                            <th>Participants</th> 
                            <th style="min-width: 150px;">Action</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trainings as $training)
                        <tr>
                            <td class="align-middle font-weight-bold">{{ $training->title }}</td>
                            <td class="align-middle">{{ $training->venue }}</td>
                            <td class="align-middle text-center small">{{ $training->start_time }}</td>
                            <td class="align-middle text-center small">{{ $training->end_time }}</td>
                            
                            <td class="align-middle text-center">
                                @php
                                    $currentCount = $training->participants->count();
                                    $capacity = $training->capacity;
                                    $isFull = $capacity > 0 && $currentCount >= $capacity;
                                @endphp
                                
                                <div style="font-size: 1rem; color: #000 !important; font-weight: 700;">
                                    <i class="fas fa-user {{ $isFull ? 'text-danger' : 'text-info' }}" style="margin-right: 5px;"></i>
                                    <span style="color: #000 !important;">{{ $currentCount }} / {{ $capacity ?? '∞' }}</span>
                                </div>
                            </td>

                            <td class="align-middle text-center">
                             
                                <a href="{{ route('training.show', $training->id) }}" class="btn btn-info btn-sm shadow-sm">
                                    <i class="fas fa-eye"></i> View & Edit
                                </a>

                                @if(session('role') === 'Admin' || (isset($isAdmin) && $isAdmin))
                               
                                    <form action="{{ route('training.destroy', $training->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('Confirm Delete?')">
                                            <i class="fas fa-trash"></i>
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