@extends('layouts.app')

@section('title', 'Archived Residents')

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

<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-archive" style="color: #8b5cf6;"></i> Archived Residents</h1>
                <p>View and manage archived resident records</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('secretary.residents.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Active Residents
                </a>
            </div>
        </div>

        <!-- Search -->
        <div class="search-section">
            <form action="{{ route('secretary.residents.archived') }}" method="GET" class="search-form">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="Search archived residents..." value="{{ request('search') }}" class="search-input">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                @if(request('search'))
                    <a href="{{ route('secretary.residents.archived') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Archived Residents Table -->
        <div class="data-table">
            <div class="table-header">
                <h3><i class="fas fa-archive" style="color: #8b5cf6;"></i> Archived Residents</h3>
                <div class="table-info">
                    <span class="badge">Total: {{ $archivedResidents->total() }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Resident ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Birth Date</th>
                            <th>Purok</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedResidents as $resident)
                        <tr>
                            <td><span class="resident-id">{{ $resident->resident_id }}</span></td>
                            <td>
                                <div class="resident-name">
                                    {{ $resident->first_name }} {{ $resident->last_name }}
                                    @if($resident->middle_name)
                                        <span class="middle-name">{{ $resident->middle_name[0] }}.</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $resident->gender }}</td>
                            <td>{{ $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->format('M d, Y') : '' }}</td>
                            <td><span class="purok-badge">Purok {{ $resident->purok }}</span></td>
                            <td>
                            <span class="archived-date">
                            {{ $resident->deleted_at ? \Carbon\Carbon::parse($resident->deleted_at)->format('M d, Y h:i A') : ($resident->archived_at ? \Carbon\Carbon::parse($resident->archived_at)->format('M d, Y h:i A') : 'N/A') }}
                            </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-icon restore-btn" title="Restore" onclick="confirmRestore('{{ $resident->id }}')">
                                        <i class="fas fa-trash-restore"></i>
                                    </button>
                                    <form id="restore-form-{{ $resident->id }}" action="{{ route('secretary.residents.restore', $resident->id) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>

                                    <button type="button" class="btn-icon force-delete-btn" title="Delete Permanently" onclick="confirmForceDelete('{{ $resident->id }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="force-delete-form-{{ $resident->id }}" action="{{ route('secretary.residents.force-delete', $resident->id) }}" method="POST" style="display: none;">
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
                                    <i class="fas fa-archive"></i>
                                    <h3>No archived residents</h3>
                                    <p>The archive is empty. Archived residents will appear here.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($archivedResidents->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $archivedResidents->firstItem() }} to {{ $archivedResidents->lastItem() }} of {{ $archivedResidents->total() }} results
                </div>
                {{ $archivedResidents->links() }}
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

/* Page Header */
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

/* Buttons */
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
    border: 2px solid #8b5cf6;
    color: #8b5cf6;
}

.btn-outline:hover {
    background: #8b5cf6;
    color: white;
}

.btn-primary {
    background: #8b5cf6;
    color: white;
}

.btn-primary:hover {
    background: #7c3aed;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

/* Search Section */
.search-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.search-form {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.search-wrapper {
    flex: 1;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 1rem;
}

.search-input {
    width: 100%;
    padding: 0.8rem 1rem 0.8rem 2.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Data Table */
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

/* Resident Info */
.resident-id {
    font-family: monospace;
    font-weight: 600;
    color: #8b5cf6;
    background: #f3e8ff;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    font-size: 0.9rem;
}

.resident-name {
    font-weight: 500;
    color: #333;
}

.middle-name {
    color: #666;
    font-size: 0.85rem;
    margin-left: 0.25rem;
}

.purok-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    background: #f3e8ff;
    color: #8b5cf6;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.archived-date {
    color: #666;
    font-size: 0.9rem;
}

/* Action Buttons */
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

.restore-btn {
    color: #10b981;
}
.restore-btn:hover {
    background: #d1fae5;
    color: #059669;
}

.force-delete-btn {
    color: #dc2626;
}
.force-delete-btn:hover {
    background: #fee2e2;
    color: #b91c1c;
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
    justify-content: space-between;
    align-items: center;
}

.pagination-info {
    color: #666;
    font-size: 0.9rem;
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

.btn-confirm.restore {
    background: #10b981; /* Green for restore */
}
.btn-confirm {
    padding: 0.75rem 1.5rem;
    background: #dc2626;
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
    background: #b91c1c;
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
.toast-content { display: flex; align-items: center; gap: 0.75rem; }
.toast-icon { font-size: 24px; flex-shrink: 0; }
.toast-icon.success { color: #10b981; }
.toast-icon.error { color: #dc2626; }

@keyframes slideUp {
    from { transform: translate(-50%, 20px); opacity: 0; }
    to { transform: translate(-50%, 0); opacity: 1; }
}
@keyframes fadeOut {
    from { opacity: 1; transform: translate(-50%, 0); }
    to { opacity: 0; transform: translate(-50%, -10px); }
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

    .search-form {
        flex-direction: column;
        align-items: stretch;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-icon {
        width: 100%;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
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
        toast.style.borderLeftColor = '#dc2626';
        if (toastIcon) {
            toastIcon.classList.remove('success');
            toastIcon.classList.add('error');
        }
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
// Confirm restore
function confirmRestore(id) {
    currentAction = 'restore';
    currentId = id;
    document.getElementById('modalTitle').textContent = 'Restore Resident';
    document.getElementById('confirmMessage').textContent = 'Restore this resident? They will be moved back to active residents.';
    document.getElementById('confirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Confirm force delete
function confirmForceDelete(id) {
    currentAction = 'forceDelete';
    currentId = id;
    document.getElementById('modalTitle').textContent = 'Permanently Delete Resident';
    document.getElementById('confirmMessage').textContent = 'WARNING: This will permanently delete this resident. This action cannot be undone!';
    document.getElementById('confirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Close modal
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    document.body.style.overflow = '';
    currentAction = null;
    currentId = null;
}

// Execute action
function executeAction() {
    if (currentAction === 'restore') {
        document.getElementById('restore-form-' + currentId).submit();
    } else if (currentAction === 'forceDelete') {
        document.getElementById('force-delete-form-' + currentId).submit();
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
