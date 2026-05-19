@props(['title' => 'WebDev2'])
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
<body class="auth-flow-body {{ ($isRtl ?? false) ? 'is-rtl' : 'is-ltr' }}">
<div class="auth-locale-bar">
    <x-locale-switcher />
</div>
<div class="auth-flow-shell">
    <main class="auth-flow-card">
        <p class="auth-wordmark auth-wordmark--center"><a href="{{ route('login') }}">{{ __('ui.app_name') }}</a></p>

        @if (session('status'))
            <div class="alert-success alert-banner">{{ session('status') }}</div>
        @endif

        @php
            $bannerErrors = collect($errors->getMessages())
                ->except(['code', 'resend', 'channel'])
                ->flatten();
        @endphp
        @if ($bannerErrors->isNotEmpty())
            <div class="alert-error alert-banner">
                <ul>
                    @foreach ($bannerErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</div>
@stack('scripts')
</body>
</html>
