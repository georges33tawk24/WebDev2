<x-layouts.auth-flow :title="__('ui.auth.collect_phone') . ' — ' . __('ui.app_name')">
    <h1 class="twofa-title">{{ __('ui.auth.collect_phone') }}</h1>
    <p class="twofa-sub">{{ __('ui.auth.collect_phone_sub') }}</p>

    <form method="POST" action="{{ route('2fa.collect-phone.store') }}" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="phone">{{ __('ui.auth.phone') }}</label>
        <div class="input-shell">
            <input
                id="phone"
                type="tel"
                name="phone"
                value="{{ old('phone') }}"
                placeholder="+96170123456"
                required
                autocomplete="tel"
                autofocus
            >
        </div>

        @error('phone')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary btn-block twofa-submit">{{ __('ui.auth.continue') }}</button>
    </form>
</x-layouts.auth-flow>
