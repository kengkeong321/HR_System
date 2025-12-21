<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HR System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .flash-message {
        position: fixed;
        top: -80px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1050;
        transition: top .4s ease, opacity .4s ease;
        opacity: 0;
      }
      .flash-message.show { top: 20px; opacity: 1; }

      @keyframes shake {
        10%, 90% { transform: translateX(-1px); }
        20%, 80% { transform: translateX(2px); }
        30%, 50%, 70% { transform: translateX(-4px); }
        40%, 60% { transform: translateX(4px); }
      }
      .invalid-shake { animation: shake .45s; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
      <div class="container">
        <a class="navbar-brand" href="#">HR System</a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            @if(session('user_name'))
              <li class="nav-item">
                <span class="nav-link">{{ session('user_name') }}</span>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="{{ route('staff.payroll.my_payslips') }}">
              </li>
              
              <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="btn btn-link nav-link" type="submit">Logout</button>
                </form>
              </li>
            @else
              <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
            @endif
          </ul>
        </div>
      </div>
    </nav>

    @if(session('success'))
      <div id="flash-success" class="flash-message">
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
      </div>
    @endif

    <div class="container">


      @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      (function () {
        const flash = document.getElementById('flash-success');
        if (flash) {
          setTimeout(() => flash.classList.add('show'), 50);
          setTimeout(() => { flash.classList.remove('show'); setTimeout(() => flash.remove(), 500); }, 3050);
        }

        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
          try {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.classList.add('invalid-shake');
            setTimeout(() => firstInvalid.classList.remove('invalid-shake'), 500);
          } catch (e) {}
        }
      })();
    </script>
  </body>
</html>
