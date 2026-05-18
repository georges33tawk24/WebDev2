@extends('layouts.admin')

@section('title', __('ui.admin.reports_title'))
@section('page-title', __('ui.admin.reports_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.reports_title') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.reports_platform_sub') }}</div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">
    <div class="stat-card">
        
        <div class="stat-label">{{ __('ui.admin.total_offices') }}</div>
        <div class="stat-number">{{ localized_number($totalOffices) }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">{{ __('ui.admin.total_requests') }}</div>
        <div class="stat-number">{{ localized_number($totalRequests) }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">{{ __('ui.admin.total_citizens') }}</div>
        <div class="stat-number">{{ localized_number($totalCitizens) }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">{{ __('ui.admin.total_revenue') }}</div>
        <div class="stat-number">{{ localized_money($totalRevenue) }}</div>
    </div>
</div>

{{-- Charts Row --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">

    {{-- {{ __('ui.admin.requests_by_status') }} Chart --}}
    <div class="card">
        <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.admin.requests_by_status') }}</div>
        <canvas id="statusChart" height="200"></canvas>
    </div>

    {{-- Monthly Requests Chart --}}
    <div class="card">
        <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.admin.monthly_requests_6') }}</div>
        <canvas id="monthlyChart" height="200"></canvas>
    </div>

</div>

{{-- {{ __('ui.admin.requests_per_office') }} --}}
<div class="card" style="margin-bottom:24px;">
    <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.admin.requests_per_office') }}</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-primary">{{ __('ui.table.office_name') }}</th>
                    <th class="col-secondary">{{ __('ui.table.municipality') }}</th>
                    <th class="col-count">{{ __('ui.admin.total_requests') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requestsPerOffice as $office)
                <tr>
                    <td class="col-primary" style="font-weight:600; color:#111827;">{{ $office->localized('name') }}</td>
                    <td class="col-secondary">{{ $office->localized('municipality') ?? __('ui.na') }}</td>
                    <td class="col-count">
                        <span class="count-badge count-badge--blue">
                            {{ localized_number($office->service_requests_count) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.admin.no_data_yet') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- {{ __('ui.admin.requests_per_service') }} --}}
<div class="card">
    <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.admin.requests_per_service') }}</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-primary">{{ __('ui.admin.service_name') }}</th>
                    <th class="col-price">{{ __('ui.table.price') }}</th>
                    <th class="col-count">{{ __('ui.admin.total_requests') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requestsPerService as $service)
                <tr>
                    <td class="col-primary" style="font-weight:600; color:#111827;">{{ $service->localized('name') }}</td>
                    <td class="col-price">{{ localized_money($service->price) }}</td>
                    <td class="col-count">
                        <span class="count-badge count-badge--green">
                            {{ localized_number($service->service_requests_count) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.admin.no_data_yet') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // {{ __('ui.admin.requests_by_status') }} Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($requestsByStatus->pluck('status')->map(fn($s) => __('ui.status.'.$s))) !!},
            datasets: [{
                data: {!! json_encode($requestsByStatus->pluck('total')) !!},
                backgroundColor: ['#fef3c7', '#dbeafe', '#ffedd5', '#d1fae5', '#fee2e2', '#ede9fe'],
                borderColor: ['#92400e', '#1e40af', '#9a3412', '#065f46', '#991b1b', '#5b21b6'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyRequests->pluck('month')) !!},
            datasets: [{
                label: @js(__('ui.admin.total_requests')),
                data: {!! json_encode($monthlyRequests->pluck('total')) !!},
                backgroundColor: '#1a56db',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endsection