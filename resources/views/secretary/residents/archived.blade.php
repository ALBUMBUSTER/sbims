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

<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Archived Residents</h1>
            <p>View and manage archived resident records</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left icon-small"></i>
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
            <button type="submit" class="btn-search">Search</button>
            @if(request('search'))
                <a href="{{ route('secretary.residents.archived') }}" class="btn-clear">
                    <i class="fas fa-times icon-small"></i>
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="card">
        <div class="card-body">
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
                            <td>Purok {{ $resident->purok }}</td>
                            <td>{{ $resident->archived_at ? \Carbon\Carbon::parse($resident->archived_at)->format('M d, Y h:i A') : '' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('secretary.residents.show', $resident) }}" class="btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
                                    <i class="fas fa-archive empty-icon"></i>
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
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <span>{{ $archivedResidents->firstItem() }}</span> to <span>{{ $archivedResidents->lastItem() }}</span> of <span>{{ $archivedResidents->total() }}</span> results
                </div>

                <div class="pagination-links">
                    {{-- Previous Page Link --}}
                    @if($archivedResidents->onFirstPage())
                        <span class="pagination-link disabled"><i class="fas fa-chevron-left"></i> Previous</span>
                    @else
                        <a href="{{ $archivedResidents->previousPageUrl() }}" class="pagination-link"><i class="fas fa-chevron-left"></i> Previous</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach($archivedResidents->links()->elements as $element)
                        @if(is_string($element))
                            <span class="pagination-link dots">{{ $element }}</span>
                        @endif
                        @if(is_array($element))
                            @foreach($element as $page => $url)
                                @if($page == $archivedResidents->currentPage())
                                    <span class="pagination-link active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if($archivedResidents->hasMorePages())
                        <a href="{{ $archivedResidents->nextPageUrl() }}" class="pagination-link">Next <i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="pagination-link disabled">Next <i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Reuse existing styles from index, plus add these */
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

.empty-icon {
    font-size: 64px;
    color: #cbd5e0;
    margin-bottom: 1rem;
}

/* Override toast for this page */
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

function confirmRestore(id) {
    if (confirm('Restore this resident? They will be moved back to active residents.')) {
        document.getElementById('restore-form-' + id).submit();
    }
}

function confirmForceDelete(id) {
    if (confirm('WARNING: This will permanently delete this resident. This action cannot be undone!')) {
        document.getElementById('force-delete-form-' + id).submit();
    }
}
</script>

{{-- Session messages --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
@endif
@endpush
