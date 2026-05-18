<x-layouts.auth-flow title="ID Upload — WebDev2">
    <h1 class="twofa-title">{{ ($required ?? true) ? 'Upload your ID' : 'Update your ID' }}</h1>
    <p class="twofa-sub">
        @if ($required ?? true)
            We need a photo of your ID to verify your citizen account. You must upload an ID before using the portal.
        @else
            Upload a new ID document to replace the one on file. Information will be read automatically when OCR is configured.
        @endif
    </p>

    @if (session('status'))
        <p class="twofa-inline-success">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('id-upload.store') }}" enctype="multipart/form-data" class="auth-form">
        @csrf
        <label class="field-label" for="id_document">ID document (JPG, PNG, or PDF)</label>
        <div class="input-shell input-shell--file">
            <input id="id_document" type="file" name="id_document" accept=".jpg,.jpeg,.png,.pdf" required class="input-file-native">
        </div>

        @error('id_document')
            <p class="twofa-inline-error">{{ $message }}</p>
        @enderror

        <div id="id-preview" class="id-preview" hidden>
            <p class="field-hint-block">Detected from your ID:</p>
            <p><strong>Name:</strong> <span id="preview-name">—</span></p>
            <p><strong>Date of birth:</strong> <span id="preview-dob">—</span></p>
        </div>

        <button type="submit" class="btn-primary btn-block">{{ ($required ?? true) ? 'Upload and continue' : 'Save new ID' }}</button>
    </form>

    @if (! ($required ?? true))
        <p style="margin-top: 16px; text-align: center;">
            <a href="{{ route('citizen.dashboard') }}" class="auth-link">← Back to dashboard</a>
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
