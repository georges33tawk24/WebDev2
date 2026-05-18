@extends('layouts.admin')

@section('title', __('ui.admin.citizens_title'))
@section('page-title', __('ui.admin.citizens_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.citizens_title') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.citizens_sub') }}</div>
    </div>
    <a href="{{ route('admin.users.citizens.create') }}" class="btn-primary">{{ __('ui.admin.add_citizen') }}</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.table.name') }}</th>
                <th>{{ __('ui.table.email') }}</th>
                <th>{{ __('ui.table.phone') }}</th>
                <th>{{ __('ui.table.status') }}</th>
                <th>{{ __('ui.table.joined') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($citizens as $citizen)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $citizen->name }}</td>
                <td>{{ $citizen->email }}</td>
                <td>{{ $citizen->phone ?? __('ui.na') }}</td>
                <td>
                    @if($citizen->is_active)
                        <span class="badge badge-active">{{ __('ui.active') }}</span>
                    @else
                        <span class="badge badge-inactive">{{ __('ui.inactive') }}</span>
                    @endif
                </td>
                <td style="color:#6b7280;">{{ $citizen->created_at->format('M d, Y') }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.users.toggle', $citizen) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="{{ $citizen->is_active ? 'btn-danger' : 'btn-primary' }}" style="padding:6px 12px; font-size:12px;">
                            {{ $citizen->is_active ? __('ui.admin.deactivate') : __('ui.admin.activate') }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.no_results') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $citizens->links() }}
</div>
@endsection