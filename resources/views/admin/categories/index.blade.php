@extends('layouts.admin')

@section('title', __('ui.admin.categories_page_title'))
@section('page-title', __('ui.admin.categories_page_title'))

@section('content')
@php($catalogPrefix = $catalogPrefix ?? 'admin')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.categories_page_title') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.categories_sub') }}</div>
    </div>
    <a href="{{ route($catalogPrefix . '.categories.create') }}" class="btn-primary">{{ __('ui.admin.add_category') }}</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.admin.category_name') }}</th>
                <th>{{ __('ui.admin.description') }}</th>
                <th>{{ __('ui.table.total_services') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $category->localized('name') }}</td>
                <td>{{ $category->localized('description') ?? __('ui.na') }}</td>
                <td>
                    <span class="badge" style="background:#dbeafe; color:#1e40af;">
                        {{ $category->services_count }} {{ __('ui.admin.services_count', ['count' => '']) }}
                    </span>
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route($catalogPrefix . '.categories.edit', $category) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route($catalogPrefix . '.categories.destroy', $category) }}" onsubmit="return confirm(@js(__('ui.admin.confirm_delete_category')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; color:#6b7280; padding:32px;">
                    {{ __('ui.admin.no_categories') }} <a href="{{ route($catalogPrefix . '.categories.create') }}" style="color:#1a56db;">{{ __('ui.admin.create_office_link') }}</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $categories->links() }}
</div>
@endsection