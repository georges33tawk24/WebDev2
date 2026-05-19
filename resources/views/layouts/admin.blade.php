<!DOCTYPE html>
<html lang="{{ $htmlLocale ?? 'en' }}" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('ui.nav.admin_panel')) — {{ __('ui.eservices') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        html[dir="rtl"] body,
        body.is-rtl {
            font-family: 'Cairo', 'Inter', ui-sans-serif, system-ui, sans-serif;
            direction: rtl;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--blue-dark);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            inset-inline-start: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: start;
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
            text-align: start;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
            text-align: start;
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
            inset-inline-start: 260px;
            inset-inline-end: 0;
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

        /* Language toggle — top bar, left of your name */
        .navbar .locale-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 8px;
            border-radius: 999px;
            text-decoration: none;
            color: #fff;
            cursor: pointer;
            user-select: none;
            background: rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.28);
            transition: background 0.15s;
        }

        .navbar .locale-toggle:hover {
            background: rgba(0, 0, 0, 0.28);
        }

        .navbar .locale-toggle:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }

        .navbar .locale-toggle__label {
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.04em;
            opacity: 0.5;
            min-width: 1.35rem;
            text-align: center;
        }

        .navbar .locale-toggle--en .locale-toggle__label--en,
        .navbar .locale-toggle--ar .locale-toggle__label--ar {
            opacity: 1;
        }

        .navbar .locale-toggle__track {
            position: relative;
            flex-shrink: 0;
            width: 44px;
            height: 24px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.4);
        }

        .navbar .locale-toggle__thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
            transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar .locale-toggle--ar .locale-toggle__thumb {
            transform: translateX(20px);
        }

        .navbar-icon-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            color: #fff;
            background: rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.28);
            text-decoration: none;
            transition: background 0.15s;
        }

        .navbar-icon-btn:hover {
            background: rgba(0, 0, 0, 0.28);
            color: #fff;
        }

        .navbar-icon-btn svg {
            width: 22px;
            height: 22px;
        }

        .navbar-icon-btn__badge {
            position: absolute;
            top: -4px;
            inset-inline-end: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 18px;
            text-align: center;
        }

        .chat-unread-pill {
            display: inline-block;
            margin-inline-start: 8px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            vertical-align: middle;
        }

        .chat-thread {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
            max-height: 420px;
            overflow-y: auto;
            background: #f9fafb;
        }

        .chat-thread__empty {
            color: #6b7280;
            text-align: center;
            margin: 0;
        }

        .chat-bubble-row {
            display: flex;
            margin-bottom: 14px;
        }

        .chat-bubble-row--mine {
            justify-content: flex-end;
        }

        .chat-bubble-row--theirs {
            justify-content: flex-start;
        }

        .chat-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
        }

        .chat-bubble--mine {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .chat-bubble--theirs {
            background: #fff;
            color: #111827;
        }

        .chat-bubble__author {
            font-size: 13px;
            opacity: 0.8;
            margin: 0 0 6px;
        }

        .chat-bubble__text {
            line-height: 1.5;
            margin: 0;
        }

        .chat-bubble__time {
            font-size: 12px;
            opacity: 0.7;
            margin: 8px 0 0;
        }

        .chat-reply-form {
            margin-top: 0;
        }

        .navbar-notifications {
            position: relative;
        }

        .navbar-notifications__panel {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            inset-inline-end: 0;
            width: min(360px, 92vw);
            max-height: 400px;
            overflow: auto;
            background: #fff;
            color: #111827;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
            z-index: 200;
        }

        .navbar-notifications--open .navbar-notifications__panel {
            display: block;
        }

        .navbar-notifications__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 14px;
        }

        .navbar-notifications__mark {
            font-size: 12px;
            color: #2563eb;
            text-decoration: none;
        }

        .navbar-notifications__list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .navbar-notifications-item {
            padding: 12px 14px;
            border-bottom: 1px solid #f3f4f6;
        }

        .navbar-notifications-item--unread {
            background: #eff6ff;
        }

        .navbar-notifications-item--action {
            cursor: pointer;
        }

        .navbar-notifications-item--action:hover {
            background: #f3f4f6;
        }

        .navbar-notifications-item--unread.navbar-notifications-item--action:hover {
            background: #dbeafe;
        }

        .navbar-notifications-item p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #4b5563;
        }

        .navbar-notifications-item small {
            color: #9ca3af;
            font-size: 11px;
        }

        .navbar-notifications-empty {
            padding: 20px 14px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
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
            margin-inline-start: 260px;
            margin-top: 64px;
            flex: 1;
            padding: 32px;
            min-height: calc(100vh - 64px);
            text-align: start;
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
            text-align: start;
            vertical-align: middle;
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
            text-align: start;
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; }

        .data-table {
            table-layout: fixed;
        }

        .data-table .col-primary {
            width: 46%;
            word-break: break-word;
        }

        .data-table .col-secondary {
            width: 28%;
        }

        .data-table .col-price {
            width: 14%;
            text-align: end;
        }

        .data-table thead th.col-price {
            text-align: end;
        }

        .data-table .col-count {
            width: 12rem;
            text-align: center;
        }

        .data-table thead th.col-count {
            text-align: center;
        }

        .data-table td.col-count {
            text-align: center;
        }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.5rem;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
        }

        .count-badge--blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .count-badge--green {
            background: #d1fae5;
            color: #065f46;
        }

        html[dir="rtl"] thead th,
        html[dir="rtl"] tbody td {
            letter-spacing: 0;
        }

        html[dir="rtl"] .data-table .col-price,
        html[dir="rtl"] .data-table thead th.col-price {
            text-align: start;
        }

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
        .badge-scheduled     { background: #dbeafe; color: #1e40af; }
        .badge-cancelled     { background: #f3f4f6; color: #6b7280; }
        .badge-rescheduled   { background: #ffedd5; color: #9a3412; }
        .badge-paid          { background: #d1fae5; color: #065f46; }
        .badge-failed        { background: #fee2e2; color: #991b1b; }
        .badge-active        { background: #d1fae5; color: #065f46; }
        .badge-inactive      { background: #f3f4f6; color: #6b7280; }

        /* ── Centered form pages (create / edit) ── */
        .form-page {
            width: 100%;
            max-width: 42rem;
            margin-inline: auto;
        }

        .form-page--wide {
            max-width: 52rem;
        }

        .form-page .page-header {
            margin-bottom: 24px;
        }

        .form-page .card {
            width: 100%;
        }

        .form-page .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 8px;
        }

        html[dir="rtl"] .form-page .form-actions {
            flex-direction: row-reverse;
        }

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

        .dashboard-intro__title {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .dashboard-intro__subtitle {
            font-size: 18px;
            color: var(--text-muted);
        }

        .report-chart {
            position: relative;
            height: 240px;
            max-height: 240px;
            width: 100%;
        }

        .report-chart canvas {
            display: block;
            max-width: 100% !important;
            max-height: 100% !important;
        }

        .reports-charts-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 900px) {
            .reports-charts-grid {
                grid-template-columns: 1fr;
            }
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

        select.form-control {
            width: 100%;
            padding-inline: 14px 2.5rem;
            appearance: auto;
        }

        html[dir="rtl"] select.form-control {
            padding-inline: 2.5rem 14px;
        }

        .password-input-wrap {
            position: relative;
        }

        .password-input-wrap .form-control {
            padding-inline-end: 2.75rem;
        }

        html[dir="rtl"] .password-input-wrap .form-control {
            padding-inline-end: 14px;
            padding-inline-start: 2.75rem;
        }

        .password-toggle-btn {
            position: absolute;
            top: 50%;
            inset-inline-end: 10px;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: var(--text-medium);
            cursor: pointer;
        }

        .password-toggle-btn:hover {
            color: var(--text-dark);
            background: rgba(0, 0, 0, 0.04);
        }

        .password-toggle-btn:focus-visible {
            outline: 2px solid var(--blue-primary);
            outline-offset: 2px;
        }

        [data-toggle-pass] .password-toggle-icon--visible {
            display: none;
        }

        [data-toggle-pass] .password-toggle-icon--hidden {
            display: block;
        }

        [data-toggle-pass].is-password-visible .password-toggle-icon--visible {
            display: block;
        }

        [data-toggle-pass].is-password-visible .password-toggle-icon--hidden {
            display: none;
        }

        .form-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }

        html[dir="rtl"] .sidebar,
        body.is-rtl .sidebar {
            left: auto;
            right: 0;
        }

        html[dir="rtl"] .navbar,
        body.is-rtl .navbar {
            left: 0;
            right: 260px;
        }

        html[dir="rtl"] .main-content,
        body.is-rtl .main-content {
            margin-left: 0;
            margin-right: 260px;
        }

        html[dir="rtl"] .sidebar-nav,
        html[dir="rtl"] .sidebar-footer,
        body.is-rtl .sidebar-nav,
        body.is-rtl .sidebar-footer {
            direction: rtl;
        }

        html[dir="rtl"] .nav-link,
        body.is-rtl .nav-link {
            flex-direction: row;
            justify-content: flex-start;
        }

        html[dir="rtl"] .navbar-user,
        body.is-rtl .navbar-user {
            flex-direction: row-reverse;
        }

        html[dir="rtl"] .btn-logout,
        body.is-rtl .btn-logout {
            flex-direction: row;
            justify-content: flex-start;
            text-align: start;
        }

        html[dir="rtl"] .alert-warning {
            flex-direction: row-reverse;
        }

        /* ── Pagination ── */
        .eservices-pagination {
            margin-top: 20px;
        }

        .eservices-pagination__list {
            display: flex;
            align-items: stretch;
            flex-wrap: wrap;
            gap: 0;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .eservices-pagination__list li {
            display: flex;
        }

        .eservices-pagination__item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1;
            text-decoration: none;
            color: var(--text-medium);
            background: var(--white);
            border: 1px solid var(--border);
            margin-inline-start: -1px;
            box-sizing: border-box;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }

        .eservices-pagination__list li:first-child .eservices-pagination__item {
            margin-inline-start: 0;
            border-start-start-radius: 8px;
            border-end-start-radius: 8px;
        }

        .eservices-pagination__list li:last-child .eservices-pagination__item {
            border-start-end-radius: 8px;
            border-end-end-radius: 8px;
        }

        .eservices-pagination__item:hover:not(.eservices-pagination__item--active):not(.eservices-pagination__item--disabled):not(.eservices-pagination__item--dots) {
            background: #f3f4f6;
            color: var(--text-dark);
            z-index: 1;
            position: relative;
        }

        .eservices-pagination__item--active {
            background: var(--blue-primary);
            border-color: var(--blue-primary);
            color: #fff;
            z-index: 2;
            position: relative;
        }

        .eservices-pagination__item--disabled {
            color: #9ca3af;
            background: #f9fafb;
            cursor: not-allowed;
        }

        .eservices-pagination__item--dots {
            cursor: default;
            background: var(--white);
        }

        html[dir="rtl"] .eservices-pagination__list {
            flex-direction: row-reverse;
        }
    </style>
</head>
<body class="{{ ($isRtl ?? false) ? 'is-rtl' : 'is-ltr' }}">

{{-- Sidebar --}}
<aside class="sidebar">
    @php($roleSlug = auth()->user()->role?->slug)

    <div class="sidebar-logo">
        <span>{{ __('ui.eservices') }}</span>
        @if ($roleSlug === 'citizen')
            <small>{{ __('ui.nav.citizen_portal') }}</small>
        @elseif ($roleSlug === 'office_staff')
            <small>{{ __('ui.nav.staff_panel') }}</small>
        @else
            <small>{{ __('ui.nav.admin_panel') }}</small>
        @endif
    </div>

    <nav class="sidebar-nav">
@if ($roleSlug === 'citizen')
    <div class="nav-section-label">{{ __('ui.overview') }}</div>
    <a href="{{ route('citizen.dashboard') }}" class="nav-link {{ request()->routeIs('citizen.dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        {{ __('ui.nav.dashboard') }}
    </a>
    <div class="nav-section-label">{{ __('ui.citizen_services_section') }}</div>
    <a href="{{ route('citizen.services') }}" class="nav-link {{ request()->routeIs('citizen.services*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        {{ __('ui.nav.browse_services') }}
    </a>
    <a href="{{ route('citizen.requests') }}" class="nav-link {{ request()->routeIs('citizen.requests*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        {{ __('ui.nav.my_requests') }}
    </a>
    <a href="{{ route('citizen.chats.index') }}" class="nav-link {{ request()->routeIs('citizen.chats*') || request()->routeIs('citizen.chat') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        {{ __('ui.nav.chats') }}
    </a>
    <div class="nav-section-label">{{ __('ui.citizen_payments_section') }}</div>
    <a href="{{ route('citizen.payments') }}" class="nav-link {{ request()->routeIs('citizen.payments*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        {{ __('ui.nav.payments') }}
    </a>
    <a href="{{ route('citizen.history') }}" class="nav-link {{ request()->routeIs('citizen.history*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ __('ui.nav.history') }}
    </a>
    <div class="nav-section-label">{{ __('ui.citizen_explore_section') }}</div>
    <a href="{{ route('citizen.maps') }}" class="nav-link {{ request()->routeIs('citizen.maps') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        {{ __('ui.nav.offices_map') }}
    </a>
    <a href="{{ route('citizen.appointments') }}" class="nav-link {{ request()->routeIs('citizen.appointments*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        {{ __('ui.nav.appointments') }}
    </a>
    <div class="nav-section-label">{{ __('ui.account_section') }}</div>
    <a href="{{ route('id-upload') }}" class="nav-link {{ request()->routeIs('id-upload*') ? 'active' : '' }} {{ auth()->user()->needsIdDocument() ? 'nav-link--attention' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        {{ auth()->user()->needsIdDocument() ? __('ui.nav.upload_id_required') : __('ui.nav.update_id') }}
    </a>
@elseif ($roleSlug === 'office_staff')
    <div class="nav-section-label">{{ __('ui.overview') }}</div>
    <a href="{{ route('dashboard.staff') }}" class="nav-link {{ request()->routeIs('dashboard.staff') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        {{ __('ui.nav.dashboard') }}
    </a>
    <div class="nav-section-label">{{ __('ui.requests_section') }}</div>
    <a href="{{ route('staff.requests.index') }}" class="nav-link {{ request()->routeIs('staff.requests.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        {{ __('ui.nav.service_requests') }}
    </a>
    <a href="{{ route('staff.chats.index') }}" class="nav-link {{ request()->routeIs('staff.chats.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        {{ __('ui.nav.chats') }}
    </a>
    <a href="{{ route('staff.appointments.index') }}" class="nav-link {{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        {{ __('ui.nav.appointments') }}
    </a>
    <div class="nav-section-label">{{ __('ui.office_section') }}</div>
    <a href="{{ route('staff.office.edit') }}" class="nav-link {{ request()->routeIs('staff.office.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
        {{ __('ui.nav.office_profile') }}
    </a>
    <div class="nav-section-label">{{ __('ui.catalog_section') }}</div>
    <a href="{{ route('staff.categories.index') }}" class="nav-link {{ request()->routeIs('staff.categories.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        {{ __('ui.nav.categories') }}
    </a>
    <a href="{{ route('staff.services.index') }}" class="nav-link {{ request()->routeIs('staff.services.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        {{ __('ui.nav.services') }}
    </a>
    <div class="nav-section-label">{{ __('ui.feedback_section') }}</div>
    <a href="{{ route('staff.feedback.index') }}" class="nav-link {{ request()->routeIs('staff.feedback.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        {{ __('ui.nav.citizen_feedback') }}
    </a>
@else
    <div class="nav-section-label">{{ __('ui.overview') }}</div>
    <a href="{{ route('dashboard.admin') }}" class="nav-link {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        {{ __('ui.nav.dashboard') }}
    </a>
    <div class="nav-section-label">{{ __('ui.management') }}</div>
    <a href="{{ route('admin.offices.index') }}" class="nav-link {{ request()->routeIs('admin.offices.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        {{ __('ui.nav.government_offices') }}
    </a>
    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        {{ __('ui.nav.users_accounts') }}
    </a>
    <a href="{{ route('admin.citizens.index') }}" class="nav-link {{ request()->routeIs('admin.citizens.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        {{ __('ui.nav.citizens') }}
    </a>
    <a href="{{ route('admin.requests.index') }}" class="nav-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        {{ __('ui.nav.all_requests') }}
    </a>
    <div class="nav-section-label">{{ __('ui.reports') }}</div>
    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        {{ __('ui.nav.analytics_reports') }}
    </a>
    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        {{ __('ui.nav.categories') }}
    </a>
    <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        {{ __('ui.nav.services') }}
    </a>
@endif
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout" style="width:100%; display:flex; align-items:center; gap:10px; text-align:start;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                {{ __('ui.nav.logout') }}
            </button>
        </form>
    </div>
</aside>

{{-- Navbar --}}
<nav class="navbar">
    <span class="navbar-title">@yield('page-title', 'Dashboard')</span>
    <div class="navbar-right">
        <x-locale-switcher />
        @if ($roleSlug === 'office_staff' || $roleSlug === 'citizen')
            <a href="{{ $roleSlug === 'office_staff' ? route('staff.chats.index') : route('citizen.chats.index') }}" class="navbar-icon-btn" title="{{ __('ui.nav.chats') }}" aria-label="{{ __('ui.nav.chats') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                @php($navbarUnreadChats = $roleSlug === 'office_staff' ? staff_unread_chat_count() : citizen_unread_chat_count())
                @if ($navbarUnreadChats > 0)
                    <span class="navbar-icon-btn__badge">{{ localized_digits((string) $navbarUnreadChats) }}</span>
                @endif
            </a>
        @endif
        <div class="navbar-notifications" id="navbar-notifications">
            <button type="button" class="navbar-icon-btn" id="navbar-notifications-toggle" aria-label="{{ __('ui.nav.notifications') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="navbar-icon-btn__badge" id="navbar-notifications-badge" hidden>0</span>
            </button>
            <div class="navbar-notifications__panel">
                <div class="navbar-notifications__header">
                    <span>{{ __('ui.nav.notifications') }}</span>
                    <a href="#" class="navbar-notifications__mark" id="navbar-notifications-mark-all">{{ __('ui.notifications.mark_all_read') }}</a>
                </div>
                <ul class="navbar-notifications__list" id="navbar-notifications-list"></ul>
            </div>
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
            <span>{{ __('ui.alerts.id_required') }}</span>
            <a href="{{ route('id-upload') }}" class="btn-primary" style="padding: 8px 14px; font-size: 13px;">{{ __('ui.alerts.upload_id_now') }}</a>
        </div>
    @endif

    @if(filled(config('services.webpush.public_key')))
        <div id="push-enable-banner" class="alert-warning" style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;" hidden>
            <span id="push-enable-message">{{ __('ui.notifications.push_enable_prompt') }}</span>
            <button type="button" id="push-enable-btn" class="btn-primary" style="padding:8px 14px; font-size:13px; white-space:nowrap;">
                {{ __('ui.notifications.push_enable_button') }}
            </button>
        </div>
    @endif

    @yield('content')
</main>

<script>
    window.__PUSH_CONFIG__ = {
        publicKeyUrl: @json(route('api.push.public-key')),
        subscribeUrl: @json(route('api.push.subscribe')),
        csrf: @json(csrf_token()),
        promptLabel: @json(__('ui.notifications.push_enable_prompt')),
        deniedLabel: @json(__('ui.notifications.push_denied_hint')),
        sslLabel: @json(__('ui.notifications.push_ssl_hint')),
        enabledLabel: @json(__('ui.notifications.push_enabled')),
    };
</script>
<script>
    window.__NOTIFICATIONS_CONFIG__ = {
        pollUrl: @json(route('api.notifications.index')),
        streamUrl: @json(live_updates_stream_url()),
        pollSeconds: @json((int) config('services.live_updates.poll_seconds', 5)),
        markAllUrl: @json(route('api.notifications.read-all')),
        markReadUrlTemplate: @json(url('/api/notifications/__ID__/read')),
        markOneLabel: @json(__('ui.notifications.mark_one_read')),
        csrf: @json(csrf_token()),
        emptyLabel: @json(__('ui.notifications.empty')),
    };
    window.__LIVE_CONFIG__ = {
        streamUrl: @json(live_updates_stream_url()),
        trackRequests: true,
    };
    const notificationsRoot = document.getElementById('navbar-notifications');
    const notificationsToggle = document.getElementById('navbar-notifications-toggle');
    notificationsToggle?.addEventListener('click', function (event) {
        event.stopPropagation();
        notificationsRoot?.classList.toggle('navbar-notifications--open');
    });
    document.addEventListener('click', function (event) {
        if (notificationsRoot && !notificationsRoot.contains(event.target)) {
            notificationsRoot.classList.remove('navbar-notifications--open');
        }
    });
</script>
<script>
    window.passwordToggleLabels = {
        show: @json(__('ui.auth.show_password')),
        hide: @json(__('ui.auth.hide_password')),
    };
</script>
@vite(['resources/js/navbar-notifications.js', 'resources/js/push-notifications.js', 'resources/js/password-toggle.js'])
@stack('scripts')
</body>
</html>