@extends('layouts.app')

@section('title', 'Archived Blotter Cases')

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-archive" style="color: #667eea;"></i> Archived Blotter Cases</h1>
                <p>Restore or permanently delete archived cases</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('secretary.blotter.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Blotters
                </a>
            </div>
        </div>

        <!-- Search -->
        <div class="filter-container">
            <form action="{{ route('secretary.blotter.archived') }}" method="GET" class="filter-form">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           name="search"
                           placeholder="Search archived cases..."
                           value="{{ request('search') }}"
                           class="search-input">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('secretary.blotter.archived') }}" class="btn btn-secondary">Clear</a>
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

        <!-- Archived Cases Table -->
        <div class="data-table">
            <div class="table-header">
                <h3><i class="fas fa-archive" style="color: #667eea;"></i> Archived Cases</h3>
                <div class="table-info">
                    <span class="badge">Total: {{ $archived->total() }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
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
            @php
                // Get complainants data from various possible sources
                $complainantsList = [];

                // Check if there's a direct relationship loaded
                if(isset($case->complainants) && $case->complainants instanceof \Illuminate\Database\Eloquent\Collection && $case->complainants->count() > 0) {
                    foreach($case->complainants as $complainant) {
                        $complainantsList[] = $complainant->full_name ?? $complainant->name ?? 'Unknown';
                    }
                }
                // Check for single complainant relationship
                elseif(isset($case->complainant) && $case->complainant) {
                    $complainantsList[] = $case->complainant->full_name ?? $case->complainant->name ?? 'N/A';
                }
                // Check for complainant_name field
                elseif(isset($case->complainant_name) && $case->complainant_name) {
                    $complainantsList[] = $case->complainant_name;
                }
                // Check for complainants JSON field
                elseif(isset($case->complainants_list) && $case->complainants_list) {
                    $complainantList = is_array($case->complainants_list) ? $case->complainants_list : json_decode($case->complainants_list, true);
                    if($complainantList && count($complainantList) > 0) {
                        foreach($complainantList as $comp) {
                            $complainantsList[] = is_array($comp) ? ($comp['name'] ?? $comp['full_name'] ?? 'Unknown') : $comp;
                        }
                    }
                }

                $firstComplainant = count($complainantsList) > 0 ? $complainantsList[0] : 'N/A';
                $complainantInitials = $firstComplainant !== 'N/A' ? substr($firstComplainant, 0, 1) : '?';
            @endphp
            <div class="user-info">
                <span class="user-avatar">{{ $complainantInitials }}</span>
                <span>{{ $firstComplainant }}</span>
            </div>
            @if(count($complainantsList) > 1)
                <div class="party-list">
                    <small class="text-muted">+{{ count($complainantsList) - 1 }} more</small>
                    <div class="hidden-details" style="display: none;">
                        @foreach($complainantsList as $comp)
                            <div><i class="fas fa-user"></i> {{ $comp }}</div>
                        @endforeach
                    </div>
                    <button class="toggle-details-btn" onclick="toggleDetails(this)" style="background: none; border: none; color: #667eea; font-size: 0.7rem; cursor: pointer; padding: 0;">
                        <i class="fas fa-chevron-down"></i> View all
                    </button>
                </div>
            @endif
        </td>
        <td>
            @php
                // Get respondents data from various possible sources
                $respondentsList = [];

                // Check if there's a direct relationship loaded
                if(isset($case->respondents) && $case->respondents instanceof \Illuminate\Database\Eloquent\Collection && $case->respondents->count() > 0) {
                    foreach($case->respondents as $respondent) {
                        $respondentsList[] = $respondent->full_name ?? $respondent->name ?? 'Unknown';
                    }
                }
                // Check for single respondent relationship
                elseif(isset($case->respondent) && $case->respondent) {
                    $respondentsList[] = $case->respondent->full_name ?? $case->respondent->name ?? 'N/A';
                }
                // Check for respondent_name field
                elseif(isset($case->respondent_name) && $case->respondent_name) {
                    $respondentsList[] = $case->respondent_name;
                }
                // Check for respondents JSON field
                elseif(isset($case->respondents_list) && $case->respondents_list) {
                    $respondentList = is_array($case->respondents_list) ? $case->respondents_list : json_decode($case->respondents_list, true);
                    if($respondentList && count($respondentList) > 0) {
                        foreach($respondentList as $resp) {
                            $respondentsList[] = is_array($resp) ? ($resp['name'] ?? $resp['full_name'] ?? 'Unknown') : $resp;
                        }
                    }
                }

                $firstRespondent = count($respondentsList) > 0 ? $respondentsList[0] : 'N/A';
            @endphp
            <div class="user-info">
                <i class="fas fa-user-circle" style="color: #667eea; font-size: 1.2rem;"></i>
                <span>{{ $firstRespondent }}</span>
            </div>
            @if(count($respondentsList) > 1)
                <div class="party-list">
                    <small class="text-muted">+{{ count($respondentsList) - 1 }} more</small>
                    <div class="hidden-details" style="display: none;">
                        @foreach($respondentsList as $resp)
                            <div><i class="fas fa-user"></i> {{ $resp }}</div>
                        @endforeach
                    </div>
                    <button class="toggle-details-btn" onclick="toggleDetails(this)" style="background: none; border: none; color: #667eea; font-size: 0.7rem; cursor: pointer; padding: 0;">
                        <i class="fas fa-chevron-down"></i> View all
                    </button>
                </div>
            @endif
        </td>
        <td>
            <span class="incident-type">{{ $case->incident_type }}</span>
        </td>
        <td>
            <span class="status-badge status-{{ strtolower($case->status) }}">
                {{ $case->status }}
            </span>
        </td>
        <td>{{ $case->deleted_at ? $case->deleted_at->format('M d, Y h:i A') : 'N/A' }}</td>
        <td>
            <div class="action-buttons">
                <button type="button" class="btn-icon restore-btn" title="Restore"
                    onclick="confirmRestore('{{ $case->id }}')">
                    <i class="fas fa-undo-alt"></i>
                </button>
                <form id="restore-form-{{ $case->id }}" action="{{ route('secretary.blotter.restore', $case->id) }}" method="POST" style="display: none;">
                    @csrf
                </form>

                {{-- Delete button visible for Admin, Secretary, and Captain (role_id 1, 2, 3) --}}
                @if(in_array(auth()->user()->role_id, [1, 2, 3]))
                <button type="button" class="btn-icon delete-btn" title="Delete Permanently"
                    onclick="confirmForceDelete('{{ $case->id }}')">
                    <i class="fas fa-trash-alt"></i>
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
                <i class="fas fa-archive"></i>
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

.text-muted {
    color: #6b7280;
    font-size: 0.7rem;
    margin-left: 0.5rem;
}

.case-id {
    font-family: monospace;
    font-weight: 600;
    color: #667eea;
    background: #eef2ff;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    font-size: 0.9rem;
}

.incident-type {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    background: #e9ecef;
    border-radius: 20px;
    font-size: 0.85rem;
    color: #4a5568;
}

.status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-ongoing { background: #cce5ff; color: #004085; }
.status-investigating { background: #cce5ff; color: #004085; }
.status-hearings { background: #e2d5f1; color: #553c9a; }
.status-settled { background: #d4edda; color: #155724; }
.status-referred { background: #e2d5f1; color: #553c9a; }
.status-dropped { background: #fee2e2; color: #991b1b; }

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
    // Function to toggle details view for multiple parties
function toggleDetails(button) {
    const hiddenDiv = button.parentElement.querySelector('.hidden-details');
    const icon = button.querySelector('i');

    if (hiddenDiv) {
        if (hiddenDiv.style.display === 'none' || hiddenDiv.style.display === '') {
            hiddenDiv.style.display = 'block';
            button.innerHTML = '<i class="fas fa-chevron-up"></i> Show less';
        } else {
            hiddenDiv.style.display = 'none';
            button.innerHTML = '<i class="fas fa-chevron-down"></i> View all';
        }
    }
}

// Make toggleDetails globally available
window.toggleDetails = toggleDetails;

let currentFormId = null;

function confirmRestore(id) {
    console.log('Restore clicked for ID:', id);
    currentAction = 'restore';
    currentFormId = id;

    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const confirmMessage = document.getElementById('confirmMessage');

    if (modalTitle) modalTitle.textContent = 'Restore Case';
    if (confirmMessage) confirmMessage.textContent = 'Are you sure you want to restore this archived case?';

    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Modal not found!');
        alert('Error: Modal not found');
    }
}

function confirmForceDelete(id) {
    console.log('Force delete clicked for ID:', id);
    currentAction = 'forceDelete';
    currentFormId = id;

    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const confirmMessage = document.getElementById('confirmMessage');

    if (modalTitle) modalTitle.textContent = 'Permanently Delete Case';
    if (confirmMessage) confirmMessage.textContent = 'Are you sure you want to permanently delete this case? This action cannot be undone.';

    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Modal not found!');
        alert('Error: Modal not found');
    }
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    currentAction = null;
    currentFormId = null;
}

function executeAction() {
    console.log('Execute action:', currentAction, 'Form ID:', currentFormId);

    if (currentAction === 'restore') {
        const form = document.getElementById('restore-form-' + currentFormId);
        if (form) {
            console.log('Submitting restore form');
            form.submit();
        } else {
            console.error('Restore form not found for ID:', currentFormId);
            alert('Error: Form not found');
        }
    } else if (currentAction === 'forceDelete') {
        const form = document.getElementById('force-delete-form-' + currentFormId);
        if (form) {
            console.log('Submitting force delete form');
            form.submit();
        } else {
            console.error('Force delete form not found for ID:', currentFormId);
            alert('Error: Form not found');
        }
    }

    closeConfirmModal();
}

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Archive page loaded');

    // Find and log all restore and delete buttons for debugging
    const restoreBtns = document.querySelectorAll('.restore-btn');
    const deleteBtns = document.querySelectorAll('.delete-btn');
    console.log('Found restore buttons:', restoreBtns.length);
    console.log('Found delete buttons:', deleteBtns.length);

    // Check if modal exists
    const modal = document.getElementById('confirmModal');
    console.log('Modal found:', !!modal);

    // Add click event to confirm button
    const confirmBtn = document.getElementById('confirmActionBtn');
    if (confirmBtn) {
        // Remove any existing event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        newConfirmBtn.addEventListener('click', executeAction);
        console.log('Confirm button event attached');
    } else {
        console.error('Confirm button not found!');
    }

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeConfirmModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('confirmModal');
            if (modal && modal.style.display === 'flex') {
                closeConfirmModal();
            }
        }
    });
});

// Make functions globally available
window.confirmRestore = confirmRestore;
window.confirmForceDelete = confirmForceDelete;
window.closeConfirmModal = closeConfirmModal;
window.executeAction = executeAction;
</script>

{{-- Session messages --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    if (toast && toastMessage) {
        toastMessage.textContent = "{{ session('success') }}";
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
});</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    if (toast && toastMessage) {
        toastMessage.textContent = "{{ session('error') }}";
        toast.style.borderLeftColor = '#dc2626';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
});</script>
@endif
@endpush
