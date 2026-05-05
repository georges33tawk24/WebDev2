<x-layouts.guest>
    <h1 class="auth-title">ID Upload</h1>
    <p class="auth-subtitle">Upload or replace your identification document.</p>

    <form method="POST" action="{{ route('id-upload.store') }}" enctype="multipart/form-data" class="auth-form">
        @csrf
        <label for="id_document">ID Upload</label>
        <input id="id_document" type="file" name="id_document" required>

        <button type="submit" class="btn-primary">Upload</button>
    </form>

    <div class="auth-links">
        <a href="{{ route('dashboard.citizen') }}">Back to dashboard</a>
    </div>
</x-layouts.guest>
