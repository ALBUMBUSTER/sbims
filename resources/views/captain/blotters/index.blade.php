@extends('layouts.app')

@section('title', 'Blotter Cases')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Blotter Cases</h1>
            <p>View and manage blotter cases</p>
        </div>
        <div class="page-actions">
            <a href="<?php echo route('captain.dashboard'); ?>" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
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
                <span class="stat-value"><?php echo $statusCounts['pending'] ?? 0; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon investigating">
                <i class="fas fa-search"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Investigating</span>
                <span class="stat-value"><?php echo $statusCounts['investigating'] ?? 0; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon hearings">
                <i class="fas fa-gavel"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Hearings</span>
                <span class="stat-value"><?php echo $statusCounts['hearings'] ?? 0; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon settled">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Settled</span>
                <span class="stat-value"><?php echo $statusCounts['settled'] ?? 0; ?></span>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="filters-section">
        <form action="<?php echo route('captain.blotters.index'); ?>" method="GET" class="filters-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by blotter #, complainant, respondent, incident..."
                       value="<?php echo $_GET['search'] ?? ''; ?>"
                       class="search-input">
            </div>

            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Investigating" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Investigating') ? 'selected' : ''; ?>>Investigating</option>
                    <option value="Hearings" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Hearings') ? 'selected' : ''; ?>>Hearings</option>
                    <option value="Settled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Settled') ? 'selected' : ''; ?>>Settled</option>
                    <option value="Unsolved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Unsolved') ? 'selected' : ''; ?>>Unsolved</option>
                    <option value="Dismissed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Dismissed') ? 'selected' : ''; ?>>Dismissed</option>
                </select>
            </div>

            <div class="filter-group">
                <input type="date" name="date_from" placeholder="From Date" value="<?php echo $_GET['date_from'] ?? ''; ?>" class="filter-input">
            </div>

            <div class="filter-group">
                <input type="date" name="date_to" placeholder="To Date" value="<?php echo $_GET['date_to'] ?? ''; ?>" class="filter-input">
            </div>

            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="<?php echo route('captain.blotters.index'); ?>" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Blotters Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Blotter #</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Incident Type</th>
                            <th>Incident Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($blotters->count() > 0): ?>
                            <?php foreach($blotters as $blotter): ?>
                            <tr>
                                <td><span class="blotter-number"><?php echo $blotter->blotter_number; ?></span></td>
                                <td>
                                    <div class="resident-info">
                                        <span class="resident-name"><?php echo $blotter->complainant_name ?? 'N/A'; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $blotter->respondent_name; ?></td>
                                <td>
                                    <span class="incident-type"><?php echo $blotter->incident_type; ?></span>
                                </td>
                                <td><?php echo \Carbon\Carbon::parse($blotter->incident_date)->format('M d, Y'); ?></td>
                                <td><?php echo $blotter->incident_location; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($blotter->status); ?>">
                                        <?php echo $blotter->status; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo route('captain.blotters.show', $blotter); ?>" class="btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-gavel fa-3x"></i>
                                        <h3>No blotter cases found</h3>
                                        <p>Try adjusting your search or filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($blotters->hasPages()): ?>
            <div class="pagination-wrapper">
                <?php echo $blotters->links(); ?>
            </div>
            <?php endif; ?>
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

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
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
.stat-icon.investigating { background: #3b82f6; }
.stat-icon.hearings { background: #8b5cf6; }
.stat-icon.settled { background: #10b981; }

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

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    vertical-align: middle;
}

.table tr:hover td {
    background: #f8fafc;
}

/* Blotter Number */
.blotter-number {
    font-family: monospace;
    font-weight: 600;
    color: #667eea;
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

.status-unsolved {
    background: #fee2e2;
    color: #dc2626;
}

.status-dismissed {
    background: #e2e8f0;
    color: #4a5568;
}

/* View Button */
.btn-view {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #667eea;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s;
}

.btn-view:hover {
    background: #eef2ff;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
}

.empty-state i {
    color: #cbd5e0;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #666;
}

/* Pagination */
.pagination-wrapper {
    margin-top: 1.5rem;
}

.text-center {
    text-align: center;
}
</style>
@endpush
