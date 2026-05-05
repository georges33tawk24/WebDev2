<x-layouts.auth-split title="Forgot password — WebDev2" heroVariant="login">
    <h1 class="split-heading">Forgot password?</h1>
    <p class="split-sub">No worries — enter your email and we’ll send you a link to choose a new password.</p>

    <form method="POST" action="{{ route('password.email') }}" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="email">Email address</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email" autofocus>
        </div>

        <button type="submit" class="btn-primary btn-block">Send reset link</button>
    </form>

    <p class="split-footer">
        Remember your password?
        <a href="{{ route('login') }}">Back to login</a>
    </p>
</x-layouts.auth-split>
