{{-- Loong Wei Lim --}}
@extends('layouts.app')

@section('title', 'Login')

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title mb-3">Login</h4>

          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
              <label class="form-label">Username</label>
              <input name="user_name" value="{{ old('user_name') }}" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control" required>
            </div>

            <button class="btn btn-primary" type="submit">Login</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
