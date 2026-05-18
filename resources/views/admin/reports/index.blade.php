@extends('layouts.admin')

@section('title', 'Analytics & Reports')
@section('page-title', 'Analytics & Reports')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Analytics & Reports</div>
        <div class="page-subtitle">Platform overview and statistics</div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">
    <div class="stat-card">
        
        <div class="stat-label">Total Offices</div>
        <div class="stat-number">{{ $totalOffices }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Total Requests</div>
        <div class="stat-number">{{ $totalRequests }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Total Citizens</div>
        <div class="stat-number">{{ $totalCitizens }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Total Revenue</div>
        <div class="stat-number">${{ number_format($totalRevenue, 2) }}</div>
    </div>
</div>

{{-- Charts Row --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">

    {{-- Requests by Status Chart --}}
    <div class="card">
        <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Requests by Status</div>
        <canvas id="statusChart" height="200"></canvas>
    </div>

    {{-- Monthly Requests Chart --}}
    <div class="card">
        <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Monthly Requests (Last 6 Months)</div>
        <canvas id="monthlyChart" height="200"></canvas>
    </div>

</div>

{{-- Requests per Office --}}
<div class="card" style="margin-bottom:24px;">
    <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Requests per Office</div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Office Name</th>
                    <th>Municipality</th>
                    <th>Total Requests</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requestsPerOffice as $office)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ $office->name }}</td>
                    <td>{{ $office->municipality ?? '—' }}</td>
                    <td>
                        <span class="badge" style="background:#dbeafe; color:#1e40af;">
                            {{ $office->service_requests_count }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#6b7280; padding:32px;">No data yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Requests per Service --}}
<div class="card">
    <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Requests per Service</div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Price</th>
                    <th>Total Requests</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requestsPerService as $service)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ $service->name }}</td>
                    <td>${{ number_format($service->price, 2) }}</td>
                    <td>
                        <span class="badge" style="background:#d1fae5; color:#065f46;">
                            {{ $service->service_requests_count }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#6b7280; padding:32px;">No data yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Requests by Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($requestsByStatus->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))) !!},
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
                label: 'Requests',
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