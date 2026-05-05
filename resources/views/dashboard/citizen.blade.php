<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard</title>
    @vite(['resources/css/app.css'])
</head>
<body class="app-body">
    <main class="auth-page">
        <div class="auth-card">
            <h1 class="auth-title">Citizen Dashboard</h1>
            <p class="auth-subtitle">Welcome, {{ auth()->user()->name }}.</p>
            <div class="auth-links">
                <a href="{{ route('id-upload') }}">Go to ID Upload page</a>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">Logout</button>
            </form>
        </div>
    </main>
</body>
</html>
