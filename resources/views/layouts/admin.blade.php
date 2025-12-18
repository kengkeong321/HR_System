<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin - HR System')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    /* Sidebar layout with collapse behavior */
    body {
      min-height: 100vh;
    }

    .sidebar {
      width: 240px;
      transition: width .2s;
      position: sticky;
      top: 56px;
      /* offset so it sits below the navbar */
      height: calc(100vh - 56px);
      overflow-y: auto;
      padding-bottom: 1rem;
    }

    .sidebar .nav-link {
      color: #333;
    }

    .sidebar .nav-link .label {
      white-space: nowrap;
    }

    /* collapsed */
    .sidebar-collapsed .sidebar {
      width: 64px;
    }

    .sidebar-collapsed .sidebar h5 {
      display: none;
    }

    .sidebar-collapsed .sidebar .nav-link {
      justify-content: center;
      padding-left: 7px;
    }

    .sidebar-collapsed .sidebar .nav-link .label {
      display: none;
    }

    /* flash success message */
    .flash-message {
      position: fixed;
      top: -80px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1050;
      transition: top .4s ease, opacity .4s ease;
      opacity: 0;
    }

    .flash-message.show {
      top: 20px;
      opacity: 1;
    }

    /* invalid field attention */
    @keyframes shake {

      10%,
      90% {
        transform: translateX(-1px);
      }

      20%,
      80% {
        transform: translateX(2px);
      }

      30%,
      50%,
      70% {
        transform: translateX(-4px);
      }

      40%,
      60% {
        transform: translateX(4px);
      }
    }

    .invalid-shake {
      animation: shake .45s;
    }

    @media (max-width: 767px) {
      .sidebar {
        width: 100%;
      }
    }
  </style>
</head>

<body class="{{ session('sidebar_collapsed') ? 'sidebar-collapsed' : '' }}">
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

  @if(session('success'))
  <div id="flash-success" class="flash-message">
    <div class="alert alert-success mb-0">{{ session('success') }}</div>
  </div>
  @endif

  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-light sidebar p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Admin</h5>
          <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary" aria-label="Toggle sidebar"><i class="bi bi-chevron-left"></i></button>
        </div>

        <ul class="nav flex-column">
          @if(session('role') === 'Admin')
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-2"></i><span class="label">Users</span></a>
          </li>
          @endif

          <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="{{ route('admin.faculties.index') }}"><i class="bi bi-building me-2"></i><span class="label">Faculties</span></a>
          </li>

          <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="{{ route('admin.departments.index') }}"><i class="bi bi-diagram-3 me-2"></i><span class="label">Departments</span></a>
          </li>

          <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="{{ route('admin.courses.index') }}"><i class="bi bi-journal-bookmark me-2"></i><span class="label">Courses</span></a>
          </li>

          <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i><span class="label">Dashboard</span></a>
          </li>

          <div class="nav-header text-muted small text-uppercase mt-3 mb-1 ml-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
            ATTENDANCE
          </div>

          <li class="nav-item">
            <a href="{{ route('admin.attendance.create') }}"
              class="nav-link {{ request()->routeIs('admin.attendance.create') ? 'active' : '' }} d-flex align-items-center">
              <i class="fas fa-calendar-check mr-3" style="width: 25px; text-align: center; margin-left: -3%; margin-right: 2%;"></i>
              <span>Mark Attendance</span>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.attendance.index') }}"
              class="nav-link {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }} d-flex align-items-center">
              <i class="fas fa-history mr-3" style="width: 25px; text-align: center; margin-left: -3%; margin-right: 2%;"></i>
              <span>Attendance Logs</span>
            </a>
          </li>
        </ul>

        <ul class="nav flex-column">
          <div class="nav-header text-muted small text-uppercase mt-3 mb-1 ml-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
            PAYROLL & ALLOWANCES
          </div>

          <li class="nav-item"> <a href="{{ route('admin.payroll.index') }}"
              class="nav-link {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }} d-flex align-items-center">
              <i class="bi bi-cash-stack me-2" style="width: 20px; text-align: center;"></i>
              <span class="label">Payroll Management</span>
            </a>
          </li>
        </ul>

      </aside>

      <main class="col-md-9 col-lg-10 p-4">

        @yield('content')
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function() {
      const btn = document.getElementById('sidebarToggle');
      if (!btn) return;
      btn.addEventListener('click', function() {
        document.body.classList.toggle('sidebar-collapsed');
        const collapsed = document.body.classList.contains('sidebar-collapsed');
        try {
          localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0');
          // optionally persist to session so server-side rendering can pick it up
          fetch('/_sidebar/toggle', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              collapsed: collapsed ? 1 : 0
            })
          }).catch(() => {});
        } catch (e) {}
      });

      // Restore preference from localStorage if available
      try {
        const s = localStorage.getItem('sidebar_collapsed');
        if (s === '1') document.body.classList.add('sidebar-collapsed');
      } catch (e) {}

      // Flash success message handling: slide down for 3s
      const flash = document.getElementById('flash-success');
      if (flash) {
        setTimeout(() => flash.classList.add('show'), 50);
        setTimeout(() => {
          flash.classList.remove('show');
          setTimeout(() => flash.remove(), 500);
        }, 3050);
      }

      // Per-field error focus: if there are server-side field errors, focus and shake first invalid
      const firstInvalid = document.querySelector('.is-invalid');
      if (firstInvalid) {
        try {
          firstInvalid.focus();
          firstInvalid.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
          firstInvalid.classList.add('invalid-shake');
          setTimeout(() => firstInvalid.classList.remove('invalid-shake'), 500);
        } catch (e) {}
      }

      // AJAX pagination: intercept pagination links and load via POST so page is not shown in URL
      document.addEventListener('click', function(e) {
        const a = e.target.closest('.ajax-paginate .pagination a');
        if (!a) return;
        e.preventDefault();

        const container = a.closest('.ajax-paginate');
        if (!container) return;
        const url = container.dataset.url;
        const href = a.getAttribute('href') || '';
        const page = (() => {
          try {
            return new URL(href, window.location.origin).searchParams.get('page') || 1;
          } catch (e) {
            return 1;
          }
        })();

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            page: page
          })
        }).then(r => r.text()).then(html => {
          container.innerHTML = html;
          // scroll to container
          try {
            container.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          } catch (e) {}
        }).catch(() => {
          // on error fallback to navigation
          window.location.href = a.href;
        });
      });
    })();
  </script>
</body>

</html>