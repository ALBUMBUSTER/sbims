@extends('layouts.app')

@section('title', 'System Activity Logs')

@push('styles')
<style>
    .logs-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .log-stat-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        text-align: center;
    }

    .log-stat-card h4 {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .log-stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #667eea;
    }

    .log-stat-label {
        font-size: 0.8rem;
        color: #999;
        margin-top: 0.5rem;
    }

    .filter-container {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .filter-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .filter-group {
        margin-bottom: 0;
    }

    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #555;
        font-size: 0.9rem;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 0.6rem;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 0.9rem;
        background: #f8fafc;
    }

    .filter-group input[type="date"] {
        padding: 0.5rem;
    }

    .filter-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    /* Fixed Button Styles */
    .filter-actions button {
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 2px solid transparent;
    }

    .filter-actions button[type="button"] {
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #e2e8f0;
    }

    .filter-actions button[type="button"]:hover {
        background: #e2e8f0;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    .filter-actions button[type="submit"] {
        background: #667eea;
        color: white;
        border: 2px solid #667eea;
    }

    .filter-actions button[type="submit"]:hover {
        background: #5a67d8;
        border-color: #5a67d8;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.3);
    }

    .filter-actions button[type="submit"]:active {
        transform: translateY(0);
        box-shadow: none;
    }

    .log-row {
        transition: background-color 0.2s;
    }

    .log-row:hover {
        background-color: #f8fafc;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        background: #667eea;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 500;
        color: #333;
    }

    .user-role {
        font-size: 0.8rem;
        color: #666;
    }

    .action-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }

    .action-login { background: #d1fae5; color: #065f46; }
    .action-logout { background: #fef3c7; color: #d97706; }
    .action-create { background: #dbeafe; color: #1e40af; }
    .action-update { background: #f3e8ff; color: #7c3aed; }
    .action-delete { background: #fee2e2; color: #dc2626; }
    .action-other { background: #f1f5f9; color: #64748b; }

    /* Pagination Styles */
    .pagination-container {
        padding: 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .pagination-info {
        color: #666;
        font-size: 0.9rem;
        text-align: center;
    }

    /* Override Laravel pagination styles */
    .pagination {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination li {
        display: inline-flex;
    }

    .pagination li a,
    .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 0.9rem;
        border-radius: 12px;
        font-size: 0.95rem;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .pagination li a:hover {
        background: #eef2ff;
        border-color: #667eea;
        color: #667eea;
    }

    .pagination li.active span {
        background: #667eea;
        color: #ffffff;
        border-color: #667eea;
        font-weight: 600;
    }

    .pagination li.disabled span {
        background: #f1f5f9;
        color: #cbd5e1;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }

    /* Hide the default Laravel pagination info text */
    .pagination .text-sm {
        display: none;
    }

    /* Previous and Next buttons styling */
    .pagination li:first-child a,
    .pagination li:first-child span,
    .pagination li:last-child a,
    .pagination li:last-child span {
        padding: 0 1.2rem;
    }

    .logs-summary {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 1rem;
        padding: 0.5rem 0;
    }

    .no-logs {
        text-align: center;
        padding: 3rem;
        color: #666;
        font-style: italic;
    }

    .table-actions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .export-btn {
        background: #10b981;
        color: white;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .export-btn:hover {
        background: #0da271;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
    }

    .export-btn:active {
        transform: translateY(0);
        box-shadow: none;
    }

    /* Table Styles */
    .data-table {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        overflow-x: auto;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .table-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.1rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }

    td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        color: #333;
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>System Activity Logs</h1>
                <p>Monitor all user activities and system events</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('admin.logs.export', $filters) }}" class="export-btn">
                    <span>📥</span> Export Logs
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="logs-stats-grid">
            <div class="log-stat-card">
                <h4>Total Logs</h4>
                <div class="log-stat-value">{{ $stats['total_logs'] }}</div>
                <div class="log-stat-label">All Activities</div>
            </div>
            <div class="log-stat-card">
                <h4>Logs Today</h4>
                <div class="log-stat-value">{{ $stats['logs_today'] }}</div>
                <div class="log-stat-label">Today's Activities</div>
            </div>
            <div class="log-stat-card">
                <h4>Unique Users</h4>
                <div class="log-stat-value">{{ $stats['unique_users'] }}</div>
                <div class="log-stat-label">Active Users</div>
            </div>
            <div class="log-stat-card">
                <h4>Most Active User</h4>
                <div class="log-stat-value" style="font-size: 1.5rem;">{{ $stats['most_active_user'] }}</div>
                <div class="log-stat-label">Highest Activity</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
            <div class="filter-title">
                <span>🔍</span> Filter Logs
            </div>

            <form method="GET" action="{{ route('admin.logs.index') }}">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="user_id">User</label>
                        <select id="user_id" name="user_id">
                            <option value="all">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>
                                    {{ $user->username }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="action">Action Type</label>
                        <select id="action" name="action">
                            <option value="all">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ $filters['action'] == $action ? 'selected' : '' }}>
                                    {{ ucfirst($action) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="from_date">From Date</label>
                        <input type="date"
                               id="from_date"
                               name="from_date"
                               value="{{ $filters['from_date'] }}"
                               placeholder="dd/mm/yyyy">
                    </div>

                    <div class="filter-group">
                        <label for="to_date">To Date</label>
                        <input type="date"
                               id="to_date"
                               name="to_date"
                               value="{{ $filters['to_date'] }}"
                               placeholder="dd/mm/yyyy">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="button" onclick="resetFilters()">
                        <span>🔄</span> Reset
                    </button>
                    <button type="submit">
                        <span>✅</span> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Activity Logs Table -->
        <div class="data-table">
            <div class="table-header">
                <h3>Activity Logs</h3>
            </div>

            @if($logs->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr class="log-row">
                        <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ substr($log->user->username ?? 'S', 0, 1) }}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $log->user->full_name ?? ($log->user->username ?? 'System') }}</div>
                                    @if($log->user)
                                        <div class="user-role">{{ $log->user->username }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($log->user)
                                <span class="action-badge action-other">
                                    {{ ucfirst($log->user->role) }}
                                </span>
                            @else
                                <span class="action-badge action-other">System</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $actionClass = 'action-other';
                                if (str_contains(strtolower($log->action), 'login')) $actionClass = 'action-login';
                                elseif (str_contains(strtolower($log->action), 'logout')) $actionClass = 'action-logout';
                                elseif (str_contains(strtolower($log->action), 'create')) $actionClass = 'action-create';
                                elseif (str_contains(strtolower($log->action), 'update')) $actionClass = 'action-update';
                                elseif (str_contains(strtolower($log->action), 'delete')) $actionClass = 'action-delete';
                            @endphp
                            <span class="action-badge {{ $actionClass }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->ip_address ?? 'N/A' }}</td>
                        <td title="{{ $log->user_agent }}">
                            {{ $log->short_user_agent }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                </div>
                {{ $logs->links('pagination::simple-bootstrap-4') }}
            </div>
            @else
            <div class="no-logs">
                <p>No activity logs found for the selected filters.</p>
            </div>
            @endif
        </div>
    </main>
</div>

@push('scripts')
<script>
    // Reset filters
    function resetFilters() {
        document.getElementById('user_id').value = 'all';
        document.getElementById('action').value = 'all';
        document.getElementById('from_date').value = '';
        document.getElementById('to_date').value = '';

        // Submit the form to reset
        document.querySelector('form').submit();
    }

    // Date validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;

        if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
            alert('From date cannot be later than To date');
            e.preventDefault();
        }
    });

    // Auto-set date format placeholder
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        const monthAgo = new Date();
        monthAgo.setMonth(monthAgo.getMonth() - 1);
        const monthAgoStr = monthAgo.toISOString().split('T')[0];

        // Set default "from date" to one month ago if empty
        const fromDateInput = document.getElementById('from_date');
        if (!fromDateInput.value) {
            fromDateInput.value = monthAgoStr;
        }

        // Set default "to date" to today if empty
        const toDateInput = document.getElementById('to_date');
        if (!fromDateInput.value) {
            toDateInput.value = today;
        }
    });
</script>
@endpush
@endsection
