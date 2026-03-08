@extends('layouts.app')

@section('title', 'Captain Dashboard')

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Captain Dashboard</h1>
                <p class="welcome-text">Welcome back, {{ auth()->user()->full_name ?? auth()->user()->name ?? 'Captain' }}!</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('captain.reports.index') }}" class="btn-primary">
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

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon residents-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Residents</h3>
                    <div class="stat-value">{{ $totalResidents }}</div>
                    <span class="stat-label">Registered Residents</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blotter-icon">
                    <i class="fas fa-gavel"></i>
                </div>
                <div class="stat-details">
                    <h3>Blotter Cases</h3>
                    <div class="stat-value">{{ $totalBlotters }}</div>
                    <span class="stat-label">Total Cases</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3>Pending</h3>
                    <div class="stat-value">{{ $pendingBlotters }}</div>
                    <span class="stat-label">Awaiting Action</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon approved-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3>Settled</h3>
                    <div class="stat-value">{{ $settledBlotters }}</div>
                    <span class="stat-label">Resolved Cases</span>
                </div>
            </div>
        </div>

        <!-- Second Row Stats -->
        <div class="stats-grid secondary">
            <div class="stat-card small">
                <div class="stat-icon certificate-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-details">
                    <h3>Certificates</h3>
                    <div class="stat-value">{{ $totalCertificates }}</div>
                    <span class="stat-label">Total Requests</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon pending-small-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-details">
                    <h3>Pending Approvals</h3>
                    <div class="stat-value">{{ $pendingCertificates }}</div>
                    <span class="stat-label">Need Review</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon active-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-details">
                    <h3>Active Cases</h3>
                    <div class="stat-value">{{ $activeBlotters }}</div>
                    <span class="stat-label">Under Investigation</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon released-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-details">
                    <h3>Released</h3>
                    <div class="stat-value">{{ $releasedCertificates }}</div>
                    <span class="stat-label">Completed</span>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Grid -->
        <div class="dashboard-grid">
            <!-- Monthly Statistics Chart -->
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Monthly Statistics ({{ date('Y') }})</h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="approvals-card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Pending Approvals</h3>
                    <a href="{{ route('captain.approvals.index') }}" class="view-link">View All</a>
                </div>
                <div class="card-body">
                    @if(count($pendingApprovals) > 0)
                        <div class="approvals-list">
                            @foreach($pendingApprovals as $approval)
                            <div class="approval-item">
                                <div class="approval-info">
                                    <span class="approval-name">{{ $approval->first_name }} {{ $approval->last_name }}</span>
                                    <span class="approval-type">{{ $approval->certificate_type }}</span>
                                </div>
                                <div class="approval-actions">
                                    <a href="{{ route('captain.approvals.index') }}" class="btn-approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="{{ route('captain.approvals.index') }}" class="btn-reject">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="no-data">No pending approvals</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Blotter Cases -->
        <div class="recent-cases">
            <div class="card-header">
                <h3><i class="fas fa-gavel"></i> Recent Blotter Cases</h3>
                <a href="{{ route('captain.blotters.index') }}" class="view-link">View All</a>
            </div>
            <div class="card-body">
                @if(count($recentBlotters) > 0)
                    <table class="cases-table">
                        <thead>
                            <tr>
                                <th>Case #</th>
                                <th>Complainant</th>
                                <th>Respondent</th>
                                <th>Incident Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBlotters as $case)
                            <tr>
                                <td><span class="case-number">{{ $case->blotter_number ?? 'N/A' }}</span></td>
                                <td>{{ $case->complainant_name ?? 'N/A' }}</td>
                                <td>{{ $case->respondent_name }}</td>
                                <td>{{ $case->incident_type }}</td>
                                <td>{{ \Carbon\Carbon::parse($case->created_at)->format('M d, Y') }}</td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($case->status) }}">
                                        {{ $case->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('captain.blotters.show', $case) }}" class="btn-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="no-data">No blotter cases found</p>
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
.stat-icon.certificate-icon { background: linear-gradient(135deg, #06d6a0, #05a87a); }
.stat-icon.pending-small-icon { background: linear-gradient(135deg, #ffd166, #e6b422); }
.stat-icon.active-icon { background: linear-gradient(135deg, #ef476f, #d43f5e); }
.stat-icon.released-icon { background: linear-gradient(135deg, #06d6a0, #05a87a); }

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
    justify-content: space-between;
    align-items: center;
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
    flex-direction: column;
}

.approval-name {
    font-weight: 600;
    color: #333;
}

.approval-type {
    font-size: 0.8rem;
    color: #666;
}

.approval-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-approve, .btn-reject {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-approve {
    background: #d4edda;
    color: #155724;
}

.btn-approve:hover {
    background: #c3e6cb;
}

.btn-reject {
    background: #fee2e2;
    color: #dc2626;
}

.btn-reject:hover {
    background: #fecaca;
}

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

.status-investigating {
    background: #cce5ff;
    color: #004085;
}

.status-hearings {
    background: #e2d5f1;
    color: #553c9a;
}

.status-settled {
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Statistics Chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');

    // Get the data from PHP - using json_encode directly
    const monthlyStats = <?php echo json_encode($monthlyStats); ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Blotter Cases',
                    data: monthlyStats.blotters || Array(12).fill(0),
                    borderColor: '#f72585',
                    backgroundColor: 'rgba(247, 37, 133, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Certificates',
                    data: monthlyStats.certificates || Array(12).fill(0),
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
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
});
</script>
@endpush
