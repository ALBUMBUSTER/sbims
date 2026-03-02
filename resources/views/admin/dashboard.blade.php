@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Admin Dashboard</h1>
                <p class="welcome-text">Welcome back, {{ auth()->user()->name ?? 'Admin' }}! Here's your system overview.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('admin.logs.index') }}" class="btn-view-all">
                    <i class="fas fa-history"></i>
                    View All Logs
                </a>
            </div>
        </div>

        <!-- User Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon admin-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-details">
                    <h3>Administrators</h3>
                    <div class="stat-value">{{ $userStats['admin'] ?? 0 }}</div>
                    <span class="stat-label">System Administrators</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon captain-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-details">
                    <h3>Barangay Captains</h3>
                    <div class="stat-value">{{ $userStats['captain'] ?? 0 }}</div>
                    <span class="stat-label">Barangay Officials</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon secretary-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="stat-details">
                    <h3>Secretaries</h3>
                    <div class="stat-value">{{ $userStats['secretary'] ?? 0 }}</div>
                    <span class="stat-label">Barangay Secretaries</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon resident-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3>Residents</h3>
                    <div class="stat-value">{{ number_format($systemStats['total_residents'] ?? 0) }}</div>
                    <span class="stat-label">Total Barangay Residents</span>
                </div>
            </div>
        </div>

        <!-- System Overview Cards -->
        <div class="overview-grid">
            <div class="overview-card">
                <div class="overview-header">
                    <i class="fas fa-file-alt"></i>
                    <h3>Certificates</h3>
                </div>
                <div class="overview-body">
                    <div class="overview-item">
                        <span>Total Requests</span>
                        <strong>{{ number_format($systemStats['total_certificates'] ?? 0) }}</strong>
                    </div>
                    <div class="overview-item">
                        <span>Pending</span>
                        <strong class="text-warning">{{ number_format($systemStats['pending_certificates'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            <div class="overview-card">
                <div class="overview-header">
                    <i class="fas fa-gavel"></i>
                    <h3>Blotter Cases</h3>
                </div>
                <div class="overview-body">
                    <div class="overview-item">
                        <span>Total Cases</span>
                        <strong>{{ number_format($systemStats['total_blotters'] ?? 0) }}</strong>
                    </div>
                    <div class="overview-item">
                        <span>Active</span>
                        <strong class="text-danger">{{ number_format($systemStats['active_blotters'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            <div class="overview-card">
                <div class="overview-header">
                    <i class="fas fa-users-cog"></i>
                    <h3>Users</h3>
                </div>
                <div class="overview-body">
                    <div class="overview-item">
                        <span>Total Users</span>
                        <strong>{{ number_format($systemStats['total_users'] ?? 0) }}</strong>
                    </div>
                    <div class="overview-item">
                        <span>Active</span>
                        <strong class="text-success">{{ number_format($systemStats['active_users'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            <div class="overview-card">
                <div class="overview-header">
                    <i class="fas fa-home"></i>
                    <h3>Residents</h3>
                </div>
                <div class="overview-body">
                    <div class="overview-item">
                        <span>Total</span>
                        <strong>{{ number_format($systemStats['total_residents'] ?? 0) }}</strong>
                    </div>
                    <div class="overview-item">
                        <span>Voters</span>
                        <strong>{{ number_format(\App\Models\Resident::where('is_voter', true)->count() ?? 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Summary -->
        <div class="quick-summary">
            <div class="summary-item">
                <span class="summary-label">Total System Users</span>
                <span class="summary-value">{{ number_format($systemStats['total_users'] ?? 0) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Barangay Residents</span>
                <span class="summary-value">{{ number_format($systemStats['total_residents'] ?? 0) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">System Status</span>
                <span class="summary-value status-active">Active</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Last Updated</span>
                <span class="summary-value">{{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <!-- Activities by Role -->
        <?php
            $totalActivities = array_sum($activitiesByRole ?? []);
            $adminWidth = isset($activitiesByRole['admin']) && $activitiesByRole['admin'] > 0 ? min(100, ($activitiesByRole['admin'] / max(1, $totalActivities) * 100)) : 0;
            $captainWidth = isset($activitiesByRole['captain']) && $activitiesByRole['captain'] > 0 ? min(100, ($activitiesByRole['captain'] / max(1, $totalActivities) * 100)) : 0;
            $secretaryWidth = isset($activitiesByRole['secretary']) && $activitiesByRole['secretary'] > 0 ? min(100, ($activitiesByRole['secretary'] / max(1, $totalActivities) * 100)) : 0;
        ?>

        <div class="role-activity">
            <div class="role-header">
                <i class="fas fa-chart-bar"></i>
                <h3>Activities by Role</h3>
            </div>
            <div class="role-bars">
                <div class="role-bar">
                    <span class="role-label">Admin</span>
                    <div class="bar-container">
                        <div class="bar admin" style="width: <?php echo $adminWidth; ?>%;"></div>
                    </div>
                    <span class="role-count"><?php echo $activitiesByRole['admin'] ?? 0; ?></span>
                </div>
                <div class="role-bar">
                    <span class="role-label">Captain</span>
                    <div class="bar-container">
                        <div class="bar captain" style="width: <?php echo $captainWidth; ?>%;"></div>
                    </div>
                    <span class="role-count"><?php echo $activitiesByRole['captain'] ?? 0; ?></span>
                </div>
                <div class="role-bar">
                    <span class="role-label">Secretary</span>
                    <div class="bar-container">
                        <div class="bar secretary" style="width: <?php echo $secretaryWidth; ?>%;"></div>
                    </div>
                    <span class="role-count"><?php echo $activitiesByRole['secretary'] ?? 0; ?></span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        @if(isset($recentActivities) && $recentActivities->count() > 0)
        <div class="data-table">
            <div class="table-header">
                <div class="header-title">
                    <i class="fas fa-clock"></i>
                    <h3>Recent System Activity</h3>
                </div>
                <a href="{{ route('admin.logs.index') }}" class="btn-view-all small">
                    View All
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> User</th>
                            <th><i class="fas fa-tag"></i> Role</th>
                            <th><i class="fas fa-bolt"></i> Action</th>
                            <th><i class="fas fa-align-left"></i> Description</th>
                            <th><i class="fas fa-calendar"></i> Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $activity)
                        <tr>
                            <td class="user-cell">
                                @if($activity->user)
                                    <?php
                                        $avatarColor = '#4361ee'; // admin default
                                        if($activity->user->role_id == 2) {
                                            $avatarColor = '#f72585';
                                        } elseif($activity->user->role_id == 3) {
                                            $avatarColor = '#4cc9f0';
                                        }
                                        $userInitial = strtoupper(substr($activity->user->name ?? $activity->user->username, 0, 1));
                                    ?>
                                    <div class="user-info">
                                        <span class="user-avatar" style="background: <?php echo $avatarColor; ?>;">
                                            <?php echo $userInitial; ?>
                                        </span>
                                        <span>{{ $activity->user->name ?? $activity->user->username }}</span>
                                    </div>
                                @else
                                    <div class="user-info">
                                        <span class="user-avatar system">S</span>
                                        <span>System</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($activity->user)
                                    <?php
                                        $roleClass = 'admin';
                                        $roleName = 'Admin';
                                        if($activity->user->role_id == 2) {
                                            $roleClass = 'captain';
                                            $roleName = 'Captain';
                                        } elseif($activity->user->role_id == 3) {
                                            $roleClass = 'secretary';
                                            $roleName = 'Secretary';
                                        }
                                    ?>
                                    <span class="role-badge role-<?php echo $roleClass; ?>">
                                        <?php echo $roleName; ?>
                                    </span>
                                @else
                                    <span class="role-badge role-system">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="action-badge action-{{ $activity->action_type ?? 'other' }}">
                                    {{ $activity->action }}
                                </span>
                            </td>
                            <td>{{ $activity->description }}</td>
                            <td class="timestamp">{{ \Carbon\Carbon::parse($activity->created_at)->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="data-table">
            <div class="table-header">
                <div class="header-title">
                    <i class="fas fa-clock"></i>
                    <h3>Recent System Activity</h3>
                </div>
            </div>
            <div class="no-data">
                <i class="fas fa-database fa-3x"></i>
                <p>No activity logs found.</p>
                <small>Activities will appear here as users perform actions across the system.</small>
            </div>
        </div>
        @endif
    </main>
</div>
@endsection

<!-- Keep all your existing styles - they're perfect! -->
@push('styles')
<!-- Your existing styles remain exactly the same -->
<style>
:root {
    --primary-color: #4361ee;
    --secondary-color: #3f37c9;
    --success-color: #4cc9f0;
    --danger-color: #f72585;
    --warning-color: #f8961e;
    --info-color: #4895ef;
    --dark-color: #1e1b4b;
    --light-color: #f8f9fa;
    --gray-color: #6c757d;
}

.main-container {
    display: flex;
    min-height: calc(100vh - 70px);
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
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
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    font-size: 2rem;
    font-weight: 700;
}

.page-title .welcome-text {
    color: var(--gray-color);
    font-size: 0.95rem;
}

.btn-view-all {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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

.btn-view-all:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
}

.btn-view-all.small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    background: linear-gradient(135deg, var(--info-color), var(--primary-color));
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
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

.stat-icon.admin-icon { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
.stat-icon.captain-icon { background: linear-gradient(135deg, #f72585, #b5179e); }
.stat-icon.secretary-icon { background: linear-gradient(135deg, #4cc9f0, #4895ef); }
.stat-icon.resident-icon { background: linear-gradient(135deg, #f8961e, #f3722c); }

.stat-details {
    flex: 1;
}

.stat-details h3 {
    color: var(--gray-color);
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--dark-color);
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--gray-color);
}

/* Overview Grid */
.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.overview-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.overview-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.overview-header i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.overview-header h3 {
    color: var(--dark-color);
    font-size: 1rem;
    margin: 0;
}

.overview-body {
    display: flex;
    justify-content: space-around;
}

.overview-item {
    text-align: center;
}

.overview-item span {
    display: block;
    color: var(--gray-color);
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.overview-item strong {
    font-size: 1.5rem;
    color: var(--dark-color);
}

.text-warning { color: var(--warning-color); }
.text-danger { color: var(--danger-color); }
.text-success { color: #10b981; }

/* Quick Summary */
.quick-summary {
    display: flex;
    gap: 2rem;
    background: white;
    padding: 1.2rem 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    flex-wrap: wrap;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.summary-label {
    color: var(--gray-color);
    font-size: 0.9rem;
}

.summary-value {
    font-weight: 600;
    color: var(--dark-color);
}

.summary-value.status-active {
    color: #10b981;
    background: #d1fae5;
    padding: 0.2rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Role Activity */
.role-activity {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.role-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.role-header i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.role-header h3 {
    color: var(--dark-color);
    font-size: 1.1rem;
    margin: 0;
}

.role-bars {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.role-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.role-label {
    width: 80px;
    color: var(--gray-color);
    font-weight: 500;
}

.bar-container {
    flex: 1;
    height: 20px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.bar {
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.bar.admin { background: linear-gradient(90deg, #4361ee, #3a0ca3); }
.bar.captain { background: linear-gradient(90deg, #f72585, #b5179e); }
.bar.secretary { background: linear-gradient(90deg, #4cc9f0, #4895ef); }

.role-count {
    width: 40px;
    color: var(--dark-color);
    font-weight: 600;
}

/* Data Table */
.data-table {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    overflow: hidden;
}

.table-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.header-title i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.header-title h3 {
    margin: 0;
    color: var(--dark-color);
    font-size: 1.2rem;
    font-weight: 600;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #f8fafc;
}

th {
    padding: 1.2rem 1.5rem;
    text-align: left;
    color: var(--gray-color);
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

th i {
    margin-right: 0.5rem;
    font-size: 0.9rem;
}

td {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
}

tbody tr:hover {
    background: #f8fafc;
}

/* User Info */
.user-cell .user-info {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    color: white;
}

.user-avatar.system {
    background: linear-gradient(135deg, var(--gray-color), #495057);
}

/* Role Badge */
.role-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.role-badge.role-admin {
    background: #4361ee20;
    color: #4361ee;
}

.role-badge.role-captain {
    background: #f7258520;
    color: #f72585;
}

.role-badge.role-secretary {
    background: #4cc9f020;
    color: #4cc9f0;
}

.role-badge.role-system {
    background: #e2e8f0;
    color: var(--gray-color);
}

/* Action Badge */
.action-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.action-badge.action-create { background: #d4edda; color: #155724; }
.action-badge.action-update { background: #cce5ff; color: #004085; }
.action-badge.action-delete { background: #fee2e2; color: #dc2626; }
.action-badge.action-process { background: #fff3cd; color: #856404; }
.action-badge.action-login { background: #e2d5f1; color: #553c9a; }
.action-badge.action-logout { background: #e2e8f0; color: #4a5568; }
.action-badge.action-export { background: #d1fae5; color: #065f46; }
.action-badge.action-generate { background: #e0e7ff; color: #3730a3; }
.action-badge.action-certificate { background: #ffe4e6; color: #be185d; }
.action-badge.action-blotter { background: #fed7aa; color: #92400e; }
.action-badge.action-resident { background: #bfdbfe; color: #1e40af; }

.timestamp {
    color: var(--gray-color);
    font-size: 0.9rem;
}

.no-data {
    padding: 4rem 2rem;
    text-align: center;
    color: var(--gray-color);
}

.no-data i {
    color: #e9ecef;
    margin-bottom: 1rem;
}

.no-data p {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.no-data small {
    color: var(--gray-color);
    display: block;
    margin-bottom: 1.5rem;
}

.no-data-action {
    margin-top: 1.5rem;
}

.btn-migrate {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #e9ecef;
    color: var(--dark-color);
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-migrate:hover {
    background: #dee2e6;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .content {
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .stats-grid,
    .overview-grid {
        grid-template-columns: 1fr;
    }

    .quick-summary {
        flex-direction: column;
        gap: 1rem;
    }

    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}
</style>
@endpush
