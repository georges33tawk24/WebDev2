@extends('layouts.admin')

@section('title', __('ui.admin.services_title'))
@section('page-title', __('ui.admin.services_title'))

@section('content')
@php($catalogPrefix = $catalogPrefix ?? 'admin')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.services_title') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.services_manage_sub') }}</div>
    </div>
    <a href="{{ route($catalogPrefix . '.services.create') }}" class="btn-primary">{{ __('ui.admin.add_service') }}</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.admin.service_name') }}</th>
                <th>{{ __('ui.table.category') }}</th>
                <th>{{ __('ui.table.office') }}</th>
                <th>{{ __('ui.table.price') }}</th>
                <th>{{ __('ui.table.duration') }}</th>
                <th>{{ __('ui.table.status') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $service->localized('name') }}</td>
                <td>{{ $service->category?->name ?? __('ui.na') }}</td>
                <td>{{ $service->office?->name ?? __('ui.na') }}</td>
                <td>{{ localized_money($service->price) }}</td>
                <td>{{ $service->estimated_duration_minutes ? $service->estimated_duration_minutes . ' mins' : __('ui.na') }}</td>
                <td>
                    @if($service->is_active)
                        <span class="badge badge-active">{{ __('ui.active') }}</span>
                    @else
                        <span class="badge badge-inactive">{{ __('ui.inactive') }}</span>
                    @endif
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route($catalogPrefix . '.services.edit', $service) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route($catalogPrefix . '.services.destroy', $service) }}" onsubmit="return confirm(@js(__('ui.admin.confirm_delete_service')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#6b7280; padding:32px;">
                    {{ __('ui.admin.no_services') }} <a href="{{ route($catalogPrefix . '.services.create') }}" style="color:#1a56db;">{{ __('ui.admin.create_office_link') }}</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $services->links() }}
</div>
@endsection