@extends('layouts.app')

@section('title', 'Archived Certificates')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Archived Certificates</h1>
            <p>Restore or permanently delete archived certificates</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.certificates.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left icon-small"></i>
                Back to Certificates
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="filters-section">
        <form action="{{ route('secretary.certificates.archived') }}" method="GET" class="filters-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       name="search"
                       placeholder="Search archived certificates..."
                       value="{{ request('search') }}"
                       class="search-input">
            </div>
            <button type="submit" class="btn-filter">Search</button>
            <a href="{{ route('secretary.certificates.archived') }}" class="btn-clear">Clear</a>
        </form>
    </div>

    <!-- Custom Confirmation Modal (reuse same modal from residents) -->
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

    <!-- Archived Certificates Table -->
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
                                <div class="resident-info">
                                    <span class="resident-name">{{ $certificate->resident->first_name ?? '' }} {{ $certificate->resident->last_name ?? '' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="cert-type">{{ $certificate->certificate_type }}</span>
                            </td>
                            <td>{{ Str::limit($certificate->purpose, 30) }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($certificate->status) }}">
                                    {{ $certificate->status }}
                                </span>
                            </td>
                            <td>{{ $certificate->deleted_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-icon restore-btn" title="Restore"
                                        onclick="confirmRestore('{{ $certificate->id }}')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <form id="restore-form-{{ $certificate->id }}" action="{{ route('secretary.certificates.restore', $certificate->id) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>

                                    @if(auth()->user()->role_id == 1) {{-- Only Admin --}}
                                    <button type="button" class="btn-icon delete-btn" title="Delete Permanently"
                                        onclick="confirmForceDelete('{{ $certificate->id }}')">
                                        <i class="fas fa-trash"></i>
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
                                    <i class="fas fa-archive empty-icon"></i>
                                    <h3>No archived certificates found</h3>
                                    <p>Archived certificates will appear here.</p>
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

@push('scripts')
<script>
// Confirmation modal variables
let currentAction = null;
let currentFormId = null;

function confirmRestore(id) {
    currentAction = 'restore';
    currentFormId = id;
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to restore this archived certificate?';
    document.getElementById('confirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function confirmForceDelete(id) {
    currentAction = 'forceDelete';
    currentFormId = id;
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
