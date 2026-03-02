@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<style>
    .user-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .user-stat-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border-left: 4px solid #667eea;
        text-align: center;
    }

    .user-stat-card h4 {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .user-stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #667eea;
    }

    .status-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #fee2e2;
        color: #dc2626;
    }

    .role-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }

    .role-admin { background: #dbeafe; color: #1e40af; }
    .role-captain { background: #f3e8ff; color: #7c3aed; }
    .role-secretary { background: #dcfce7; color: #166534; }
    /* .role-resident { background: #fef3c7; color: #d97706; } */

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-sm {
        padding: 0.3rem 0.7rem;
        font-size: 0.8rem;
    }

    .no-data-message {
        text-align: center;
        padding: 3rem;
        color: #666;
        font-style: italic;
    }

    .search-box {
        padding: 0.5rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        width: 250px;
        font-size: 0.9rem;
    }

    .search-box:focus {
        outline: none;
        border-color: #667eea;
    }

    /* Three Dots Button Styles */
    .action-menu-container {
        position: relative;
        display: inline-block;
    }

    .three-dots-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s;
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
        min-width: 180px;
        z-index: 1000;
        display: none;
        padding: 0.5rem 0;
    }

    .action-dropdown.show {
        display: block;
    }

    .action-dropdown-item {
        display: block;
        width: 100%;
        padding: 0.6rem 1rem;
        text-decoration: none;
        color: #374151;
        font-size: 0.9rem;
        transition: background-color 0.2s;
        border: none;
        background: none;
        text-align: left;
        cursor: pointer;
    }

    .action-dropdown-item:hover {
        background-color: #f3f4f6;
    }

    .action-dropdown-item.danger {
        color: #dc2626;
    }

    .action-dropdown-item.danger:hover {
        background-color: #fee2e2;
    }

    .action-dropdown-divider {
        height: 1px;
        background-color: #e5e7eb;
        margin: 0.5rem 0;
    }

    .text-muted {
        color: #9ca3af;
        font-style: italic;
        padding: 0.5rem 1rem;
        display: block;
    }

    /* Responsive breakpoints */
    @media (max-width: 1200px) {
        .action-buttons {
            display: none;
        }

        .action-menu-container {
            display: block;
        }
    }

    @media (min-width: 1201px) {
        .action-menu-container {
            display: none;
        }
    }

    /* For very small screens */
    @media (max-width: 768px) {
        table {
            font-size: 0.85rem;
        }

        .role-badge, .status-badge {
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
        }
    }
    
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>User Management</h1>
                <p>Manage system users and their permissions</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('admin.users.create') }}" class="btn btn-outline">
                    <span>Add New User</span>
                </a>
            </div>
        </div>

        <!-- Statistics -->
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
                <h3>System Users</h3>
                <div>
                    <input type="text" class="search-box" placeholder="Search users..." id="userSearch">
                </div>
            </div>

            @if($users->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <td>{{ $user->full_name ?? 'N/A' }}</td>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
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
                {{ \Carbon\Carbon::parse($user->last_login)->format('M d, Y h:i A') }}
            @else
                Never
            @endif
        </td>
        <td>
            <!-- Desktop Action Buttons (visible on larger screens) -->
            <div class="action-buttons">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm">
                    <span>✏️</span> Edit
                </a>

                @if($user->id !== auth()->id())
                    @if($user->is_active)
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST"
                              style="display: inline;"
                              onsubmit="return confirm('Are you sure you want to deactivate this user?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <span>🚫</span> Deactivate
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST"
                              style="display: inline;"
                              onsubmit="return confirm('Are you sure you want to activate this user?')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <span>✅</span> Activate
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                          style="display: inline;"
                          onsubmit="return confirm('Delete this user permanently? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <span>🗑️</span> Delete
                        </button>
                    </form>
                @else
                    <span class="text-muted">Current User</span>
                @endif
            </div>

            <!-- Mobile/Tablet Three Dots Menu (visible on smaller screens) -->
            <div class="action-menu-container">
                <button class="three-dots-btn" onclick="toggleDropdown(this)">
                    ⋮
                </button>
                <div class="action-dropdown">
                    <a href="{{ route('admin.users.edit', $user) }}" class="action-dropdown-item">
                        ✏️ Edit
                    </a>

                    @if($user->id !== auth()->id())
                        <div class="action-dropdown-divider"></div>

                        @if($user->is_active)
                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST"
                                  class="dropdown-form"
                                  onsubmit="return confirm('Are you sure you want to deactivate this user?')">
                                @csrf
                                <button type="submit" class="action-dropdown-item">
                                    🚫 Deactivate
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST"
                                  class="dropdown-form"
                                  onsubmit="return confirm('Are you sure you want to activate this user?')">
                                @csrf
                                <button type="submit" class="action-dropdown-item">
                                    ✅ Activate
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              class="dropdown-form"
                              onsubmit="return confirm('Delete this user permanently? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-dropdown-item danger">
                                🗑️ Delete
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
            @else
            <div class="no-data-message">
                <p>No users found in the system.</p>
            </div>
            @endif
        </div>
    </main>
</div>

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

    // Dropdown toggle functionality
    function toggleDropdown(button) {
        // Close all other dropdowns first
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

    // Prevent dropdown from closing when clicking inside it
    document.querySelectorAll('.action-dropdown').forEach(dropdown => {
        dropdown.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });
</script>
@endpush
@endsection
