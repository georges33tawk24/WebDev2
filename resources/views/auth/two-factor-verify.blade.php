<x-layouts.auth-flow title="{{ __('ui.auth.verify_page_title') }} — {{ __('ui.app_name') }}">
    <div class="twofa-back-row">
        <form method="POST" action="{{ route('logout') }}" class="twofa-back-form">
            @csrf
            <button type="submit" class="btn-icon-back" aria-label="{{ __('ui.auth.sign_out_back') }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </form>
    </div>

    <h1 class="twofa-title">
        {{ $channel === 'sms' ? __('ui.auth.verify_sms') : __('ui.auth.verify_email') }}
    </h1>
    <p class="twofa-sub">
        @if ($channel === 'sms')
            {!! __('ui.auth.verify_sms_sub', ['phone' => '<strong>'.$maskedDestination.'</strong>']) !!}
        @else
            {!! __('ui.auth.verify_sub', ['email' => '<strong>'.$maskedDestination.'</strong>']) !!}
        @endif
    </p>

    <form method="POST" action="{{ route('2fa.verify.submit') }}" class="otp-form" id="otp-form" novalidate>
        @csrf
        <input type="hidden" name="code" id="otp-hidden" value="{{ old('code') }}">

        <div class="otp-grid" role="group" aria-label="{{ __('ui.auth.otp_group_label') }}">
            @php
                $oldDigits = str_split(preg_replace('/\D/', '', (string) old('code', '')));
            @endphp
            @for ($i = 0; $i < 6; $i++)
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    class="otp-cell"
                    data-otp-index="{{ $i }}"
                    value="{{ $oldDigits[$i] ?? '' }}"
                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    aria-label="{{ __('ui.auth.otp_digit', ['n' => $i + 1]) }}"
                    @if ($i === 0) autofocus @endif
                >
            @endfor
        </div>

        <p class="twofa-inline-error" id="otp-client-error" hidden></p>

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

        @if ($channel === 'email' && ($smsAvailable ?? false))
            <form method="POST" action="{{ route('2fa.channel') }}" class="twofa-alt-method-form">
                @csrf
                <input type="hidden" name="channel" value="sms">
                <button type="submit" class="btn-text-link">
                    {{ ($hasPhone ?? false) ? __('ui.auth.use_sms_instead') : __('ui.auth.use_sms_add_phone') }}
                </button>
            </form>
        @elseif ($channel === 'sms' && ($emailAvailable ?? false))
            <form method="POST" action="{{ route('2fa.channel') }}" class="twofa-alt-method-form">
                @csrf
                <input type="hidden" name="channel" value="email">
                <button type="submit" class="btn-text-link">{{ __('ui.auth.use_email_instead') }}</button>
            </form>
        @endif
    </div>

    @push('scripts')
        <script>
            (function () {
                var cells = Array.prototype.slice.call(document.querySelectorAll('.otp-cell'));
                var hidden = document.getElementById('otp-hidden');
                var form = document.getElementById('otp-form');
                var clientError = document.getElementById('otp-client-error');
                var resendLabel = document.getElementById('resend-label');
                var resendBtn = document.getElementById('resend-btn');
                var resendForm = document.getElementById('resend-form');
                var resendText = @json(__('ui.auth.resend_code'));
                var resendInTemplate = @json(__('ui.auth.resend_in', ['time' => '__TIME__']));
                var codeRequiredMsg = @json(__('ui.auth.otp_code_required'));
                var seconds = {{ (int) ($resendCooldownSeconds ?? 0) }};

                function sync() {
                    hidden.value = cells.map(function (c) {
                        return (c.value || '').replace(/\D/g, '');
                    }).join('');
                }

                function showClientError(message) {
                    if (!clientError) {
                        return;
                    }

                    clientError.textContent = message;
                    clientError.hidden = !message;
                }

                cells.forEach(function (cell, idx) {
                    cell.addEventListener('input', function () {
                        cell.value = (cell.value || '').replace(/\D/g, '').slice(-1);
                        sync();
                        showClientError('');
                        if (cell.value && idx < cells.length - 1) {
                            cells[idx + 1].focus();
                        }
                    });

                    cell.addEventListener('keydown', function (e) {
                        if (e.key === 'Backspace' && !cell.value && idx > 0) {
                            cells[idx - 1].focus();
                        }

                        if (e.key === 'ArrowLeft' && idx > 0) {
                            cells[idx - 1].focus();
                        }

                        if (e.key === 'ArrowRight' && idx < cells.length - 1) {
                            cells[idx + 1].focus();
                        }
                    });

                    cell.addEventListener('paste', function (e) {
                        e.preventDefault();
                        var text = (e.clipboardData || window.clipboardData).getData('text') || '';
                        var digits = text.replace(/\D/g, '').slice(0, 6).split('');
                        digits.forEach(function (d, i) {
                            if (cells[i]) {
                                cells[i].value = d;
                            }
                        });
                        sync();
                        showClientError('');
                        var next = Math.min(digits.length, cells.length - 1);
                        cells[next].focus();
                    });
                });

                form.addEventListener('submit', function (e) {
                    sync();

                    if (hidden.value.length !== 6) {
                        e.preventDefault();
                        showClientError(codeRequiredMsg);
                        var firstEmpty = cells.find(function (c) { return !c.value; });
                        (firstEmpty || cells[0]).focus();
                    }
                });

                function formatCountdown(remaining) {
                    var m = String(Math.floor(remaining / 60)).padStart(2, '0');
                    var s = String(remaining % 60).padStart(2, '0');

                    return m + ':' + s;
                }

                function tick() {
                    if (!resendBtn || !resendLabel) {
                        return;
                    }

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

                if (resendForm) {
                    resendForm.addEventListener('submit', function (e) {
                        if (resendBtn.disabled) {
                            e.preventDefault();
                        }
                    });
                }

                sync();
                tick();

                var firstEmpty = cells.find(function (c) { return !c.value; });
                if (firstEmpty) {
                    firstEmpty.focus();
                }
            })();
        </script>
    @endpush
</x-layouts.auth-flow>
