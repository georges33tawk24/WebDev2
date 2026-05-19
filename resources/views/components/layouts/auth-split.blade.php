@props([
    'title' => 'WebDev2',
    'heroVariant' => 'login',
])
<!DOCTYPE html>
<html lang="{{ $htmlLocale ?? 'en' }}" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <x-auth-locale-styles />
</head>
<body class="auth-split-body {{ ($isRtl ?? false) ? 'is-rtl' : 'is-ltr' }}">
<div class="auth-locale-bar">
    <x-locale-switcher />
</div>
<div class="auth-split-shell">
    <div class="auth-split-grid">
        <aside class="auth-split-visual auth-split-visual--{{ $heroVariant }}" aria-hidden="true">
            @if ($heroVariant === 'register')
                @include('auth.partials.hero-register')
            @else
                @include('auth.partials.hero-login')
            @endif
        </aside>

        <main class="auth-split-panel">
            <p class="auth-wordmark"><a href="{{ route('login') }}">{{ __('ui.app_name') }}</a></p>

            @if (session('status'))
                <div class="alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert-error" role="alert" aria-live="polite">
                    @foreach ($errors->all() as $error)
                        <p class="auth-alert-message">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
<script>
    window.passwordToggleLabels = {
        show: @json(__('ui.auth.show_password')),
        hide: @json(__('ui.auth.hide_password')),
    };
</script>
@vite('resources/js/password-toggle.js')
@stack('scripts')
