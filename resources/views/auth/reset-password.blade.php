<x-layouts.auth-split title="New password — WebDev2" heroVariant="login">
    <h1 class="split-heading">Set a new password</h1>
    <p class="split-sub">Choose a strong password you haven’t used before on this site.</p>

    <form method="POST" action="{{ route('password.update') }}" class="auth-form auth-form--split">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label class="field-label" for="email">Email address</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username">
        </div>

        <label class="field-label" for="password">New password</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password" aria-label="Show password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <label class="field-label" for="password_confirmation">Confirm new password</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password_confirmation" aria-label="Show password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <button type="submit" class="btn-primary btn-block">Update password</button>
    </form>

    <p class="split-footer">
        <a href="{{ route('login') }}">Back to login</a>
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
