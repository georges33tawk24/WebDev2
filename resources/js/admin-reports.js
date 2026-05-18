import Chart from 'chart.js/auto';

function readChartPayload() {
    const el = document.getElementById('report-chart-data');

    if (!el?.textContent) {
        return null;
    }

    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function initReportsCharts() {
    const payload = readChartPayload();

    if (!payload) {
        return;
    }

    const statusCanvas = document.getElementById('statusChart');
    const monthlyCanvas = document.getElementById('monthlyChart');

    if (statusCanvas && payload.status) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: payload.status.labels,
                datasets: [{
                    data: payload.status.data,
                    backgroundColor: ['#fef3c7', '#dbeafe', '#ffedd5', '#d1fae5', '#fee2e2', '#ede9fe'],
                    borderColor: ['#92400e', '#1e40af', '#9a3412', '#065f46', '#991b1b', '#5b21b6'],
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }

    if (monthlyCanvas && payload.monthly) {
        new Chart(monthlyCanvas, {
            type: 'bar',
            data: {
                labels: payload.monthly.labels,
                datasets: [{
                    label: payload.monthly.datasetLabel,
                    data: payload.monthly.data,
                    backgroundColor: '#1a56db',
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                    },
                },
            },
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReportsCharts);
} else {
    initReportsCharts();
}
