@extends('layouts.admin')

@section('title', __('ui.admin.users_title'))
@section('page-title', __('ui.admin.users_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.users_title') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.users_manage_sub') }}</div>
    </div>
    <a href="{{ route('admin.users.staff.create') }}" class="btn-primary"> {{ __('ui.admin.add_staff') }}</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.table.name') }}</th>
                <th>{{ __('ui.table.email') }}</th>
                <th>{{ __('ui.table.role') }}</th>
                <th>{{ __('ui.table.office') }}</th>
                <th>{{ __('ui.table.status') }}</th>
                <th>{{ __('ui.table.joined') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="badge" style="background:#f3f4f6; color:#374151;">
                        @php $roleSlug = $user->role?->slug; @endphp
                        {{ in_array($roleSlug, ['admin', 'office_staff', 'citizen'], true) ? __('ui.roles.'.$roleSlug) : __('ui.na') }}
                    </span>
                </td>
                <td style="color:#6b7280;">{{ $user->office?->localized('name') ?? __('ui.na') }}</td>
                <td>
                    @if($user->is_active)
                        <span class="badge badge-active">{{ __('ui.active') }}</span>
                    @else
                        <span class="badge badge-inactive">{{ __('ui.inactive') }}</span>
                    @endif
                </td>
                <td style="color:#6b7280;">{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="{{ $user->is_active ? 'btn-danger' : 'btn-primary' }}" style="padding:6px 12px; font-size:12px;">
                            {{ $user->is_active ? __('ui.admin.deactivate') : __('ui.admin.activate') }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.no_results') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $users->links() }}
</div>
@endsection