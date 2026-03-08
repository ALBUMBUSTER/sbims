    @extends('layouts.app')

    @section('title', 'Clerk Dashboard')

    @section('content')
    <div class="main-container">
        <main class="content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Clerk Dashboard</h1>
                    <p>Welcome back, {{ auth()->user()->full_name ?? auth()->user()->name }}!</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon residents-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Residents</h3>
                        <div class="stat-value">{{ $stats['total_residents'] }}</div>
                        <a href="{{ route('clerk.residents.index') }}" class="stat-link">View Residents →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon certificate-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Certificates</h3>
                        <div class="stat-value">{{ $stats['total_certificates'] }}</div>
                        <a href="{{ route('clerk.certificates.index') }}" class="stat-link">View Certificates →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Pending</h3>
                        <div class="stat-value">{{ $stats['pending_certificates'] }}</div>
                        <span class="stat-label">Awaiting Approval</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon released-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Released</h3>
                        <div class="stat-value">{{ $stats['released_certificates'] }}</div>
                        <span class="stat-label">Completed</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="actions-grid">
                    <a href="{{ route('clerk.certificates.create') }}" class="action-card">
                        <i class="fas fa-file-medical"></i>
                        <span>Issue Certificate</span>
                    </a>
                    <a href="{{ route('clerk.residents.index') }}" class="action-card">
                        <i class="fas fa-search"></i>
                        <span>Look Up Resident</span>
                    </a>
                    <a href="{{ route('clerk.reports.summary') }}" class="action-card">
                        <i class="fas fa-chart-bar"></i>
                        <span>View Reports</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity-grid">
                <!-- Recent Certificates -->
                <div class="recent-card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-alt"></i> Recent Certificates</h3>
                        <a href="{{ route('clerk.certificates.index') }}" class="view-link">View All</a>
                    </div>
                    <div class="card-body">
                        @if(count($stats['recent_certificates']) > 0)
                            <table class="recent-table">
                                <thead>
                                    <tr>
                                        <th>Certificate #</th>
                                        <th>Resident</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['recent_certificates'] as $cert)
                                    <tr>
                                        <td>{{ $cert->certificate_id }}</td>
                                        <td>{{ $cert->resident->full_name ?? 'N/A' }}</td>
                                        <td>{{ $cert->certificate_type }}</td>
                                        <td>
                                            <span class="status-badge status-{{ strtolower($cert->status) }}">
                                                {{ $cert->status }}
                                            </span>
                                        </td>
                                        <td>{{ $cert->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="no-data">No recent certificates</p>
                        @endif
                    </div>
                </div>

                <!-- Recent Residents -->
                <div class="recent-card">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> Recent Residents</h3>
                        <a href="{{ route('clerk.residents.index') }}" class="view-link">View All</a>
                    </div>
                    <div class="card-body">
                        @if(count($stats['recent_residents']) > 0)
                            <table class="recent-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Purok</th>
                                        <th>Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['recent_residents'] as $resident)
                                    <tr>
                                        <td>{{ $resident->resident_id }}</td>
                                        <td>{{ $resident->full_name }}</td>
                                        <td>Purok {{ $resident->purok }}</td>
                                        <td>{{ $resident->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="no-data">No recent residents</p>
                        @endif
                    </div>
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

    .page-title p {
        color: #666;
        font-size: 1rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
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

    .stat-icon.residents-icon { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
    .stat-icon.certificate-icon { background: linear-gradient(135deg, #06d6a0, #05a87a); }
    .stat-icon.pending-icon { background: linear-gradient(135deg, #f8961e, #f3722c); }
    .stat-icon.released-icon { background: linear-gradient(135deg, #4cc9f0, #4895ef); }

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

    .stat-link {
        color: #4361ee;
        text-decoration: none;
        font-size: 0.9rem;
        display: inline-block;
        margin-top: 0.5rem;
    }

    .stat-link:hover {
        text-decoration: underline;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #999;
        display: block;
        margin-top: 0.25rem;
    }

    /* Quick Actions */
    .quick-actions {
        margin-bottom: 2rem;
    }

    .quick-actions h2 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .action-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-decoration: none;
        color: #333;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
        border: 1px solid #e2e8f0;
    }

    .action-card:hover {
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        color: white;
        transform: translateY(-5px);
    }

    .action-card i {
        font-size: 2rem;
    }

    .action-card span {
        font-size: 0.9rem;
        text-align: center;
    }

    /* Recent Activity Grid */
    .recent-activity-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .recent-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .card-header h3 {
        margin: 0;
        color: #333;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-header h3 i {
        color: #4361ee;
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

    .card-body {
        padding: 1.5rem;
    }

    .recent-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recent-table th {
        text-align: left;
        padding: 0.75rem;
        background: #f8fafc;
        color: #666;
        font-weight: 600;
        font-size: 0.8rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .recent-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
        color: #333;
        font-size: 0.9rem;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
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
        background: #cce5ff;
        color: #004085;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .no-data {
        color: #999;
        text-align: center;
        padding: 2rem;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .recent-activity-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr 1fr;
        }

        .recent-table {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .actions-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    @endpush
