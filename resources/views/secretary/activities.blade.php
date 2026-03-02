@extends('layouts.app')

@section('title', 'All Activities')

@section('content')
<div class="activities-container">
    <div class="page-header">
        <div class="page-title">
            <h1>All Activities</h1>
            <p>Complete activity log</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.dashboard') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form action="{{ route('secretary.activities') }}" method="GET" class="filters-form">
            <div class="filter-group">
                <input type="text" name="action" placeholder="Filter by action..." value="{{ request('action') }}" class="filter-input">
            </div>

            <div class="filter-group">
                <input type="text" name="search" placeholder="Search description or user..." value="{{ request('search') }}" class="filter-input">
            </div>

            <div class="filter-group">
                <input type="date" name="date_from" placeholder="From date" value="{{ request('date_from') }}" class="filter-input">
            </div>

            <div class="filter-group">
                <input type="date" name="date_to" placeholder="To date" value="{{ request('date_to') }}" class="filter-input">
            </div>

            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="{{ route('secretary.activities') }}" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Activities Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="activities-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Date/Time</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                        <tr>
                            <td>
                                <div class="user-info">
                                    <span class="user-name">{{ $activity->user->name ?? 'System' }}</span>
                                    @if($activity->user)
                                        <span class="user-email">{{ $activity->user->email }}</span>
                                    @endif
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
                                    @elseif(str_contains(strtolower($activity->action), 'login'))
                                        <span class="action-badge action-login">Login</span>
                                    @elseif(str_contains(strtolower($activity->action), 'logout'))
                                        <span class="action-badge action-logout">Logout</span>
                                    @else
                                        <span class="action-badge action-other">{{ $activity->action }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $activity->description }}</td>
                            <td>{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                            <td><span class="ip-address">{{ $activity->ip_address }}</span></td>
                            <td class="user-agent-cell" title="{{ $activity->user_agent }}">
                                {{ $activity->short_user_agent }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="empty-state">
                                    <x-heroicon-o-clock class="empty-icon" />
                                    <h3>No activities found</h3>
                                    <p>Try adjusting your filters or clearing them to see more results.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($activities->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination">
                    {{-- Previous Page Link --}}
                    @if($activities->onFirstPage())
                        <span class="pagination-item disabled" aria-disabled="true">
                            <span class="page-link">&laquo; Previous</span>
                        </span>
                    @else
                        <a href="{{ $activities->previousPageUrl() }}" class="pagination-item" rel="prev">
                            <span class="page-link">&laquo; Previous</span>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach($activities->getUrlRange(1, $activities->lastPage()) as $page => $url)
                        @if($page == $activities->currentPage())
                            <span class="pagination-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </span>
                        @else
                            <a href="{{ $url }}" class="pagination-item">
                                <span class="page-link">{{ $page }}</span>
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if($activities->hasMorePages())
                        <a href="{{ $activities->nextPageUrl() }}" class="pagination-item" rel="next">
                            <span class="page-link">Next &raquo;</span>
                        </a>
                    @else
                        <span class="pagination-item disabled" aria-disabled="true">
                            <span class="page-link">Next &raquo;</span>
                        </span>
                    @endif
                </div>

                <div class="pagination-info">
                    Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} results
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Add these additional styles for the activity log */
.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #333;
}

.user-email {
    font-size: 0.8rem;
    color: #666;
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

.action-login {
    background: #e2d5f1;
    color: #553c9a;
}

.action-logout {
    background: #e2e8f0;
    color: #4a5568;
}

.action-other {
    background: #e2e8f0;
    color: #4a5568;
}

.ip-address {
    font-family: monospace;
    color: #666;
}

.user-agent-cell {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #666;
    font-size: 0.85rem;
}

/* Keep all your existing styles from the previous activities.blade.php */
.activities-container { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.page-title h1 { color: #333; margin-bottom: 0.5rem; font-size: 1.8rem; }
.page-title p { color: #666; font-size: 1rem; }

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.95rem;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
}

.btn-filter {
    padding: 0.5rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-filter:hover {
    background: #5a67d8;
}

.btn-clear {
    padding: 0.5rem 1.5rem;
    background: #e2e8f0;
    color: #4a5568;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s;
}

.btn-clear:hover {
    background: #cbd5e0;
}

/* Card */
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-body {
    padding: 1.5rem;
}

/* Table */
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
}

.empty-icon {
    width: 64px;
    height: 64px;
    color: #cbd5e0;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.empty-state p {
    color: #666;
    margin-bottom: 1.5rem;
}

/* Pagination Styles - Fixed and Improved */
.pagination-wrapper {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.pagination {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination-item {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #4a5568;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s ease;
    cursor: pointer;
}

.pagination-item:hover:not(.disabled):not(.active) {
    background: #f7fafc;
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.1);
}

.pagination-item.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
    cursor: default;
}

.pagination-item.disabled {
    background: #f7fafc;
    color: #cbd5e0;
    border-color: #e2e8f0;
    cursor: not-allowed;
    opacity: 0.7;
}

.pagination-item .page-link {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    line-height: 1;
}

.pagination-info {
    color: #718096;
    font-size: 0.9rem;
    text-align: center;
    background: #f7fafc;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    display: inline-block;
}

/* Mobile Responsive Pagination */
@media (max-width: 640px) {
    .pagination {
        gap: 0.25rem;
    }

    .pagination-item {
        min-width: 35px;
        height: 35px;
        padding: 0 0.5rem;
        font-size: 0.85rem;
    }

    .pagination-info {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
}

.text-center {
    text-align: center;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #eef2ff;
}

.icon-small {
    width: 16px;
    height: 16px;
}
</style>
@endpush
