<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MGVCL Feeder')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root { --sidebar-width: 240px; --topbar-height: 56px; }
        body { background: #f0f4f8; }

        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background: #1a3a5c;
            position: fixed; top: 0; left: 0; right: 0; z-index: 1030;
        }

        /* Sidebar — only for admin/circle */
        .sidebar {
            width: var(--sidebar-width);
            position: fixed; top: var(--topbar-height); bottom: 0; left: 0;
            background: #fff;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            z-index: 1020;
            transition: transform .25s ease;
        }
        .sidebar .nav-link {
            color: #444;
            border-radius: 6px;
            margin: 2px 8px;
            padding: 8px 12px;
            font-size: .9rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #e8f0fe;
            color: #1a3a5c;
            font-weight: 600;
        }
        .sidebar .nav-link i { width: 20px; }

        /* Sidebar backdrop (mobile) */
        .sidebar-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 1019;
        }
        .sidebar-backdrop.show { display: block; }

        /* Main content */
        .main-content {
            margin-top: var(--topbar-height);
            padding: 1.5rem;
        }
        @if(auth()->user()?->hasAnyRole(['admin', 'circle']))
        @media (min-width: 992px) {
            .main-content { margin-left: var(--sidebar-width); }
        }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.sidebar-open { transform: translateX(0); }
        }
        @endif

        /* Status badges */
        .badge-fully-on     { background: #198754; }
        .badge-partially-on { background: #fd7e14; }
        .badge-fully-off    { background: #dc3545; }

        /* Table improvements */
        .table th { white-space: nowrap; font-size: .85rem; font-weight: 600; }
        .table td { vertical-align: middle; font-size: .88rem; }
    </style>
    @stack('styles')
</head>
<body>

{{-- Topbar --}}
<nav class="topbar d-flex align-items-center px-3">
    @if(auth()->user()?->hasAnyRole(['admin', 'circle']))
    <button class="btn btn-link text-white me-2 d-lg-none p-0" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
    @endif
    <a class="navbar-brand text-white fw-bold text-decoration-none me-auto" href="{{ route('dashboard') }}">
        <i class="bi bi-lightning-charge-fill me-1"></i> MGVCL Feeder
    </a>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white-50 small d-none d-md-block">
            <i class="bi bi-person-circle me-1"></i>
            {{ auth()->user()->name }}
            <span class="badge bg-secondary ms-1" style="font-size:.7rem;">{{ auth()->user()->getRoleNames()->first() }}</span>
        </span>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</nav>

{{-- Sidebar backdrop (mobile) --}}
@if(auth()->user()?->hasAnyRole(['admin', 'circle']))
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
@endif

{{-- Sidebar — admin & circle only --}}
@if(auth()->user()?->hasAnyRole(['admin', 'circle']))
<aside class="sidebar" id="sidebar">
    <nav class="nav flex-column pt-3">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="{{ route('feeders.index') }}" class="nav-link {{ request()->routeIs('feeders.*') ? 'active' : '' }}">
            <i class="bi bi-lightning me-2"></i> Feeder Status
        </a>

        @can('view-status-logs')
        <hr class="mx-3 my-1">
        <small class="text-muted px-3 py-1" style="font-size:.75rem;">MASTER DATA</small>
        @endcan

        @can('manage-feeder')
        <hr class="mx-3 my-1">
        <small class="text-muted px-3 py-1" style="font-size:.75rem;">MASTER DATA</small>
        <a href="{{ route('master.divisions.index') }}" class="nav-link {{ request()->routeIs('master.divisions.*') ? 'active' : '' }}">
            <i class="bi bi-building me-2"></i> Divisions
        </a>
        <a href="{{ route('master.sub-divisions.index') }}" class="nav-link {{ request()->routeIs('master.sub-divisions.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-2 me-2"></i> Sub Divisions
        </a>
        <a href="{{ route('master.substations.index') }}" class="nav-link {{ request()->routeIs('master.substations.*') ? 'active' : '' }}">
            <i class="bi bi-grid me-2"></i> Substations
        </a>
        <a href="{{ route('master.feeders.index') }}" class="nav-link {{ request()->routeIs('master.feeders.*') ? 'active' : '' }}">
            <i class="bi bi-reception-4 me-2"></i> Feeder Master
        </a>
        @endcan

        @can('manage-users')
        <hr class="mx-3 my-1">
        <small class="text-muted px-3 py-1" style="font-size:.75rem;">ADMIN</small>
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people me-2"></i> Users
        </a>
        <a href="{{ route('admin.circles.index') }}" class="nav-link {{ request()->routeIs('admin.circles.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3 me-2"></i> Circles
        </a>
        @endcan
    </nav>
</aside>
@endif

{{-- Main Content --}}
<main class="main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ env("PUSHER_APP_KEY") }}',
    cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
    forceTLS: true,
});
</script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('table[data-dt]').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, 'All']],
        order: [],
        language: { search: 'Search:', zeroRecords: 'No records found.' },
        columnDefs: [{ orderable: false, targets: '_all' === 'actions' ? -1 : [] }]
    });
});
</script>
@if(auth()->user()?->hasAnyRole(['admin', 'circle']))
<script>
(function () {
    var toggle   = document.getElementById('sidebarToggle');
    var sidebar  = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    if (!toggle || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        backdrop && backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        backdrop && backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });
    backdrop && backdrop.addEventListener('click', closeSidebar);

    // close on nav link click (mobile UX)
    sidebar.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) closeSidebar();
        });
    });
})();
</script>
@endif
@stack('scripts')
</body>
</html>
