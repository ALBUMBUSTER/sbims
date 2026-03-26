@extends('layouts.app')

@section('title', 'Captain Dashboard')

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-tachometer-alt"></i> Captain Dashboard</h1>
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
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
<!-- ========== TERM END WARNING BANNER (PERMANENT - NO ALERT CLASS) ========== -->
@if(auth()->user()->role_id == 2 && auth()->user()->term_end_date)
    @php
        $daysLeft = auth()->user()->getDaysLeftInTerm();
    @endphp
    @if($daysLeft <= 30 && $daysLeft > 0)
        <div class="term-warning-card warning">
            <div class="term-warning-content">
                <i class="fas fa-clock"></i>
                <div class="term-warning-text">
                    <strong>Term Reminder:</strong> Your term as Barangay Captain ends on
                    <strong>{{ \Carbon\Carbon::parse(auth()->user()->term_end_date)->format('F d, Y') }}</strong>.
                    @if($daysLeft <= 7)
                        <span class="text-danger font-weight-bold">Only {{ $daysLeft }} day(s) remaining!</span>
                    @endif
                </div>
            </div>
            <div class="term-warning-badge">
                <span class="days-left-badge">{{ $daysLeft }} days left</span>
            </div>
        </div>
    @elseif($daysLeft <= 0)
        <div class="term-warning-card expired">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="term-warning-text">
                <strong>Term Expired:</strong> Your term as Barangay Captain ended on
                <strong>{{ \Carbon\Carbon::parse(auth()->user()->term_end_date)->format('F d, Y') }}</strong>.
                Please contact the administrator for account renewal.
            </div>
        </div>
    @endif
@endif
<!-- ========== END TERM WARNING ========== -->

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon residents-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Residents</h3>
                    <div class="stat-value">{{ $totalResidents }}</div>
                    <span class="stat-label"><i class="fas fa-user-check"></i> Registered Residents</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blotter-icon">
                    <i class="fas fa-gavel"></i>
                </div>
                <div class="stat-details">
                    <h3>Blotter Cases</h3>
                    <div class="stat-value">{{ $totalBlotters }}</div>
                    <span class="stat-label"><i class="fas fa-folder-open"></i> Total Cases</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3>Pending</h3>
                    <div class="stat-value">{{ $pendingBlotters }}</div>
                    <span class="stat-label"><i class="fas fa-hourglass-half"></i> Awaiting Action</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon approved-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3>Settled</h3>
                    <div class="stat-value">{{ $settledBlotters }}</div>
                    <span class="stat-label"><i class="fas fa-check-double"></i> Resolved Cases</span>
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
                    <span class="stat-label"><i class="fas fa-file-signature"></i> Total Requests</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon pending-small-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-details">
                    <h3>Pending Approvals</h3>
                    <div class="stat-value">{{ $pendingCertificates }}</div>
                    <span class="stat-label"><i class="fas fa-clipboard-list"></i> Need Review</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon active-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-details">
                    <h3>Active Cases</h3>
                    <div class="stat-value">{{ $activeBlotters }}</div>
                    <span class="stat-label"><i class="fas fa-search"></i> Under Investigation</span>
                </div>
            </div>

            <div class="stat-card small">
                <div class="stat-icon released-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-details">
                    <h3>Released</h3>
                    <div class="stat-value">{{ $releasedCertificates }}</div>
                    <span class="stat-label"><i class="fas fa-flag-checkered"></i> Completed</span>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Grid -->
        <div class="dashboard-grid">
            <!-- Monthly Statistics Chart -->
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Monthly Statistics ({{ date('Y') }})</h3>
                    <span class="badge">Certificates | New Residents | Blotter Cases</span>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="approvals-card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Pending Approvals</h3>
                    <a href="{{ route('captain.approvals.index') }}" class="view-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
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
                                    <a href="{{ route('captain.approvals.index') }}" class="btn-approve" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="{{ route('captain.approvals.index') }}" class="btn-reject" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-data">
                            <i class="fas fa-inbox fa-2x"></i>
                            <p>No pending approvals</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Blotter Cases -->
        <div class="recent-cases">
            <div class="card-header">
                <h3><i class="fas fa-gavel"></i> Recent Blotter Cases</h3>
                <a href="{{ route('captain.blotters.index') }}" class="view-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                @if(count($recentBlotters) > 0)
                    <table class="cases-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Case #</th>
                                <th><i class="fas fa-user"></i> Complainant</th>
                                <th><i class="fas fa-user-tie"></i> Respondent</th>
                                <th><i class="fas fa-tag"></i> Incident Type</th>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-circle"></i> Status</th>
                                <th><i class="fas fa-cog"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBlotters as $case)
                            <tr>
                                <td><span class="case-number">{{ $case->blotter_number ?? $case->case_id ?? 'N/A' }}</span></td>
                                <td>{{ $case->complainant_name ?? $case->complainant->first_name ?? 'N/A' }}</td>
                                <td>{{ $case->respondent_name }}</td>
                                <td>{{ $case->incident_type }}</td>
                                <td>{{ \Carbon\Carbon::parse($case->created_at)->format('M d, Y') }}</td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($case->status) }}">
                                        {{ $case->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('captain.blotters.show', $case->id) }}" class="btn-view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="no-data">
                        <i class="fas fa-folder-open fa-2x"></i>
                        <p>No blotter cases found</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
/* Add badge style for chart header */
.badge {
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}
/* ========== PERMANENT TERM WARNING CARD ========== */
.term-warning-card {
    margin-bottom: 1.5rem;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.2s ease;
}

/* Warning (Active term with days left) */
.term-warning-card.warning {
    background: #fef3c7;
    border-left: 5px solid #f59e0b;
    color: #92400e;
}

/* Expired term */
.term-warning-card.expired {
    background: #fee2e2;
    border-left: 5px solid #dc2626;
    color: #991b1b;
}

/* Content wrapper for warning */
.term-warning-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

/* Icon styling */
.term-warning-card i {
    font-size: 1.5rem;
}

.term-warning-card.warning i {
    color: #f59e0b;
}

.term-warning-card.expired i {
    color: #dc2626;
}

/* Text content */
.term-warning-text {
    font-size: 0.95rem;
    line-height: 1.4;
    flex: 1;
}

.term-warning-text strong {
    font-weight: 700;
}

/* Danger text for urgent reminders */
.text-danger {
    color: #dc2626 !important;
    font-weight: 600;
}

/* Days left badge */
.term-warning-badge {
    flex-shrink: 0;
}

.days-left-badge {
    background: #f59e0b;
    color: white;
    padding: 0.35rem 0.9rem;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    display: inline-block;
}

.term-warning-card.expired .days-left-badge {
    background: #dc2626;
}

/* Responsive Design */
@media (max-width: 768px) {
    .term-warning-card {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }

    .term-warning-content {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }

    .term-warning-text {
        text-align: center;
    }

    .days-left-badge {
        display: inline-block;
        margin-top: 0.5rem;
    }
}

@media (max-width: 480px) {
    .term-warning-card {
        padding: 0.875rem;
    }

    .term-warning-card i {
        font-size: 1.25rem;
    }

    .term-warning-text {
        font-size: 0.85rem;
    }

    .days-left-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }
}
/* Rest of your existing styles remain the same */
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

.page-title h1 i {
    color: #4361ee;
    margin-right: 0.5rem;
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success i {
    color: #28a745;
}

.btn-close {
    background: none;
    border: none;
    cursor: pointer;
    opacity: 0.5;
    margin-left: auto;
}

.btn-close i {
    font-size: 1rem;
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
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.stat-label i {
    font-size: 0.7rem;
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
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.view-link:hover {
    text-decoration: underline;
}

.view-link i {
    font-size: 0.8rem;
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
    text-decoration: none;
}

.btn-approve {
    background: #d4edda;
    color: #155724;
}

.btn-approve:hover {
    background: #c3e6cb;
    transform: translateY(-1px);
}

.btn-reject {
    background: #fee2e2;
    color: #dc2626;
}

.btn-reject:hover {
    background: #fecaca;
    transform: translateY(-1px);
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

.cases-table th i {
    margin-right: 0.3rem;
    color: #4361ee;
    font-size: 0.8rem;
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

.status-ongoing {
    background: #cce5ff;
    color: #004085;
}

.status-referred {
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
    text-decoration: none;
}

.btn-view:hover {
    background: #dee2e6;
    transform: translateY(-1px);
}

.no-data {
    color: #999;
    text-align: center;
    padding: 2rem;
    margin: 0;
}

.no-data i {
    color: #e2e8f0;
    margin-bottom: 0.5rem;
}

.no-data p {
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
{{-- Load Chart.js locally via Vite --}}
@vite(['resources/js/chart-config.js'])

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Statistics Chart
    const ctx = document.getElementById('monthlyChart')?.getContext('2d');
    if (!ctx) return;

    // Get the data from PHP
    const certificateStats = @json($certificateStats ?? []);
    const residentStats = @json($residentStats ?? []);
    const blotterStats = @json($blotterStats ?? []);

    // Prepare data arrays for 12 months
    let certificatesData = Array(12).fill(0);
    let residentsData = Array(12).fill(0);
    let blottersData = Array(12).fill(0);

    // Fill certificate data
    if (certificateStats && certificateStats.length > 0) {
        certificateStats.forEach(stat => {
            const monthIndex = stat.month - 1;
            certificatesData[monthIndex] = stat.total;
        });
    }

    // Fill resident data
    if (residentStats && residentStats.length > 0) {
        residentStats.forEach(stat => {
            const monthIndex = stat.month - 1;
            residentsData[monthIndex] = stat.total;
        });
    }

    // Fill blotter data
    if (blotterStats && blotterStats.length > 0) {
        blotterStats.forEach(stat => {
            const monthIndex = stat.month - 1;
            blottersData[monthIndex] = stat.total;
        });
    }

    // Initialize the chart with three datasets
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Certificates Issued',
                    data: certificatesData,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4361ee',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'New Residents',
                    data: residentsData,
                    borderColor: '#06d6a0',
                    backgroundColor: 'rgba(6, 214, 160, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#06d6a0',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Blotter Cases',
                    data: blottersData,
                    borderColor: '#f72585',
                    backgroundColor: 'rgba(247, 37, 133, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#f72585',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} records`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Number of Records',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>
@endpush
