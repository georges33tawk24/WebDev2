@props([
    'name' => 'status',
    'selected' => null,
])

<select {{ $attributes->merge(['name' => $name, 'class' => 'form-control']) }}>
    @foreach (service_request_statuses() as $status)
        <option value="{{ $status }}" @selected($selected === $status)>{{ __('ui.status.'.$status) }}</option>
    @endforeach
</select>
