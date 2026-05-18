<x-layouts.auth-flow :title="__('ui.auth.collect_email') . ' — ' . __('ui.app_name')">
    <h1 class="twofa-title">{{ __('ui.auth.collect_email') }}</h1>
    <p class="twofa-sub">
        @if (auth()->user()->socialAccounts()->exists())
            {{ __('ui.auth.collect_email_social_sub') }}
        @else
            {{ __('ui.auth.collect_email_invalid_sub') }}
        @endif
    </p>

    <form method="POST" action="{{ route('2fa.collect-email.store') }}" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="email">{{ __('ui.auth.email') }}</label>
        <div class="input-shell">
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="{{ __('ui.placeholders.email_gmail') }}"
                required
                autocomplete="email"
                autofocus
            >
        </div>

        @error('email')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary btn-block twofa-submit">{{ __('ui.auth.continue') }}</button>
    </form>
</x-layouts.auth-flow>
