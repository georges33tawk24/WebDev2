<x-layouts.auth-flow title="{{ __('ui.auth.verify_page_title') }} — {{ __('ui.app_name') }}">
    <div class="twofa-back-row">
        <form method="POST" action="{{ route('logout') }}" class="twofa-back-form">
            @csrf
            <button type="submit" class="btn-icon-back" aria-label="{{ __('ui.auth.sign_out_back') }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </form>
    </div>

    <h1 class="twofa-title">{{ __('ui.auth.verify_email') }}</h1>
    <p class="twofa-sub">{!! __('ui.auth.verify_sub', ['email' => '<strong>'.$maskedEmail.'</strong>']) !!}</p>

    <form method="POST" action="{{ route('2fa.verify.submit') }}" class="otp-form" id="otp-form">
        @csrf
        <input type="hidden" name="code" id="otp-hidden" value="{{ old('code') }}" required>

        <div class="otp-grid" role="group" aria-label="{{ __('ui.auth.otp_group_label') }}">
            @for ($i = 0; $i < 6; $i++)
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    class="otp-cell"
                    data-otp-index="{{ $i }}"
                    autocomplete="one-time-code"
                    aria-label="{{ __('ui.auth.otp_digit', ['n' => $i + 1]) }}"
                >
            @endfor
        </div>

        @error('code')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        @error('resend')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-primary btn-block twofa-submit">{{ __('ui.auth.verify') }}</button>
    </form>

    <div class="twofa-links">
        <form method="POST" action="{{ route('2fa.resend') }}" id="resend-form">
            @csrf
            <button type="submit" class="btn-text-link" id="resend-btn">
                {{ __('ui.auth.resend_prompt') }}
                <span id="resend-label">{{ __('ui.auth.resend_code') }}</span>
            </button>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                var cells = Array.prototype.slice.call(document.querySelectorAll('.otp-cell'));
                var hidden = document.getElementById('otp-hidden');
                var form = document.getElementById('otp-form');
                var resendLabel = document.getElementById('resend-label');
                var resendBtn = document.getElementById('resend-btn');
                var resendForm = document.getElementById('resend-form');
                var resendText = @json(__('ui.auth.resend_code'));
                var resendInTemplate = @json(__('ui.auth.resend_in', ['time' => '__TIME__']));
                var seconds = {{ (int) ($resendCooldownSeconds ?? 0) }};

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

                function formatCountdown(remaining) {
                    var m = String(Math.floor(remaining / 60)).padStart(2, '0');
                    var s = String(remaining % 60).padStart(2, '0');
                    return m + ':' + s;
                }

                function tick() {
                    if (seconds <= 0) {
                        resendBtn.disabled = false;
                        resendLabel.textContent = resendText;
                        return;
                    }
                    resendBtn.disabled = true;
                    resendLabel.textContent = resendInTemplate.replace('__TIME__', formatCountdown(seconds));
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
