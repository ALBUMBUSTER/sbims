@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Reports</h1>
            <p>View system reports and analytics</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clerk.dashboard') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <div class="reports-grid">
        <!-- Residents Report Card -->
        <div class="report-card">
            <div class="report-icon residents">
                <i class="fas fa-users"></i>
            </div>
            <div class="report-content">
                <h3>Residents Report</h3>
                <p>View demographic data, population statistics, and resident profiles</p>
                <div class="report-actions">
                    <a href="{{ route('clerk.reports.residents') }}" class="btn-primary btn-sm">View Report</a>
                </div>
            </div>
        </div>

        <!-- Certificates Report Card -->
        <div class="report-card">
            <div class="report-icon certificates">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="report-content">
                <h3>Certificates Report</h3>
                <p>Certificate issuance statistics, status breakdown, and trends</p>
                <div class="report-actions">
                    <a href="{{ route('clerk.reports.certificates') }}" class="btn-primary btn-sm">View Report</a>
                </div>
            </div>
        </div>

        <!-- Summary Report Card -->
        <div class="report-card">
            <div class="report-icon summary">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="report-content">
                <h3>Summary Report</h3>
                <p>Overall system summary with key metrics and monthly trends</p>
                <div class="report-actions">
                    <a href="{{ route('clerk.reports.summary') }}" class="btn-primary btn-sm">View Report</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
        <h2>Current Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label">Total Residents</span>
                <span class="stat-value">{{ \App\Models\Resident::count() }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Total Certificates</span>
                <span class="stat-value">{{ \App\Models\Certificate::count() }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">This Month</span>
                <span class="stat-value">{{ \App\Models\Certificate::whereMonth('created_at', now()->month)->count() }}</span>
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

.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.report-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    transition: transform 0.3s;
}

.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.report-icon {
    width: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
}

.report-icon.residents { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
.report-icon.certificates { background: linear-gradient(135deg, #06d6a0, #05a87a); }
.report-icon.summary { background: linear-gradient(135deg, #f8961e, #f3722c); }

.report-content {
    flex: 1;
    padding: 1.5rem;
}

.report-content h3 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.report-content p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.report-actions {
    margin-top: 1rem;
}

.btn-primary.btn-sm {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.9rem;
    transition: opacity 0.3s;
}

.btn-primary.btn-sm:hover {
    opacity: 0.9;
}

.quick-stats {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quick-stats h2 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 5px;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

@media (max-width: 768px) {
    .report-card {
        flex-direction: column;
    }

    .report-icon {
        width: 100%;
        padding: 1rem;
    }
}
</style>
@endpush
