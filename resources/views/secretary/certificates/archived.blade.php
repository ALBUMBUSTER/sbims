@extends('layouts.app')

@section('title', 'Archived Certificates')

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-archive" style="color: #667eea;"></i> Archived Certificates</h1>
                <p>Restore or permanently delete archived certificates</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('secretary.certificates.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Certificates
                </a>
            </div>
        </div>

        <!-- Search -->
        <div class="filter-container">
            <form action="{{ route('secretary.certificates.archived') }}" method="GET" class="filter-form">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           name="search"
                           placeholder="Search archived certificates..."
                           value="{{ request('search') }}"
                           class="search-input">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('secretary.certificates.archived') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
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
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmActionBtn">Confirm</button>
                </div>
            </div>
        </div>

        <!-- Archived Certificates Table -->
        <div class="data-table">
            <div class="table-header">
                <h3><i class="fas fa-file-alt" style="color: #667eea;"></i> Archived Certificates</h3>
                <div class="table-info">
                    <span class="badge">Total: {{ $archived->total() }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Certificate #</th>
                            <th>Resident Name</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archived as $certificate)
                        <tr>
                            <td>
                                <span class="certificate-number">{{ $certificate->certificate_id }}</span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <span class="user-avatar">{{ substr($certificate->resident->first_name ?? 'R', 0, 1) }}{{ substr($certificate->resident->last_name ?? 'N', 0, 1) }}</span>
                                    <span>{{ $certificate->resident->first_name ?? '' }} {{ $certificate->resident->last_name ?? '' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="cert-type">{{ $certificate->certificate_type }}</span>
                            </td>
                            <td>
                                <span class="purpose-text" title="{{ $certificate->purpose }}">
                                    {{ Str::limit($certificate->purpose, 30) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'Pending' => ['bg' => '#fff3cd', 'text' => '#856404'],
                                        'Processing' => ['bg' => '#cce5ff', 'text' => '#004085'],
                                        'Approved' => ['bg' => '#d4edda', 'text' => '#155724'],
                                        'Released' => ['bg' => '#d1fae5', 'text' => '#065f46'],
                                        'Rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                    ];
                                    $color = $statusColors[$certificate->status] ?? ['bg' => '#e9ecef', 'text' => '#4a5568'];
                                @endphp
                                <span class="status-badge" style="background: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                    {{ $certificate->status }}
                                </span>
                            </td>
                            <td>
                                <div class="date-info">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>{{ $certificate->deleted_at->format('M d, Y') }}</span>
                                </div>
                                <div class="time-info">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ $certificate->deleted_at->format('h:i A') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-icon restore-btn" title="Restore"
                                        onclick="confirmRestore('{{ $certificate->id }}')">
                                        <i class="fas fa-undo-alt"></i>
                                    </button>
                                    <form id="restore-form-{{ $certificate->id }}" action="{{ route('secretary.certificates.restore', $certificate->id) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>

                                    {{-- Delete button visible for Admin, Secretary, and Captain (role_id 1, 2, 3) --}}
                                    @if(in_array(auth()->user()->role_id, [1, 2, 3]))
                                    <button type="button" class="btn-icon delete-btn" title="Delete Permanently"
                                        onclick="confirmForceDelete('{{ $certificate->id }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="force-delete-form-{{ $certificate->id }}" action="{{ route('secretary.certificates.force-delete', $certificate->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-archive"></i>
                                    <h3>No archived certificates found</h3>
                                    <p>Archived certificates will appear here.</p>
                                    @if(request('search'))
                                        <a href="{{ route('secretary.certificates.archived') }}" class="btn btn-outline" style="margin-top: 1rem;">
                                            <i class="fas fa-times"></i> Clear Search
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($archived->hasPages())
            <div class="pagination-wrapper">
                {{ $archived->links() }}
            </div>
            @endif
        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
.main-container {
    display: flex;
    min-height: calc(100vh - 70px);
    background: #f8fafc;
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
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.page-title h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.page-title p {
    color: #666;
    font-size: 0.95rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-outline {
    background: white;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline:hover {
    background: #667eea;
    color: white;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.filter-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.search-wrapper {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    color: #999;
    z-index: 1;
}

.search-input {
    flex: 1;
    padding: 0.8rem 1rem 0.8rem 2.8rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.data-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    overflow: hidden;
}

.table-header {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-info .badge {
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    text-align: left;
    padding: 1rem 1.5rem;
    background: #f8fafc;
    color: #4a5568;
    font-weight: 600;
    font-size: 0.9rem;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
}

.certificate-number {
    font-family: monospace;
    font-weight: 600;
    color: #667eea;
    background: #eef2ff;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    font-size: 0.9rem;
}

.cert-type {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    background: #e9ecef;
    border-radius: 20px;
    font-size: 0.85rem;
    color: #4a5568;
}

.purpose-text {
    color: #4a5568;
    font-size: 0.9rem;
    cursor: help;
}

.status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.date-info, .time-info {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.85rem;
    color: #666;
}

.date-info i, .time-info i {
    width: 14px;
    color: #667eea;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 6px;
    background: #f8fafc;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-icon:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

.restore-btn { color: #10b981; }
.restore-btn:hover { background: #d1fae5; color: #065f46; }

.delete-btn { color: #dc2626; }
.delete-btn:hover { background: #fee2e2; color: #b91c1c; }

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
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
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.empty-state i {
    font-size: 3rem;
    color: #e2e8f0;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #999;
}

/* Pagination */
.pagination-wrapper {
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: center;
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

    .search-wrapper {
        flex-direction: column;
        align-items: stretch;
    }

    .search-icon {
        left: 1rem;
        top: 1.2rem;
    }

    .search-input {
        padding-left: 2.5rem;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-icon {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>

function confirmRestore(id) {
    currentAction = 'restore';
    currentFormId = id;
    document.getElementById('modalTitle').textContent = 'Restore Certificate';
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to restore this archived certificate?';
    document.getElementById('confirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function confirmForceDelete(id) {
    currentAction = 'forceDelete';
    currentFormId = id;
    document.getElementById('modalTitle').textContent = 'Permanently Delete Certificate';
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to permanently delete this certificate? This action cannot be undone.';
    document.getElementById('confirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    document.body.style.overflow = '';
    currentAction = null;
    currentFormId = null;
}

function executeAction() {
    if (currentAction === 'restore') {
        document.getElementById('restore-form-' + currentFormId).submit();
    } else if (currentAction === 'forceDelete') {
        document.getElementById('force-delete-form-' + currentFormId).submit();
    }
    closeConfirmModal();
}

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
@endpush
