@extends('layouts.app')

@section('title', 'Residents List')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Residents List</h1>
            <p>View all barangay residents</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('captain.dashboard') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="filters-section">
        <form action="{{ route('captain.residents.index') }}" method="GET" class="filters-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by name, ID, or address..."
                       value="{{ request('search') }}"
                       class="search-input">
            </div>

            <div class="filter-group">
                <select name="purok" class="filter-select">
                    <option value="">All Purok</option>
                    @foreach($puroks as $purok)
                        <option value="{{ $purok }}" {{ request('purok') == $purok ? 'selected' : '' }}>
                            Purok {{ $purok }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-filter">Filter</button>
            <a href="{{ route('captain.residents.index') }}" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Residents Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Resident ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Birthdate</th>
                            <th>Age</th>
                            <th>Purok</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($residents as $resident)
                        <tr>
                            <td><span class="resident-id"><?php echo $resident->resident_id; ?></span></td>
                            <td>
                                <div class="resident-name">
                                    <?php echo $resident->first_name . ' ' . $resident->last_name; ?>
                                    <?php if($resident->middle_name): ?>
                                        <span class="middle-name"><?php echo substr($resident->middle_name, 0, 1); ?>.</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo $resident->gender; ?></td>
                            <td>
                                <?php if($resident->birthdate): ?>
                                    <?php echo \Carbon\Carbon::parse($resident->birthdate)->format('M d, Y'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->age : 'N/A'; ?></td>
                            <td>Purok <?php echo $resident->purok; ?></td>
                            <td><?php echo $resident->contact_number ?? 'N/A'; ?></td>
                            <td>
                                <div class="status-badges">
                                    <?php if($resident->is_voter): ?>
                                        <span class="badge badge-voter" title="Registered Voter">V</span>
                                    <?php endif; ?>
                                    <?php if($resident->is_senior): ?>
                                        <span class="badge badge-senior" title="Senior Citizen">S</span>
                                    <?php endif; ?>
                                    <?php if($resident->is_pwd): ?>
                                        <span class="badge badge-pwd" title="PWD">P</span>
                                    <?php endif; ?>
                                    <?php if($resident->is_4ps): ?>
                                        <span class="badge badge-4ps" title="4Ps Member">4Ps</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo route('captain.residents.show', $resident); ?>" class="btn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-3x"></i>
                                    <h3>No residents found</h3>
                                    <p>Try adjusting your search or filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <?php if($residents->hasPages()): ?>
            <div class="pagination-wrapper">
                <?php echo $residents->links(); ?>
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

.filter-select {
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
    white-space: nowrap;
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

/* Resident Info */
.resident-id {
    font-family: monospace;
    font-weight: 600;
    color: #555;
}

.resident-name {
    font-weight: 600;
    color: #333;
}

.middle-name {
    color: #666;
    font-size: 0.9rem;
    margin-left: 0.25rem;
}

/* Status Badges */
.status-badges {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: help;
}

.badge-voter {
    background: #d4edda;
    color: #155724;
}

.badge-senior {
    background: #cce5ff;
    color: #004085;
}

.badge-pwd {
    background: #fff3cd;
    color: #856404;
}

.badge-4ps {
    background: #e2d5f1;
    color: #553c9a;
    width: auto;
    padding: 0 8px;
    border-radius: 15px;
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

.btn-view i {
    font-size: 1rem;
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
    margin-bottom: 1.5rem;
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
