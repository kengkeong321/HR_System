<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Staff - HR System')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <style>
    /* Re-inserting your specific Admin Design CSS */
    body {
      min-height: 100vh;
      background-color: #f8f9fa;
    }

    .sidebar {
      width: 240px;
      transition: width .2s;
      position: sticky;
      top: 56px;
      height: calc(100vh - 56px);
      overflow-y: auto;
      background: white;
      border-right: 1px solid #dee2e6;
    }

    .sidebar .nav-link {
      color: #333;
      padding: 10px 20px;
    }

    .sidebar .nav-link.active {
      background-color: #0d6efd;
      color: white !important;
      rounded: 5px;
    }

    .sidebar-collapsed .sidebar {
      width: 64px;
    }

    .sidebar-collapsed .sidebar .label,
    .sidebar-collapsed .sidebar h5 {
      display: none;
    }

    /* Flash Message Design */
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
  </style>
</head>

<body class="{{ session('sidebar_collapsed') ? 'sidebar-collapsed' : '' }}">

  @include('layouts.partials.navbar')

  <div class="container-fluid">
    <div class="row">
      <aside class="col-auto sidebar p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0 fw-bold">Staff Portal</h5>
          <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-chevron-left"></i>
          </button>
        </div>

        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}">
              <i class="bi bi-speedometer2 me-2"></i><span class="label">Dashboard</span>
            </a>
          </li>

          <div class="nav-header text-muted small text-uppercase mt-3 mb-1 ms-3">Attendance</div>

          <li class="nav-item">
            <a href="{{ route('staff.attendance.create') }}"
              class="nav-link d-flex align-items-center {{ request()->routeIs('staff.attendance.*') ? 'active' : '' }}">
              <i class="fas fa-user-clock me-2" style="width: 20px;"></i>
              <span class="label">Mark Attendance</span>
            </a>
          </li>
        </ul>

        <div class="nav-header text-muted small text-uppercase mt-3 mb-1 ms-3 label">Financials</div>

        <li class="nav-item">
          <a class="nav-link d-flex align-items-center {{ request()->routeIs('staff.payroll.*') ? 'active' : '' }}"
            href="{{ route('staff.payroll.my_payslips') }}">
            <i class="bi bi-wallet2 me-2"></i>
            <span class="label">My Payslips</span>
          </a>
        </li>

        <div class="nav-header text-muted small text-uppercase mt-3 mb-1 ms-3 label">Self-Service</div>

        <li class="nav-item">
          <a class="nav-link d-flex align-items-center {{ request()->routeIs('staff.claims.*') ? 'active' : '' }}"
            href="{{ route('staff.claims.create') }}">
            <i class="bi bi-file-earmark-plus me-2"></i>
            <span class="label">Submit Claim</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link d-flex align-items-center {{ request()->routeIs('staff.claims.*') ? 'active' : '' }}"
            href="{{ route('staff.claims.index') }}">
            <i class="bi bi-file-earmark-plus me-2"></i>
            <span class="label">My Claims</span>

            {{-- Use the variable shared by the AppServiceProvider --}}
            @if(isset($sidebarRejectionCount) && $sidebarRejectionCount > 0)
            <span class="badge rounded-pill bg-danger ms-auto">
              {{ $sidebarRejectionCount }}
            </span>
            @endif
          </a>
        <li class="nav-item">
            <a href="{{ route('staff.leave.index') }}" 
              class="nav-link d-flex align-items-center {{ request()->routeIs('staff.leave.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-plus me-2"></i>
                <p class="mb-0">Request Leave</p>
            </a>
        </li>
      </aside>

      <main class="col p-4">
        @yield('content')
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.body.classList.toggle('sidebar-collapsed');
    });
  </script>
</body>

</html>