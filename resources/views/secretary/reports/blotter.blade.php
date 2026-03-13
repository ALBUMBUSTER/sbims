@extends('layouts.app')

@section('title', 'Blotter Report')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Blotter Report</h1>
            <p>Case status and incident statistics</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.reports.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Reports
            </a>
            <form action="{{ route('secretary.reports.export') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="type" value="blotter">
                <input type="hidden" name="format" value="excel">
                @if(request('date_from'))
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                @endif
                @if(request('date_to'))
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <button type="submit" class="btn-primary">
                    <i class="fas fa-file-excel icon-small"></i>
                    Export to Excel
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filters-section">
        <form action="{{ route('secretary.reports.blotter') }}" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="date_from">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="filter-input">
            </div>
            <div class="filter-group">
                <label for="date_to">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="filter-input">
            </div>
            <div class="filter-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="filter-input">
                    <option value="">All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Investigating" {{ request('status') == 'Investigating' ? 'selected' : '' }}>Investigating</option>
                    <option value="Hearings" {{ request('status') == 'Hearings' ? 'selected' : '' }}>Hearings</option>
                    <option value="Settled" {{ request('status') == 'Settled' ? 'selected' : '' }}>Settled</option>
                    <option value="Referred" {{ request('status') == 'Referred' ? 'selected' : '' }}>Referred</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Generate</button>
                <a href="{{ route('secretary.reports.blotter') }}" class="btn-clear">Reset</a>
            </div>
        </form>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <x-heroicon-o-scale />
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Cases</span>
                <span class="stat-value">{{ $statistics['total'] }}</span>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <x-heroicon-o-clock />
            </div>
            <div class="stat-content">
                <span class="stat-label">Pending</span>
                <span class="stat-value">{{ $statistics['by_status']['pending'] }}</span>
            </div>
        </div>

        <div class="stat-card ongoing">
            <div class="stat-icon">
                <x-heroicon-o-arrow-path />
            </div>
            <div class="stat-content">
                <span class="stat-label">Ongoing</span>
                <span class="stat-value">{{ $statistics['by_status']['ongoing'] }}</span>
            </div>
        </div>

        <div class="stat-card settled">
            <div class="stat-icon">
                <x-heroicon-o-check-circle />
            </div>
            <div class="stat-content">
                <span class="stat-label">Settled</span>
                <span class="stat-value">{{ $statistics['by_status']['settled'] }}</span>
            </div>
        </div>

        <div class="stat-card referred">
            <div class="stat-icon">
                <x-heroicon-o-arrow-right-circle />
            </div>
            <div class="stat-content">
                <span class="stat-label">Referred</span>
                <span class="stat-value">{{ $statistics['by_status']['referred'] ?? 0 }}</span>
            </div>
        </div>

        <div class="stat-card resolution">
            <div class="stat-icon">
                <x-heroicon-o-chart-bar />
            </div>
            <div class="stat-content">
                <span class="stat-label">Resolution Rate</span>
                <span class="stat-value">{{ $statistics['resolution_rate'] }}%</span>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Status Distribution Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Case Status Distribution</h3>
                <div class="chart-total">Total: {{ $statistics['total'] }} cases</div>
            </div>
            <div class="chart-body">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot pending"></span> Pending:</span>
                    <span class="stat-value">{{ $statistics['by_status']['pending'] }} ({{ round(($statistics['by_status']['pending'] / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot ongoing"></span> Ongoing:</span>
                    <span class="stat-value">{{ $statistics['by_status']['ongoing'] }} ({{ round(($statistics['by_status']['ongoing'] / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot settled"></span> Settled:</span>
                    <span class="stat-value">{{ $statistics['by_status']['settled'] }} ({{ round(($statistics['by_status']['settled'] / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot referred"></span> Referred:</span>
                    <span class="stat-value">{{ $statistics['by_status']['referred'] ?? 0 }} ({{ round(($statistics['by_status']['referred'] ?? 0 / $statistics['total']) * 100, 1) }}%)</span>
                </div>
            </div>
        </div>

        <!-- Incident Type Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-list"></i> Incident Type Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="incidentTypeChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @foreach($statistics['by_type'] as $type)
                <div class="chart-stat-item">
                    <span class="stat-label">{{ $type->incident_type }}:</span>
                    <span class="stat-value">{{ $type->total }} ({{ round(($type->total / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Monthly Case Trend (Last 6 Months)</h3>
            </div>
            <div class="chart-body">
                <canvas id="monthlyTrendChart" width="800" height="250"></canvas>
            </div>
            <div class="purok-stats" style="padding: 0.5rem 1rem 1rem;">
                @foreach($statistics['monthly_trend'] as $trend)
                <div class="purok-stat-item" style="grid-template-columns: 80px 1fr 50px;">
                    <span class="purok-label">{{ date('M Y', mktime(0, 0, 0, $trend->month, 1, $trend->year)) }}</span>
                    <span class="purok-value">{{ $trend->total }} cases</span>
                    <span class="purok-percentage">{{ round(($trend->total / $statistics['monthly_trend']->max('total')) * 100, 1) }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Tables -->
    <div class="details-grid" style="margin-top: 1.5rem;">
        <!-- By Incident Type Table -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-document-text class="detail-icon" />
                <h3>Distribution by Incident Type</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Incident Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statistics['by_type'] as $type)
                        <tr>
                            <td>{{ $type->incident_type }}</td>
                            <td>{{ $type->total }}</td>
                            <td>{{ round(($type->total / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Trend Table -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-chart-bar class="detail-icon" />
                <h3>Monthly Trend (Last 6 Months)</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statistics['monthly_trend'] as $trend)
                        <tr>
                            <td>{{ date('F', mktime(0, 0, 0, $trend->month, 1)) }}</td>
                            <td>{{ $trend->year }}</td>
                            <td>{{ $trend->total }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Case Resolution Timeline -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-clock class="detail-icon" />
                <h3>Case Resolution Timeline</h3>
            </div>
            <div class="detail-body">
                <div class="stats-mini-grid" style="display: grid; gap: 0.5rem;">
                    <div class="chart-stat-item" style="justify-content: space-between;">
                        <span class="stat-label">Average Resolution Time:</span>
                        <span class="stat-value">{{ $statistics['avg_resolution_days'] ?? 0 }} days</span>
                    </div>
                    <div class="chart-stat-item" style="justify-content: space-between;">
                        <span class="stat-label">Fastest Resolution:</span>
                        <span class="stat-value">{{ $statistics['min_resolution_days'] ?? 0 }} days</span>
                    </div>
                    <div class="chart-stat-item" style="justify-content: space-between;">
                        <span class="stat-label">Slowest Resolution:</span>
                        <span class="stat-value">{{ $statistics['max_resolution_days'] ?? 0 }} days</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blotters List -->
    {{-- @if($blotters->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Blotter Cases List</h3>
            <span class="record-count">Showing {{ $blotters->firstItem() }} - {{ $blotters->lastItem() }} of {{ $blotters->total() }} records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case #</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Incident Type</th>
                            <th>Incident Date</th>
                            <th>Status</th>
                            <th>Filed Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blotters as $blotter)
                        <tr>
                            <td><span class="case-id">{{ $blotter->case_id }}</span></td>
                            <td>
                                @if($blotter->complainant)
                                    {{ $blotter->complainant->first_name }} {{ $blotter->complainant->last_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $blotter->respondent_name }}</td>
                            <td>{{ $blotter->incident_type }}</td>
                            <td>{{ $blotter->incident_date ? $blotter->incident_date->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                @php
                                    $statusClass = match(strtolower($blotter->status)) {
                                        'pending' => 'status-pending',
                                        'investigating', 'ongoing' => 'status-investigating',
                                        'hearings' => 'status-hearings',
                                        'settled' => 'status-settled',
                                        'referred' => 'status-referred',
                                        default => 'status-default'
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $blotter->status }}
                                </span>
                            </td>
                            <td>{{ $blotter->created_at ? $blotter->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('secretary.blotter.show', $blotter) }}" class="btn-view" target="_blank">
                                    <x-heroicon-o-eye class="icon-small" />
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $blotters->appends(request()->query())->links() }}
            </div>
        </div>
    </div> --}}
    {{-- @else
    <div class="empty-state">
        <x-heroicon-o-scale class="empty-icon" />
        <h3>No Blotter Cases Found</h3>
        <p>There are no blotter cases matching your criteria.</p>
        <a href="{{ route('secretary.blotter.create') }}" class="btn-primary">
            <x-heroicon-o-plus class="icon-small" />
            Create New Case
        </a>
    </div>
    @endif --}}
</div>
@endsection

@push('styles')
<style>
.container-fluid {
    padding: 1.2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 0.8rem;
}

.page-title h1 {
    color: #333;
    margin-bottom: 0.4rem;
    font-size: 1.4rem;
}

.page-title p {
    color: #666;
    font-size: 0.8rem;
}

/* Buttons */
.btn-primary, .btn-secondary, .btn-view {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.2rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #eef2ff;
}

.btn-view {
    padding: 0.3rem 0.6rem;
    background: #f3f4f6;
    color: #4b5563;
    font-size: 0.7rem;
}

.btn-view:hover {
    background: #e5e7eb;
}

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 10px;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 0.8rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 140px;
}

.filter-group label {
    display: block;
    margin-bottom: 0.4rem;
    color: #555;
    font-size: 0.75rem;
    font-weight: 500;
}

.filter-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.8rem;
}

.filter-actions {
    display: flex;
    gap: 0.4rem;
    align-items: center;
}

.btn-filter {
    padding: 0.5rem 1.2rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.8rem;
}

.btn-filter:hover {
    background: #5a67d8;
}

.btn-clear {
    padding: 0.5rem 1.2rem;
    background: #e2e8f0;
    color: #4a5568;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.8rem;
}

.btn-clear:hover {
    background: #cbd5e0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card.total .stat-icon { background: #667eea; }
.stat-card.pending .stat-icon { background: #f59e0b; }
.stat-card.ongoing .stat-icon { background: #8b5cf6; }
.stat-card.settled .stat-icon { background: #10b981; }
.stat-card.referred .stat-icon { background: #ef4444; }
.stat-card.resolution .stat-icon { background: #3b82f6; }

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.8rem;
}

.stat-icon svg {
    width: 20px;
    height: 20px;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.7rem;
    margin-bottom: 0.2rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 1.2rem;
    font-weight: bold;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
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
    padding: 0.8rem 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.chart-header h3 {
    margin: 0;
    font-size: 0.85rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.chart-header h3 i {
    color: #667eea;
    font-size: 0.9rem;
}

.chart-total {
    font-weight: 600;
    color: #667eea;
    background: #eef2ff;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.7rem;
}

.chart-body {
    padding: 1rem;
    position: relative;
    height: 200px;
}

.chart-mini-table {
    padding: 0 1rem 1rem 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.4rem;
}

.chart-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.2rem 0.4rem;
    background: #f8fafc;
    border-radius: 5px;
    font-size: 0.7rem;
}

.chart-stat-item .stat-label {
    color: #666;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.chart-stat-item .stat-value {
    color: #333;
    font-weight: 600;
    font-size: 0.7rem;
}

.legend-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.legend-dot.pending { background: #f59e0b; }
.legend-dot.ongoing { background: #8b5cf6; }
.legend-dot.settled { background: #10b981; }
.legend-dot.referred { background: #ef4444; }

/* Purok Stats (reused for trend) */
.purok-stats {
    padding: 0.5rem 1rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.purok-stat-item {
    display: grid;
    grid-template-columns: 80px 1fr 50px;
    align-items: center;
    gap: 0.8rem;
    font-size: 0.7rem;
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
    height: 16px;
    background: #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.purok-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 8px;
    transition: width 0.3s;
}

.purok-percentage {
    font-weight: 600;
    color: #333;
    text-align: right;
    font-size: 0.7rem;
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-header {
    padding: 0.7rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.detail-icon {
    width: 16px;
    height: 16px;
    color: #667eea;
}

.detail-header h3 {
    color: #333;
    font-size: 0.8rem;
    margin: 0;
}

.detail-body {
    padding: 0.7rem;
}

/* Mini Table */
.mini-table {
    width: 100%;
    border-collapse: collapse;
}

.mini-table th {
    text-align: left;
    padding: 0.4rem;
    background: #f8f9fa;
    color: #555;
    font-size: 0.65rem;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}

.mini-table td {
    padding: 0.4rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    font-size: 0.7rem;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.65rem;
    font-weight: 600;
    min-width: 70px;
    text-align: center;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-investigating, .status-ongoing {
    background: #dbeafe;
    color: #1e40af;
}

.status-hearings {
    background: #ede9fe;
    color: #6d28d9;
}

.status-settled {
    background: #d1fae5;
    color: #065f46;
}

.status-referred {
    background: #fee2e2;
    color: #991b1b;
}

.status-default {
    background: #f3f4f6;
    color: #4b5563;
}

/* Card */
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-top: 1.5rem;
}

.card-header {
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    color: #333;
    font-size: 0.9rem;
    margin: 0;
}

.record-count {
    color: #666;
    font-size: 0.7rem;
}

.card-body {
    padding: 1rem;
}

/* Data Table */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
}

.data-table th {
    text-align: left;
    padding: 0.7rem;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    padding: 0.7rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
}

/* Case ID */
.case-id {
    font-family: monospace;
    font-weight: 600;
    color: #667eea;
    background: #f0f3ff;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
}

/* Pagination */
.pagination-wrapper {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    display: flex;
    gap: 0.3rem;
    list-style: none;
    padding: 0;
}

.pagination-wrapper .page-item .page-link {
    padding: 0.3rem 0.6rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    color: #667eea;
    text-decoration: none;
    font-size: 0.7rem;
}

.pagination-wrapper .page-item.active .page-link {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 1.5rem;
}

.empty-icon {
    width: 48px;
    height: 48px;
    color: #a0aec0;
    margin-bottom: 0.8rem;
}

.empty-state h3 {
    color: #4a5568;
    font-size: 1rem;
    margin-bottom: 0.4rem;
}

.empty-state p {
    color: #718096;
    font-size: 0.8rem;
    margin-bottom: 1rem;
}

.icon-small {
    width: 14px;
    height: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }

    .chart-card.full-width {
        grid-column: span 1;
    }
}

@media (max-width: 768px) {
    .page-actions {
        width: 100%;
        flex-direction: column;
    }

    .btn-primary, .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }

    .purok-stat-item {
        grid-template-columns: 80px 1fr 45px;
        gap: 0.4rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .purok-stat-item {
        grid-template-columns: 1fr;
        gap: 0.2rem;
    }

    .chart-mini-table {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Pie Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: ['Pending', 'Ongoing', 'Settled', 'Referred'],
            datasets: [{
                data: [
                    {{ $statistics['by_status']['pending'] }},
                    {{ $statistics['by_status']['ongoing'] }},
                    {{ $statistics['by_status']['settled'] }},
                    {{ $statistics['by_status']['referred'] ?? 0 }}
                ],
                backgroundColor: ['#f59e0b', '#8b5cf6', '#10b981', '#ef4444'],
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

    // Incident Type Pie Chart
    new Chart(document.getElementById('incidentTypeChart'), {
        type: 'pie',
        data: {
            labels: [@foreach($statistics['by_type'] as $type) '{{ $type->incident_type }}', @endforeach],
            datasets: [{
                data: [@foreach($statistics['by_type'] as $type) {{ $type->total }}, @endforeach],
                backgroundColor: ['#667eea', '#8b5cf6', '#10b981', '#f59e0b', '#ec4899', '#3b82f6'],
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

    // Monthly Trend Line Chart
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: [@foreach($statistics['monthly_trend'] as $trend) '{{ date('M', mktime(0, 0, 0, $trend->month, 1)) }}', @endforeach],
            datasets: [{
                label: 'Number of Cases',
                data: [@foreach($statistics['monthly_trend'] as $trend) {{ $trend->total }}, @endforeach],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
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
                            return `Cases: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#e2e8f0' },
                    ticks: {
                        stepSize: 1,
                        font: { size: 9 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 } }
                }
            }
        }
    });
});
</script>
@endpush
