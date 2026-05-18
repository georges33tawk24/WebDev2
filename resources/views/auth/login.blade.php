<x-layouts.auth-split title="{{ __('ui.auth.login') }} — {{ __('ui.app_name') }}" heroVariant="login">
    <h1 class="split-heading">{{ __('ui.auth.welcome_back') }} <span class="split-heading-wave" aria-hidden="true">👋</span></h1>
    <p class="split-sub">{{ __('ui.auth.login_sub') }}</p>

    @include('auth.partials.social-buttons')

    <div class="auth-divider"><span>{{ __('ui.auth.or_email') }}</span></div>

    <form method="POST" action="{{ route('login.attempt') }}" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="email">{{ __('ui.auth.email') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--start" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.placeholders.email') }}" required autocomplete="username">
        </div>

        <label class="field-label" for="password">{{ __('ui.auth.password') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--start" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password" type="password" name="password" placeholder="{{ __('ui.placeholders.password') }}" required autocomplete="current-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password" aria-label="{{ __('ui.auth.show_password') }}">
                <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <div class="auth-row-between">
            <label class="inline-checkbox">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                {{ __('ui.auth.remember_me') }}
            </label>
            <a href="{{ route('password.request') }}" class="auth-inline-link">{{ __('ui.auth.forgot_password') }}</a>
        </div>

        <button type="submit" class="btn-primary btn-block">{{ __('ui.auth.login') }}</button>
    </form>

    <p class="split-footer">
        {{ __('ui.auth.no_account') }}
        <a href="{{ route('register') }}">{{ __('ui.auth.register_link') }}</a>
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
                    btn.setAttribute('aria-label', next === 'password' ? @json(__('ui.auth.show_password')) : @json(__('ui.auth.hide_password')));
                });
            });
        </script>
    @endpush
</x-layouts.auth-split>
