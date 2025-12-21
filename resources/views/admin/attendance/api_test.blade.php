@extends('layouts.admin')
@section('content')
{{-- Mu Jun Yi --}}
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h3>Attendance API Connection Test</h3>
            <p class="text-muted">Testing the RESTful exposure of today's attendance records.</p>
            
            <button id="testApi" class="btn btn-primary mb-3">Fetch My Module Data</button>

            <div class="bg-dark text-success p-3 rounded">
                <pre id="apiDebug" class="mb-0">Click button to test...</pre>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('testApi').addEventListener('click', async () => {
    const debug = document.getElementById('apiDebug');
    debug.textContent = "Requesting: /api/attendance/summary...";
    
    try {
        const res = await fetch('/api/attendance/summary');
        const json = await res.json();
        debug.textContent = JSON.stringify(json, null, 4);
    } catch (err) {
        debug.textContent = "Error: " + err.message;
    }
});
</script>
@endsection