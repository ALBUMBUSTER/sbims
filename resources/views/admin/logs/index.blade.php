@extends('layouts.app')

@section('title', 'System Activity Logs')

@push('styles')
<style>
    /* ===== RESET & CONTAINER FIXES ===== */
    .main-container {
        display: flex;
        min-height: calc(100vh - 70px);
        background: #f8fafc;
        width: 100%;
        overflow-x: hidden;
    }

    .content {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        overflow-x: hidden;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        background: white;
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 1rem;
        width: 100%;
        box-sizing: border-box;
    }

    .page-title h1 {
        color: #333;
        margin-bottom: 0.25rem;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .page-title p {
        color: #666;
        font-size: 0.875rem;
    }

    .page-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .export-btn {
        background: #10b981;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .export-btn:hover {
        background: #0da271;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
    }

    /* ===== STATS CARDS ===== */
    .logs-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
        width: 100%;
    }

    .log-stat-card {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        text-align: center;
        min-width: 0;
    }

    .log-stat-card h4 {
        color: #666;
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .log-stat-value {
        font-size: 1.75rem;
        font-weight: bold;
        color: #667eea;
        line-height: 1.2;
    }

    .log-stat-label {
        font-size: 0.75rem;
        color: #999;
        margin-top: 0.5rem;
    }

    /* ===== FILTER SECTION ===== */
    .filter-container {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        width: 100%;
        box-sizing: border-box;
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

    .filter-title i {
        color: #667eea;
        font-size: 1.2rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .filter-group {
        margin-bottom: 0;
        min-width: 0;
    }

    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #555;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 0.6rem;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 0.9rem;
        background: #f8fafc;
        box-sizing: border-box;
    }

    .filter-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
        flex-wrap: wrap;
    }

    .filter-actions button {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 2px solid transparent;
        white-space: nowrap;
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

    /* ===== DATA TABLE ===== */
    .data-table {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        width: 100%;
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .table-header h3 {
        margin: 0;
        color: #333;
        font-size: 1rem;
    }

    .table-header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .delete-btn {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .delete-btn:hover:not(:disabled) {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
    }

    .delete-btn:disabled {
        background: #fca5a5;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* ===== TABLE CONTAINER WITH SCROLL ===== */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f1f5f9;
    }

    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px; /* Table will scroll at this width */
    }

    th {
        text-align: left;
        padding: 1rem;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.8rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: #333;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .checkbox-column {
        width: 40px;
        text-align: center;
    }

    .select-all-checkbox,
    .log-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #667eea;
    }

    /* ===== USER INFO STYLES ===== */
    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
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
        flex-shrink: 0;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .user-name {
        font-weight: 500;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 0.75rem;
        color: #666;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .action-badge {
        padding: 0.25rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-block;
        white-space: nowrap;
    }

    .action-login { background: #d1fae5; color: #065f46; }
    .action-logout { background: #fef3c7; color: #d97706; }
    .action-create { background: #dbeafe; color: #1e40af; }
    .action-update { background: #f3e8ff; color: #7c3aed; }
    .action-delete { background: #fee2e2; color: #dc2626; }
    .action-other { background: #f1f5f9; color: #64748b; }

    /* ===== PAGINATION ===== */
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
        font-size: 0.85rem;
        text-align: center;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination li a,
    .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
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
    }

    .pagination li.disabled span {
        background: #f1f5f9;
        color: #cbd5e1;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }

    /* ===== EMPTY STATE ===== */
    .no-logs {
        text-align: center;
        padding: 2rem;
        color: #666;
        font-style: italic;
    }

    /* ===== MODAL STYLES ===== */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .modal-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.1rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
    }

    .modal-body {
        margin-bottom: 1.5rem;
        color: #666;
        font-size: 0.95rem;
    }

    .modal-footer {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .modal-footer button {
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-footer .cancel-btn {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
    }

    .modal-footer .cancel-btn:hover {
        background: #e2e8f0;
    }

    .modal-footer .confirm-btn {
        background: #ef4444;
        border: none;
        color: white;
    }

    .modal-footer .confirm-btn:hover {
        background: #dc2626;
    }

    /* ===== TOAST NOTIFICATION ===== */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .toast {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 280px;
        max-width: 350px;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        border-left: 4px solid;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.success { border-left-color: #10b981; }
    .toast.error { border-left-color: #ef4444; }
    .toast.warning { border-left-color: #f59e0b; }
    .toast.info { border-left-color: #3b82f6; }

    .toast.success .toast-icon { color: #10b981; }
    .toast.error .toast-icon { color: #ef4444; }
    .toast.warning .toast-icon { color: #f59e0b; }
    .toast.info .toast-icon { color: #3b82f6; }

    .toast-icon {
        font-size: 1.2rem;
    }

    .toast-content {
        flex: 1;
    }

    .toast-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .toast-message {
        color: #64748b;
        font-size: 0.8rem;
    }

    .toast-close {
        color: #94a3b8;
        cursor: pointer;
        font-size: 1rem;
        padding: 4px;
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    }

    .loading-overlay.show {
        display: flex;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */
    @media (max-width: 992px) {
        .logs-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 0.75rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-actions {
            width: 100%;
        }

        .export-btn {
            width: 100%;
            justify-content: center;
        }

        .logs-stats-grid {
            grid-template-columns: 1fr;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
        }

        .filter-actions button {
            width: 100%;
            justify-content: center;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-header-actions {
            width: 100%;
        }

        .delete-btn {
            width: 100%;
            justify-content: center;
        }

        th, td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }

        /* Hide User Agent column on mobile */
        th:nth-child(8), td:nth-child(8) {
            display: none;
        }
    }

    @media (max-width: 480px) {
        /* Hide Role column on very small screens */
        th:nth-child(4), td:nth-child(4) {
            display: none;
        }

        .action-badge {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }

        .modal-content {
            padding: 1.25rem;
        }

        .toast {
            min-width: 250px;
        }
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <!-- Toast Container -->
        <div class="toast-container" id="toastContainer"></div>

        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
        </div>

        <div class="page-header">
            <div class="page-title">
                <h1>System Activity Logs</h1>
                <p>Monitor all user activities and system events</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('admin.logs.export', request()->query()) }}" class="export-btn">
                    <i class="fas fa-download"></i> Export Logs
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="logs-stats-grid" id="statsContainer">
            <div class="log-stat-card">
                <h4>Total Logs</h4>
                <div class="log-stat-value" id="totalLogs">{{ $stats['total_logs'] }}</div>
                <div class="log-stat-label">All Activities</div>
            </div>
            <div class="log-stat-card">
                <h4>Logs Today</h4>
                <div class="log-stat-value" id="logsToday">{{ $stats['logs_today'] }}</div>
                <div class="log-stat-label">Today's Activities</div>
            </div>
            <div class="log-stat-card">
                <h4>Unique Users</h4>
                <div class="log-stat-value" id="uniqueUsers">{{ $stats['unique_users'] }}</div>
                <div class="log-stat-label">Active Users</div>
            </div>
            <div class="log-stat-card">
                <h4>Most Active User</h4>
                <div class="log-stat-value" style="font-size: 1.2rem;" id="mostActiveUser">{{ $stats['most_active_user'] }}</div>
                <div class="log-stat-label">Highest Activity</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
            <div class="filter-title">
                <i class="fas fa-filter"></i> Filter Logs
            </div>

            <form method="GET" action="{{ route('admin.logs.index') }}" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="user_id">User</label>
                        <select id="user_id" name="user_id">
                            <option value="all">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? 'all') == $user->id ? 'selected' : '' }}>
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
                                <option value="{{ $action }}" {{ ($filters['action'] ?? 'all') == $action ? 'selected' : '' }}>
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
                               value="{{ $filters['from_date'] ?? '' }}"
                               placeholder="dd/mm/yyyy">
                    </div>

                    <div class="filter-group">
                        <label for="to_date">To Date</label>
                        <input type="date"
                               id="to_date"
                               name="to_date"
                               value="{{ $filters['to_date'] ?? '' }}"
                               placeholder="dd/mm/yyyy">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="button" onclick="resetFilters()">
                        <i class="fas fa-redo-alt"></i> Reset
                    </button>
                    <button type="submit">
                        <i class="fas fa-check"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Activity Logs Table -->
        <div class="data-table">
            <div class="table-header">
                <h3>Activity Logs</h3>
                <div class="table-header-actions">
                    <button type="button" class="delete-btn" id="bulkDeleteBtn" disabled onclick="openBulkDeleteModal()">
                        <i class="fas fa-trash-alt"></i> Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>

            @if($logs->count() > 0)
            <div id="logsTableContainer">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th class="checkbox-column">
                                    <input type="checkbox" class="select-all-checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                                </th>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            @foreach($logs as $log)
                            <tr class="log-row" id="log-row-{{ $log->id }}">
                                <td class="checkbox-column">
                                    <input type="checkbox" value="{{ $log->id }}" class="log-checkbox" onclick="updateBulkDeleteButton()">
                                </td>
                                <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            {{ substr($log->user->username ?? 'S', 0, 1) }}
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name" title="{{ $log->user->full_name ?? ($log->user->username ?? 'System') }}">
                                                {{ Str::limit($log->user->full_name ?? ($log->user->username ?? 'System'), 20) }}
                                            </div>
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
                                <td title="{{ $log->description }}">{{ Str::limit($log->description, 30) }}</td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                <td title="{{ $log->user_agent }}">
                                    {{ $log->short_user_agent ?? Str::limit($log->user_agent, 20) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" id="paginationContainer">
                <div class="pagination-info">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                </div>
                {{ $logs->appends(request()->query())->links('pagination::simple-bootstrap-4') }}
            </div>
            @else
            <div class="no-logs">
                <p>No activity logs found for the selected filters.</p>
            </div>
            @endif
        </div>
    </main>
</div>

<!-- Bulk Delete Modal -->
<div class="modal" id="bulkDeleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Multiple Logs</h3>
            <button class="modal-close" onclick="closeBulkDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <span id="bulkDeleteCount"></span> selected log entries? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn" onclick="closeBulkDeleteModal()">Cancel</button>
            <button class="confirm-btn" onclick="confirmBulkDelete()">Delete All</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Global variables
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');
    const selectAllCheckbox = document.getElementById('selectAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');
    const loadingOverlay = document.getElementById('loadingOverlay');

    // Toast Notification System
    function showToast(title, message, type = 'success', duration = 5000) {
        const toastContainer = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const toastHtml = `
            <div id="${toastId}" class="toast ${type}">
                <div class="toast-icon">${icons[type]}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <div class="toast-close" onclick="closeToast('${toastId}')">✕</div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        setTimeout(() => {
            const toast = document.getElementById(toastId);
            if (toast) toast.classList.add('show');
        }, 10);

        setTimeout(() => closeToast(toastId), duration);
    }

    function closeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }
    }

    // Reset filters
    function resetFilters() {
        document.getElementById('user_id').value = 'all';
        document.getElementById('action').value = 'all';
        document.getElementById('from_date').value = '';
        document.getElementById('to_date').value = '';
        document.getElementById('filterForm').submit();
    }

    // Date validation
    document.getElementById('filterForm')?.addEventListener('submit', function(e) {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
            e.preventDefault();
            showToast('Invalid Date Range', 'From date cannot be later than To date', 'error');
        }
    });

    // Auto-set dates
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        const monthAgo = new Date();
        monthAgo.setMonth(monthAgo.getMonth() - 1);
        const monthAgoStr = monthAgo.toISOString().split('T')[0];

        const fromDateInput = document.getElementById('from_date');
        if (!fromDateInput.value) fromDateInput.value = monthAgoStr;

        const toDateInput = document.getElementById('to_date');
        if (!toDateInput.value) toDateInput.value = today;
    });

    // Modal functions
    function openBulkDeleteModal() {
        const checkboxes = document.querySelectorAll('.log-checkbox:checked');
        if (checkboxes.length > 0) {
            document.getElementById('bulkDeleteCount').textContent = checkboxes.length;
            bulkDeleteModal.classList.add('show');
        }
    }

    function closeBulkDeleteModal() {
        bulkDeleteModal.classList.remove('show');
    }

    function confirmBulkDelete() {
        const checkboxes = document.querySelectorAll('.log-checkbox:checked');
        if (checkboxes.length === 0) {
            closeBulkDeleteModal();
            return;
        }

        const logIds = Array.from(checkboxes).map(cb => cb.value);

        closeBulkDeleteModal();
        showLoading();

        // Get the current URL parameters for filters
        const params = new URLSearchParams(window.location.search);
        const url = '{{ route("admin.logs.bulk-delete") }}?' + params.toString();

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ log_ids: logIds })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            if (data.success) {
                // Remove all selected rows with fade effect
                let deletedCount = 0;
                logIds.forEach((id, index) => {
                    const row = document.getElementById(`log-row-${id}`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            deletedCount++;

                            // After last row is removed
                            if (deletedCount === logIds.length) {
                                showToast('Success', data.message, 'success');

                                // Update bulk delete button
                                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                                updateBulkDeleteButton();

                                // Refresh statistics
                                refreshStatistics();

                                // Check if table is empty
                                if (document.querySelectorAll('.log-row').length === 0) {
                                    setTimeout(() => location.reload(), 300);
                                }
                            }
                        }, index * 50);
                    }
                });
            } else {
                showToast('Error', data.message || 'Failed to delete log entries', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showToast('Error', error.message || 'Failed to delete log entries. Please try again.', 'error');
        });
    }

    // Refresh statistics via AJAX
    function refreshStatistics() {
        const params = new URLSearchParams(window.location.search);

        fetch(`/admin/logs/refresh-stats?${params.toString()}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalLogs').textContent = data.stats.total_logs;
                document.getElementById('logsToday').textContent = data.stats.logs_today;
                document.getElementById('uniqueUsers').textContent = data.stats.unique_users;
                document.getElementById('mostActiveUser').textContent = data.stats.most_active_user;
            }
        })
        .catch(error => console.error('Error refreshing stats:', error));
    }

    // Loading overlay
    function showLoading() {
        loadingOverlay.classList.add('show');
    }

    function hideLoading() {
        loadingOverlay.classList.remove('show');
    }

    // Bulk delete functions
    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.log-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkDeleteButton();
    }

    function updateBulkDeleteButton() {
        const checkboxes = document.querySelectorAll('.log-checkbox:checked');
        const count = checkboxes.length;

        if (selectedCount) selectedCount.textContent = count;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;

        // Update select all checkbox
        if (selectAllCheckbox) {
            const allCheckboxes = document.querySelectorAll('.log-checkbox');
            if (allCheckboxes.length > 0) {
                selectAllCheckbox.checked = Array.from(allCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target === bulkDeleteModal) closeBulkDeleteModal();
    }

    // Initialize
    updateBulkDeleteButton();
</script>
@endpush
@endsection
