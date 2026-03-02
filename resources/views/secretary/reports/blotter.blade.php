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
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-document-arrow-down class="icon-small" />
                    Export PDF
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

    <!-- Detailed Statistics -->
    <div class="details-grid">
        <!-- By Incident Type -->
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
                            <td>
                                <div class="percentage-bar">
                                    <span class="percentage-value">{{ round(($type->total / $statistics['total']) * 100, 1) }}%</span>
                                    <div class="progress-bar">
<div class="progress-fill" style="width: <?php echo round(($type->total / $statistics['total']) * 100, 1); ?>%;"></div>                                    </div>
                                </div>
                            </td>
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

        <!-- Monthly Trend -->
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
                            <td>
                                <span class="trend-badge">{{ $trend->total }}</span>
                            </td>
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
    </div>

    <!-- Blotters List -->
    @if($blotters->count() > 0)
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
                                    <div class="resident-info">
                                        <span class="resident-name">{{ $blotter->complainant->first_name }} {{ $blotter->complainant->last_name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="respondent-info">
                                    <span class="respondent-name">{{ $blotter->respondent_name }}</span>
                                    @if($blotter->respondent_address)
                                        <small class="respondent-address">{{ Str::limit($blotter->respondent_address, 30) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="incident-type">{{ $blotter->incident_type }}</span>
                            </td>
                            <td>
                                <span class="incident-date">{{ $blotter->incident_date ? $blotter->incident_date->format('M d, Y') : 'N/A' }}</span>
                            </td>
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
                            <td>
                                <span class="filed-date">{{ $blotter->created_at ? $blotter->created_at->format('M d, Y') : 'N/A' }}</span>
                            </td>
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
    </div>
    @else
    <div class="empty-state">
        <x-heroicon-o-scale class="empty-icon" />
        <h3>No Blotter Cases Found</h3>
        <p>There are no blotter cases matching your criteria.</p>
        <a href="{{ route('secretary.blotter.create') }}" class="btn-primary">
            <x-heroicon-o-plus class="icon-small" />
            Create New Case
        </a>
    </div>
    @endif
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

/* Buttons */
.btn-primary, .btn-secondary, .btn-view {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
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

.btn-view {
    padding: 0.4rem 0.8rem;
    background: #f3f4f6;
    color: #4b5563;
    font-size: 0.85rem;
}

.btn-view:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* Filters Section */
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
    font-size: 0.95rem;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
    font-size: 0.95rem;
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
    font-size: 0.95rem;
}

.btn-clear:hover {
    background: #cbd5e0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.stat-card.total .stat-icon { background: #667eea; }
.stat-card.pending .stat-icon { background: #f59e0b; }
.stat-card.ongoing .stat-icon { background: #8b5cf6; }
.stat-card.settled .stat-icon { background: #10b981; }
.stat-card.referred .stat-icon { background: #ef4444; }
.stat-card.resolution .stat-icon { background: #3b82f6; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
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
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.detail-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.detail-header {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.detail-icon {
    width: 20px;
    height: 20px;
    color: #667eea;
}

.detail-header h3 {
    color: #333;
    font-size: 1.1rem;
    margin: 0;
    font-weight: 600;
}

.detail-body {
    padding: 1.5rem;
}

/* Mini Table */
.mini-table {
    width: 100%;
    border-collapse: collapse;
}

.mini-table th {
    text-align: left;
    padding: 0.75rem 0.5rem;
    background: #f8f9fa;
    color: #555;
    font-size: 0.85rem;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.mini-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    font-size: 0.95rem;
}

.mini-table tr:last-child td {
    border-bottom: none;
}

.mini-table tr:hover td {
    background: #f8fafc;
}

/* Percentage Bar */
.percentage-bar {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.percentage-value {
    font-size: 0.85rem;
    color: #4b5563;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Trend Badge */
.trend-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #667eea;
    color: white;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 500;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    min-width: 90px;
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
    margin-top: 2rem;
}

.card-header {
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.card-header h3 {
    color: #333;
    font-size: 1.2rem;
    margin: 0;
    font-weight: 600;
}

.record-count {
    color: #666;
    font-size: 0.95rem;
    background: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
}

.card-body {
    padding: 1.5rem;
}

/* Data Table */
.table-responsive {
    overflow-x: auto;
    border-radius: 8px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.data-table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #4a5568;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #2d3748;
}

.data-table tr:hover td {
    background: #f8fafc;
}

/* Case ID */
.case-id {
    font-family: monospace;
    font-weight: 600;
    color: #667eea;
    background: #f0f3ff;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
}

/* Resident and Respondent Info */
.resident-info, .respondent-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.resident-name, .respondent-name {
    font-weight: 500;
    color: #2d3748;
}

.respondent-address {
    color: #718096;
    font-size: 0.85rem;
}

/* Incident Type */
.incident-type {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #f3f4f6;
    color: #4b5563;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 500;
}

/* Incident Date */
.incident-date, .filed-date {
    color: #4b5563;
    font-size: 0.9rem;
    white-space: nowrap;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.empty-icon {
    width: 64px;
    height: 64px;
    color: #a0aec0;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #4a5568;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #718096;
    margin-bottom: 1.5rem;
}

/* Pagination */
.pagination-wrapper {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
}

.pagination-wrapper .page-item .page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    color: #667eea;
    text-decoration: none;
    transition: all 0.3s;
}

.pagination-wrapper .page-item .page-link:hover {
    background: #f0f3ff;
    border-color: #667eea;
}

.pagination-wrapper .page-item.active .page-link {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #cbd5e0;
    pointer-events: none;
    background: #f8f9fa;
}

/* Text utilities */
.text-center {
    text-align: center;
}

.text-muted {
    color: #718096;
}

.icon-small {
    width: 16px;
    height: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .filters-form {
        flex-direction: column;
    }

    .filter-group {
        width: 100%;
    }

    .filter-actions {
        width: 100%;
        justify-content: flex-end;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .page-actions {
        width: 100%;
        display: flex;
        gap: 0.5rem;
    }

    .btn-primary, .btn-secondary {
        flex: 1;
        justify-content: center;
    }
}
</style>
@endpush
