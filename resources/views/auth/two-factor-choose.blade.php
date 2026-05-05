<x-layouts.auth-flow title="Two-factor — WebDev2">
    <div class="twofa-back-row">
        <form method="POST" action="{{ route('logout') }}" class="twofa-back-form">
            @csrf
            <button type="submit" class="btn-icon-back" aria-label="Sign out and go back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </form>
    </div>

    <h1 class="twofa-title">Two-Factor Authentication</h1>
    <p class="twofa-sub">Add an extra layer of security to your account.</p>

    @error('channel')
        <p class="twofa-inline-error">{{ $message }}</p>
    @enderror

    <div class="method-stack">
        <form method="POST" action="{{ route('2fa.method') }}">
            @csrf
            <button type="submit" name="channel" value="email" class="method-card">
                <span class="method-icon method-icon--mail" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
                </span>
                <span class="method-body">
                    <span class="method-name">Email verification <span class="method-badge">Recommended</span></span>
                    <span class="method-desc">Receive a 6-digit code in your inbox.</span>
                </span>
                <span class="method-chevron" aria-hidden="true">›</span>
            </button>
        </form>

        <form method="POST" action="{{ route('2fa.method') }}">
            @csrf
            <button type="submit" name="channel" value="sms" class="method-card {{ $hasPhone ? '' : 'method-card--disabled' }}" @if(! $hasPhone) disabled @endif>
                <span class="method-icon method-icon--sms" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/><path d="M14.05 9a9 9 0 0 1 .92 3.92"/><path d="M13 2a22 22 0 0 1 9 9"/></svg>
                </span>
                <span class="method-body">
                    <span class="method-name">SMS verification</span>
                    <span class="method-desc">
                        @if ($hasPhone)
                            We’ll text you a 6-digit code.
                        @else
                            Add a phone number when you register to enable SMS.
                        @endif
                    </span>
                </span>
                <span class="method-chevron" aria-hidden="true">›</span>
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('2fa.defer') }}" class="twofa-defer-form">
        @csrf
        <button type="submit" class="btn-text-muted">I’ll do this later</button>
    </form>
</x-layouts.auth-flow>
