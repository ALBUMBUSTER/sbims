@extends('layouts.app')

@section('title', 'Summary Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Summary Report</h1>
            <p>Yearly overview for {{ $statistics['year'] ?? date('Y') }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.reports.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Reports
            </a>
            <form action="{{ route('secretary.reports.export') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="summary">
                <input type="hidden" name="format" value="excel">
                <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-file-excel icon-small"></i>
                    Export to Excel
                </button>
            </form>
        </div>
    </div>

    <!-- Year Filter -->
    <div class="filters-section">
        <form action="{{ route('secretary.reports.summary') }}" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="year">Select Year</label>
                <select name="year" id="year" class="filter-input">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Generate</button>
            </div>
        </form>
    </div>

    <!-- Year Overview Cards -->
    <div class="year-overview">
        <!-- Residents Overview Card -->
        <div class="overview-card residents">
            <div class="overview-icon">
                <x-heroicon-o-users />
            </div>
            <div class="overview-content">
                <h3>Residents</h3>
                <div class="overview-stats">
                    <div class="overview-stat">
                        <span class="stat-label">Total</span>
                        <span class="stat-value">{{ $statistics['residents']['total'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">New this year</span>
                        <span class="stat-value">{{ $statistics['residents']['new_this_year'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Male</span>
                        <span class="stat-value">{{ $statistics['residents']['by_gender']['male'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Female</span>
                        <span class="stat-value">{{ $statistics['residents']['by_gender']['female'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificates Overview Card -->
        <div class="overview-card certificates">
            <div class="overview-icon">
                <x-heroicon-o-document-text />
            </div>
            <div class="overview-content">
                <h3>Certificates</h3>
                <div class="overview-stats">
                    <div class="overview-stat">
                        <span class="stat-label">Total</span>
                        <span class="stat-value">{{ $statistics['certificates']['total'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Issued this year</span>
                        <span class="stat-value">{{ $statistics['certificates']['issued_this_year'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Pending</span>
                        <span class="stat-value">{{ $statistics['certificates']['by_status']['pending'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Released</span>
                        <span class="stat-value">{{ $statistics['certificates']['by_status']['released'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blotter Cases Overview Card -->
        <div class="overview-card blotters">
            <div class="overview-icon">
                <x-heroicon-o-scale />
            </div>
            <div class="overview-content">
                <h3>Blotter Cases</h3>
                <div class="overview-stats">
                    <div class="overview-stat">
                        <span class="stat-label">Total</span>
                        <span class="stat-value">{{ $statistics['blotters']['total'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Filed this year</span>
                        <span class="stat-value">{{ $statistics['blotters']['filed_this_year'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Active</span>
                        <span class="stat-value">{{ $statistics['blotters']['by_status']['active'] ?? 0 }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Settled</span>
                        <span class="stat-value">{{ $statistics['blotters']['by_status']['settled'] ?? 0 }}</span>
                    </div>
                </div>
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
                    <span><span class="legend-dot male"></span> Male: {{ $statistics['residents']['by_gender']['male'] ?? 0 }}</span>
                    <span><span class="legend-dot female"></span> Female: {{ $statistics['residents']['by_gender']['female'] ?? 0 }}</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="genderChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Certificate Status Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-file-alt"></i> Certificate Status</h3>
                <div class="chart-total">{{ $statistics['certificates']['total'] ?? 0 }} total</div>
            </div>
            <div class="chart-body">
                <canvas id="certificateStatusChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @php
                    $certTotal = $statistics['certificates']['total'] ?? 0;
                    $certPending = $statistics['certificates']['by_status']['pending'] ?? 0;
                    $certApproved = $statistics['certificates']['by_status']['approved'] ?? 0;
                    $certReleased = $statistics['certificates']['by_status']['released'] ?? 0;
                    $certRejected = $statistics['certificates']['by_status']['rejected'] ?? 0;
                    $certSafeTotal = max($certTotal, 1);
                @endphp
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot pending"></span> Pending:</span>
                    <span class="stat-value">{{ $certPending }} ({{ round(($certPending / $certSafeTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot approved"></span> Approved:</span>
                    <span class="stat-value">{{ $certApproved }} ({{ round(($certApproved / $certSafeTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot released"></span> Released:</span>
                    <span class="stat-value">{{ $certReleased }} ({{ round(($certReleased / $certSafeTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot rejected"></span> Rejected:</span>
                    <span class="stat-value">{{ $certRejected }} ({{ round(($certRejected / $certSafeTotal) * 100, 1) }}%)</span>
                </div>
            </div>
        </div>

        <!-- Blotter Status Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-gavel"></i> Blotter Case Status</h3>
                <div class="chart-total">{{ $statistics['blotters']['total'] ?? 0 }} total</div>
            </div>
            <div class="chart-body">
                <canvas id="blotterStatusChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @php
                    $blotterTotal = $statistics['blotters']['total'] ?? 0;
                    $blotterPending = $statistics['blotters']['by_status']['pending'] ?? 0;
                    $blotterOngoing = $statistics['blotters']['by_status']['ongoing'] ?? 0;
                    $blotterSettled = $statistics['blotters']['by_status']['settled'] ?? 0;
                    $blotterReferred = $statistics['blotters']['by_status']['referred'] ?? 0;
                    $blotterSafeTotal = max($blotterTotal, 1);
                @endphp
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot pending"></span> Pending:</span>
                    <span class="stat-value">{{ $blotterPending }} ({{ round(($blotterPending / $blotterSafeTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot ongoing"></span> Ongoing:</span>
                    <span class="stat-value">{{ $blotterOngoing }} ({{ round(($blotterOngoing / $blotterSafeTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot settled"></span> Settled:</span>
                    <span class="stat-value">{{ $blotterSettled }} ({{ round(($blotterSettled / $blotterSafeTotal) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot referred"></span> Referred:</span>
                    <span class="stat-value">{{ $blotterReferred }} ({{ round(($blotterReferred / $blotterSafeTotal) * 100, 1) }}%)</span>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison Line Chart -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Monthly Comparison for {{ $statistics['year'] ?? date('Y') }}</h3>
            </div>
            <div class="chart-body" style="height: 300px;">
                <canvas id="monthlyComparisonChart" width="800" height="250"></canvas>
            </div>
            <div class="comparison-legend-wrapper">
                <div class="comparison-legend">
                    <span><span class="legend-dot residents"></span> Residents</span>
                    <span><span class="legend-dot certificates"></span> Certificates</span>
                    <span><span class="legend-dot blotters"></span> Blotters</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="summary-stats-grid">
        <!-- Year Summary Card -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-chart-bar class="detail-icon" />
                <h3>Year {{ $statistics['year'] ?? date('Y') }} Summary</h3>
            </div>
            <div class="detail-body">
                <div class="summary-items">
                    <div class="summary-item">
                        <span class="summary-label">Total Residents:</span>
                        <span class="summary-value">{{ $statistics['residents']['total'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">New Residents:</span>
                        <span class="summary-value">{{ $statistics['residents']['new_this_year'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Total Certificates:</span>
                        <span class="summary-value">{{ $statistics['certificates']['total'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Certificates Issued:</span>
                        <span class="summary-value">{{ $statistics['certificates']['issued_this_year'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Total Blotter Cases:</span>
                        <span class="summary-value">{{ $statistics['blotters']['total'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Cases Filed:</span>
                        <span class="summary-value">{{ $statistics['blotters']['filed_this_year'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics Card -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-clock class="detail-icon" />
                <h3>Performance Metrics</h3>
            </div>
            <div class="detail-body">
                <div class="summary-items">
                    @php
                        $certificatesMonthly = $statistics['certificates']['monthly'] ?? [];
                        $blottersMonthly = $statistics['blotters']['monthly'] ?? [];
                        $certificatesTotal = array_sum($certificatesMonthly);
                        $blottersTotal = array_sum($blottersMonthly);
                        $certReleased = $statistics['certificates']['by_status']['released'] ?? 0;
                        $blotterSettled = $statistics['blotters']['by_status']['settled'] ?? 0;
                        $certGrandTotal = $statistics['certificates']['total'] ?? 1;
                        $blotterGrandTotal = $statistics['blotters']['total'] ?? 1;
                    @endphp
                    <div class="summary-item">
                        <span class="summary-label">Avg Certificates/Month:</span>
                        <span class="summary-value">{{ $certificatesTotal > 0 ? round($certificatesTotal / 12, 1) : 0 }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Avg Cases/Month:</span>
                        <span class="summary-value">{{ $blottersTotal > 0 ? round($blottersTotal / 12, 1) : 0 }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Certificate Release Rate:</span>
                        <span class="summary-value">{{ round(($certReleased / $certGrandTotal) * 100, 1) }}%</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Case Resolution Rate:</span>
                        <span class="summary-value">{{ round(($blotterSettled / $blotterGrandTotal) * 100, 1) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ==================== */
/* Container & Layout   */
/* ==================== */
.container-fluid {
    padding: 1.2rem;
}

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
    height: 40px;
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

.btn-filter:hover {
    background: #5a67d8;
}

/* ==================== */
/* Year Overview Cards  */
/* ==================== */
.year-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.overview-card {
    background: white;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    align-items: flex-start;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.overview-card.residents .overview-icon { background: #667eea; }
.overview-card.certificates .overview-icon { background: #f59e0b; }
.overview-card.blotters .overview-icon { background: #ef4444; }

.overview-icon {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.overview-icon svg {
    width: 24px;
    height: 24px;
    color: white;
}

.overview-content {
    flex: 1;
}

.overview-content h3 {
    color: #333;
    font-size: 0.9rem;
    margin-bottom: 0.8rem;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.overview-stat {
    text-align: left;
}

.overview-stat .stat-label {
    display: block;
    color: #666;
    font-size: 0.65rem;
    margin-bottom: 0.1rem;
}

.overview-stat .stat-value {
    display: block;
    color: #333;
    font-size: 1rem;
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

.chart-legend {
    display: flex;
    gap: 0.8rem;
    font-size: 0.7rem;
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

/* ==================== */
/* Legend Dots          */
/* ==================== */
.legend-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.legend-dot.male { background: #3b82f6; }
.legend-dot.female { background: #ec4899; }
.legend-dot.pending { background: #f59e0b; }
.legend-dot.approved { background: #10b981; }
.legend-dot.released { background: #3b82f6; }
.legend-dot.rejected { background: #ef4444; }
.legend-dot.ongoing { background: #8b5cf6; }
.legend-dot.settled { background: #10b981; }
.legend-dot.referred { background: #ef4444; }
.legend-dot.residents { background: #667eea; }
.legend-dot.certificates { background: #f59e0b; }
.legend-dot.blotters { background: #ef4444; }

/* ==================== */
/* Comparison Legend    */
/* ==================== */
.comparison-legend-wrapper {
    padding: 0.5rem 1rem 1rem;
    display: flex;
    justify-content: center;
}

.comparison-legend {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    font-size: 0.75rem;
}

.comparison-legend span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* ==================== */
/* Summary Stats Grid   */
/* ==================== */
.summary-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
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

.summary-items {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.3rem 0;
    border-bottom: 1px dashed #e2e8f0;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    color: #666;
    font-size: 0.7rem;
}

.summary-value {
    color: #333;
    font-weight: 600;
    font-size: 0.8rem;
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
}

@media (max-width: 768px) {
    .page-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .page-actions form {
        width: 100%;
    }

    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .year-overview {
        grid-template-columns: 1fr;
    }

    .comparison-legend {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .page-actions {
        width: 100%;
    }
}
</style>
@endpush
@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Safely get statistics with fallbacks
    const statistics = {
        year: {{ $statistics['year'] ?? date('Y') }},
        residents: {
            total: {{ $statistics['residents']['total'] ?? 0 }},
            new_this_year: {{ $statistics['residents']['new_this_year'] ?? 0 }},
            by_gender: {
                male: {{ $statistics['residents']['by_gender']['male'] ?? 0 }},
                female: {{ $statistics['residents']['by_gender']['female'] ?? 0 }}
            },
            monthly: @json($statistics['residents']['monthly'] ?? [])
        },
        certificates: {
            total: {{ $statistics['certificates']['total'] ?? 0 }},
            issued_this_year: {{ $statistics['certificates']['issued_this_year'] ?? 0 }},
            by_status: {
                pending: {{ $statistics['certificates']['by_status']['pending'] ?? 0 }},
                approved: {{ $statistics['certificates']['by_status']['approved'] ?? 0 }},
                released: {{ $statistics['certificates']['by_status']['released'] ?? 0 }},
                rejected: {{ $statistics['certificates']['by_status']['rejected'] ?? 0 }}
            },
            monthly: @json($statistics['certificates']['monthly'] ?? [])
        },
        blotters: {
            total: {{ $statistics['blotters']['total'] ?? 0 }},
            filed_this_year: {{ $statistics['blotters']['filed_this_year'] ?? 0 }},
            by_status: {
                pending: {{ $statistics['blotters']['by_status']['pending'] ?? 0 }},
                ongoing: {{ $statistics['blotters']['by_status']['ongoing'] ?? 0 }},
                settled: {{ $statistics['blotters']['by_status']['settled'] ?? 0 }},
                referred: {{ $statistics['blotters']['by_status']['referred'] ?? 0 }},
                active: {{ $statistics['blotters']['by_status']['active'] ?? 0 }}
            },
            monthly: @json($statistics['blotters']['monthly'] ?? [])
        }
    };

    /**
     * Gender Distribution Pie Chart
     */
    if (document.getElementById('genderChart')) {
        new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [statistics.residents.by_gender.male, statistics.residents.by_gender.female],
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
    }

    /**
     * Certificate Status Pie Chart
     */
    if (document.getElementById('certificateStatusChart')) {
        new Chart(document.getElementById('certificateStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'Approved', 'Released', 'Rejected'],
                datasets: [{
                    data: [
                        statistics.certificates.by_status.pending,
                        statistics.certificates.by_status.approved,
                        statistics.certificates.by_status.released,
                        statistics.certificates.by_status.rejected
                    ],
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
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
    }

    /**
     * Blotter Status Pie Chart
     */
    if (document.getElementById('blotterStatusChart')) {
        new Chart(document.getElementById('blotterStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'Ongoing', 'Settled', 'Referred'],
                datasets: [{
                    data: [
                        statistics.blotters.by_status.pending,
                        statistics.blotters.by_status.ongoing,
                        statistics.blotters.by_status.settled,
                        statistics.blotters.by_status.referred
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
    }

    /**
     * Monthly Comparison Line Chart
     */
    if (document.getElementById('monthlyComparisonChart')) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Ensure we have 12 months of data (pad with zeros if needed)
        const residentsData = Array(12).fill(0);
        const certificatesData = Array(12).fill(0);
        const blottersData = Array(12).fill(0);

        // Fill in available data
        if (Array.isArray(statistics.residents.monthly)) {
            statistics.residents.monthly.forEach((value, index) => {
                if (index < 12) residentsData[index] = value;
            });
        }
        if (Array.isArray(statistics.certificates.monthly)) {
            statistics.certificates.monthly.forEach((value, index) => {
                if (index < 12) certificatesData[index] = value;
            });
        }
        if (Array.isArray(statistics.blotters.monthly)) {
            statistics.blotters.monthly.forEach((value, index) => {
                if (index < 12) blottersData[index] = value;
            });
        }

        new Chart(document.getElementById('monthlyComparisonChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Residents',
                        data: residentsData,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Certificates',
                        data: certificatesData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Blotters',
                        data: blottersData,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
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
    }

    // Note: Mini trend charts are commented out in the HTML
    // If you want to enable them, uncomment the HTML section and these chart initializations
});
</script>
@endpush
