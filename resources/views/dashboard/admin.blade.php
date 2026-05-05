<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css'])
</head>
<body class="app-body">
    <main class="auth-page">
        <div class="auth-card">
            <h1 class="auth-title">Admin Dashboard</h1>
            <p class="auth-subtitle">Welcome, {{ auth()->user()->name }}.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">Logout</button>
            </form>
        </div>
    </main>
</body>
</html>
