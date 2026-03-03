@extends('layouts.app')

@section('title', 'Residents Report')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Residents Report</h1>
            <p>Demographic data and population statistics</p>
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
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3>Total Residents</h3>
                <div class="stat-value">{{ $statistics['total'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon male">
                <i class="fas fa-mars"></i>
            </div>
            <div class="stat-details">
                <h3>Male</h3>
                <div class="stat-value">{{ $statistics['by_gender']['male'] }}</div>
                <span class="stat-percentage">{{ round(($statistics['by_gender']['male'] / $statistics['total']) * 100, 1) }}%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon female">
                <i class="fas fa-venus"></i>
            </div>
            <div class="stat-details">
                <h3>Female</h3>
                <div class="stat-value">{{ $statistics['by_gender']['female'] }}</div>
                <span class="stat-percentage">{{ round(($statistics['by_gender']['female'] / $statistics['total']) * 100, 1) }}%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon senior">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-details">
                <h3>Senior Citizens</h3>
                <div class="stat-value">{{ $statistics['by_status']['seniors'] }}</div>
            </div>
        </div>
    </div>

    <div class="reports-grid">
        <!-- Gender Distribution Chart -->
        <div class="chart-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Gender Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="genderChart" height="200"></canvas>
            </div>
        </div>

        <!-- Purok Distribution Chart -->
        <div class="chart-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Population by Purok</h3>
            </div>
            <div class="card-body">
                <canvas id="purokChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="details-grid">
        <!-- By Purok -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-map-marker-alt"></i> Population by Purok</h3>
            </div>
            <div class="card-body">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Purok</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statistics['by_purok'] as $purok)
                        <tr>
                            <td>Purok {{ $purok->purok }}</td>
                            <td>{{ $purok->total }}</td>
                            <td>{{ round(($purok->total / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- By Civil Status -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-heart"></i> Civil Status Distribution</h3>
            </div>
            <div class="card-body">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statistics['by_civil_status'] as $status)
                        <tr>
                            <td>{{ $status->civil_status }}</td>
                            <td>{{ $status->total }}</td>
                            <td>{{ round(($status->total / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Age Distribution -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-alt"></i> Age Distribution</h3>
            </div>
            <div class="card-body">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Age Range</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statistics['age_distribution'] as $range => $count)
                        <tr>
                            <td>{{ $range }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ round(($count / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Special Status -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-star"></i> Special Status</h3>
            </div>
            <div class="card-body">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Registered Voters</td>
                            <td>{{ $statistics['by_status']['voters'] }}</td>
                            <td>{{ round(($statistics['by_status']['voters'] / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>Senior Citizens</td>
                            <td>{{ $statistics['by_status']['seniors'] }}</td>
                            <td>{{ round(($statistics['by_status']['seniors'] / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>PWD</td>
                            <td>{{ $statistics['by_status']['pwd'] }}</td>
                            <td>{{ round(($statistics['by_status']['pwd'] / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>4Ps Members</td>
                            <td>{{ $statistics['by_status']['4ps'] }}</td>
                            <td>{{ round(($statistics['by_status']['4ps'] / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Residents List -->
    <div class="residents-list">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Resident Records</h3>
            <span class="record-count">Showing {{ $residents->firstItem() }} - {{ $residents->lastItem() }} of {{ $residents->total() }} records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Purok</th>
                            <th>Civil Status</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residents as $resident)
                        <tr>
                            <td>{{ $resident->resident_id }}</td>
                            <td>{{ $resident->full_name }}</td>
                            <td>{{ $resident->gender }}</td>
                            <td>{{ $resident->age }}</td>
                            <td>Purok {{ $resident->purok }}</td>
                            <td>{{ $resident->civil_status }}</td>
                            <td>
                                <div class="mini-badges">
                                    @if($resident->is_voter) <span class="mini-badge voter" title="Voter">V</span> @endif
                                    @if($resident->is_senior) <span class="mini-badge senior" title="Senior">S</span> @endif
                                    @if($resident->is_pwd) <span class="mini-badge pwd" title="PWD">P</span> @endif
                                    @if($resident->is_4ps) <span class="mini-badge fourps" title="4Ps">4Ps</span> @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($residents->hasPages())
            <div class="pagination-wrapper">
                {{ $residents->links() }}
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
.stat-icon.male { background: linear-gradient(135deg, #3b82f6, #1e40af); }
.stat-icon.female { background: linear-gradient(135deg, #ec4899, #9d174d); }
.stat-icon.senior { background: linear-gradient(135deg, #f59e0b, #b45309); }

.stat-details {
    flex: 1;
}

.stat-details h3 {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1.2;
}

.stat-percentage {
    color: #667eea;
    font-size: 0.9rem;
    font-weight: 500;
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

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.details-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
}

.details-table th {
    text-align: left;
    padding: 0.75rem;
    background: #f8fafc;
    color: #666;
    font-weight: 600;
    font-size: 0.85rem;
    border-bottom: 2px solid #e2e8f0;
}

.details-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
}

.details-table tr:last-child td {
    border-bottom: none;
}

/* Residents List */
.residents-list {
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

.mini-badges {
    display: flex;
    gap: 0.25rem;
}

.mini-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: 0.7rem;
    font-weight: 600;
}

.mini-badge.voter { background: #d4edda; color: #155724; }
.mini-badge.senior { background: #cce5ff; color: #004085; }
.mini-badge.pwd { background: #fff3cd; color: #856404; }
.mini-badge.fourps {
    background: #e2d5f1;
    color: #553c9a;
    width: auto;
    padding: 0 6px;
    border-radius: 12px;
}

.pagination-wrapper {
    padding: 1rem;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .reports-grid {
        grid-template-columns: 1fr;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .table {
        font-size: 0.85rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = document.getElementById('chart-data');

    // Gender Distribution Chart
    const genderCtx = document.getElementById('genderChart');
    if (genderCtx) {
        const maleCount = parseInt(chartData.dataset.genderMale) || 0;
        const femaleCount = parseInt(chartData.dataset.genderFemale) || 0;

        new Chart(genderCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [maleCount, femaleCount],
                    backgroundColor: ['#3b82f6', '#ec4899'],
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
    }

    // Purok Distribution Chart
    const purokCtx = document.getElementById('purokChart');
    if (purokCtx) {
        // Parse purok data from data attribute
        const purokData = JSON.parse(chartData.dataset.purok || '[]');

        const purokLabels = [];
        const purokValues = [];

        purokData.forEach(function(purok) {
            purokLabels.push('Purok ' + (purok.purok || purok.purok_number || ''));
            purokValues.push(parseInt(purok.total || purok.count || purok.residents_count || 0));
        });

        new Chart(purokCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: purokLabels,
                datasets: [{
                    label: 'Number of Residents',
                    data: purokValues,
                    backgroundColor: '#4361ee',
                    borderRadius: 5
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
    }
});
</script>
@endpush
