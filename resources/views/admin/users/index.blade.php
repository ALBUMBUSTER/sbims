@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<style>
    /* ===== RESET & CONTAINER FIXES ===== */
    .main-container {
        display: flex;
        min-height: calc(100vh - 70px);
        background: #f8fafc;
        width: 100%;
        overflow-x: hidden; /* THIS FIXES THE MAIN HORIZONTAL SCROLL */
    }

    .content {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        overflow-x: hidden; /* THIS FIXES THE CONTENT SCROLL */
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        background: white;
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 1rem;
        width: 100%;
        box-sizing: border-box;
    }

    .page-title h1 {
        color: #333;
        margin-bottom: 0.25rem;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .page-title p {
        color: #666;
        font-size: 0.875rem;
    }

    .page-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    /* ===== STATS CARDS - FIXED GRID ===== */
    .user-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
        width: 100%;
    }

    .user-stat-card {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border-left: 4px solid #667eea;
        text-align: center;
        min-width: 0; /* Prevents overflow */
    }

    .user-stat-card h4 {
        color: #666;
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-stat-value {
        font-size: 1.75rem;
        font-weight: bold;
        color: #667eea;
        line-height: 1.2;
    }

    /* ===== BADGES ===== */
    .status-badge, .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-block;
        white-space: nowrap;
    }

    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #fee2e2; color: #dc2626; }

    .role-admin { background: #dbeafe; color: #1e40af; }
    .role-captain { background: #f3e8ff; color: #7c3aed; }
    .role-secretary { background: #dcfce7; color: #166534; }
    .role-clerk { background: #f59e0b20; color: #f59e0b; }

    /* ===== BUTTONS ===== */
    .btn-outline, .btn-danger, .btn-success {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.2s;
        cursor: pointer;
        border: 1px solid;
        background: white;
        white-space: nowrap;
    }

    .btn-outline { border-color: #667eea; color: #667eea; }
    .btn-outline:hover { background: #eef2ff; }

    .btn-danger { border-color: #dc2626; color: #dc2626; }
    .btn-danger:hover { background: #fee2e2; }

    .btn-success { border-color: #10b981; color: #10b981; }
    .btn-success:hover { background: #d1fae5; }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    /* ===== TABLE SECTION - FIXED SCROLL ===== */
    .data-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        overflow: hidden;
        width: 100%;
    }

    .table-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        background: white;
    }

    .table-header h3 {
        margin: 0;
        color: #333;
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-header h3 i {
        color: #667eea;
    }

    /* ===== SEARCH BOX ===== */
    .search-box {
        padding: 0.5rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        width: 250px;
        max-width: 100%;
        font-size: 0.875rem;
    }

    .search-box:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* ===== TABLE CONTAINER WITH CUSTOM SCROLL ===== */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        /* Custom scrollbar styling */
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f1f5f9;
    }

    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px; /* Table will scroll at this width */
    }

    thead {
        background: #f8fafc;
    }

    th {
        padding: 1rem;
        text-align: left;
        color: #666;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    th i {
        margin-right: 0.5rem;
        color: #667eea;
        font-size: 0.8rem;
    }

    td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
        vertical-align: middle;
        font-size: 0.875rem;
    }

    tbody tr:hover {
        background: #f8fafc;
    }

    /* ===== ACTION BUTTONS CONTAINER ===== */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* ===== THREE DOTS MENU ===== */
    .action-menu-container {
        position: relative;
        display: inline-block;
    }

    .three-dots-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.4rem;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
    }

    .three-dots-btn i {
        font-size: 1rem;
        color: #667eea;
    }

    .three-dots-btn:hover {
        background-color: #f3f4f6;
    }

    .action-dropdown {
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 160px;
        z-index: 1000;
        display: none;
        padding: 0.25rem 0;
        margin-top: 0.25rem;
    }

    .action-dropdown.show {
        display: block;
        animation: dropdownFade 0.2s ease;
    }

    @keyframes dropdownFade {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .action-dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.5rem 1rem;
        text-decoration: none;
        color: #374151;
        font-size: 0.85rem;
        transition: background-color 0.2s;
        border: none;
        background: none;
        text-align: left;
        cursor: pointer;
    }

    .action-dropdown-item i {
        width: 14px;
        font-size: 0.8rem;
        color: #667eea;
    }

    .action-dropdown-item:hover {
        background-color: #f3f4f6;
    }

    .action-dropdown-item.danger {
        color: #dc2626;
    }

    .action-dropdown-item.danger i {
        color: #dc2626;
    }

    .action-dropdown-item.danger:hover {
        background-color: #fee2e2;
    }

    .action-dropdown-divider {
        height: 1px;
        background-color: #e5e7eb;
        margin: 0.25rem 0;
    }

    .text-muted {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.8rem;
        display: inline-block;
        padding: 0.25rem 0.5rem;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */
    @media (min-width: 1201px) {
        .action-menu-container {
            display: none;
        }
    }

    @media (max-width: 1200px) {
        .action-buttons {
            display: none;
        }
        .action-menu-container {
            display: inline-block;
        }
    }

    @media (max-width: 992px) {
        .user-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        th, td {
            padding: 0.875rem;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 0.75rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-actions {
            width: 100%;
        }

        .btn-outline, .btn-danger, .btn-success {
            width: 100%;
            justify-content: center;
        }

        .user-stats-grid {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-box {
            width: 100%;
        }

        th, td {
            padding: 0.75rem;
            font-size: 0.8rem;
        }

        th i {
            display: none; /* Hide icons on mobile */
        }

        /* Hide email on mobile */
        th:nth-child(4), td:nth-child(4) {
            display: none;
        }

        .role-badge, .status-badge {
            padding: 0.2rem 0.4rem;
            font-size: 0.65rem;
        }
    }

    @media (max-width: 480px) {
        /* Hide last login on very small screens */
        th:nth-child(7), td:nth-child(7) {
            display: none;
        }

        th, td {
            padding: 0.6rem;
        }
    }

    /* ===== EMPTY STATE ===== */
    .no-data-message {
        text-align: center;
        padding: 2rem;
        color: #666;
    }

    .no-data-message i {
        font-size: 2.5rem;
        color: #e9ecef;
        margin-bottom: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>User Management</h1>
                <p>Manage system users and their permissions</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('admin.users.create') }}" class="btn-outline">
                    <i class="fas fa-plus"></i>
                    <span>Add New User</span>
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="user-stats-grid">
            <div class="user-stat-card">
                <h4>Total Users</h4>
                <div class="user-stat-value">{{ $stats['total'] }}</div>
            </div>
            <div class="user-stat-card">
                <h4>Active Users</h4>
                <div class="user-stat-value">{{ $stats['active'] }}</div>
            </div>
            <div class="user-stat-card">
                <h4>Administrators</h4>
                <div class="user-stat-value">{{ $stats['admins'] }}</div>
            </div>
            <div class="user-stat-card">
                <h4>Today's Logins</h4>
                <div class="user-stat-value">{{ $stats['today_logins'] }}</div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="data-table">
            <div class="table-header">
                <h3><i class="fas fa-users"></i> System Users</h3>
                <div>
                    <input type="text" class="search-box" placeholder="Search users..." id="userSearch">
                </div>
            </div>

            @if($users->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> Username</th>
                            <th><i class="fas fa-id-card"></i> Full Name</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-tag"></i> Role</th>
                            <th><i class="fas fa-circle"></i> Status</th>
                            <th><i class="fas fa-clock"></i> Last Login</th>
                            <th><i class="fas fa-cog"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="user-row">
                            <td>#{{ $user->id }}</td>
                            <td><strong>{{ $user->username }}</strong></td>
                            <td>{{ $user->full_name ?? 'N/A' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $user->is_active ? 'active' : 'inactive' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @if($user->last_login)
                                    {{ \Carbon\Carbon::parse($user->last_login)->format('M d, Y') }}
                                @else
                                    Never
                                @endif
                            </td>
                            <td>
                                <!-- Desktop Action Buttons -->
                                <div class="action-buttons">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-outline btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    @if($user->id !== auth()->id())
                                        @if($user->is_active)
                                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                    <i class="fas fa-ban"></i> Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-success btn-sm" onclick="return confirm('Are you sure you want to activate this user?')">
                                                    <i class="fas fa-check-circle"></i> Activate
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Delete this user permanently? This action cannot be undone.')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">Current</span>
                                    @endif
                                </div>

                                <!-- Mobile Three Dots Menu -->
                                <div class="action-menu-container">
                                    <button class="three-dots-btn" onclick="toggleDropdown(this)">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="action-dropdown">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="action-dropdown-item">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        @if($user->id !== auth()->id())
                                            <div class="action-dropdown-divider"></div>

                                            @if($user->is_active)
                                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="action-dropdown-item" onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                        <i class="fas fa-ban"></i> Deactivate
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="action-dropdown-item" onclick="return confirm('Are you sure you want to activate this user?')">
                                                        <i class="fas fa-check-circle"></i> Activate
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-dropdown-item danger" onclick="return confirm('Delete this user permanently? This action cannot be undone.')">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">Current User</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="no-data-message">
                <i class="fas fa-database"></i>
                <p>No users found in the system.</p>
            </div>
            @endif
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    // Search functionality
    document.getElementById('userSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Dropdown toggle
    function toggleDropdown(button) {
        // Close all other dropdowns
        document.querySelectorAll('.action-dropdown.show').forEach(dropdown => {
            if (dropdown !== button.nextElementSibling) {
                dropdown.classList.remove('show');
            }
        });

        // Toggle current dropdown
        const dropdown = button.nextElementSibling;
        dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-menu-container')) {
            document.querySelectorAll('.action-dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });

    // Close dropdown on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.action-dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
</script>
@endpush
