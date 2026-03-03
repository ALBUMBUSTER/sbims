@extends('layouts.app')

@section('title', 'Certificates Report')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Certificates Report</h1>
            <p>Certificate issuance statistics and trends</p>
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

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-details">
                <h3>Total Certificates</h3>
                <div class="stat-value">{{ $statistics['total'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3>Pending</h3>
                <div class="stat-value">{{ $statistics['by_status']['pending'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon approved">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3>Approved</h3>
                <div class="stat-value">{{ $statistics['by_status']['approved'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon released">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-details">
                <h3>Released</h3>
                <div class="stat-value">{{ $statistics['by_status']['released'] }}</div>
            </div>
        </div>
    </div>

    <div class="reports-grid">
        <!-- Status Distribution Chart -->
        <div class="chart-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Certificate Status</h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="chart-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line"></i> Monthly Trend</h3>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Certificate Types -->
    <div class="types-card">
        <div class="card-header">
            <h3><i class="fas fa-tags"></i> Certificate Types</h3>
        </div>
        <div class="card-body">
            <div class="types-grid">
                @foreach($statistics['by_type'] as $type)
                <div class="type-item">
                    <div class="type-name">{{ $type->certificate_type }}</div>
                    <div class="type-count">{{ $type->total }}</div>
                    <div class="type-bar">
                        <div class="type-bar-fill" style="width: <?php echo ($type->total / $statistics['total']) * 100; ?>%"></div>
                    </div>
                    <div class="type-percentage">{{ round(($type->total / $statistics['total']) * 100, 1) }}%</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Certificates List -->
    <div class="certificates-list">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Recent Certificates</h3>
            <span class="record-count">Showing {{ $certificates->firstItem() }} - {{ $certificates->lastItem() }} of {{ $certificates->total() }} records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Certificate #</th>
                            <th>Resident</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($certificates as $cert)
                        <tr>
                            <td>{{ $cert->certificate_id }}</td>
                            <td>{{ $cert->resident->full_name ?? 'N/A' }}</td>
                            <td>{{ $cert->certificate_type }}</td>
                            <td>{{ Str::limit($cert->purpose, 30) }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($cert->status) }}">
                                    {{ $cert->status }}
                                </span>
                            </td>
                            <td>{{ $cert->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($certificates->hasPages())
            <div class="pagination-wrapper">
                {{ $certificates->links() }}
            </div>
            @endif
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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.total { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
.stat-icon.pending { background: linear-gradient(135deg, #f59e0b, #b45309); }
.stat-icon.approved { background: linear-gradient(135deg, #10b981, #047857); }
.stat-icon.released { background: linear-gradient(135deg, #3b82f6, #1e40af); }

.stat-details h3 {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

/* Reports Grid */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.chart-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.record-count {
    color: #666;
    font-size: 0.9rem;
}

.card-body {
    padding: 1.5rem;
}

/* Types Card */
.types-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.types-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.type-item {
    display: grid;
    grid-template-columns: 150px 60px 1fr 60px;
    align-items: center;
    gap: 1rem;
}

.type-name {
    color: #333;
    font-weight: 500;
}

.type-count {
    color: #667eea;
    font-weight: 600;
    text-align: right;
}

.type-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.type-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 4px;
}

.type-percentage {
    color: #666;
    font-size: 0.9rem;
}

/* Certificates List */
.certificates-list {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    text-align: left;
    padding: 1rem;
    background: #f8fafc;
    color: #666;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-approved { background: #d4edda; color: #155724; }
.status-released { background: #cce5ff; color: #004085; }
.status-rejected { background: #f8d7da; color: #721c24; }

.pagination-wrapper {
    padding: 1rem;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .type-item {
        grid-template-columns: 1fr 60px;
        gap: 0.5rem;
    }

    .type-bar {
        grid-column: 1 / -1;
        order: 1;
    }

    .type-percentage {
        text-align: right;
    }

    .reports-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
   new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Approved', 'Released', 'Rejected'],
        datasets: [{
            data: <?php echo json_encode([
                $statistics['by_status']['pending'],
                $statistics['by_status']['approved'],
                $statistics['by_status']['released'],
                $statistics['by_status']['rejected']
            ]); ?>,
            backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

    // Monthly Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthlyData = Array(12).fill(0);

    <?php foreach($statistics['monthly_trend'] as $trend): ?>
    monthlyData[<?php echo $trend->month - 1; ?>] = <?php echo $trend->total; ?>;
    <?php endforeach; ?>

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Certificates Issued',
                data: monthlyData,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endpush
