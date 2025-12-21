{{-- Mu Jun Yi --}}
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">HR System</a>
    <div class="d-flex ms-auto">
      @if(session('user_name'))
      <span class="me-3 align-self-center">{{ session('user_name') }}</span>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-secondary" type="submit">Logout</button>
      </form>
      @else
      <a class="btn btn-outline-primary" href="{{ route('login') }}">Login</a>
      @endif
    </div>
  </div>
</nav>