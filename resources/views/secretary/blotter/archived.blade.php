@extends('layouts.app')

@section('title', 'Archived Blotter Cases')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Archived Blotter Cases</h1>
            <p>Restore or permanently delete archived cases</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.blotter.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left icon-small"></i>
                Back to Blotters
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="filters-section">
        <form action="{{ route('secretary.blotter.archived') }}" method="GET" class="filters-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       name="search"
                       placeholder="Search archived cases..."
                       value="{{ request('search') }}"
                       class="search-input">
            </div>
            <button type="submit" class="btn-filter">Search</button>
            <a href="{{ route('secretary.blotter.archived') }}" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirmModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-content">
            <div class="confirm-modal-header">
                <div class="confirm-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Confirm Action</h3>
            </div>
            <div class="confirm-modal-body">
                <p id="confirmMessage">Are you sure?</p>
            </div>
            <div class="confirm-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn-confirm" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Archived Cases Table -->
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
                            <th>Status</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archived as $case)
                        <tr>
                            <td>
                                <span class="case-id">{{ $case->case_id }}</span>
                            </td>
                            <td>
                                <div class="resident-info">
                                    <span class="resident-name">{{ $case->complainant->first_name ?? '' }} {{ $case->complainant->last_name ?? '' }}</span>
                                </div>
                            </td>
                            <td>{{ $case->respondent_name }}</td>
                            <td>
                                <span class="incident-type">{{ $case->incident_type }}</span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ strtolower($case->status) }}">
                                    {{ $case->status }}
                                </span>
                            </td>
                            <td>{{ $case->deleted_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-icon restore-btn" title="Restore"
                                        onclick="confirmRestore('{{ $case->id }}')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <form id="restore-form-{{ $case->id }}" action="{{ route('secretary.blotter.restore', $case->id) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>

                                    @if(auth()->user()->role_id <= 2) {{-- Admin or Secretary --}}
                                    <button type="button" class="btn-icon delete-btn" title="Delete Permanently"
                                        onclick="confirmForceDelete('{{ $case->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="force-delete-form-{{ $case->id }}" action="{{ route('secretary.blotter.force-delete', $case->id) }}" method="POST" style="display: none;">
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
                                    <i class="fas fa-archive empty-icon"></i>
                                    <h3>No archived cases found</h3>
                                    <p>Archived blotter cases will appear here.</p>
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
    </div>
</div>
@endsection

@push('styles')
<style>
/* Add these styles to your existing styles */
.restore-btn {
    color: #10b981;
}
.restore-btn:hover {
    background: #d1fae5;
    color: #065f46;
}

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
</style>
@endpush

@push('scripts')
<script>
// Confirmation modal variables
let currentAction = null;
let currentFormId = null;

function confirmRestore(id) {
    currentAction = 'restore';
    currentFormId = id;
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to restore this archived case?';
    document.getElementById('confirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function confirmForceDelete(id) {
    currentAction = 'forceDelete';
    currentFormId = id;
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to permanently delete this case? This action cannot be undone.';
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
