@props([
    'name',
    'label',
    'id' => null,
    'required' => false,
    'autocomplete' => 'new-password',
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="form-group">
    <label class="form-label" for="{{ $inputId }}">{{ $label }}</label>
    <div class="password-input-wrap">
        <input
            type="password"
            name="{{ $name }}"
            id="{{ $inputId }}"
            class="form-control"
            @required($required)
            autocomplete="{{ $autocomplete }}"
        >
        <button
            type="button"
            class="password-toggle-btn"
            data-toggle-pass
            aria-controls="{{ $inputId }}"
            aria-label="{{ __('ui.auth.show_password') }}"
            aria-pressed="false"
        >
            <x-password-toggle-icons />
        </button>
    </div>
    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
