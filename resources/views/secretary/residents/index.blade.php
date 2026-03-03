@extends('layouts.app')

@section('title', 'Resident Records')

@section('content')
<!-- Toast Notification -->
<div id="toast" class="toast">
    <div class="toast-content">
        <x-heroicon-o-check-circle class="toast-icon success" />
        <span id="toastMessage">Resident saved successfully!</span>
    </div>
</div>

<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Resident Records</h1>
            <p>Manage barangay residents</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.residents.create') }}" class="btn-primary">
                <x-heroicon-o-plus class="icon-small" />
                Add New Resident
            </a>
            <a href="{{ route('secretary.residents.import') }}" class="btn-secondary">
                <x-heroicon-o-cloud-arrow-up class="icon-small" />
                Import CSV
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-section">
        <form action="{{ route('secretary.residents.index') }}" method="GET" class="search-form">
            <div class="search-wrapper">
                <x-heroicon-o-magnifying-glass class="search-icon" />
                <input type="text" name="search" placeholder="Search by name, ID, address, or contact..." value="{{ request('search') }}" class="search-input">
            </div>
            <button type="submit" class="btn-search">Search</button>
            @if(request('search'))
                <a href="{{ route('secretary.residents.index') }}" class="btn-clear">
                    <x-heroicon-o-x-mark class="icon-small" />
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
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($residents as $resident)
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
                            <td>{{ $resident->contact_number ?? 'N/A' }}</td>
                            <td>
                                <div class="status-badges">
                                    @if($resident->is_voter) <span class="badge badge-voter" title="Registered Voter">V</span> @endif
                                    @if($resident->is_senior) <span class="badge badge-senior" title="Senior Citizen">S</span> @endif
                                    @if($resident->is_pwd) <span class="badge badge-pwd" title="PWD">P</span> @endif
                                    @if($resident->is_4ps) <span class="badge badge-4ps" title="4Ps Member">4Ps</span> @endif
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('secretary.residents.show', $resident) }}" class="btn-icon" title="View">
                                        <x-heroicon-o-eye />
                                    </a>
                                    <a href="{{ route('secretary.residents.edit', $resident) }}" class="btn-icon" title="Edit">
                                        <x-heroicon-o-pencil />
                                    </a>
                                    <button type="button" class="btn-icon delete-btn" title="Delete" onclick="confirmDelete('{{ $resident->id }}')">
                                        <x-heroicon-o-trash />
                                    </button>
                                    <form id="delete-form-{{ $resident->id }}" action="{{ route('secretary.residents.destroy', $resident) }}" method="POST" style="display: none;">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <x-heroicon-o-users class="empty-icon" />
                                    <h3>No residents found</h3>
                                    <p>Get started by adding your first resident record or import from CSV.</p>
                                    <div class="empty-actions">
                                        <a href="{{ route('secretary.residents.create') }}" class="btn-primary">
                                            <x-heroicon-o-plus class="icon-small" />
                                            Add New Resident
                                        </a>
                                        <a href="{{ route('secretary.residents.import') }}" class="btn-secondary">
                                            <x-heroicon-o-cloud-arrow-up class="icon-small" />
                                            Import CSV
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($residents->hasPages())
<div class="pagination-container">
    <div class="pagination-info">
        Showing <span>{{ $residents->firstItem() }}</span> to <span>{{ $residents->lastItem() }}</span> of <span>{{ $residents->total() }}</span> results
    </div>

    <div class="pagination-links">
        {{-- Previous Page Link --}}
        @if($residents->onFirstPage())
            <span class="pagination-link disabled">Previous</span>
        @else
            <a href="{{ $residents->previousPageUrl() }}" class="pagination-link">Previous</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach($residents->links()->elements as $element)
            {{-- "Three Dots" Separator --}}
            @if(is_string($element))
                <span class="pagination-link dots">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $residents->currentPage())
                        <span class="pagination-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if($residents->hasMorePages())
            <a href="{{ $residents->nextPageUrl() }}" class="pagination-link">Next →</a>
        @else
            <span class="pagination-link disabled">Next →</span>
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
/* Shared styles (same as create.blade.php but without form-specific styles) */
.container-fluid { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.page-title h1 { color: #333; margin-bottom: 0.5rem; font-size: 1.8rem; }
.page-title p { color: #666; font-size: 1rem; }

/* Page Actions - New button styles */
.page-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    cursor: pointer;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-primary:hover { opacity: 0.9; color: white; }

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}
.btn-secondary:hover { background: #eef2ff; }

/* Search Section */
.search-section { margin-bottom: 1.5rem; }
.search-form { display: flex; gap: 1rem; max-width: 600px; }
.search-wrapper { flex: 1; position: relative; }
.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #999;
}
.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.95rem;
    transition: border-color 0.3s;
}
.search-input:focus { outline: none; border-color: #667eea; }

.btn-search {
    padding: 0.75rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.95rem;
    transition: background 0.3s;
}
.btn-search:hover { background: #5a67d8; }

.btn-clear {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    color: #666;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.9rem;
    transition: all 0.3s;
    border: 1px solid #e2e8f0;
}
.btn-clear:hover {
    background: #e2e8f0;
    color: #333;
}

/* Buttons (shared) */
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
    cursor: pointer;
    border: none;
    color: #667eea;
    background: none;
}
.btn-icon:hover {
    background: #eef2ff;
    transform: translateY(-2px);
}
.btn-icon svg { width: 18px; height: 18px; }
.delete-btn:hover { background: #fee2e2; color: #dc2626; }

.icon-small { width: 16px; height: 16px; }

/* Card */
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}
.card-body { padding: 1.5rem; }

/* Table */
.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
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
.table tr:hover td { background: #f8fafc; }

/* Resident Info */
.resident-id { font-family: monospace; font-weight: 600; color: #555; }
.resident-name { font-weight: 600; color: #333; }
.middle-name { color: #666; font-size: 0.9rem; margin-left: 0.25rem; }

/* Status Badges */
.status-badges { display: flex; gap: 0.25rem; flex-wrap: wrap; }
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
.badge-voter { background: #d4edda; color: #155724; }
.badge-senior { background: #cce5ff; color: #004085; }
.badge-pwd { background: #fff3cd; color: #856404; }
.badge-4ps {
    background: #e2d5f1;
    color: #553c9a;
    width: auto;
    padding: 0 8px;
    border-radius: 15px;
}

/* Action Buttons */
.action-buttons { display: flex; gap: 0.5rem; }

/* Empty State */
.empty-state { text-align: center; padding: 3rem; }
.empty-icon {
    width: 64px;
    height: 64px;
    color: #cbd5e0;
    margin-bottom: 1rem;
}
.empty-state h3 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}
.empty-state p { color: #666; margin-bottom: 1.5rem; }

.empty-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Pagination */
.pagination-wrapper { margin-top: 1.5rem; }
.text-center { text-align: center; }

/* Toast Notification (shared) */
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
.toast-icon { width: 24px; height: 24px; flex-shrink: 0; }
.toast-icon.success { color: #10b981; }
.toast-icon.error { color: #dc2626; }

/* Responsive */
@media (max-width: 768px) {
    .page-actions {
        width: 100%;
        justify-content: stretch;
    }

    .btn-primary, .btn-secondary {
        flex: 1;
        justify-content: center;
    }

    .search-form {
        flex-wrap: wrap;
    }

    .empty-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Animations */
@keyframes slideUp {
    from { transform: translate(-50%, 20px); opacity: 0; }
    to { transform: translate(-50%, 0); opacity: 1; }
}
@keyframes fadeOut {
    from { opacity: 1; transform: translate(-50%, 0); }
    to { opacity: 0; transform: translate(-50%, -10px); }
}
/* Pagination Styles */
.pagination-container {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

@media (min-width: 640px) {
    .pagination-container {
        flex-direction: row;
        justify-content: space-between;
    }
}

.pagination-info {
    color: #64748b;
    font-size: 0.9rem;
}

.pagination-info span {
    font-weight: 600;
    color: #1e293b;
}

.pagination-links {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 0.75rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #4b5563;
    background: white;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination-link:hover:not(.disabled):not(.active) {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #1e293b;
}

.pagination-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.pagination-link.disabled {
    background: #f1f5f9;
    color: #94a3b8;
    border-color: #e2e8f0;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-link.dots {
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: default;
}

.pagination-link.dots:hover {
    background: transparent;
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

// Delete confirmation function
function confirmDelete(residentId) {
    if (confirm('Are you sure you want to delete this resident? This action cannot be undone.')) {
        document.getElementById('delete-form-' + residentId).submit();
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
