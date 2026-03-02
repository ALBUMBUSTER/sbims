@extends('layouts.app')

@section('title', 'Blotter Cases')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Blotter Cases</h1>
            <p>Manage and track all blotter cases</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.blotter.create') }}" class="btn-primary">
                <x-heroicon-o-plus class="icon-small" />
                New Blotter Case
            </a>
        </div>
    </div>

    <!-- Status Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending">
                <x-heroicon-o-clock />
            </div>
            <div class="stat-content">
                <span class="stat-label">Pending</span>
                <span class="stat-value">{{ $statusCounts['pending'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ongoing">
                <x-heroicon-o-magnifying-glass />
            </div>
            <div class="stat-content">
                <span class="stat-label">Ongoing</span>
                <span class="stat-value">{{ $statusCounts['ongoing'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon settled">
                <x-heroicon-o-check-badge />
            </div>
            <div class="stat-content">
                <span class="stat-label">Settled</span>
                <span class="stat-value">{{ $statusCounts['settled'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon referred">
                <x-heroicon-o-arrow-right-circle />
            </div>
            <div class="stat-content">
                <span class="stat-label">Referred</span>
                <span class="stat-value">{{ $statusCounts['referred'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="filters-section">
        <form action="{{ route('secretary.blotter.index') }}" method="GET" class="filters-form">
            <div class="search-wrapper">
                <x-heroicon-o-magnifying-glass class="search-icon" />
                <input type="text"
                       name="search"
                       placeholder="Search by case ID, complainant, respondent, incident..."
                       value="{{ request('search') }}"
                       class="search-input">
            </div>

            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Ongoing" {{ request('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="Settled" {{ request('status') == 'Settled' ? 'selected' : '' }}>Settled</option>
                    <option value="Referred" {{ request('status') == 'Referred' ? 'selected' : '' }}>Referred</option>
                </select>
            </div>

            <div class="filter-group">
                <input type="date" name="date_from" placeholder="From Date" value="{{ request('date_from') }}" class="filter-input">
            </div>

            <div class="filter-group">
                <input type="date" name="date_to" placeholder="To Date" value="{{ request('date_to') }}" class="filter-input">
            </div>

            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="{{ route('secretary.blotter.index') }}" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Blotter Cases Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Incident Type</th>
                            <th>Incident Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blotters as $blotter)
                        <tr>
                            <td>
                                <span class="case-id">{{ $blotter->case_id }}</span>
                            </td>
                            <td>
                                <div class="resident-info">
                                    <span class="resident-name">{{ $blotter->complainant->first_name ?? '' }} {{ $blotter->complainant->last_name ?? '' }}</span>
                                </div>
                            </td>
                            <td>{{ $blotter->respondent_name }}</td>
                            <td>
                                <span class="incident-type">{{ $blotter->incident_type }}</span>
                            </td>
                            <td>{{ $blotter->incident_date ? $blotter->incident_date->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $blotter->incident_location }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($blotter->status) }}">
                                    {{ $blotter->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('secretary.blotter.show', $blotter) }}" class="btn-icon" title="View">
                                        <x-heroicon-o-eye />
                                    </a>
                                    <a href="{{ route('secretary.blotter.edit', $blotter) }}" class="btn-icon" title="Edit">
                                        <x-heroicon-o-pencil />
                                    </a>
                                    <button type="button" class="btn-icon delete-btn" title="Delete"
                                        onclick="confirmDelete('{{ $blotter->id }}')">
                                        <x-heroicon-o-trash />
                                    </button>
                                    <form id="delete-form-{{ $blotter->id }}"
                                          action="{{ route('secretary.blotter.destroy', $blotter) }}"
                                          method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <x-heroicon-o-document-text class="empty-icon" />
                                    <h3>No blotter cases found</h3>
                                    <p>Get started by filing your first blotter case.</p>
                                    <a href="{{ route('secretary.blotter.create') }}" class="btn-primary">
                                        <x-heroicon-o-plus class="icon-small" />
                                        New Blotter Case
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($blotters->hasPages())
            <div class="pagination-wrapper">
                {{ $blotters->links() }}
            </div>
            @endif
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
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
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

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
    color: white;
}

.stat-icon.pending { background: #f59e0b; }
.stat-icon.ongoing { background: #3b82f6; }
.stat-icon.settled { background: #10b981; }
.stat-icon.referred { background: #8b5cf6; }

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 1.8rem;
    font-weight: bold;
}

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

.search-wrapper {
    flex: 2;
    min-width: 250px;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #999;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.95rem;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-select, .filter-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.95rem;
}

.btn-filter {
    padding: 0.75rem 1.5rem;
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
    padding: 0.75rem 1.5rem;
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

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    vertical-align: middle;
}

.data-table tr:hover td {
    background: #f8fafc;
}

/* Case ID */
.case-id {
    font-family: monospace;
    font-weight: 600;
    color: #667eea;
}

/* Resident Info */
.resident-info {
    display: flex;
    flex-direction: column;
}

.resident-name {
    font-weight: 500;
    color: #333;
}

/* Incident Type */
.incident-type {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #eef2ff;
    color: #667eea;
    border-radius: 20px;
    font-size: 0.85rem;
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

.status-ongoing {
    background: #cce5ff;
    color: #004085;
}

.status-settled {
    background: #d4edda;
    color: #155724;
}

.status-referred {
    background: #e2d5f1;
    color: #553c9a;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
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

.btn-icon:hover {
    background: #eef2ff;
    transform: translateY(-2px);
}

.btn-icon svg {
    width: 18px;
    height: 18px;
}

.delete-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Buttons */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: opacity 0.3s;
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    opacity: 0.9;
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

/* Pagination */
.pagination-wrapper {
    margin-top: 1.5rem;
}

.text-center {
    text-align: center;
}

.icon-small {
    width: 16px;
    height: 16px;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this blotter case? This action cannot be undone.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
