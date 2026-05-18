<x-layouts.auth-flow title="{{ __('ui.auth.upload_id') }} — {{ __('ui.app_name') }}">
    <h1 class="twofa-title">{{ ($required ?? true) ? __('ui.auth.upload_id') : __('ui.auth.update_id') }}</h1>
    <p class="twofa-sub">
        @if ($required ?? true)
            {{ __('ui.auth.id_required_sub') }}
        @else
            {{ __('ui.auth.id_optional_sub') }}
        @endif
    </p>

    @if (session('status'))
        <p class="twofa-inline-success">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('id-upload.store') }}" enctype="multipart/form-data" class="auth-form">
        @csrf
        <label class="field-label" for="id_document">{{ __('ui.auth.id_file_label') }}</label>
        <div class="input-shell input-shell--file">
            <input id="id_document" type="file" name="id_document" accept=".jpg,.jpeg,.png,.pdf" required class="input-file-native">
        </div>

        @error('id_document')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <div id="id-preview" class="id-preview" hidden>
            <p class="field-hint-block">{{ __('ui.auth.detected_from_id') }}</p>
            <p><strong>{{ __('ui.auth.name') }}:</strong> <span id="preview-name">—</span></p>
            <p><strong>{{ __('ui.auth.date_of_birth') }}:</strong> <span id="preview-dob">—</span></p>
        </div>

        <button type="submit" class="btn-primary btn-block">{{ ($required ?? true) ? __('ui.auth.upload_continue') : __('ui.auth.save_new_id') }}</button>
    </form>

    @if (! ($required ?? true))
        <p style="margin-top: 16px; text-align: center;">
            <a href="{{ route('citizen.dashboard') }}" class="auth-link">{{ __('ui.auth.back_dashboard') }}</a>
        </p>
    @endif

    @push('scripts')
        <script>
            (function () {
                var input = document.getElementById('id_document');
                var preview = document.getElementById('id-preview');
                var token = document.querySelector('meta[name="csrf-token"]');
                if (!input || !token) return;

                input.addEventListener('change', function () {
                    if (!input.files || !input.files[0]) return;
                    var data = new FormData();
                    data.append('id_document', input.files[0]);
                    data.append('_token', token.getAttribute('content'));

                    fetch('{{ route('api.id-document.parse') }}', {
                        method: 'POST',
                        body: data,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (json) {
                            if (!json.parsed) return;
                            preview.hidden = false;
                            document.getElementById('preview-name').textContent = json.name || '—';
                            document.getElementById('preview-dob').textContent = json.date_of_birth || '—';
                        })
                        .catch(function () {});
                });
            })();
        </script>
    @endpush
</x-layouts.auth-flow>
