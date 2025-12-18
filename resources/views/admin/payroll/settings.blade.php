@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">Global Payroll Settings</h2>

    <div class="card shadow col-md-8">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Malaysian Statutory Rates (%)</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payroll.settings.update') }}" method="POST">
                @csrf
                @foreach($configs as $config)
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <label class="fw-bold mb-0">{{ $config->description }}</label>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="number" step="0.01" name="configs[{{ $config->config_key }}]" 
                                   class="form-control" value="{{ $config->config_value }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-5 shadow">Update Global Rates</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection