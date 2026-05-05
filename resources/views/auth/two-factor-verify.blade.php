<x-layouts.auth-flow title="Verify — WebDev2">
    <div class="twofa-back-row">
        <form method="POST" action="{{ route('2fa.reset-method') }}" class="twofa-back-form">
            @csrf
            <button type="submit" class="btn-icon-back" aria-label="Choose another method">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </form>
    </div>

    @if ($twoFactorChannel === 'sms')
        <h1 class="twofa-title">Verify with SMS</h1>
        <p class="twofa-sub">Enter the code we sent to <strong>{{ $maskedPhone }}</strong>.</p>
    @else
        <h1 class="twofa-title">Verify your email</h1>
        <p class="twofa-sub">Enter the code we sent to <strong>{{ $maskedEmail }}</strong>.</p>
    @endif

    <form method="POST" action="{{ route('2fa.verify.submit') }}" class="otp-form" id="otp-form">
        @csrf
        <input type="hidden" name="code" id="otp-hidden" value="{{ old('code') }}" required>

        <div class="otp-grid" role="group" aria-label="Verification code digits">
            @for ($i = 0; $i < 6; $i++)
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    class="otp-cell"
                    data-otp-index="{{ $i }}"
                    autocomplete="one-time-code"
                    aria-label="Digit {{ $i + 1 }}"
                >
            @endfor
        </div>

        @error('code')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary btn-block twofa-submit">Verify</button>
    </form>

    <div class="twofa-links">
        <form method="POST" action="{{ route('2fa.resend') }}" id="resend-form">
            @csrf
            <button type="submit" class="btn-text-link" id="resend-btn">Didn’t receive the code? <span id="resend-label">Resend</span></button>
        </form>
        <form method="POST" action="{{ route('2fa.reset-method') }}" class="twofa-alt-method-form">
            @csrf
            <button type="submit" class="btn-text-muted">Use a different method</button>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                var cells = Array.prototype.slice.call(document.querySelectorAll('.otp-cell'));
                var hidden = document.getElementById('otp-hidden');
                var form = document.getElementById('otp-form');

                function sync() {
                    hidden.value = cells.map(function (c) { return (c.value || '').replace(/\D/g, ''); }).join('');
                }

                cells.forEach(function (cell, idx) {
                    cell.addEventListener('input', function () {
                        cell.value = (cell.value || '').replace(/\D/g, '').slice(-1);
                        sync();
                        if (cell.value && idx < cells.length - 1) cells[idx + 1].focus();
                    });
                    cell.addEventListener('keydown', function (e) {
                        if (e.key === 'Backspace' && !cell.value && idx > 0) cells[idx - 1].focus();
                    });
                    cell.addEventListener('paste', function (e) {
                        e.preventDefault();
                        var text = (e.clipboardData || window.clipboardData).getData('text') || '';
                        var digits = text.replace(/\D/g, '').slice(0, 6).split('');
                        digits.forEach(function (d, i) {
                            if (cells[i]) cells[i].value = d;
                        });
                        sync();
                        var next = Math.min(digits.length, cells.length - 1);
                        cells[next].focus();
                    });
                });

                form.addEventListener('submit', function () {
                    sync();
                });

                var resendBtn = document.getElementById('resend-btn');
                var resendLabel = document.getElementById('resend-label');
                var seconds = 45;
                function tick() {
                    if (seconds <= 0) {
                        resendBtn.disabled = false;
                        resendLabel.textContent = 'Resend';
                        return;
                    }
                    resendBtn.disabled = true;
                    resendLabel.textContent = 'Resend (' + String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0') + ')';
                    seconds -= 1;
                    setTimeout(tick, 1000);
                }
                tick();
            })();
        </script>
    @endpush
</x-layouts.auth-flow>
