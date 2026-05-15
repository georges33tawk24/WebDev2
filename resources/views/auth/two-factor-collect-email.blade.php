<x-layouts.auth-flow title="Email for verification — WebDev2">
    <h1 class="twofa-title">Add your email</h1>
    <p class="twofa-sub">
        @if (auth()->user()->socialAccounts()->exists())
            Your sign-in provider did not share a usable email. Enter a real address so we can send your 6-digit verification code.
        @else
            We cannot send a verification code to your current email address. Enter a real address to continue.
        @endif
    </p>

    <form method="POST" action="{{ route('2fa.collect-email.store') }}" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="email">Email address</label>
        <div class="input-shell">
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@gmail.com"
                required
                autocomplete="email"
                autofocus
            >
        </div>

        @error('email')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary btn-block twofa-submit">Continue</button>
    </form>
</x-layouts.auth-flow>
