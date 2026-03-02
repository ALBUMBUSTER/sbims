@extends('layouts.app')

@section('title', 'Secretary Dashboard')

@section('content')
<div class="dashboard-container">
    <!-- Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Secretary Dashboard</h1>
            <p>Barangay Secretary Panel</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                @svg('heroicon-o-users', 'icon-size')
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Residents</span>
                <span class="stat-value">{{ $totalResidents ?? 0 }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                @svg('heroicon-o-folder-open', 'icon-size')
            </div>
            <div class="stat-content">
                <span class="stat-label">Active Blotter Cases</span>
                <span class="stat-value">{{ $activeBlotterCases ?? 0 }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                @svg('heroicon-o-document-text', 'icon-size')
            </div>
            <div class="stat-content">
                <span class="stat-label">Pending Certificates</span>
                <span class="stat-value">{{ $pendingCertificates ?? 0 }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                @svg('heroicon-o-check-badge', 'icon-size')
            </div>
            <div class="stat-content">
                <span class="stat-label">Certificates Today</span>
                <span class="stat-value">{{ $certificatesToday ?? 0 }}</span>
            </div>
        </div>
    </div>

<!-- Recent Activities -->
<div class="recent-activities">
    <div class="section-header">
        <h2>Recent Activities</h2>
        <a href="{{ route('secretary.activities') }}" class="view-all-link">
            View All
            @svg('heroicon-o-arrow-right', 'icon-small')
        </a>
    </div>

    <div class="table-responsive">
        <table class="activities-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Date/Time</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $activity)
                <tr>
                    <td>
                        <div class="user-info">
                            <span class="user-name">{{ $activity->user->name ?? 'System' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="action-type">
                            @if(str_contains(strtolower($activity->action), 'create'))
                                <span class="action-badge action-create">Create</span>
                            @elseif(str_contains(strtolower($activity->action), 'update'))
                                <span class="action-badge action-update">Update</span>
                            @elseif(str_contains(strtolower($activity->action), 'delete'))
                                <span class="action-badge action-delete">Delete</span>
                            @else
                                <span class="action-badge action-other">{{ $activity->action }}</span>
                            @endif
                        </div>
                    </td>
                    <td>{{ $activity->description }}</td>
                    <td>{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                    <td><span class="ip-address">{{ $activity->ip_address }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="empty-state">
                            <x-heroicon-o-clock class="empty-icon" />
                            <h3>No activities found</h3>
                            <p>Activities will appear here as you perform actions.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection

@push('styles')
<style>
/* Add icon size classes */
.icon-size {
    width: 30px;
    height: 30px;
}

.icon-small {
    width: 16px;
    height: 16px;
    display: inline-block;
}

.dashboard-container {
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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.stat-icon {
    margin-right: 1rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

.stat-icon svg {
    width: 30px;
    height: 30px;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 2rem;
    font-weight: bold;
}

/* Recent Activities Section */
.recent-activities {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    color: #333;
    font-size: 1.3rem;
    margin: 0;
}

.view-all-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: background 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.view-all-link svg {
    width: 16px;
    height: 16px;
    color: currentColor;
}

.view-all-link:hover {
    background: #eef2ff;
    text-decoration: underline;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
}

.activities-table {
    width: 100%;
    border-collapse: collapse;
}

.activities-table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.activities-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    vertical-align: middle;
}

.activities-table tr:hover td {
    background: #f8fafc;
}

.activity-id {
    font-family: monospace;
    font-weight: 600;
    color: #555;
}

/* Activity Type */
.activity-type {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.type-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
}

.type-icon.certificate {
    background: #e2d5f1;
    color: #553c9a;
}

.type-icon.blotter {
    background: #fee2e2;
    color: #dc2626;
}

.type-icon.resident {
    background: #d4edda;
    color: #155724;
}

.type-icon.other {
    background: #e2e8f0;
    color: #4a5568;
}

.type-icon svg {
    width: 16px;
    height: 16px;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-released {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #fee2e2;
    color: #dc2626;
}

/* Action Button */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #667eea;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s;
    border: none;
    background: none;
    cursor: pointer;
}

.btn-action:hover {
    background: #eef2ff;
    transform: translateY(-2px);
}

.btn-action:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-action svg {
    width: 18px;
    height: 18px;
}

.text-center {
    text-align: center;
}
/* Activity styles for dashboard */
.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #333;
}

.action-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.action-create {
    background: #d4edda;
    color: #155724;
}

.action-update {
    background: #cce5ff;
    color: #004085;
}

.action-delete {
    background: #fee2e2;
    color: #dc2626;
}

.action-other {
    background: #e2e8f0;
    color: #4a5568;
}

.ip-address {
    font-family: monospace;
    color: #666;
}

.recent-activities {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.activities-table {
    width: 100%;
    border-collapse: collapse;
}

.activities-table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.activities-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    vertical-align: middle;
}

.activities-table tr:hover td {
    background: #f8fafc;
}
</style>
@endpush
