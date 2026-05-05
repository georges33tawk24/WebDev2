<x-layouts.auth-split title="Register — WebDev2" heroVariant="register">
    <h1 class="split-heading">Create your account</h1>
    <p class="split-sub">Join us and start your secure journey.</p>

    @include('auth.partials.social-buttons')

    <div class="auth-divider"><span>or register with email</span></div>

    <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="name">Full name</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Jane Doe" required autocomplete="name">
        </div>

        <label class="field-label" for="email">Email address</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
        </div>

        <label class="field-label" for="phone">Phone <span class="field-hint">(optional, for SMS 2FA)</span></label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="+961 XX XXX XXX" autocomplete="tel">
        </div>

        <label class="field-label" for="id_document">ID document</label>
        <div class="input-shell input-shell--file">
            <input id="id_document" type="file" name="id_document" accept=".jpg,.jpeg,.png,.pdf" required class="input-file-native">
        </div>
        <p class="field-hint-block">JPG, PNG, or PDF — max 5&nbsp;MB.</p>

        <label class="field-label" for="password">Password</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password" aria-label="Show password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <label class="field-label" for="password_confirmation">Confirm password</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password_confirmation" aria-label="Show password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <label class="inline-checkbox terms-checkbox">
            <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
            I agree to the <span class="auth-muted-link">Terms of Service</span> and <span class="auth-muted-link">Privacy Policy</span>
        </label>

        <button type="submit" class="btn-primary btn-block">Register</button>
    </form>

    <p class="split-footer">
        Already have an account?
        <a href="{{ route('login') }}">Login</a>
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
