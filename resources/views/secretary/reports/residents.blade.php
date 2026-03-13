@extends('layouts.app')

@section('title', 'Residents Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Residents Report</h1>
            <p>Demographics and statistics</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.reports.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Reports
            </a>
            <form action="{{ route('secretary.reports.export') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="residents">
                <input type="hidden" name="format" value="excel">
                @if(request('date_from'))
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                @endif
                @if(request('date_to'))
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                @endif
                <button type="submit" class="btn-primary">
                    <i class="fas fa-file-excel icon-small"></i>
                    Export to Excel
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filters-section">
        <form action="{{ route('secretary.reports.residents') }}" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="date_from">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="filter-input">
            </div>
            <div class="filter-group">
                <label for="date_to">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="filter-input">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Generate</button>
                <a href="{{ route('secretary.reports.residents') }}" class="btn-clear">Reset</a>
            </div>
        </form>
    </div>

    <!-- Key Statistics Cards -->
    <div class="stats-grid">
        <!-- Total Residents Card -->
        <div class="stat-card total">
            <div class="stat-icon">
                <x-heroicon-o-users />
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Residents</span>
                <span class="stat-value">{{ $statistics['total'] }}</span>
            </div>
        </div>

        <!-- Male Residents Card -->
        <div class="stat-card male">
            <div class="stat-icon">
                <x-heroicon-o-user />
            </div>
            <div class="stat-content">
                <span class="stat-label">Male</span>
                <span class="stat-value">{{ $statistics['by_gender']['male'] }}</span>
            </div>
        </div>

        <!-- Female Residents Card -->
        <div class="stat-card female">
            <div class="stat-icon">
                <x-heroicon-o-user />
            </div>
            <div class="stat-content">
                <span class="stat-label">Female</span>
                <span class="stat-value">{{ $statistics['by_gender']['female'] }}</span>
            </div>
        </div>

        <!-- Registered Voters Card -->
        <div class="stat-card voters">
            <div class="stat-icon">
                <x-heroicon-o-check-badge />
            </div>
            <div class="stat-content">
                <span class="stat-label">Registered Voters</span>
                <span class="stat-value">{{ $statistics['by_status']['voters'] }}</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Gender Distribution Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-venus-mars"></i> Gender Distribution</h3>
                <div class="chart-legend">
                    <span><span class="legend-dot male"></span> Male: {{ $statistics['by_gender']['male'] }}</span>
                    <span><span class="legend-dot female"></span> Female: {{ $statistics['by_gender']['female'] }}</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="genderChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Civil Status Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-heart"></i> Civil Status</h3>
            </div>
            <div class="chart-body">
                <canvas id="civilStatusChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @foreach($statistics['by_civil_status'] as $status)
                <div class="chart-stat-item">
                    <span class="stat-label">{{ $status->civil_status }}:</span>
                    <span class="stat-value">{{ $status->total }} ({{ round(($status->total / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Age Distribution Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-calendar"></i> Age Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="ageChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @foreach($statistics['age_distribution'] as $range => $count)
                <div class="chart-stat-item">
                    <span class="stat-label">{{ $range }}:</span>
                    <span class="stat-value">{{ $count }} ({{ round(($count / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Special Categories Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-star"></i> Special Categories</h3>
            </div>
            <div class="chart-body">
                <canvas id="specialChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                <div class="chart-stat-item">
                    <span class="stat-label">Senior Citizens:</span>
                    <span class="stat-value">{{ $statistics['by_status']['seniors'] }} ({{ round(($statistics['by_status']['seniors'] / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label">PWD:</span>
                    <span class="stat-value">{{ $statistics['by_status']['pwd'] }} ({{ round(($statistics['by_status']['pwd'] / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label">4Ps Members:</span>
                    <span class="stat-value">{{ $statistics['by_status']['4ps'] }} ({{ round(($statistics['by_status']['4ps'] / $statistics['total']) * 100, 1) }}%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Purok Distribution Bar Chart -->
    <div class="chart-card full-width">
        <div class="chart-header">
            <h3><i class="fas fa-map-pin"></i> Population by Purok</h3>
            <div class="chart-total">Total: {{ $statistics['total'] }} residents</div>
        </div>
        <div class="chart-body">
            <canvas id="purokChart" width="800" height="300"></canvas>
        </div>
        <!-- Purok Statistics with Progress Bars -->
        <div class="purok-stats">
            @foreach($statistics['by_purok'] as $purok)
            <div class="purok-stat-item">
                <span class="purok-label">Purok {{ $purok->purok }}</span>
                <span class="purok-value">{{ $purok->total }} residents</span>
                <div class="purok-bar">
                    <div class="purok-bar-fill" style="width: {{ round(($purok->total / $statistics['total']) * 100, 1) }}%;"></div>
                </div>
                <span class="purok-percentage">{{ round(($purok->total / $statistics['total']) * 100, 1) }}%</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Detailed Data Tables -->
    <div class="details-grid">
        <!-- Purok Distribution Table -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-map-pin class="detail-icon" />
                <h3>Distribution by Purok</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
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

        <!-- Civil Status Table -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-heart class="detail-icon" />
                <h3>Civil Status</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
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

        <!-- Age Distribution Table -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-calendar class="detail-icon" />
                <h3>Age Distribution</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
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

        <!-- Special Categories Table -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-star class="detail-icon" />
                <h3>Special Categories</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
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

    <!-- Residents List (Commented out - enable if needed) -->
    {{-- @if($residents->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Residents List</h3>
            <span class="record-count">Showing {{ $residents->count() }} records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Resident ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Birthdate</th>
                            <th>Age</th>
                            <th>Purok</th>
                            <th>Civil Status</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residents as $resident)
                        <tr>
                            <td>{{ $resident->resident_id }}</td>
                            <td>{{ $resident->first_name }} {{ $resident->last_name }}</td>
                            <td>{{ $resident->gender }}</td>
                            <td>{{ $resident->birthdate ? $resident->birthdate->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $resident->birthdate ? $resident->birthdate->age : 'N/A' }}</td>
                            <td>Purok {{ $resident->purok }}</td>
                            <td>{{ $resident->civil_status }}</td>
                            <td>{{ $resident->contact_number ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif --}}
</div>
@endsection

@push('styles')
<style>
/* ==================== */
/* Container & Layout   */
/* ==================== */
.container-fluid {
    padding: 1.5rem;
}

/* ==================== */
/* Page Header          */
/* ==================== */
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
    margin: 0;
}

/* ==================== */
/* Page Actions         */
/* ==================== */
.page-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.page-actions form {
    margin: 0;
    padding: 0;
    line-height: 0; /* Remove extra line-height from form */
}

/* ==================== */
/* Buttons              */
/* ==================== */
.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border-radius: 5px;
    font-size: 0.85rem;
    font-weight: 500;
    height: 40px; /* Fixed height for perfect alignment */
    line-height: 1;
    box-sizing: border-box;
    vertical-align: middle;
    white-space: nowrap;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #eef2ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.icon-small {
    width: 16px;
    height: 16px;
    display: inline-block;
    vertical-align: middle;
}

/* ==================== */
/* Filter Section       */
/* ==================== */
.filters-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-size: 0.9rem;
    font-weight: 500;
}

.filter-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-filter {
    padding: 0.5rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    height: 38px;
}

.btn-filter:hover {
    background: #5a67d8;
}

.btn-clear {
    padding: 0.5rem 1.5rem;
    background: #e2e8f0;
    color: #4a5568;
    text-decoration: none;
    border-radius: 5px;
    height: 38px;
    display: inline-flex;
    align-items: center;
}

.btn-clear:hover {
    background: #cbd5e0;
}

/* ==================== */
/* Statistics Cards     */
/* ==================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card.total .stat-icon { background: #667eea; }
.stat-card.male .stat-icon { background: #3b82f6; }
.stat-card.female .stat-icon { background: #ec4899; }
.stat-card.voters .stat-icon { background: #10b981; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

/* ==================== */
/* Charts Grid          */
/* ==================== */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chart-card.full-width {
    grid-column: span 2;
}

.chart-header {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.chart-header h3 {
    margin: 0;
    font-size: 1rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-header h3 i {
    color: #667eea;
}

.chart-legend {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
}

.legend-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 0.3rem;
}

.legend-dot.male { background: #3b82f6; }
.legend-dot.female { background: #ec4899; }

.chart-total {
    font-weight: 600;
    color: #667eea;
    background: #eef2ff;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
}

.chart-body {
    padding: 1.5rem;
    position: relative;
    height: 250px;
}

.chart-mini-table {
    padding: 0 1.5rem 1.5rem 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.5rem;
}

.chart-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.3rem 0.5rem;
    background: #f8fafc;
    border-radius: 5px;
    font-size: 0.85rem;
}

/* ==================== */
/* Purok Stats          */
/* ==================== */
.purok-stats {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.purok-stat-item {
    display: grid;
    grid-template-columns: 80px 80px 1fr 60px;
    align-items: center;
    gap: 1rem;
    font-size: 0.9rem;
}

.purok-label {
    font-weight: 600;
    color: #333;
}

.purok-value {
    color: #667eea;
    font-weight: 500;
}

.purok-bar {
    height: 20px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.purok-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 10px;
    transition: width 0.3s;
}

.purok-percentage {
    font-weight: 600;
    color: #333;
    text-align: right;
}

/* ==================== */
/* Details Grid         */
/* ==================== */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-header {
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-icon {
    width: 20px;
    height: 20px;
    color: #667eea;
}

.detail-header h3 {
    color: #333;
    font-size: 1rem;
    margin: 0;
}

.detail-body {
    padding: 1rem;
}

/* ==================== */
/* Mini Table           */
/* ==================== */
.mini-table {
    width: 100%;
    border-collapse: collapse;
}

.mini-table th {
    text-align: left;
    padding: 0.5rem;
    background: #f8f9fa;
    color: #555;
    font-size: 0.85rem;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}

.mini-table td {
    padding: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    font-size: 0.9rem;
}

.mini-table tr:last-child td {
    border-bottom: none;
}

/* ==================== */
/* Responsive Design    */
/* ==================== */
@media (max-width: 1024px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }

    .chart-card.full-width {
        grid-column: span 1;
    }

    .purok-stat-item {
        grid-template-columns: 80px 80px 1fr 60px;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }

    .purok-stat-item {
        grid-template-columns: 60px 60px 1fr 50px;
        gap: 0.5rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .page-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .page-actions form {
        width: 100%;
    }

    .page-actions .btn-primary,
    .page-actions .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .purok-stat-item {
        grid-template-columns: 1fr;
        gap: 0.3rem;
    }

    .purok-bar {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    /**
     * Gender Distribution Pie Chart
     * Shows male vs female population
     */
    new Chart(document.getElementById('genderChart'), {
        type: 'pie',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [{{ $statistics['by_gender']['male'] }}, {{ $statistics['by_gender']['female'] }}],
                backgroundColor: ['#3b82f6', '#ec4899'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    /**
     * Civil Status Pie Chart
     * Shows distribution of civil status
     */
    new Chart(document.getElementById('civilStatusChart'), {
        type: 'pie',
        data: {
            labels: [@foreach($statistics['by_civil_status'] as $status) '{{ $status->civil_status }}', @endforeach],
            datasets: [{
                data: [@foreach($statistics['by_civil_status'] as $status) {{ $status->total }}, @endforeach],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    /**
     * Age Distribution Pie Chart
     * Shows population by age ranges
     */
    new Chart(document.getElementById('ageChart'), {
        type: 'pie',
        data: {
            labels: [@foreach($statistics['age_distribution'] as $range => $count) '{{ $range }}', @endforeach],
            datasets: [{
                data: [@foreach($statistics['age_distribution'] as $count) {{ $count }}, @endforeach],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    /**
     * Special Categories Pie Chart
     * Shows senior citizens, PWD, and 4Ps members
     */
    new Chart(document.getElementById('specialChart'), {
        type: 'pie',
        data: {
            labels: ['Senior Citizens', 'PWD', '4Ps Members'],
            datasets: [{
                data: [
                    {{ $statistics['by_status']['seniors'] }},
                    {{ $statistics['by_status']['pwd'] }},
                    {{ $statistics['by_status']['4ps'] }}
                ],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = {{ $statistics['total'] }};
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    /**
     * Purok Bar Chart
     * Shows population per purok
     */
    new Chart(document.getElementById('purokChart'), {
        type: 'bar',
        data: {
            labels: [@foreach($statistics['by_purok'] as $purok) 'Purok {{ $purok->purok }}', @endforeach],
            datasets: [{
                label: 'Number of Residents',
                data: [@foreach($statistics['by_purok'] as $purok) {{ $purok->total }}, @endforeach],
                backgroundColor: '#667eea',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw || 0;
                            const total = {{ $statistics['total'] }};
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `Residents: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { display: true, color: '#e2e8f0' },
                    ticks: { stepSize: 20 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
