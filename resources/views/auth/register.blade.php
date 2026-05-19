<x-layouts.auth-split title="{{ __('ui.auth.register') }} — {{ __('ui.app_name') }}" heroVariant="register">
    <h1 class="split-heading">{{ __('ui.auth.create_account') }}</h1>
    <p class="split-sub">{{ __('ui.auth.register_sub') }}</p>

    @include('auth.partials.social-buttons')

    <div class="auth-divider"><span>{{ __('ui.auth.or_register_email') }}</span></div>

    <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="auth-form auth-form--split">
        @csrf

        <label class="field-label" for="name">{{ __('ui.auth.full_name') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('ui.placeholders.name') }}" required autocomplete="name">
        </div>

        <label class="field-label" for="email">{{ __('ui.auth.email') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            </span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.placeholders.email') }}" required autocomplete="email">
        </div>

        <label class="field-label" for="phone">{{ __('ui.auth.phone_optional') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="{{ __('ui.placeholders.phone_lb') }}" autocomplete="tel">
        </div>

        <label class="field-label" for="id_document">{{ __('ui.auth.id_document') }}</label>
        <div class="input-shell input-shell--file">
            <input id="id_document" type="file" name="id_document" accept=".jpg,.jpeg,.png,.pdf" required class="input-file-native">
        </div>
        <p class="field-hint-block">{{ __('ui.auth.id_file_hint') }}</p>

        <div id="register-id-preview" class="id-preview" hidden>
            <p class="field-hint-block">{{ __('ui.auth.detected_from_id') }}</p>
            <p><strong>{{ __('ui.auth.name') }}:</strong> <span id="register-preview-name">—</span></p>
            <p><strong>{{ __('ui.auth.date_of_birth') }}:</strong> <span id="register-preview-dob">—</span></p>
            <p id="register-id-ocr-hint" class="field-hint-block" hidden></p>
        </div>

        <label class="field-label" for="password">{{ __('ui.auth.password') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password" type="password" name="password" placeholder="{{ __('ui.placeholders.password') }}" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password" aria-label="{{ __('ui.auth.show_password') }}" aria-pressed="false">
                <x-password-toggle-icons />
            </button>
        </div>

        <label class="field-label" for="password_confirmation">{{ __('ui.auth.confirm_password') }}</label>
        <div class="input-shell">
            <span class="input-icon input-icon--left" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('ui.placeholders.password') }}" required autocomplete="new-password">
            <button type="button" class="input-icon-btn" data-toggle-pass aria-controls="password_confirmation" aria-label="{{ __('ui.auth.show_password') }}" aria-pressed="false">
                <x-password-toggle-icons />
            </button>
        </div>

        <label class="inline-checkbox terms-checkbox">
            <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
            {{ __('ui.auth.terms_prefix') }}
            <span class="auth-muted-link">{{ __('ui.auth.terms_of_service') }}</span>
            {{ __('ui.auth.terms_and') }}
            <span class="auth-muted-link">{{ __('ui.auth.privacy_policy') }}</span>
        </label>

        <button type="submit" class="btn-primary btn-block">{{ __('ui.auth.register') }}</button>
    </form>

    <p class="split-footer">
        {{ __('ui.auth.have_account') }}
        <a href="{{ route('login') }}">{{ __('ui.auth.login_link') }}</a>
    </p>

    @push('scripts')
        <script>
            (function () {
                var input = document.getElementById('id_document');
                var preview = document.getElementById('register-id-preview');
                var token = document.querySelector('meta[name="csrf-token"]');
                if (!input || !token || !preview) return;

                input.addEventListener('change', function () {
                    if (!input.files || !input.files[0]) return;
                    var data = new FormData();
                    data.append('id_document', input.files[0]);
                    data.append('_token', token.getAttribute('content'));

                    fetch('{{ route('register.id-preview') }}', {
                        method: 'POST',
                        body: data,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (json) {
                            preview.hidden = false;
                            var hint = document.getElementById('register-id-ocr-hint');
                            var nameInput = document.getElementById('name');
                            if (json.name && nameInput && !nameInput.value) {
                                nameInput.value = json.name;
                            }
                            document.getElementById('register-preview-name').textContent = json.name || '—';
                            document.getElementById('register-preview-dob').textContent = json.date_of_birth || '—';
                            if (hint) {
                                if (json.message) {
                                    hint.textContent = json.message;
                                    hint.hidden = false;
                                } else {
                                    hint.textContent = '';
                                    hint.hidden = true;
                                }
                            }
                        })
                        .catch(function () {});
                });
            })();
        </script>
    @endpush
</x-layouts.auth-split>
