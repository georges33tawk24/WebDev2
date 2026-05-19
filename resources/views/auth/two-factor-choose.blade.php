<x-layouts.auth-flow title="{{ __('ui.auth.choose_2fa_title') }} — {{ __('ui.app_name') }}">
    <div class="twofa-back-row">
        <form method="POST" action="{{ route('logout') }}" class="twofa-back-form">
            @csrf
            <button type="submit" class="btn-icon-back" aria-label="{{ __('ui.auth.sign_out_back') }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </form>
    </div>

    <h1 class="twofa-title">{{ __('ui.auth.choose_2fa_title') }}</h1>
    <p class="twofa-sub">{{ __('ui.auth.choose_2fa_sub') }}</p>

    <form method="POST" action="{{ route('2fa.channel') }}" class="auth-form twofa-channel-form">
        @csrf

        <fieldset class="twofa-channel-options">
            <legend class="sr-only">{{ __('ui.auth.choose_2fa_title') }}</legend>

            @if ($emailAvailable)
                <label class="twofa-channel-option">
                    <input
                        type="radio"
                        name="channel"
                        value="email"
                        @checked(old('channel', $preferredChannel) === 'email')
                        required
                    >
                    <span class="twofa-channel-option-body">
                        <strong>{{ __('ui.auth.channel_email') }}</strong>
                        <span>{{ ($hasDeliverableEmail ?? false) ? __('ui.auth.channel_email_hint') : __('ui.auth.channel_email_collect_hint') }}</span>
                    </span>
                </label>
            @endif

            @if ($smsAvailable)
                <label class="twofa-channel-option">
                    <input
                        type="radio"
                        name="channel"
                        value="sms"
                        @checked(old('channel', $preferredChannel) === 'sms')
                        @if (! $emailAvailable) required @endif
                    >
                    <span class="twofa-channel-option-body">
                        <strong>{{ __('ui.auth.channel_sms') }}</strong>
                        <span>{{ __('ui.auth.channel_sms_hint') }}</span>
                    </span>
                </label>
            @endif
        </fieldset>

        @error('channel')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary btn-block twofa-submit">{{ __('ui.auth.send_code') }}</button>
    </form>

</x-layouts.auth-flow>
