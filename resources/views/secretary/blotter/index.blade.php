@extends('layouts.app')

@section('title', 'Blotter Cases')

@section('content')
<!-- Toast Notification -->
<div id="toast" class="toast">
    <div class="toast-content">
        <i class="fas fa-check-circle toast-icon success"></i>
        <span id="toastMessage">Operation completed successfully!</span>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="confirm-modal" style="display: none;">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header">
            <div class="confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 id="modalTitle">Confirm Archive</h3>
        </div>
        <div class="confirm-modal-body">
            <p id="confirmMessage">Are you sure you want to archive this blotter case? It will be moved to the archive.</p>
        </div>
        <div class="confirm-modal-footer">
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn-confirm" id="confirmActionBtn">Yes, Archive</button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Blotter Cases</h1>
            <p>Manage and track all blotter cases</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.blotter.create') }}" class="btn-primary">
                <i class="fas fa-plus icon-small"></i>
                New Blotter Case
            </a>
        </div>
    </div>

    <!-- Status Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Pending</span>
                <span class="stat-value">{{ $statusCounts['pending'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ongoing">
                <i class="fas fa-search"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Ongoing</span>
                <span class="stat-value">{{ $statusCounts['ongoing'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon settled">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Settled</span>
                <span class="stat-value">{{ $statusCounts['settled'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon referred">
                <i class="fas fa-arrow-right"></i>
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
                <i class="fas fa-search search-icon"></i>
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
            @php
                $complainants = $blotter->complainants;
            @endphp
            @if($complainants->count() > 0)
                <div class="resident-info">
                    <span class="resident-name">
                        {{ $complainants->first()->name }}
                        @if($complainants->count() > 1)
                            <span class="badge-more">+{{ $complainants->count() - 1 }} more</span>
                        @endif
                    </span>
                    @if($complainants->count() > 1)
                        <small class="text-muted">Total: {{ $complainants->count() }} complainant(s)</small>
                    @endif
                </div>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td>
            @php
                $respondents = $blotter->respondents;
            @endphp
            @if($respondents->count() > 0)
                <div class="resident-info">
                    <span class="resident-name">
                        {{ $respondents->first()->name }}
                        @if($respondents->count() > 1)
                            <span class="badge-more">+{{ $respondents->count() - 1 }} more</span>
                        @endif
                    </span>
                    @if($respondents->count() > 1)
                        <small class="text-muted">Total: {{ $respondents->count() }} respondent(s)</small>
                    @endif
                </div>
            @else
                {{ $blotter->respondent_name ?? 'N/A' }}
            @endif
        </td>
        <td>
            <span class="incident-type">{{ $blotter->incident_type }}</span>
        </td>
        <td>{{ $blotter->incident_date ? $blotter->incident_date->format('M d, Y') : 'N/A' }}</td>
        <td>{{ Str::limit($blotter->incident_location, 15) }}</td>
        <td>
            <span class="status-badge status-{{ strtolower($blotter->status) }}">
                {{ $blotter->status }}
            </span>
        </td>
        <td>
            <div class="action-buttons">
                <a href="{{ route('secretary.blotter.show', $blotter) }}" class="btn-icon" title="View">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('secretary.blotter.edit', $blotter) }}" class="btn-icon" title="Edit">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                @if(auth()->user()->role_id != 4)
                    <button type="button" class="btn-icon archive-btn" title="Archive" onclick="confirmArchive('{{ $blotter->id }}')">
                        <i class="fas fa-archive"></i>
                    </button>
                    <form id="archive-form-{{ $blotter->id }}" action="{{ route('secretary.blotter.archive', $blotter) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @endif
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="8" class="text-center">
            <div class="empty-state">
                <i class="fas fa-file-alt empty-icon"></i>
                <h3>No blotter cases found</h3>
                <p>Get started by filing your first blotter case.</p>
                <a href="{{ route('secretary.blotter.create') }}" class="btn-primary">
                    <i class="fas fa-plus icon-small"></i>
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
<div class="pagination-container">
    <div class="pagination-info">
        Showing <span>{{ $blotters->firstItem() }}</span> to <span>{{ $blotters->lastItem() }}</span> of <span>{{ $blotters->total() }}</span> results
    </div>

    <div class="pagination-links">
        {{-- Previous Page Link --}}
        @if($blotters->onFirstPage())
            <span class="pagination-link disabled"><i class="fas fa-chevron-left"></i> Previous</span>
        @else
            <a href="{{ $blotters->previousPageUrl() }}" class="pagination-link"><i class="fas fa-chevron-left"></i> Previous</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach($blotters->links()->elements as $element)
            {{-- "Three Dots" Separator --}}
            @if(is_string($element))
                <span class="pagination-link dots">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $blotters->currentPage())
                        <span class="pagination-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if($blotters->hasMorePages())
            <a href="{{ $blotters->nextPageUrl() }}" class="pagination-link">Next <i class="fas fa-chevron-right"></i></a>
        @else
            <span class="pagination-link disabled">Next <i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
</div>
@endif
        </div>
    </div>

    {{-- Archive Access Button - Hidden for Clerk --}}
    @if(auth()->user()->role_id != 4) {{-- Not a clerk --}}
    <div class="archive-access">
        <a href="{{ route('secretary.blotter.archived') }}" class="btn-archive">
            <i class="fas fa-archive"></i>
            View Archive ({{ \App\Models\Blotter::onlyTrashed()->count() }})
        </a>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Badge for multiple parties */
.badge-more {
    display: inline-block;
    background: #e2e8f0;
    color: #4a5568;
    font-size: 0.7rem;
    padding: 0.15rem 0.5rem;
    border-radius: 20px;
    margin-left: 0.5rem;
    font-weight: normal;
}

.text-muted {
    color: #6c757d;
    font-size: 0.7rem;
}
    /* Archive Access Button */
    .archive-access {
        margin-top: 2rem;
        text-align: right;
    }

    .btn-archive {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-archive:hover {
        background: #e2e8f0;
        color: #475569;
    }

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

    .stat-icon i {
        font-size: 24px;
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
        font-size: 20px;
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

    .btn-icon i {
        font-size: 18px;
    }

    /* Soft Red Archive Button */
    .archive-btn {
        color: #e53e3e; /* Soft red */
    }

    .archive-btn:hover {
        background: #fed7d7; /* Light red background */
        color: #c53030; /* Darker red on hover */
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
        font-size: 64px;
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
        font-size: 16px;
    }

    /* Confirm Modal Styles (matching residents) */
.confirm-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease forwards;
}

.confirm-modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    margin: 0 auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: modalPop 0.3s ease forwards;
    transform: scale(0.9);
    opacity: 0;
}

@keyframes modalPop {
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.confirm-modal-header {
    padding: 1.5rem 1.5rem 0.5rem 1.5rem;
    text-align: center;
}

.confirm-icon {
    font-size: 3rem;
    color: #f59e0b;
    margin-bottom: 0.5rem;
}

.confirm-modal-header h3 {
    color: #333;
    font-size: 1.3rem;
    margin: 0;
    font-weight: 600;
}

.confirm-modal-body {
    padding: 1rem 1.5rem;
    text-align: center;
}

.confirm-modal-body p {
    color: #666;
    font-size: 1rem;
    line-height: 1.5;
    margin: 0;
}

.confirm-modal-footer {
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    border-top: 1px solid #e2e8f0;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    background: #f3f4f6;
    color: #4b5563;
    border: none;
    border-radius: 5px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    flex: 1;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-confirm {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    flex: 1;
}

.btn-confirm:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

    /* Toast Notification */
    .toast {
        visibility: hidden;
        min-width: 300px;
        background-color: white;
        color: #333;
        text-align: center;
        border-radius: 8px;
        padding: 1rem;
        position: fixed;
        z-index: 1001;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-left: 4px solid #10b981;
        animation: slideUp 0.3s;
    }

    .toast.show {
        visibility: visible;
        animation: slideUp 0.3s, fadeOut 0.3s 2.7s;
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .toast-icon {
        font-size: 24px;
    }

    .toast-icon.success {
        color: #10b981;
    }

    .toast-icon.error {
        color: #e53e3e; /* Soft red for error */
    }

    @keyframes slideUp {
        from {
            transform: translate(-50%, 20px);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translate(-50%, 0);
        }
        to {
            opacity: 0;
            transform: translate(-50%, -10px);
        }
    }
    /* Confirm Modal Styles (matching residents) */
.confirm-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease forwards;
}

.confirm-modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    margin: 0 auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: modalPop 0.3s ease forwards;
    transform: scale(0.9);
    opacity: 0;
}

@keyframes modalPop {
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.confirm-modal-header {
    padding: 1.5rem 1.5rem 0.5rem 1.5rem;
    text-align: center;
}

.confirm-icon {
    font-size: 3rem;
    color: #f59e0b;
    margin-bottom: 0.5rem;
}

.confirm-modal-header h3 {
    color: #333;
    font-size: 1.3rem;
    margin: 0;
    font-weight: 600;
}

.confirm-modal-body {
    padding: 1rem 1.5rem;
    text-align: center;
}

.confirm-modal-body p {
    color: #666;
    font-size: 1rem;
    line-height: 1.5;
    margin: 0;
}

.confirm-modal-footer {
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    border-top: 1px solid #e2e8f0;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    background: #f3f4f6;
    color: #4b5563;
    border: none;
    border-radius: 5px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    flex: 1;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-confirm {
    padding: 0.75rem 1.5rem;
    background: #f59e0b; /* Orange for certificate */
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    flex: 1;
}

.btn-confirm:hover {
    background: #d97706;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Pagination styles (copy from residents) */
.pagination-container {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

@media (min-width: 640px) {
    .pagination-container {
        flex-direction: row;
        justify-content: space-between;
    }
}

.pagination-info {
    color: #64748b;
    font-size: 0.9rem;
}

.pagination-info span {
    font-weight: 600;
    color: #1e293b;
}

.pagination-links {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
}

.pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    min-width: 36px;
    height: 36px;
    padding: 0 0.75rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #4b5563;
    background: white;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination-link:hover:not(.disabled):not(.active) {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #1e293b;
}

.pagination-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.pagination-link.disabled {
    background: #f1f5f9;
    color: #94a3b8;
    border-color: #e2e8f0;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-link.dots {
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: default;
}
</style>
@endpush
<script>
console.log('=== BLOTTER DEBUG START ===');
console.log('1. Script loaded at:', new Date().toISOString());

// Check what's available immediately
(function() {
    console.log('2. Elements on page load:');
    console.log('   - confirmModal:', document.getElementById('confirmModal'));
    console.log('   - modalTitle:', document.getElementById('modalTitle'));
    console.log('   - confirmMessage:', document.getElementById('confirmMessage'));
    console.log('   - confirmActionBtn:', document.getElementById('confirmActionBtn'));
})();

// Override the function completely
window.confirmArchive = function(id) {
    console.log('✓ confirmArchive CALLED with ID:', id);
    console.log('   - Caller:', new Error().stack);

    // Show an alert first to confirm the function is called
    alert('DEBUG: Archive button clicked for ID: ' + id);

    // Try to show modal
    const modal = document.getElementById('confirmModal');
    console.log('   - Modal element found:', modal);

    if (modal) {
        console.log('   - Current modal display:', modal.style.display);
        modal.style.display = 'flex';
        modal.style.zIndex = '10000';
        console.log('   - Set modal display to flex');

        // Update text
        const titleEl = document.getElementById('modalTitle');
        const msgEl = document.getElementById('confirmMessage');

        if (titleEl) {
            titleEl.textContent = 'Archive Blotter Case';
            console.log('   - Updated title');
        } else {
            console.error('   - Title element not found!');
        }

        if (msgEl) {
            msgEl.textContent = 'Are you sure you want to archive this blotter case?';
            console.log('   - Updated message');
        } else {
            console.error('   - Message element not found!');
        }

        document.body.style.overflow = 'hidden';
    } else {
        console.error('   - MODAL NOT FOUND!');
        alert('Error: Modal not found! Check console.');
    }

    return false;
};

// Check if any other scripts might override
console.log('3. Other functions check:');
console.log('   - window.confirmArchive is now:', typeof window.confirmArchive);
console.log('=== BLOTTER DEBUG END ===');
</script>
@push('scripts')
<script>
// Toast notification function
function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');
    let toastMessage = document.getElementById('toastMessage');
    let toastIcon = document.querySelector('.toast-icon');

    if (!toast || !toastMessage) return;

    toastMessage.textContent = message;

    if (type === 'success') {
        toast.style.borderLeftColor = '#10b981';
        if (toastIcon) {
            toastIcon.classList.remove('error');
            toastIcon.classList.add('success');
        }
    } else {
        toast.style.borderLeftColor = '#e53e3e';
        if (toastIcon) {
            toastIcon.classList.remove('success');
            toastIcon.classList.add('error');
        }
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Confirm archive
function confirmArchive(id) {
    currentAction = 'archive';
    currentId = id;

    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const confirmMessage = document.getElementById('confirmMessage');

    if (modalTitle) modalTitle.textContent = 'Archive Blotter Case';
    if (confirmMessage) confirmMessage.textContent = 'Are you sure you want to archive this blotter case? It will be moved to the archive.';

    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

// Close modal
function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    currentAction = null;
    currentId = null;
}

// Execute action
function executeAction() {
    if (currentAction === 'archive' && currentId) {
        document.getElementById('archive-form-' + currentId).submit();
    }
    closeConfirmModal();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmActionBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', executeAction);
    }

    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeConfirmModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('confirmModal').style.display === 'flex') {
            closeConfirmModal();
        }
    });
});
</script>

{{-- Session messages --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
@endif
@endpush
