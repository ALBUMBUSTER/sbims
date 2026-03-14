@extends('layouts.app')

@section('title', 'Certificates')

@section('content')
<!-- Toast Notification -->
<div id="toast" class="toast">
    <div class="toast-content">
        <i class="fas fa-check-circle toast-icon success"></i>
        <span id="toastMessage">Operation completed successfully!</span>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 id="modalTitle">Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p id="confirmMessage">Are you sure?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn-confirm" id="confirmActionBtn">Confirm</button>
        </div>
    </div>
</div>

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

                                    {{-- Archive Button - Hidden for Clerk --}}
                                    @if(auth()->user()->role_id != 4) {{-- Not a clerk --}}
                                    <button type="button" class="btn-icon archive-btn-certificate" title="Archive"
                                        onclick="confirmArchive('{{ $certificate->id }}')">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                    <form id="archive-form-{{ $certificate->id }}"
                                          action="{{ route('secretary.certificates.archive', $certificate) }}"
                                          method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                    @endif
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

    {{-- Archive Access Button - Hidden for Clerk --}}
    @if(auth()->user()->role_id != 4) {{-- Not a clerk --}}
    <div class="archive-access">
        <a href="{{ route('secretary.certificates.archived') }}" class="btn-archive">
            <i class="fas fa-archive"></i>
            View Archive ({{ \App\Models\Certificate::onlyTrashed()->count() }})
        </a>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
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

    /* Orange Archive Button for Certificate */
    .archive-btn-certificate {
        color: #f59e0b; /* Orange */
    }

    .archive-btn-certificate:hover {
        background: #fff3cd; /* Light orange background */
        color: #d97706; /* Darker orange on hover */
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

    /* Modal Styles */
    .modal {
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
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 1.5rem 1.5rem 0.5rem;
        text-align: center;
    }

    .modal-icon {
        font-size: 3rem;
        color: #f59e0b;
        margin-bottom: 0.5rem;
    }

    .modal-header h3 {
        color: #333;
        font-size: 1.3rem;
        margin: 0;
        font-weight: 600;
    }

    .modal-body {
        padding: 1rem 1.5rem;
        text-align: center;
    }

    .modal-body p {
        color: #666;
        font-size: 1rem;
        line-height: 1.5;
        margin: 0;
    }

    .modal-footer {
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
        background: #f59e0b; /* Orange for certificate confirm button */
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
    }

    .btn-confirm.archive {
        background: #f59e0b; /* Orange for certificate archive */
    }

    .btn-confirm.archive:hover {
        background: #d97706;
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
        color: #f59e0b; /* Orange for error */
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
</style>
@endpush

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
        toast.style.borderLeftColor = '#f59e0b';
        if (toastIcon) {
            toastIcon.classList.remove('success');
            toastIcon.classList.add('error');
        }
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Modal variables
let currentAction = null;
let currentId = null;

// Confirm archive
function confirmArchive(id) {
    currentAction = 'archive';
    currentId = id;
    document.getElementById('modalTitle').textContent = 'Archive Certificate';
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to archive this certificate? It will be moved to the archive.';

    // Set button color for archive
    const confirmBtn = document.getElementById('confirmActionBtn');
    confirmBtn.classList.add('archive');
    confirmBtn.textContent = 'Yes, Archive';

    document.getElementById('confirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Close modal
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    document.body.style.overflow = '';

    // Reset button
    const confirmBtn = document.getElementById('confirmActionBtn');
    confirmBtn.classList.remove('archive');
    confirmBtn.textContent = 'Confirm';

    currentAction = null;
    currentId = null;
}

// Execute action
function executeAction() {
    if (currentAction === 'archive') {
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
