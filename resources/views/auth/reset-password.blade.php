<x-layouts.auth-split :title="__('ui.auth.reset_title') . ' — ' . __('ui.app_name')" heroVariant="login">
    <h1 class="split-heading">{{ __('ui.auth.set_new_password') }}</h1>
    <p class="split-sub">{{ __('ui.auth.new_password_sub') }}</p>

    <form method="POST" action="{{ route('password.update') }}" class="auth-form auth-form--split">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label class="field-label" for="email">{{ __('ui.auth.email') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username">
        </div>

        <label class="field-label" for="password">{{ __('ui.auth.reset_password') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password" type="password" name="password" placeholder="{{ __('ui.placeholders.password') }}" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password" aria-label="{{ __('ui.auth.show_password') }}" aria-pressed="false">
                <x-password-toggle-icons />
            </button>
        </div>

        <label class="field-label" for="password_confirmation">{{ __('ui.auth.confirm_new_password') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('ui.placeholders.password') }}" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password_confirmation" aria-label="{{ __('ui.auth.show_password') }}" aria-pressed="false">
                <x-password-toggle-icons />
            </button>
        </div>

        <button type="submit" class="btn-primary btn-block">{{ __('ui.auth.update_password') }}</button>
    </form>

    <p class="split-footer">
        <a href="{{ route('login') }}">{{ __('ui.auth.back_to_login') }}</a>
    </p>

</x-layouts.auth-split>
