@extends('layouts.admin')

@section('title', __('ui.admin.dashboard'))
@section('page-title', __('ui.dashboard_overview'))

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.admin.total_offices') }}</div>
        <div class="stat-number">{{ localized_number($totalOffices) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.admin.total_users') }}</div>
        <div class="stat-number">{{ localized_number($totalUsers) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.admin.total_citizens') }}</div>
        <div class="stat-number">{{ localized_number($totalCitizens) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.admin.active_staff') }}</div>
        <div class="stat-number">{{ localized_number($totalStaff) }}</div>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="page-header" style="margin-bottom: 16px;">
        <div>
            <div class="page-title" style="font-size:16px;">{{ __('ui.admin.recent_offices') }}</div>
            <div class="page-subtitle">{{ __('ui.admin.recent_offices_sub') }}</div>
        </div>
        <a href="{{ route('admin.offices.index') }}" class="btn-primary">{{ __('ui.view_all') }}</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.table.office_name') }}</th>
                    <th>{{ __('ui.table.municipality') }}</th>
                    <th>{{ __('ui.table.contact') }}</th>
                    <th>{{ __('ui.table.status') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOffices as $office)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ $office->localized('name') }}</td>
                    <td>{{ $office->municipality ?? __('ui.na') }}</td>
                    <td>{{ $office->email ?? __('ui.na') }}</td>
                    <td><span class="badge badge-active">{{ __('ui.active') }}</span></td>
                    <td>
                        <a href="{{ route('admin.offices.edit', $office) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">{{ __('ui.edit') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.admin.no_offices') }} <a href="{{ route('admin.offices.create') }}" style="color:#1a56db;">{{ __('ui.admin.create_office_link') }}</a></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="page-header" style="margin-bottom: 16px;">
        <div>
            <div class="page-title" style="font-size:16px;">{{ __('ui.admin.recent_users') }}</div>
            <div class="page-subtitle">{{ __('ui.admin.recent_users_sub') }}</div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-primary">{{ __('ui.view_all') }}</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.table.name') }}</th>
                    <th>{{ __('ui.table.email') }}</th>
                    <th>{{ __('ui.table.role') }}</th>
                    <th>{{ __('ui.table.status') }}</th>
                    <th>{{ __('ui.table.joined') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers as $user)
                @php $roleSlug = $user->role?->slug; @endphp
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge" style="background:#f3f4f6; color:#374151;">
                            {{ in_array($roleSlug, ['admin', 'office_staff', 'citizen'], true) ? __('ui.roles.'.$roleSlug) : __('ui.na') }}
                        </span>
                    </td>
                    <td>
                        @if($user->email_verified_at)
                            <span class="badge badge-active">{{ __('ui.active') }}</span>
                        @else
                            <span class="badge badge-inactive">{{ __('ui.inactive') }}</span>
                        @endif
                    </td>
                    <td style="color:#6b7280;">{{ localized_date($user->created_at, 'M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.no_results') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
