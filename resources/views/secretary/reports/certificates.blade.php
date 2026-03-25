@extends('layouts.app')

@section('title', 'Certificates Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Certificates Report</h1>
            <p>Certificate requests and issuance statistics</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.reports.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left icon-small"></i>
                Back to Reports
            </a>
            <form action="{{ route('secretary.reports.export') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="certificates">
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

    <!-- Filter Section -->
    <div class="filters-section">
        <form action="{{ route('secretary.reports.certificates') }}" method="GET" class="filters-form">
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
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Released" {{ request('status') == 'Released' ? 'selected' : '' }}>Released</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Generate</button>
                <a href="{{ route('secretary.reports.certificates') }}" class="btn-clear">Reset</a>
            </div>
        </form>
    </div>

    <!-- Key Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon"><x-heroicon-o-document-text /></div>
            <div class="stat-content">
                <span class="stat-label">Total Certificates</span>
                <span class="stat-value">{{ $statistics['total'] }}</span>
            </div>
        </div>
        <div class="stat-card pending">
            <div class="stat-icon"><x-heroicon-o-clock /></div>
            <div class="stat-content">
                <span class="stat-label">Pending</span>
                <span class="stat-value">{{ $statistics['by_status']['pending'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card approved">
            <div class="stat-icon"><x-heroicon-o-check-circle /></div>
            <div class="stat-content">
                <span class="stat-label">Approved</span>
                <span class="stat-value">{{ $statistics['by_status']['approved'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card released">
            <div class="stat-icon"><x-heroicon-o-check-badge /></div>
            <div class="stat-content">
                <span class="stat-label">Released</span>
                <span class="stat-value">{{ $statistics['by_status']['released'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-icon"><x-heroicon-o-x-circle /></div>
            <div class="stat-content">
                <span class="stat-label">Rejected</span>
                <span class="stat-value">{{ $statistics['by_status']['rejected'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Certificate Status Distribution Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Certificate Status Distribution</h3>
                <div class="chart-total">Total: {{ $statistics['total'] }} certificates</div>
            </div>
            <div class="chart-body">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @php
                    $certPending = $statistics['by_status']['pending'] ?? 0;
                    $certApproved = $statistics['by_status']['approved'] ?? 0;
                    $certReleased = $statistics['by_status']['released'] ?? 0;
                    $certRejected = $statistics['by_status']['rejected'] ?? 0;
                    $certTotal = max($statistics['total'], 1);
                @endphp
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot pending"></span> Pending:</span>
                    <span class="stat-value">{{ $certPending }} ({{ round(($certPending / $certTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot approved"></span> Approved:</span>
                    <span class="stat-value">{{ $certApproved }} ({{ round(($certApproved / $certTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot released"></span> Released:</span>
                    <span class="stat-value">{{ $certReleased }} ({{ round(($certReleased / $certTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot rejected"></span> Rejected:</span>
                    <span class="stat-value">{{ $certRejected }} ({{ round(($certRejected / $certTotal) * 100, 1) }}%)</span>
                </div>
            </div>
        </div>

        <!-- Certificate Type Distribution Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-file-alt"></i> Certificate Type Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="typeChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @php
                    $typeColors = ['#667eea', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6'];
                @endphp
                @foreach($statistics['by_type'] as $index => $type)
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot" style="background: {{ $typeColors[$index % count($typeColors)] }};"></span> {{ $type->certificate_type }}:</span>
                    <span class="stat-value">{{ $type->total }} ({{ round(($type->total / $statistics['total']) * 100, 1) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Monthly Trend Line Chart (Commented out) -->
        {{-- <div class="chart-card full-width">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Monthly Certificate Requests (Last 6 Months)</h3>
            </div>
            <div class="chart-body">
                <canvas id="monthlyTrendChart" width="800" height="250"></canvas>
            </div>
            <div class="trend-stats">
                @foreach($statistics['monthly_trend'] as $trend)
                <div class="trend-stat-item">
                    <span class="trend-label">{{ date('M Y', mktime(0, 0, 0, $trend->month, 1, $trend->year)) }}</span>
                    <span class="trend-value">{{ $trend->total }} requests</span>
                    <div class="trend-bar">
                        <div class="trend-bar-fill" style="width: {{ round(($trend->total / $statistics['monthly_trend']->max('total')) * 100, 1) }}%;"></div>
                    </div>
                    <span class="trend-percentage">{{ round(($trend->total / $statistics['monthly_trend']->max('total')) * 100, 1) }}%</span>
                </div>
                @endforeach
            </div>
        </div> --}}
    </div>

    <!-- Detailed Data Tables - 2x2 Grid Layout -->
    <div class="details-grid">
        <!-- Top Left: Distribution by Certificate Type -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-document-text class="detail-icon" />
                <h3>Distribution by Certificate Type</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Certificate Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statistics['by_type'] as $type)
                        <tr>
                            <td>{{ $type->certificate_type }}</td>
                            <td>{{ $type->total }}</td>
                            <td>{{ round(($type->total / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Right: Monthly Trend Table -->
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
                        @foreach($statistics['monthly_trend'] as $trend)
                        <tr>
                            <td>{{ date('F', mktime(0, 0, 0, $trend->month, 1)) }}</td>
                            <td>{{ $trend->year }}</td>
                            <td>{{ $trend->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Left: Status Summary Table -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-check-circle class="detail-icon" />
                <h3>Status Summary</h3>
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
                        <tr>
                            <td><span class="pending" style="margin-right: 0.3rem;"></span> Pending</td>
                            <td>{{ $certPending }}</td>
                            <td>{{ round(($certPending / $certTotal) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td><span class="approved" style="margin-right: 0.3rem;"></span> Approved</td>
                            <td>{{ $certApproved }}</td>
                            <td>{{ round(($certApproved / $certTotal) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td><span class="released" style="margin-right: 0.3rem;"></span> Released</td>
                            <td>{{ $certReleased }}</td>
                            <td>{{ round(($certReleased / $certTotal) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td><span class="rejected" style="margin-right: 0.3rem;"></span> Rejected</td>
                            <td>{{ $certRejected }}</td>
                            <td>{{ round(($certRejected / $certTotal) * 100, 1) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Right: Processing Statistics (Commented out) -->
        {{-- <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-clock class="detail-icon" />
                <h3>Processing Statistics</h3>
            </div>
            <div class="detail-body">
                <div class="stats-mini-grid">
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Issuance Rate:</span>
                        <span class="stat-mini-value">{{ $statistics['issuance_rate'] ?? 0 }}%</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Avg Processing Time:</span>
                        <span class="stat-mini-value">{{ $statistics['avg_processing_days'] ?? 0 }} days</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Fastest Processing:</span>
                        <span class="stat-mini-value">{{ $statistics['min_processing_days'] ?? 0 }} days</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Slowest Processing:</span>
                        <span class="stat-mini-value">{{ $statistics['max_processing_days'] ?? 0 }} days</span>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Certificates List (Commented out) -->
    {{-- @if($certificates->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Certificates List</h3>
            <span class="record-count">Showing {{ $certificates->firstItem() }} - {{ $certificates->lastItem() }} of {{ $certificates->total() }} records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Certificate #</th>
                            <th>Resident Name</th>
                            <th>Certificate Type</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Request Date</th>
                            <th>Release Date</th>
                            <th>Processing Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($certificates as $certificate)
                        <tr>
                            <td>{{ $certificate->certificate_id }}</td>
                            <td>
                                @if($certificate->resident)
                                    {{ $certificate->resident->first_name }} {{ $certificate->resident->last_name }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $certificate->certificate_type }}</td>
                            <td>{{ Str::limit($certificate->purpose, 20) }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($certificate->status) }}">
                                    {{ $certificate->status }}
                                </span>
                            </td>
                            <td>{{ $certificate->created_at ? $certificate->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $certificate->released_at ? $certificate->released_at->format('M d, Y') : 'Not released' }}</td>
                            <td>
                                @if($certificate->released_at && $certificate->created_at)
                                    @php
                                        $processingDays = $certificate->created_at->diffInDays($certificate->released_at);
                                    @endphp
                                    <span class="processing-days">{{ $processingDays }} days</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $certificates->appends(request()->query())->links() }}
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
.container-fluid { padding: 1.2rem; }

/* ==================== */
/* Page Header          */
/* ==================== */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.page-title h1 {
    color: #333;
    margin-bottom: 0.4rem;
    font-size: 1.4rem;
}
.page-title p {
    color: #666;
    font-size: 0.8rem;
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
    line-height: 0;
}

/* ==================== */
/* Buttons              */
/* ==================== */
.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border-radius: 5px;
    font-size: 0.85rem;
    font-weight: 500;
    height: 40px;
    line-height: 1;
    box-sizing: border-box;
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
    height: 38px;
    display: inline-flex;
    align-items: center;
}
.btn-filter:hover { background: #5a67d8; }
.btn-clear {
    padding: 0.5rem 1.2rem;
    background: #e2e8f0;
    color: #4a5568;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.8rem;
    height: 38px;
    display: inline-flex;
    align-items: center;
}
.btn-clear:hover { background: #cbd5e0; }

/* ==================== */
/* Statistics Cards     */
/* ==================== */
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
.stat-card.approved .stat-icon { background: #10b981; }
.stat-card.released .stat-icon { background: #3b82f6; }
.stat-card.rejected .stat-icon { background: #ef4444; }
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
.stat-content { flex: 1; }
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

/* ==================== */
/* Charts Grid          */
/* ==================== */
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
.chart-card.full-width { grid-column: span 2; }
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
.chart-header h3 i { color: #667eea; font-size: 0.9rem; }
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

/* ==================== */
/* Chart Mini Table     */
/* ==================== */
.chart-mini-table {
    padding: 0 1rem 1rem 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.5rem;
    border-top: 1px dashed #e2e8f0;
    margin-top: 0.5rem;
    padding-top: 1rem;
}
.chart-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0.6rem;
    background: #f8fafc;
    border-radius: 6px;
    font-size: 0.75rem;
    transition: all 0.2s ease;
}
.chart-stat-item:hover {
    background: #f1f5f9;
    transform: translateY(-1px);
}
.chart-stat-item .stat-label {
    color: #4a5568;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
}
.chart-stat-item .stat-value {
    color: #2d3748;
    font-weight: 600;
    font-size: 0.75rem;
    background: white;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    min-width: 65px;
    text-align: center;
}

/* ==================== */
/* Legend Dots          */
/* ==================== */
/*enable if needed the dots*/
/* .legend-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.legend-dot.pending { background: #f59e0b; }
.legend-dot.approved { background: #10b981; }
.legend-dot.released { background: #3b82f6; }
.legend-dot.rejected { background: #ef4444; } */

/* ==================== */
/* Trend Stats          */
/* ==================== */
.trend-stats {
    padding: 0.5rem 1rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.trend-stat-item {
    display: grid;
    grid-template-columns: 80px 1fr 50px;
    align-items: center;
    gap: 0.8rem;
    font-size: 0.7rem;
}
.trend-label { font-weight: 600; color: #333; }
.trend-value { color: #667eea; font-weight: 500; }
.trend-bar {
    height: 16px;
    background: #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.trend-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 8px;
    transition: width 0.3s;
}
.trend-percentage {
    font-weight: 600;
    color: #333;
    text-align: right;
    font-size: 0.7rem;
}

/* ==================== */
/* Details Grid - 2x2 Layout */
/* ==================== */
.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}
.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 300px;
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
    flex: 1;
    overflow-y: auto;
}

/* ==================== */
/* Mini Table           */
/* ==================== */
.mini-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
}
.mini-table th {
    text-align: left;
    padding: 0.5rem 0.4rem;
    background: #f8f9fa;
    color: #555;
    font-size: 0.7rem;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 1;
}
.mini-table td {
    padding: 0.5rem 0.4rem;
    border-bottom: 1px solid #edf2f7;
    color: #4a5568;
}
.mini-table tr:last-child td { border-bottom: none; }
.mini-table tbody tr:hover { background: #f7fafc; }

/* ==================== */
/* Stats Mini Grid      */
/* ==================== */
.stats-mini-grid {
    display: grid;
    gap: 0.5rem;
}
.stat-mini-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0.6rem;
    background: #f8fafc;
    border-radius: 5px;
    font-size: 0.7rem;
}
.stat-mini-label { color: #666; font-weight: 500; }
.stat-mini-value { color: #333; font-weight: 600; }

/* ==================== */
/* Status Badges        */
/* ==================== */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.65rem;
    font-weight: 600;
    min-width: 70px;
    text-align: center;
}
.status-pending { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-released { background: #dbeafe; color: #1e40af; }
.status-rejected { background: #fee2e2; color: #991b1b; }

/* ==================== */
/* Card                 */
/* ==================== */
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
.card-header h3 { color: #333; font-size: 0.9rem; margin: 0; }
.record-count { color: #666; font-size: 0.7rem; }
.card-body { padding: 1rem; }

/* ==================== */
/* Data Table           */
/* ==================== */
.table-responsive { overflow-x: auto; }
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

/* ==================== */
/* Pagination           */
/* ==================== */
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

/* ==================== */
/* Utilities            */
/* ==================== */
.text-muted { color: #718096; }

/* ==================== */
/* Responsive Design    */
/* ==================== */
@media (max-width: 1024px) {
    .charts-grid { grid-template-columns: 1fr; }
    .chart-card.full-width { grid-column: span 1; }
    .details-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .page-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    .page-actions form { width: 100%; }
    .btn-primary, .btn-secondary { width: 100%; justify-content: center; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-actions { width: 100%; }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
    .chart-mini-table { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<!-- Chart.js CDN with fallback for offline use -->
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Safely get statistics with fallbacks
    const statistics = {
        total: {{ $statistics['total'] ?? 0 }},
        by_status: {
            pending: {{ $statistics['by_status']['pending'] ?? 0 }},
            approved: {{ $statistics['by_status']['approved'] ?? 0 }},
            released: {{ $statistics['by_status']['released'] ?? 0 }},
            rejected: {{ $statistics['by_status']['rejected'] ?? 0 }}
        },
        by_type: @json($statistics['by_type'] ?? []),
        monthly_trend: @json($statistics['monthly_trend'] ?? [])
    };

    /**
     * Certificate Status Distribution Pie Chart
     */
    if (document.getElementById('statusChart')) {
        new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'Approved', 'Released', 'Rejected'],
                datasets: [{
                    data: [
                        statistics.by_status.pending,
                        statistics.by_status.approved,
                        statistics.by_status.released,
                        statistics.by_status.rejected
                    ],
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    /**
     * Certificate Type Distribution Pie Chart
     */
    if (document.getElementById('typeChart') && statistics.by_type.length > 0) {
        const typeLabels = statistics.by_type.map(item => item.certificate_type);
        const typeData = statistics.by_type.map(item => item.total);
        const backgroundColors = ['#667eea', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6'];

        new Chart(document.getElementById('typeChart'), {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: backgroundColors.slice(0, typeData.length),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    /**
     * Monthly Trend Line Chart (Commented out)
     */
    // if (document.getElementById('monthlyTrendChart') && statistics.monthly_trend.length > 0) {
    //     const monthLabels = statistics.monthly_trend.map(item => {
    //         const date = new Date(item.year, item.month - 1, 1);
    //         return date.toLocaleString('default', { month: 'short' });
    //     });
    //     const monthData = statistics.monthly_trend.map(item => item.total);

    //     new Chart(document.getElementById('monthlyTrendChart'), {
    //         type: 'line',
    //         data: {
    //             labels: monthLabels,
    //             datasets: [{
    //                 label: 'Number of Requests',
    //                 data: monthData,
    //                 borderColor: '#667eea',
    //                 backgroundColor: 'rgba(102, 126, 234, 0.1)',
    //                 tension: 0.4,
    //                 fill: true,
    //                 pointBackgroundColor: '#667eea',
    //                 pointBorderColor: '#fff',
    //                 pointBorderWidth: 2,
    //                 pointRadius: 4,
    //                 pointHoverRadius: 6
    //             }]
    //         },
    //         options: {
    //             responsive: true,
    //             maintainAspectRatio: false,
    //             plugins: { legend: { display: false } },
    //             scales: {
    //                 y: {
    //                     beginAtZero: true,
    //                     grid: { color: '#e2e8f0' },
    //                     ticks: { stepSize: 1, font: { size: 9 } }
    //                 },
    //                 x: {
    //                     grid: { display: false },
    //                     ticks: { font: { size: 9 } }
    //                 }
    //             }
    //         }
    //     });
    // }
});
</script>
@endpush
