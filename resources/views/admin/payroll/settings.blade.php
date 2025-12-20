@extends('layouts.admin')

@section('title', 'Global Payroll Settings')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">Global Payroll Settings</h2>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card shadow col-md-8 border-0">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-gear-fill me-2"></i>Malaysian Statutory Rates (%)
            </h6>
        </div>
        <div class="card-body">
            @php 
                $canModify = in_array(auth()->user()->role, ['HR', 'Admin']);
            @endphp

            @if(!$canModify)
                <div class="alert alert-info small border-0 shadow-sm mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>View Only Mode:</strong> Finance accounts are restricted from modifying statutory rates to maintain data integrity.
                </div>
            @endif

            <form action="{{ route('admin.payroll.settings.update') }}" method="POST">
                @csrf
                @foreach($configs as $config)
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <label for="config_{{ $config->id }}" class="fw-bold mb-0 text-secondary">
                            {{ $config->description }}
                        </label>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="number" step="0.01" 
                                   id="config_{{ $config->id }}"
                                   name="configs[{{ $config->config_key }}]" 
                                   class="form-control @if(!$canModify) bg-light @endif" 
                                   value="{{ $config->config_value }}"
                                   {{ !$canModify ? 'readonly' : '' }}
                                   title="{{ !$canModify ? 'Unauthorized to edit' : 'Enter rate percentage' }}">
                            <span class="input-group-text bg-light">%</span>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <div class="text-end mt-4">
                    @if($canModify)
                        <button type="submit" class="btn btn-primary px-5 shadow">
                            <i class="bi bi-cloud-upload me-2"></i>Update Global Rates
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary px-5 shadow" disabled 
                                title="Insufficient permissions to update global configurations">
                            <i class="bi bi-lock-fill me-2"></i>Locked 
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection