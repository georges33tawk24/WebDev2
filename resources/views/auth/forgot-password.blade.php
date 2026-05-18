<x-layouts.auth-split :title="__('ui.auth.forgot_title') . ' — ' . __('ui.app_name')" heroVariant="login">
    <h1 class="split-heading">{{ __('ui.auth.forgot_heading') }}</h1>
    <p class="split-sub">{{ __('ui.auth.forgot_sub_long') }}</p>

    <form method="POST" action="{{ route('password.email') }}" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="email">{{ __('ui.auth.email') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.placeholders.email') }}" required autocomplete="email" autofocus>
        </div>

        <button type="submit" class="btn-primary btn-block">{{ __('ui.auth.send_reset') }}</button>
    </form>

    <p class="split-footer">
        {{ __('ui.auth.remember_password') }}
        <a href="{{ route('login') }}">{{ __('ui.auth.back_to_login') }}</a>
    </p>
</x-layouts.auth-split>
