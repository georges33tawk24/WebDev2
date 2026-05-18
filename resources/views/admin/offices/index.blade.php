@extends('layouts.admin')

@section('title', __('ui.admin.offices_title'))
@section('page-title', __('ui.admin.offices_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.offices_title') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.offices_manage_sub') }}</div>
    </div>
    <a href="{{ route('admin.offices.create') }}" class="btn-primary">
         {{ __('ui.admin.add_office') }}
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.table.office_name') }}</th>
                <th>{{ __('ui.table.municipality') }}</th>
                <th>{{ __('ui.admin.contact_number') }}</th>
                <th>{{ __('ui.admin.contact_email') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($offices as $office)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $office->localized('name') }}</td>
                <td>{{ $office->municipality ?? __('ui.na') }}</td>
                <td>{{ $office->contact_number ?? __('ui.na') }}</td>
                <td>{{ $office->contact_email ?? __('ui.na') }}</td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route('admin.offices.edit', $office) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('admin.offices.destroy', $office) }}" onsubmit="return confirm(@js(__('ui.admin.confirm_delete_office')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">
                    {{ __('ui.admin.no_offices') }} <a href="{{ route('admin.offices.create') }}" style="color:#1a56db;">{{ __('ui.admin.create_office_link') }}</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $offices->links() }}
</div>
@endsection