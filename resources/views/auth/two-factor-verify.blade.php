<x-layouts.auth-flow title="Verify — WebDev2">
    <div class="twofa-back-row">
        <form method="POST" action="{{ route('logout') }}" class="twofa-back-form">
            @csrf
            <button type="submit" class="btn-icon-back" aria-label="Sign out and go back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </form>
    </div>

    <h1 class="twofa-title">Verify your email</h1>
    <p class="twofa-sub">Enter the code we sent to <strong>{{ $maskedEmail }}</strong>.</p>

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

        @error('resend')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary btn-block twofa-submit">Verify</button>
    </form>

    <div class="twofa-links">
        <form method="POST" action="{{ route('2fa.resend') }}" id="resend-form">
            @csrf
            <button type="submit" class="btn-text-link" id="resend-btn">Didn’t receive the code? <span id="resend-label">Resend</span></button>
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
                var resendForm = document.getElementById('resend-form');
                var seconds = {{ (int) ($resendCooldownSeconds ?? 0) }};

                function formatCountdown(remaining) {
                    return String(Math.floor(remaining / 60)).padStart(2, '0') + ':' + String(remaining % 60).padStart(2, '0');
                }

                function tick() {
                    if (seconds <= 0) {
                        resendBtn.disabled = false;
                        resendLabel.textContent = 'Resend';
                        return;
                    }
                    resendBtn.disabled = true;
                    resendLabel.textContent = 'Resend in ' + formatCountdown(seconds);
                    seconds -= 1;
                    setTimeout(tick, 1000);
                }

                resendForm.addEventListener('submit', function (e) {
                    if (resendBtn.disabled) {
                        e.preventDefault();
                    }
                });

                tick();
            })();
        </script>
    @endpush
</x-layouts.auth-flow>
