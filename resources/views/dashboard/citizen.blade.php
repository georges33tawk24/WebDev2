<!DOCTYPE html>
<html lang="{{ $htmlLocale ?? 'en' }}" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.citizen.dashboard_title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <x-auth-locale-styles />
</head>
<body class="app-body {{ ($isRtl ?? false) ? 'is-rtl' : 'is-ltr' }}">
    <div class="auth-locale-bar">
        <x-locale-switcher />
    </div>
    <main class="auth-page">
        <div class="auth-card">
            <h1 class="auth-title">{{ __('ui.citizen.dashboard_title') }}</h1>
            <p class="auth-subtitle">{{ __('ui.citizen.dashboard_simple_welcome', ['name' => auth()->user()->name]) }}</p>
            <div class="auth-links">
                <a href="{{ route('id-upload') }}">{{ __('ui.citizen.go_to_id_upload') }}</a>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">{{ __('ui.nav.logout') }}</button>
            </form>
        </div>
    </main>
</body>
</html>
