@extends('layouts.app')

@section('title', 'Secretary Dashboard')

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Secretary Dashboard</h1>
                <p class="welcome-text">Welcome back, {{ auth()->user()->full_name ?? auth()->user()->name ?? 'Secretary' }}!</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('secretary.reports.index') }}" class="btn-primary">
                    <i class="fas fa-download"></i>
                    Generate Report
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- First Row Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon residents-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Residents</h3>
                    <div class="stat-value">{{ $totalResidents ?? 0 }}</div>
                    <span class="stat-label">Registered Residents</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blotter-icon">
                    <i class="fas fa-gavel"></i>
                </div>
                <div class="stat-details">
                    <h3>Active Blotter Cases</h3>
                    <div class="stat-value">{{ $activeBlotterCases ?? 0 }}</div>
                    <span class="stat-label">Under Investigation</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3>Pending Certificates</h3>
                    <div class="stat-value">{{ $pendingCertificates ?? 0 }}</div>
                    <span class="stat-label">Awaiting Processing</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon approved-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3>Certificates Today</h3>
                    <div class="stat-value">{{ $certificatesToday ?? 0 }}</div>
                    <span class="stat-label">Issued Today</span>
                </div>
            </div>
        </div>

        <!-- Second Row Statistics Cards (Using available data) -->
        <div class="stats-grid secondary">
            <div class="stat-card small">
                <div class="stat-icon total-cert-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Certificates</h3>
                    <div class="stat-value">{{ $totalCertificates ?? DB::table('certificates')->count() ?? 0 }}</div>
                    <span class="stat-label">All Time</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon total-blotter-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Blotters</h3>
                    <div class="stat-value">{{ $totalBlotters ?? DB::table('blotters')->count() ?? 0 }}</div>
                    <span class="stat-label">All Cases</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon recent-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3>Recent Activities</h3>
                    <div class="stat-value">{{ $recentActivities->count() ?? 0 }}</div>
                    <span class="stat-label">Last 24 Hours</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon pending-small-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-details">
                    <h3>Pending Actions</h3>
                    <div class="stat-value">{{ ($pendingCertificates ?? 0) + ($activeBlotterCases ?? 0) }}</div>
                    <span class="stat-label">Need Attention</span>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Recent Certificates Chart (Simplified since we don't have monthly stats yet) -->
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Recent Certificate Activity</h3>
                </div>
                <div class="card-body">
                    @if(count($recentCertificates) > 0)
                        <div class="recent-certificates-list">
                            @foreach($recentCertificates as $cert)
                            <div class="recent-cert-item">
                                <div class="cert-info">
                                    <span class="cert-name">{{ $cert->first_name }} {{ $cert->last_name }}</span>
                                    <span class="cert-type">{{ $cert->certificate_type ?? 'Certificate' }}</span>
                                    <small class="cert-date">{{ \Carbon\Carbon::parse($cert->created_at)->diffForHumans() }}</small>
                                </div>
                                <span class="status-badge status-{{ strtolower($cert->status) }}">
                                    {{ $cert->status }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="no-data">No recent certificate requests</p>
                    @endif
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="approvals-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Recent Activities</h3>
                    <a href="{{ route('secretary.activities') }}" class="view-link">View All</a>
                </div>
                <div class="card-body">
                    @if($recentActivities->count() > 0)
                        <div class="approvals-list">
                            @foreach($recentActivities as $activity)
                            <div class="approval-item">
                                <div class="approval-info">
                                    <span class="approval-name">{{ $activity->user->name ?? 'System' }}</span>
                                    <span class="approval-type">
                                        <span class="action-badge action-{{ $activity->action_type ?? 'other' }}">
                                            {{ $activity->action }}
                                        </span>
                                    </span>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="approval-description">
                                    {{ Str::limit($activity->description, 50) }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="no-data">No recent activities</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Certificates Table -->
        <div class="recent-cases">
            <div class="card-header">
                <h3><i class="fas fa-file-alt"></i> Recent Certificate Requests</h3>
                <a href="{{ route('secretary.certificates.index') }}" class="view-link">View All</a>
            </div>
            <div class="card-body">
                @if(count($recentCertificates) > 0)
                    <table class="cases-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Resident Name</th>
                                <th>Certificate Type</th>
                                <th>Date Requested</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCertificates as $cert)
                            <tr>
                                <td><span class="case-number">{{ $cert->id }}</span></td>
                                <td>{{ $cert->first_name }} {{ $cert->last_name }}</td>
                                <td>{{ $cert->certificate_type ?? 'Certificate' }}</td>
                                <td>{{ \Carbon\Carbon::parse($cert->created_at)->format('M d, Y') }}</td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($cert->status) }}">
                                        {{ $cert->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('secretary.certificates.show', $cert->id) }}" class="btn-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="no-data">No certificate requests found</p>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
.main-container {
    display: flex;
    min-height: calc(100vh - 70px);
    background: #f8fafc;
}

.content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.page-title h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 2rem;
    font-weight: 700;
}

.page-title .welcome-text {
    color: #666;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.btn-close {
    float: right;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    opacity: 0.5;
}

.btn-close:hover {
    opacity: 1;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.stats-grid.secondary {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: all 0.3s;
    border: 1px solid rgba(0,0,0,0.05);
}

.stat-card.small {
    padding: 1rem;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.stat-icon.small {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
}

.stat-icon.residents-icon { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
.stat-icon.blotter-icon { background: linear-gradient(135deg, #f72585, #b5179e); }
.stat-icon.pending-icon { background: linear-gradient(135deg, #f8961e, #f3722c); }
.stat-icon.approved-icon { background: linear-gradient(135deg, #4cc9f0, #4895ef); }
.stat-icon.total-cert-icon { background: linear-gradient(135deg, #06d6a0, #05a87a); }
.stat-icon.total-blotter-icon { background: linear-gradient(135deg, #7209b7, #560bad); }
.stat-icon.recent-icon { background: linear-gradient(135deg, #3a0ca3, #4361ee); }
.stat-icon.pending-small-icon { background: linear-gradient(135deg, #ffd166, #e6b422); }

.stat-details {
    flex: 1;
}

.stat-details h3 {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: #999;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Chart Card */
.chart-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    overflow: hidden;
}

.card-header {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.card-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header h3 i {
    color: #4361ee;
}

.card-body {
    padding: 1.5rem;
}

/* Recent Certificates List */
.recent-certificates-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.recent-cert-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem;
    background: #f8fafc;
    border-radius: 8px;
    transition: all 0.3s;
}

.recent-cert-item:hover {
    background: #e2e8f0;
}

.cert-info {
    display: flex;
    flex-direction: column;
}

.cert-name {
    font-weight: 600;
    color: #333;
}

.cert-type {
    font-size: 0.85rem;
    color: #666;
}

.cert-date {
    font-size: 0.75rem;
    color: #999;
    margin-top: 0.2rem;
}

/* Approvals Card */
.approvals-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    overflow: hidden;
}

.view-link {
    color: #4361ee;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
}

.view-link:hover {
    text-decoration: underline;
}

.approvals-list {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.approval-item {
    display: flex;
    flex-direction: column;
    padding: 0.8rem;
    background: #f8fafc;
    border-radius: 8px;
    transition: all 0.3s;
}

.approval-item:hover {
    background: #e2e8f0;
}

.approval-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.3rem;
}

.approval-name {
    font-weight: 600;
    color: #333;
}

.approval-description {
    font-size: 0.85rem;
    color: #666;
}

.text-muted {
    font-size: 0.75rem;
    color: #999;
}

.action-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.action-create { background: #d4edda; color: #155724; }
.action-update { background: #cce5ff; color: #004085; }
.action-delete { background: #fee2e2; color: #dc2626; }
.action-login { background: #d1fae5; color: #059669; }
.action-other { background: #e2e8f0; color: #4a5568; }

/* Recent Cases */
.recent-cases {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    overflow: hidden;
    margin-top: 1.5rem;
}

.cases-table {
    width: 100%;
    border-collapse: collapse;
}

.cases-table th {
    text-align: left;
    padding: 1rem;
    background: #f8fafc;
    color: #666;
    font-weight: 600;
    font-size: 0.9rem;
    border-bottom: 1px solid #e2e8f0;
}

.cases-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
}

.cases-table tr:hover td {
    background: #f8fafc;
}

.case-number {
    font-family: monospace;
    font-weight: 600;
    color: #4361ee;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-released {
    background: #d4edda;
    color: #155724;
}

.btn-view {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    background: #e9ecef;
    color: #4361ee;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn-view:hover {
    background: #dee2e6;
}

.no-data {
    color: #999;
    text-align: center;
    padding: 2rem;
    margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .content {
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
