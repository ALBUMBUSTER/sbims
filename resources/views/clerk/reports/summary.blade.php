@extends('layouts.app')

@section('title', 'Summary Report')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Summary Report</h1>
            <p>System overview and key metrics for {{ $statistics['year'] }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clerk.reports.index') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Year Selector -->
    <div class="year-selector">
        <form action="{{ route('clerk.reports.summary') }}" method="GET" class="year-form">
            <label for="year">Select Year:</label>
            <select name="year" id="year" onchange="this.form.submit()" class="year-select">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $statistics['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
        <!-- Residents Section -->
        <div class="metric-section">
            <h3><i class="fas fa-users"></i> Residents</h3>
            <div class="metric-cards">
                <div class="metric-card">
                    <span class="metric-label">Total Residents</span>
                    <span class="metric-value">{{ $statistics['residents']['total'] }}</span>
                </div>
                <div class="metric-card">
                    <span class="metric-label">New This Year</span>
                    <span class="metric-value">{{ $statistics['residents']['new_this_year'] }}</span>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Male</span>
                    <span class="metric-value">{{ $statistics['residents']['by_gender']['male'] }}</span>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Female</span>
                    <span class="metric-value">{{ $statistics['residents']['by_gender']['female'] }}</span>
                </div>
            </div>
        </div>

        <!-- Certificates Section -->
        <div class="metric-section">
            <h3><i class="fas fa-file-alt"></i> Certificates</h3>
            <div class="metric-cards">
                <div class="metric-card">
                    <span class="metric-label">Total Certificates</span>
                    <span class="metric-value">{{ $statistics['certificates']['total'] }}</span>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Issued This Year</span>
                    <span class="metric-value">{{ $statistics['certificates']['issued_this_year'] }}</span>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Pending</span>
                    <span class="metric-value">{{ $statistics['certificates']['by_status']['pending'] }}</span>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Released</span>
                    <span class="metric-value">{{ $statistics['certificates']['by_status']['released'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends Chart -->
    <div class="trends-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Monthly Trends ({{ $statistics['year'] }})</h3>
        </div>
        <div class="card-body">
            <canvas id="monthlyTrendsChart" height="300"></canvas>
        </div>
    </div>

    <!-- Detailed Monthly Breakdown -->
    <div class="monthly-breakdown">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt"></i> Monthly Breakdown</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="breakdown-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>New Residents</th>
                            <th>Certificates Issued</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['January', 'February', 'March', 'April', 'May', 'June',
                                 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>{{ $statistics['residents']['monthly'][$month] ?? 0 }}</td>
                            <td>{{ $statistics['certificates']['monthly'][$month] ?? 0 }}</td>
                            <td>{{ ($statistics['residents']['monthly'][$month] ?? 0) + ($statistics['certificates']['monthly'][$month] ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th>{{ array_sum($statistics['residents']['monthly']) }}</th>
                            <th>{{ array_sum($statistics['certificates']['monthly']) }}</th>
                            <th>{{ array_sum($statistics['residents']['monthly']) + array_sum($statistics['certificates']['monthly']) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Notes -->
    <div class="summary-notes">
        <div class="note-card">
            <h4><i class="fas fa-info-circle"></i> Summary</h4>
            <p>
                As of {{ now()->format('F d, Y') }}, Barangay Libertad has a total of
                <strong>{{ $statistics['residents']['total'] }}</strong> registered residents.
                This year, <strong>{{ $statistics['residents']['new_this_year'] }}</strong> new residents
                were recorded, and <strong>{{ $statistics['certificates']['issued_this_year'] }}</strong>
                certificates have been issued. Currently, there are
                <strong>{{ $statistics['certificates']['by_status']['pending'] }}</strong> pending certificate
                requests awaiting approval.
            </p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.container-fluid {
    padding: 1.5rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.8rem;
}

.page-title p {
    color: #666;
    font-size: 1rem;
}

.page-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #eef2ff;
}

/* Year Selector */
.year-selector {
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.year-form {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.year-form label {
    color: #333;
    font-weight: 500;
}

.year-select {
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 5px;
    font-size: 0.95rem;
    min-width: 100px;
}

.year-select:focus {
    outline: none;
    border-color: #667eea;
}

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.metric-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1.5rem;
}

.metric-section h3 {
    color: #333;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.metric-section h3 i {
    color: #667eea;
}

.metric-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.metric-card {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
}

.metric-label {
    display: block;
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.metric-value {
    display: block;
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

/* Trends Card */
.trends-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.card-header {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    color: #333;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.card-header h3 i {
    color: #667eea;
}

.card-body {
    padding: 1.5rem;
}

/* Monthly Breakdown */
.monthly-breakdown {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.table-responsive {
    overflow-x: auto;
}

.breakdown-table {
    width: 100%;
    border-collapse: collapse;
}

.breakdown-table th {
    text-align: left;
    padding: 1rem;
    background: #f8fafc;
    color: #666;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.breakdown-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
}

.breakdown-table tfoot th {
    background: #f8fafc;
    border-top: 2px solid #e2e8f0;
}

/* Summary Notes */
.summary-notes {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.note-card {
    padding: 1.5rem;
}

.note-card h4 {
    color: #333;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.note-card h4 i {
    color: #667eea;
}

.note-card p {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.note-card strong {
    color: #333;
}

@media (max-width: 768px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }

    .metric-cards {
        grid-template-columns: 1fr 1fr;
    }

    .year-form {
        flex-direction: column;
        align-items: flex-start;
    }

    .year-select {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="chart-data"
     data-residents="{{ json_encode(array_values($statistics['residents']['monthly'] ?? array_fill(0, 12, 0))) }}"
     data-certificates="{{ json_encode(array_values($statistics['certificates']['monthly'] ?? array_fill(0, 12, 0))) }}"
     style="display: none;">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');

    const chartDataEl = document.getElementById('chart-data');
    const residentData = JSON.parse(chartDataEl.dataset.residents);
    const certificateData = JSON.parse(chartDataEl.dataset.certificates);

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'New Residents',
                    data: residentData,
                    backgroundColor: '#4361ee',
                    borderRadius: 5
                },
                {
                    label: 'Certificates Issued',
                    data: certificateData,
                    backgroundColor: '#10b981',
                    borderRadius: 5
                }
            ]
        }
    });
});
</script>
@endpush
