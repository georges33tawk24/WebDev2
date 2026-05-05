@props([
    'title' => 'WebDev2',
    'heroVariant' => 'login',
])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="auth-split-body">
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
            <p class="auth-wordmark"><a href="{{ route('login') }}">WebDev2</a></p>

            @if (session('status'))
                <div class="alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
