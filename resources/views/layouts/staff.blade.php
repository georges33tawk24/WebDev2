<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staff Panel') — E-Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        :root {
            --blue-primary: #1a56db;
            --blue-dark: #1e429f;
            --bg-page: #f9fafb;
            --white: #ffffff;
            --border: #e5e7eb;
            --text-muted: #6b7280;
            --text-dark: #111827;
            --text-medium: #374151;
        }

        body { background: var(--bg-page); display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1e429f 0%, #1a3a8f 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo span { font-size: 18px; font-weight: 700; color: #fff; }
        .sidebar-logo small { display: block; font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 2px; }

        .sidebar-office {
            padding: 12px 20px;
            background: rgba(255,255,255,0.08);
            margin: 12px;
            border-radius: 8px;
        }

        .sidebar-office p { font-size: 11px; color: rgba(255,255,255,0.5); }
        .sidebar-office span { font-size: 13px; font-weight: 600; color: #fff; }

        .sidebar-nav { padding: 8px 12px; flex: 1; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 8px 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .nav-link svg { width: 18px; height: 18px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .top-navbar {
            position: fixed;
            top: 0; left: 260px; right: 0;
            height: 64px;
            background: var(--blue-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 99;
            box-shadow: 0 2px 8px rgba(26,86,219,0.3);
        }

        .navbar-title { font-size: 16px; font-weight: 600; color: #fff; }

        .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff;
        }

        .main-content {
            margin-left: 260px;
            margin-top: 64px;
            flex: 1;
            padding: 32px;
            min-height: calc(100vh - 64px);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .stat-card:nth-child(2) { border-left-color: #f59e0b; }
        .stat-card:nth-child(3) { border-left-color: #065f46; }
        .stat-card:nth-child(4) { border-left-color: #5b21b6; }

        .stat-label { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .stat-number { font-size: 32px; font-weight: 700; color: var(--text-dark); line-height: 1; }

        .stat-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 4px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-title { font-size: 22px; font-weight: 700; color: var(--text-dark); }
        .page-subtitle { font-size: 14px; color: var(--text-muted); margin-top: 2px; }

        .btn-primary {
            background: var(--blue-primary);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }

        .btn-primary:hover { background: #1648c5; color: #fff; }

        .btn-secondary {
            background: var(--white);
            color: var(--text-medium);
            border: 1px solid var(--border);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
        }

        .btn-secondary:hover { background: var(--bg-page); color: var(--text-dark); }

        .btn-danger {
            background: #fee2e2; color: #dc2626;
            border: none; padding: 8px 14px;
            border-radius: 6px; font-size: 13px;
            font-weight: 500; cursor: pointer;
            text-decoration: none; transition: background 0.15s;
        }

        .table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); }

        table { width: 100%; border-collapse: collapse; background: var(--white); }

        thead th {
            background: #f3f4f6;
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        tbody td { padding: 14px 16px; font-size: 14px; color: var(--text-medium); border-bottom: 1px solid var(--border); }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; }

        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-pending      { background: #fef3c7; color: #92400e; }
        .badge-in-review    { background: #dbeafe; color: #1e40af; }
        .badge-missing-docs { background: #ffedd5; color: #9a3412; }
        .badge-approved     { background: #d1fae5; color: #065f46; }
        .badge-rejected     { background: #fee2e2; color: #991b1b; }
        .badge-completed    { background: #ede9fe; color: #5b21b6; }
        .badge-active       { background: #d1fae5; color: #065f46; }
        .badge-inactive     { background: #f3f4f6; color: #6b7280; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-medium); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; color: var(--text-dark); background: var(--white); font-family: 'Inter', sans-serif; transition: border-color 0.15s; }
        .form-control:focus { outline: none; border-color: var(--blue-primary); box-shadow: 0 0 0 3px rgba(26,86,219,0.1); }
        .form-error { font-size: 12px; color: #dc2626; margin-top: 4px; }

        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <span> E-Services</span>
        <small>Office Staff Panel</small>
    </div>

    @if(auth()->user()->office)
    <div class="sidebar-office">
        <p>Your Office</p>
        <span>{{ auth()->user()->office->name }}</span>
    </div>
    @endif

    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="{{ route('dashboard.staff') }}" class="nav-link {{ request()->routeIs('dashboard.staff') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        <div class="nav-section-label">Requests</div>
        <a href="{{ route('staff.requests.index') }}" class="nav-link {{ request()->routeIs('staff.requests.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Service Requests
        </a>

        <div class="nav-section-label">Office</div>
        <a href="{{ route('staff.office.edit') }}" class="nav-link {{ request()->routeIs('staff.office.*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
    Office Profile
</a>

        <div class="nav-section-label">Feedback</div>
        <a href="{{ route('staff.feedback.index') }}" class="nav-link {{ request()->routeIs('staff.feedback.*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
    Citizen Feedback
</a>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="width:100%; background:rgba(255,255,255,0.1); color:#fff; border:none; padding:10px 12px; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; text-align:left;">
                 Logout
            </button>
        </form>
    </div>
</aside>

{{-- Navbar --}}
<nav class="top-navbar">
    <span class="navbar-title">@yield('page-title', 'Dashboard')</span>
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <span style="color:#fff; font-size:14px; font-weight:500;">{{ auth()->user()->name }}</span>
    </div>
</nav>

{{-- Main Content --}}
<main class="main-content">
    @if(session('success'))
        <div class="alert-success"> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error"> {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

</body>
</html>