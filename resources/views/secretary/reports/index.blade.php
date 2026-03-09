@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Reports</h1>
            <p>Generate and view various reports</p>
        </div>
    </div>

    <!-- Report Cards Grid -->
    <div class="reports-grid">
        <!-- Residents Report Card -->
        <div class="report-card">
            <div class="report-icon residents">
                <i class="fas fa-users fa-3x"></i>
            </div>
            <div class="report-content">
                <h3>Residents Report</h3>
                <p>Demographics, distribution by purok, civil status, and special categories</p>
                <div class="report-stats">
                    <span class="stat">
                        <span class="stat-label">Total</span>
                        <span class="stat-value">{{ \App\Models\Resident::count() }}</span>
                    </span>
                </div>
                <a href="{{ route('secretary.reports.residents') }}" class="btn-generate">
                    Generate Report
                    <i class="fas fa-arrow-right icon-small"></i>
                </a>
            </div>
        </div>

        <!-- Certificates Report Card -->
        <div class="report-card">
            <div class="report-icon certificates">
                <i class="fas fa-file-alt fa-3x"></i>
            </div>
            <div class="report-content">
                <h3>Certificates Report</h3>
                <p>Certificate requests, status distribution, and collections</p>
                <div class="report-stats">
                    <span class="stat">
                        <span class="stat-label">Pending</span>
                        <span class="stat-value">{{ \App\Models\Certificate::where('status', 'Pending')->count() }}</span>
                    </span>
                    <span class="stat">
                        <span class="stat-label">Released</span>
                        <span class="stat-value">{{ \App\Models\Certificate::where('status', 'Released')->count() }}</span>
                    </span>
                </div>
                <a href="{{ route('secretary.reports.certificates') }}" class="btn-generate">
                    Generate Report
                    <i class="fas fa-arrow-right icon-small"></i>
                </a>
            </div>
        </div>

        <!-- Blotter Report Card -->
        <div class="report-card">
            <div class="report-icon blotter">
                <i class="fas fa-gavel fa-3x"></i>
            </div>
            <div class="report-content">
                <h3>Blotter Report</h3>
                <p>Case status, incident types, and resolution rates</p>
                <div class="report-stats">
                    <span class="stat">
                        <span class="stat-label">Active</span>
                        <span class="stat-value">{{ \App\Models\Blotter::whereIn('status', ['Pending', 'Investigating', 'Hearings'])->count() }}</span>
                    </span>
                    <span class="stat">
                        <span class="stat-label">Settled</span>
                        <span class="stat-value">{{ \App\Models\Blotter::where('status', 'Settled')->count() }}</span>
                    </span>
                </div>
                <a href="{{ route('secretary.reports.blotter') }}" class="btn-generate">
                    Generate Report
                    <i class="fas fa-arrow-right icon-small"></i>
                </a>
            </div>
        </div>

        <!-- Summary Report Card -->
        <div class="report-card">
            <div class="report-icon summary">
                <i class="fas fa-chart-bar fa-3x"></i>
            </div>
            <div class="report-content">
                <h3>Summary Report</h3>
                <p>Yearly overview with monthly trends and comparisons</p>
                <div class="report-stats">
                    <span class="stat">
                        <span class="stat-label">This Year</span>
                        <span class="stat-value">{{ date('Y') }}</span>
                    </span>
                </div>
                <a href="{{ route('secretary.reports.summary') }}" class="btn-generate">
                    Generate Report
                    <i class="fas fa-arrow-right icon-small"></i>
                </a>
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
    margin-bottom: 2rem;
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

/* Reports Grid */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.report-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    transition: transform 0.3s, box-shadow 0.3s;
}

.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.report-icon {
    width: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.report-icon i {
    color: white;
}

.report-icon.residents {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.report-icon.certificates {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.report-icon.blotter {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.report-icon.summary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.report-content {
    flex: 1;
    padding: 1.5rem;
}

.report-content h3 {
    color: #333;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.report-content p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.report-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat {
    flex: 1;
    text-align: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 1.2rem;
    font-weight: bold;
}

.btn-generate {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.btn-generate:hover {
    background: #eef2ff;
}

.icon-small {
    width: auto;
    height: auto;
    font-size: 16px;
}
</style>
@endpush
