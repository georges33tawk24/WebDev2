@props(['status'])
<span {{ $attributes->merge(['class' => 'badge badge-'.str_replace('_', '-', $status)]) }}>
    {{ __('ui.status.'.$status) }}
</span>
