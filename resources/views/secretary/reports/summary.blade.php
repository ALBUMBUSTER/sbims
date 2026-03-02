@extends('layouts.app')

@section('title', 'Summary Report')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Summary Report</h1>
            <p>Yearly overview for {{ $statistics['year'] }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.reports.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Reports
            </a>
            <form action="{{ route('secretary.reports.export') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="type" value="summary">
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-document-arrow-down class="icon-small" />
                    Export PDF
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
        <div class="overview-card residents">
            <div class="overview-icon">
                <x-heroicon-o-users />
            </div>
            <div class="overview-content">
                <h3>Residents</h3>
                <div class="overview-stats">
                    <div class="overview-stat">
                        <span class="stat-label">Total</span>
                        <span class="stat-value">{{ $statistics['residents']['total'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">New this year</span>
                        <span class="stat-value">{{ $statistics['residents']['new_this_year'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Male</span>
                        <span class="stat-value">{{ $statistics['residents']['by_gender']['male'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Female</span>
                        <span class="stat-value">{{ $statistics['residents']['by_gender']['female'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="overview-card certificates">
            <div class="overview-icon">
                <x-heroicon-o-document-text />
            </div>
            <div class="overview-content">
                <h3>Certificates</h3>
                <div class="overview-stats">
                    <div class="overview-stat">
                        <span class="stat-label">Total</span>
                        <span class="stat-value">{{ $statistics['certificates']['total'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Issued this year</span>
                        <span class="stat-value">{{ $statistics['certificates']['issued_this_year'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Pending</span>
                        <span class="stat-value">{{ $statistics['certificates']['by_status']['pending'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Released</span>
                        <span class="stat-value">{{ $statistics['certificates']['by_status']['released'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="overview-card blotters">
            <div class="overview-icon">
                <x-heroicon-o-scale />
            </div>
            <div class="overview-content">
                <h3>Blotter Cases</h3>
                <div class="overview-stats">
                    <div class="overview-stat">
                        <span class="stat-label">Total</span>
                        <span class="stat-value">{{ $statistics['blotters']['total'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Filed this year</span>
                        <span class="stat-value">{{ $statistics['blotters']['filed_this_year'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Active</span>
                        <span class="stat-value">{{ $statistics['blotters']['by_status']['active'] }}</span>
                    </div>
                    <div class="overview-stat">
                        <span class="stat-label">Settled</span>
                        <span class="stat-value">{{ $statistics['blotters']['by_status']['settled'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="trends-section">
        <h2 class="section-title">Monthly Trends for {{ $statistics['year'] }}</h2>

        <div class="trends-grid">
            <!-- Residents Monthly Trend -->
            <div class="trend-card">
                <div class="trend-header">
                    <x-heroicon-o-users class="trend-icon" />
                    <h3>Residents Registration</h3>
                </div>
                <div class="trend-body">
                    <table class="trend-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>New Residents</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statistics['residents']['monthly'] as $month => $count)
                            <tr>
                                <td>{{ $month }}</td>
                                <td>{{ $count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Certificates Monthly Trend -->
            <div class="trend-card">
                <div class="trend-header">
                    <x-heroicon-o-document-text class="trend-icon" />
                    <h3>Certificate Issuance</h3>
                </div>
                <div class="trend-body">
                    <table class="trend-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Certificates Issued</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statistics['certificates']['monthly'] as $month => $count)
                            <tr>
                                <td>{{ $month }}</td>
                                <td>{{ $count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Blotters Monthly Trend -->
            <div class="trend-card">
                <div class="trend-header">
                    <x-heroicon-o-scale class="trend-icon" />
                    <h3>Blotter Cases Filed</h3>
                </div>
                <div class="trend-body">
                    <table class="trend-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Cases Filed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statistics['blotters']['monthly'] as $month => $count)
                            <tr>
                                <td>{{ $month }}</td>
                                <td>{{ $count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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

/* Buttons */
.btn-primary, .btn-secondary {
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
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #eef2ff;
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
}

.btn-filter:hover {
    background: #5a67d8;
}

/* Year Overview */
.year-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.overview-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.overview-card.residents .overview-icon { background: #667eea; }
.overview-card.certificates .overview-icon { background: #f59e0b; }
.overview-card.blotters .overview-icon { background: #ef4444; }

.overview-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1.5rem;
}

.overview-icon svg {
    width: 30px;
    height: 30px;
    color: white;
}

.overview-content {
    flex: 1;
}

.overview-content h3 {
    color: #333;
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.overview-stat {
    text-align: center;
}

.overview-stat .stat-label {
    display: block;
    color: #666;
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.overview-stat .stat-value {
    display: block;
    color: #333;
    font-size: 1.2rem;
    font-weight: bold;
}

/* Trends Section */
.trends-section {
    margin-top: 2rem;
}

.section-title {
    color: #333;
    font-size: 1.4rem;
    margin-bottom: 1.5rem;
}

.trends-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.trend-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.trend-header {
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.trend-icon {
    width: 20px;
    height: 20px;
    color: #667eea;
}

.trend-header h3 {
    color: #333;
    font-size: 1rem;
    margin: 0;
}

.trend-body {
    padding: 1rem;
}

.trend-table {
    width: 100%;
    border-collapse: collapse;
}

.trend-table th {
    text-align: left;
    padding: 0.5rem;
    background: #f8f9fa;
    color: #555;
    font-size: 0.85rem;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}

.trend-table td {
    padding: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    font-size: 0.9rem;
}

.trend-table tr:last-child td {
    border-bottom: none;
}

.icon-small {
    width: 16px;
    height: 16px;
}
</style>
@endpush
