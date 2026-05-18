<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') — E-Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--blue-dark);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo span {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .sidebar-logo small {
            display: block;
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            margin-top: 2px;
            font-weight: 400;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
        }

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

        /* ── Navbar ── */
        .navbar {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            height: 64px;
            background: var(--blue-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 99;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }

        .navbar-title {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
        }

        .btn-logout {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }

        .btn-logout:hover { background: rgba(255,255,255,0.25); color: #fff; }

        /* ── Main Content ── */
        .main-content {
            margin-left: 260px;
            margin-top: 64px;
            flex: 1;
            padding: 32px;
            min-height: calc(100vh - 64px);
        }

        /* ── Cards ── */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }

        /* ── Stat Cards ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .stat-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
        }

        /* ── Buttons ── */
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
            font-family: 'Inter', sans-serif;
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
            font-family: 'Inter', sans-serif;
        }

        .btn-secondary:hover { background: var(--bg-page); color: var(--text-dark); }

        .btn-danger {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
            font-family: 'Inter', sans-serif;
        }

        .btn-danger:hover { background: #fecaca; }

        /* ── Tables ── */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }

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

        tbody td {
            padding: 14px 16px;
            font-size: 14px;
            color: var(--text-medium);
            border-bottom: 1px solid var(--border);
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; }

        /* ── Status Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending       { background: #fef3c7; color: #92400e; }
        .badge-in_review     { background: #dbeafe; color: #1e40af; }
        .badge-missing_docs  { background: #ffedd5; color: #9a3412; }
        .badge-approved      { background: #d1fae5; color: #065f46; }
        .badge-rejected      { background: #fee2e2; color: #991b1b; }
        .badge-completed     { background: #ede9fe; color: #5b21b6; }
        .badge-active        { background: #d1fae5; color: #065f46; }
        .badge-inactive      { background: #f3f4f6; color: #6b7280; }

        /* ── Page Header ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Alerts ── */
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-link--attention {
            background: rgba(251, 191, 36, 0.2);
            color: #fde68a;
        }

        /* ── Forms ── */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-medium);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-dark);
            background: var(--white);
            font-family: 'Inter', sans-serif;
            transition: border-color 0.15s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--blue-primary);
            box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
        }

        .form-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }
    </style>
</head>

<script type="module">
    function showRealtimeToast(title, message) {

        const toast = document.createElement('div');

        toast.style.position = 'fixed';
        toast.style.top = '24px';
        toast.style.right = '24px';
        toast.style.width = '340px';
        toast.style.background = '#ffffff';
        toast.style.border = '1px solid #dbeafe';
        toast.style.borderLeft = '5px solid #2563eb';
        toast.style.borderRadius = '16px';
        toast.style.padding = '18px';
        toast.style.boxShadow = '0 15px 40px rgba(0,0,0,0.12)';
        toast.style.zIndex = '99999';
        toast.style.animation = 'slideIn 0.25s ease';

        toast.innerHTML = `
            <div style="font-weight:700; margin-bottom:8px; color:#111827;">
                ${title}
            </div>

            <div style="font-size:14px; color:#4b5563; line-height:1.6;">
                ${message}
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 6000);
    }

    if (window.Echo) {

        @if(auth()->user()->role?->slug === 'office_staff')

            window.Echo.channel('office.{{ auth()->user()->office_id }}')
                .listen('.request.submitted', (event) => {

                    showRealtimeToast(
                        'New Service Request',
                        `${event.citizen_name} submitted a request for ${event.service_name}`
                    );

                    console.log('Realtime request received:', event);
                });

        @endif

    }

</script>

<style>
@keyframes slideIn {
    from {
        transform: translateX(120%);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<body>

{{-- Sidebar --}}
<aside class="sidebar">
    @php($roleSlug = auth()->user()->role?->slug)

    <div class="sidebar-logo">
        <span>E-Services</span>
        @if ($roleSlug === 'citizen')
            <small>Citizen Portal</small>
        @elseif ($roleSlug === 'office_staff')
            <small>Office Staff Panel</small>
        @else
            <small>Admin Control Panel</small>
        @endif
    </div>

    <nav class="sidebar-nav">
@if ($roleSlug === 'citizen')
    <div class="nav-section-label">Citizen Portal</div>
    <a href="{{ route('citizen.dashboard') }}" class="nav-link {{ request()->routeIs('citizen.dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="{{ route('citizen.services') }}" class="nav-link {{ request()->routeIs('citizen.services*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Browse Services
    </a>
    <a href="{{ route('citizen.requests') }}" class="nav-link {{ request()->routeIs('citizen.requests*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        My Requests
    </a>
    <a href="{{ route('citizen.payments') }}" class="nav-link {{ request()->routeIs('citizen.payments*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        Payments
    </a>
    <a href="{{ route('citizen.maps') }}" class="nav-link {{ request()->routeIs('citizen.maps') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Offices Map
    </a>
    <a href="{{ route('citizen.appointments') }}" class="nav-link {{ request()->routeIs('citizen.appointments*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Appointments
    </a>
    <a href="{{ route('citizen.history') }}" class="nav-link {{ request()->routeIs('citizen.history') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        History
    </a>
    <a href="{{ route('id-upload') }}" class="nav-link {{ request()->routeIs('id-upload*') ? 'active' : '' }} {{ auth()->user()->needsIdDocument() ? 'nav-link--attention' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        {{ auth()->user()->needsIdDocument() ? 'Upload ID (required)' : 'Update ID' }}
    </a>
@elseif ($roleSlug === 'office_staff')
    <div class="nav-section-label">Staff</div>
    <a href="{{ route('dashboard.staff') }}" class="nav-link {{ request()->routeIs('dashboard.staff') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="{{ route('staff.requests.index') }}" class="nav-link {{ request()->routeIs('staff.requests.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Service Requests
    </a>
@else
    <div class="nav-section-label">Overview</div>
    <a href="{{ route('dashboard.admin') }}" class="nav-link {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <div class="nav-section-label">Management</div>
    <a href="{{ route('admin.offices.index') }}" class="nav-link {{ request()->routeIs('admin.offices.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Government Offices
    </a>
    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Users & Accounts
    </a>
    <a href="{{ route('admin.citizens.index') }}" class="nav-link {{ request()->routeIs('admin.citizens.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Citizens
    </a>
    <div class="nav-section-label">Reports</div>
    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Analytics & Reports
    </a>
    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        Categories
    </a>
    <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Services
    </a>
@endif
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout" style="width:100%; display:flex; align-items:center; gap:10px; text-align:left;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </div>
</aside>

{{-- Navbar --}}
<nav class="navbar">
    <span class="navbar-title">@yield('page-title', 'Dashboard')</span>

    @php
        $unreadNotifications = \App\Models\UserNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        $unreadCount = \App\Models\UserNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
    @endphp

    <div class="navbar-right">
        <div style="position:relative;">
            <a href="{{ route('notifications.index') }}"
               style="
                    width:40px;
                    height:40px;
                    border-radius:50%;
                    background:rgba(255,255,255,0.16);
                    color:white;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    text-decoration:none;
                    position:relative;
               ">
                🔔

                @if($unreadCount > 0)
                    <span style="
                        position:absolute;
                        top:-5px;
                        right:-5px;
                        background:#ef4444;
                        color:white;
                        min-width:20px;
                        height:20px;
                        border-radius:999px;
                        font-size:11px;
                        font-weight:700;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        padding:0 6px;
                    ">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
        </div>

        <div class="navbar-user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            {{ auth()->user()->name }}
        </div>
    </div>
</nav>

{{-- Main Content --}}
<main class="main-content">
    @if(session('success'))
        <div class="alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">❌ {{ session('error') }}</div>
    @endif
    @if(session('status') && ! session('success') && ! session('error'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif
    @if($roleSlug === 'citizen' && auth()->user()->needsIdDocument())
        <div class="alert-warning">
            <span>Your account does not have a valid ID on file. Upload your ID to use the citizen portal.</span>
            <a href="{{ route('id-upload') }}" class="btn-primary" style="padding: 8px 14px; font-size: 13px;">Upload ID now</a>
        </div>
    @endif

    @yield('content')
</main>

<script type="module">
    function showRealtimeToast(title, message) {
        const toast = document.createElement('div');

        toast.style.position = 'fixed';
        toast.style.top = '90px';
        toast.style.right = '24px';
        toast.style.width = '360px';
        toast.style.background = '#ffffff';
        toast.style.border = '1px solid #dbeafe';
        toast.style.borderLeft = '5px solid #2563eb';
        toast.style.borderRadius = '16px';
        toast.style.padding = '18px';
        toast.style.boxShadow = '0 15px 40px rgba(0,0,0,0.14)';
        toast.style.zIndex = '99999';

        toast.innerHTML = `
            <div style="font-weight:700; margin-bottom:8px; color:#111827;">
                ${title}
            </div>
            <div style="font-size:14px; color:#4b5563; line-height:1.6;">
                ${message}
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 6000);
    }

    if (window.Echo) {
        @if(auth()->user()->role?->slug === 'office_staff')
            window.Echo.channel('office.{{ auth()->user()->office_id }}')
                .listen('.request.submitted', (event) => {
                    showRealtimeToast(
                        'New Service Request',
                        `${event.citizen_name} submitted a request for ${event.service_name}`
                    );
                });
        @endif

        @if(auth()->user()->role?->slug === 'citizen')
            window.Echo.channel('citizen.{{ auth()->id() }}')
                .listen('.request.status.updated', (event) => {
                    showRealtimeToast(
                        'Request Status Updated',
                        event.message
                    );
                });
        @endif
    }
</script>

</body>
</html>