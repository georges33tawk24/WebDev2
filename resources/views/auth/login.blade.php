<x-layouts.auth-split title="Login — WebDev2" heroVariant="login">
    <h1 class="split-heading">Welcome back! <span class="split-heading-wave" aria-hidden="true">👋</span></h1>
    <p class="split-sub">Login to continue to your account.</p>

    @include('auth.partials.social-buttons')

    <div class="auth-divider"><span>or continue with email</span></div>

    <form method="POST" action="{{ route('login.attempt') }}" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="email">Email address</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="username">
        </div>

        <label class="field-label" for="password">Password</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password" aria-label="Show password">
                <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <div class="auth-row-between">
            <label class="inline-checkbox">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="auth-inline-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary btn-block">Login</button>
    </form>

    <p class="split-footer">
        Don’t have an account?
        <a href="{{ route('register') }}">Register</a>
    </p>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-toggle-pass]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('aria-controls');
                    var input = document.getElementById(id);
                    if (!input) return;
                    var next = input.type === 'password' ? 'text' : 'password';
                    input.type = next;
                    btn.setAttribute('aria-label', next === 'password' ? 'Show password' : 'Hide password');
                });
            });
        </script>
    @endpush
</x-layouts.auth-split>
