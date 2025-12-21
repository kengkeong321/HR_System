{{-- Mu Jun Yi --}}
<div class="nav-header text-muted small text-uppercase ml-3 mt-3">Staff Menu</div>

<li class="nav-item">
    <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Dashboard</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('staff.attendance.create') }}" class="nav-link {{ request()->routeIs('staff.attendance.create') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-clock"></i>
        <p>My Attendance</p>
    </a>
</li>