@extends('layouts.app')

@section('title', 'Certificates')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Certificates</h1>
            <p>Manage certificate requests</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.certificates.create') }}" class="btn-primary">
                <i class="fas fa-plus icon-small"></i>
                New Certificate
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Pending</span>
            <span class="stat-value">{{ $counts['pending'] }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon approved">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Approved</span>
            <span class="stat-value">{{ $counts['approved'] }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon released">
            <i class="fas fa-check-double"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Released</span>
            <span class="stat-value">{{ $counts['released'] }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rejected">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Rejected</span>
            <span class="stat-value">{{ $counts['rejected'] }}</span>
        </div>
    </div>
</div>

    <!-- Search and Filter -->
    <div class="filters-section">
        <form action="{{ route('secretary.certificates.index') }}" method="GET" class="filters-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by certificate #, name, purpose..."
                       value="{{ request('search') }}"
                       class="search-input">
            </div>

            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Processing" {{ request('status') == 'Processing' ? 'selected' : '' }}>Processing</option>
                    <option value="Ready for Release" {{ request('status') == 'Ready for Release' ? 'selected' : '' }}>Ready for Release</option>
                    <option value="Released" {{ request('status') == 'Released' ? 'selected' : '' }}>Released</option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="filter-group">
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="Barangay Clearance" {{ request('type') == 'Barangay Clearance' ? 'selected' : '' }}>Barangay Clearance</option>
                    <option value="Certificate of Residency" {{ request('type') == 'Certificate of Residency' ? 'selected' : '' }}>Certificate of Residency</option>
                    <option value="Certificate of Indigency" {{ request('type') == 'Certificate of Indigency' ? 'selected' : '' }}>Certificate of Indigency</option>
                    <option value="Business Clearance" {{ request('type') == 'Business Clearance' ? 'selected' : '' }}>Business Clearance</option>
                </select>
            </div>

            <div class="filter-group">
                <input type="date" name="date_from" placeholder="From Date" value="{{ request('date_from') }}" class="filter-input">
            </div>

            <div class="filter-group">
                <input type="date" name="date_to" placeholder="To Date" value="{{ request('date_to') }}" class="filter-input">
            </div>

            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="{{ route('secretary.certificates.index') }}" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Certificates Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Certificate #</th>
                            <th>Resident Name</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                   <tbody>
    @forelse($certificates as $certificate)
    <tr>
        <td>
            <span class="certificate-number">{{ $certificate->certificate_id }}</span>
        </td>
        <td>
            <div class="resident-info">
                <span class="resident-name">{{ $certificate->resident->first_name }} {{ $certificate->resident->last_name }}</span>
            </div>
        </td>
        <td>
            <span class="cert-type">{{ $certificate->certificate_type }}</span>
        </td>
        <td>{{ Str::limit($certificate->purpose, 30) }}</td>
        <td>{{ $certificate->created_at ? $certificate->created_at->format('M d, Y') : 'N/A' }}</td>
        <td>
            <span class="status-badge status-{{ strtolower($certificate->status) }}">
                {{ $certificate->status }}
            </span>
        </td>
    <td>
    <div class="action-buttons">
        <a href="{{ route('secretary.certificates.show', $certificate) }}" class="btn-icon" title="View">
            <i class="fas fa-eye"></i>
        </a>
        <a href="{{ route('secretary.certificates.edit', $certificate) }}" class="btn-icon" title="Edit">
            <i class="fas fa-pencil-alt"></i>
        </a>
        @if($certificate->status === 'Released')
        <a href="{{ route('secretary.certificates.generate-doc', $certificate) }}" class="btn-icon" title="Download DOCX">
            <i class="fas fa-download"></i>
        </a>
        @endif
        <button type="button" class="btn-icon delete-btn" title="Delete"
            onclick="confirmDelete('{{ $certificate->id }}')">
            <i class="fas fa-trash"></i>
        </button>
        <form id="delete-form-{{ $certificate->id }}"
              action="{{ route('secretary.certificates.destroy', $certificate) }}"
              method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</td>
    </tr>
    @empty
    <tr>
        <td colspan="7" class="text-center">
            <div class="empty-state">
                <i class="fas fa-file-alt empty-icon"></i>
                <h3>No certificates found</h3>
                <p>Get started by creating your first certificate request.</p>
                <a href="{{ route('secretary.certificates.create') }}" class="btn-primary">
                    <i class="fas fa-plus icon-small"></i>
                    New Certificate
                </a>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
                </table>
            </div>

            @if($certificates->hasPages())
            <div class="pagination-wrapper">
                {{ $certificates->links() }}
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

.stat-icon i {
    width: 24px;
    height: 24px;
    color: white;
    font-size: 24px;
}

.stat-icon.pending { background: #f59e0b; }
.stat-icon.approved { background: #10b981; }
.stat-icon.released { background: #10b981; }
.stat-icon.rejected { background: #dc2626; }

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
    font-size: 20px;
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

/* Certificate Number */
.certificate-number {
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

/* Certificate Type */
.cert-type {
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

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-ready-for-release {
    background: #e2d5f1;
    color: #553c9a;
}

.status-released {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #fee2e2;
    color: #dc2626;
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
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this certificate? This action cannot be undone.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
