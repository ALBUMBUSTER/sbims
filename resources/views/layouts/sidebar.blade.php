@php
    $current_role = auth()->user()->role;
    $current_route = Route::currentRouteName();
@endphp

<nav class="sidebar">
    <ul class="sidebar-menu">
        <!-- Dashboard Link -->
        <li class="menu-item {{ in_array($current_route, ['admin.dashboard', 'captain.dashboard', 'secretary.dashboard', 'resident.dashboard']) ? 'active' : '' }}">
            <a href="{{ route($current_role . '.dashboard') }}">
                <x-heroicon-o-home class="menu-icon" />
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        @if($current_role == 'admin')
            <!-- Admin Menu -->
            <li class="menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}">
                    <x-heroicon-o-users class="menu-icon" />
                    <span class="menu-text">User Management</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/barangay*') ? 'active' : '' }}">
                <a href="{{ route('admin.barangay.index') }}">
                    <x-heroicon-o-building-office class="menu-icon" />
                    <span class="menu-text">Barangay Info</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/logs*') ? 'active' : '' }}">
                <a href="{{ route('admin.logs.index') }}">
                    <x-heroicon-o-document-text class="menu-icon" />
                    <span class="menu-text">System Logs</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/backups*') ? 'active' : '' }}">
                <a href="{{ route('admin.backups.index') }}">
                    <x-heroicon-o-cloud-arrow-up class="menu-icon" />
                    <span class="menu-text">Backup & Restore</span>
                </a>
            </li>

        @elseif($current_role == 'secretary')
            <!-- Secretary Menu -->
            <li class="menu-item {{ request()->routeIs('secretary.residents.*') ? 'active' : '' }}">
                <a href="{{ route('secretary.residents.index') }}">
                    <x-heroicon-o-user-group class="menu-icon" />
                    <span class="menu-text">Resident Records</span>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('secretary.blotter.*') ? 'active' : '' }}">
                <a href="{{ route('secretary.blotter.index') }}">
                    <x-heroicon-o-document-duplicate class="menu-icon" />
                    <span class="menu-text">Blotter Cases</span>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('secretary.certificates.*') ? 'active' : '' }}">
                <a href="{{ route('secretary.certificates.index') }}">
                    <x-heroicon-o-document-check class="menu-icon" />
                    <span class="menu-text">Certificates</span>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('secretary.reports.*') ? 'active' : '' }}">
                <a href="{{ route('secretary.reports.index') }}">
                    <x-heroicon-o-chart-bar class="menu-icon" />
                    <span class="menu-text">Reports</span>
                </a>
            </li>

        @elseif($current_role == 'captain')
            <!-- Captain Menu -->
            <li class="menu-item {{ request()->routeIs('captain.approvals.*') ? 'active' : '' }}">
                <a href="{{ route('captain.approvals.index') }}">
                    <x-heroicon-o-check-badge class="menu-icon" />
                    <span class="menu-text">Approvals</span>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('captain.residents.*') ? 'active' : '' }}">
                <a href="{{ route('captain.residents.index') }}">
                    <x-heroicon-o-users class="menu-icon" />
                    <span class="menu-text">Residents</span>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('captain.blotter.*') ? 'active' : '' }}">
                <a href="{{ route('captain.blotters.index') }}">
                    <x-heroicon-o-scale class="menu-icon" />
                    <span class="menu-text">Blotter Records</span>
                </a>
            </li>
        @endif
        <!-- Logout -->
        <li class="menu-item">
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; display: flex; align-items: center; width: 100%; padding: 12px 27px;">
                    <x-heroicon-o-arrow-left-on-rectangle class="menu-icon" />
                    <span class="menu-text">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</nav>

<style>
.sidebar {
    width: 250px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 0;
    min-height: calc(100vh - 70px);
}

.sidebar-menu {
    list-style: none;
    padding: 0;
}

.menu-item {
    padding: 0;
}

.menu-item a, .menu-item form button {
    display: flex;
    align-items: center;
    color: white;
    text-decoration: none;
    padding: 12px 25px;
    transition: background 0.3s;
    width: 100%;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 1rem;
}

.menu-item a:hover, .menu-item.active a, .menu-item form button:hover {
    background: rgba(255,255,255,0.1);
    border-left: 4px solid white;
}

.menu-icon {
    width: 20px;
    height: 20px;
    margin-right: 15px;
    color: white;
    flex-shrink: 0;
}

.menu-text {
    flex: 1;
    text-align: left;
}

.coming-soon {
    font-size: 0.7rem;
    opacity: 0.8;
    margin-left: 5px;
}

/* Remove the old span styling since we're using SVG icons */
.menu-item span:first-child {
    margin-right: 0;
    width: auto;
}

/* Logout button specific styling */
.menu-item form {
    width: 100%;
}

.menu-item form button {
    text-align: left;
    padding: 12px 25px;
    font-family: inherit;
}

.menu-item form button .menu-icon {
    margin-right: 15px;
}
</style>
