@props(['title' => 'WebDev2'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="auth-flow-body">
<div class="auth-flow-shell">
    <main class="auth-flow-card">
        <p class="auth-wordmark auth-wordmark--center"><a href="{{ route('login') }}">WebDev2</a></p>

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
@stack('scripts')
</body>
</html>
