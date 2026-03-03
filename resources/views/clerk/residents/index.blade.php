@extends('layouts.app')

@section('title', 'Resident Records')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Resident Records</h1>
            <p>View barangay residents (Read Only)</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clerk.dashboard') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-section">
        <form action="{{ route('clerk.residents.index') }}" method="GET" class="search-form">
            <div class="search-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" placeholder="Search by name, ID, or address..." value="{{ request('search') }}" class="search-input">
            </div>
            <button type="submit" class="btn-search">Search</button>
            @if(request('search'))
                <a href="{{ route('clerk.residents.index') }}" class="btn-clear">Clear</a>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($residents as $resident)
                        <tr>
                            <td><span class="resident-id">{{ $resident->resident_id }}</span></td>
                            <td>
                                <div class="resident-name">
                                    {{ $resident->full_name }}
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
                                <a href="{{ route('clerk.residents.show', $resident) }}" class="btn-view" title="View Details">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="2"></circle>
                                        <path d="M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2s10 4.48 10 10z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="empty-icon">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <h3>No residents found</h3>
                                    <p>No resident records match your search criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($residents->hasPages())
            <div class="pagination-wrapper">
                {{ $residents->links() }}
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
    margin-bottom: 1.5rem;
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

.page-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
    cursor: pointer;
}

.btn-secondary:hover {
    background: #eef2ff;
}

.search-section {
    margin-bottom: 1.5rem;
}

.search-form {
    display: flex;
    gap: 1rem;
    max-width: 600px;
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
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
}

.btn-search {
    padding: 0.75rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.95rem;
}

.btn-search:hover {
    background: #5a67d8;
}

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
    border: 1px solid #e2e8f0;
}

.btn-clear:hover {
    background: #e2e8f0;
    color: #333;
}

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-body {
    padding: 1.5rem;
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

.resident-id {
    font-family: monospace;
    font-weight: 600;
    color: #555;
}

.resident-name {
    font-weight: 600;
    color: #333;
}

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

.btn-view {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 5px;
    background: #eef2ff;
    color: #667eea;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-view:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

.empty-state {
    text-align: center;
    padding: 3rem;
}

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

.empty-state p {
    color: #666;
    margin-bottom: 1.5rem;
}

.pagination-wrapper {
    margin-top: 1.5rem;
}

.text-center {
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .page-actions {
        width: 100%;
    }

    .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .search-form {
        flex-wrap: wrap;
    }

    .btn-search, .btn-clear {
        width: 100%;
    }
}
</style>
@endpush
