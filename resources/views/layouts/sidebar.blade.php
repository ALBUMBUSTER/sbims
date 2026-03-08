@php
    $current_role = auth()->user()->role;
    $current_route = Route::currentRouteName();
@endphp

<nav class="sidebar">
    <!-- Sidebar Header with Logo/App Name -->
    <div class="sidebar-header">
        <div class="app-brand">
            <div class="brand-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 3L21 9V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V9Z" stroke="#667eea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 21V12H15V21" stroke="#667eea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="brand-text">
                <span class="brand-name">SBIMS-PRO</span>
                <span class="brand-location">Brgy. Libertad, Isabel, Leyte</span>
            </div>
        </div>
    </div>

    <!-- User Info Card - FIXED VERSION -->
<div class="user-card">
    <div class="user-avatar">
        {{ substr(auth()->user()->full_name, 0, 1) }}
    </div>
    <div class="user-details">
        <div class="user-name-container">
            <span class="user-fullname">{{ auth()->user()->full_name }}</span>
        </div>
        <div class="user-role-container">
            <span class="user-badge">{{ ucfirst(auth()->user()->role) }}</span>
        </div>
    </div>
</div>

    <!-- Navigation Menu -->
    <div class="nav-section">
        <div class="nav-label">MAIN MENU</div>
        <ul class="nav-menu">
            <!-- Dashboard -->
            <li class="nav-item {{ in_array($current_route, ['admin.dashboard', 'captain.dashboard', 'secretary.dashboard', 'resident.dashboard']) ? 'active' : '' }}">
                <a href="{{ route($current_role . '.dashboard') }}">
                    <span class="nav-icon">
                        <x-heroicon-o-home />
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
        </ul>
    </div>

    @if($current_role == 'admin')
        <!-- Administration Section -->
        <div class="nav-section">
            <div class="nav-label">ADMINISTRATION</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}">
                        <span class="nav-icon"><x-heroicon-o-users /></span>
                        <span class="nav-text">User Management</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/barangay*') ? 'active' : '' }}">
                    <a href="{{ route('admin.barangay.index') }}">
                        <span class="nav-icon"><x-heroicon-o-building-office /></span>
                        <span class="nav-text">Barangay Info</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/logs*') ? 'active' : '' }}">
                    <a href="{{ route('admin.logs.index') }}">
                        <span class="nav-icon"><x-heroicon-o-document-text /></span>
                        <span class="nav-text">System Logs</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/backups*') ? 'active' : '' }}">
                    <a href="{{ route('admin.backups.index') }}">
                        <span class="nav-icon"><x-heroicon-o-cloud-arrow-up /></span>
                        <span class="nav-text">Backup & Restore</span>
                    </a>
                </li>
            </ul>
        </div>

    @elseif($current_role == 'secretary')
        <!-- Secretary Menu -->
        <div class="nav-section">
            <div class="nav-label">RECORDS MANAGEMENT</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('secretary.residents.*') ? 'active' : '' }}">
                    <a href="{{ route('secretary.residents.index') }}">
                        <span class="nav-icon"><x-heroicon-o-user-group /></span>
                        <span class="nav-text">Resident Records</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('secretary.blotter.*') ? 'active' : '' }}">
                    <a href="{{ route('secretary.blotter.index') }}">
                        <span class="nav-icon"><x-heroicon-o-document-duplicate /></span>
                        <span class="nav-text">Blotter Cases</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('secretary.certificates.*') ? 'active' : '' }}">
                    <a href="{{ route('secretary.certificates.index') }}">
                        <span class="nav-icon"><x-heroicon-o-document-check /></span>
                        <span class="nav-text">Certificates</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('secretary.reports.*') ? 'active' : '' }}">
                    <a href="{{ route('secretary.reports.index') }}">
                        <span class="nav-icon"><x-heroicon-o-chart-bar /></span>
                        <span class="nav-text">Reports</span>
                    </a>
                </li>
            </ul>
        </div>

    @elseif($current_role == 'captain')
        <!-- Captain Menu -->
        <div class="nav-section">
            <div class="nav-label">CAPTAIN PANEL</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('captain.approvals.*') ? 'active' : '' }}">
                    <a href="{{ route('captain.approvals.index') }}">
                        <span class="nav-icon"><x-heroicon-o-check-badge /></span>
                        <span class="nav-text">Approvals</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('captain.residents.*') ? 'active' : '' }}">
                    <a href="{{ route('captain.residents.index') }}">
                        <span class="nav-icon"><x-heroicon-o-users /></span>
                        <span class="nav-text">Residents</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('captain.blotters.*') ? 'active' : '' }}">
                    <a href="{{ route('captain.blotters.index') }}">
                        <span class="nav-icon"><x-heroicon-o-scale /></span>
                        <span class="nav-text">Blotter Cases</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('captain.certificates.*') ? 'active' : '' }}">
                    <a href="{{ route('captain.certificates.index') }}">
                        <span class="nav-icon"><x-heroicon-o-document-text /></span>
                        <span class="nav-text">Certificates</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('captain.reports.*') ? 'active' : '' }}">
                    <a href="{{ route('captain.reports.index') }}">
                        <span class="nav-icon"><x-heroicon-o-chart-bar /></span>
                        <span class="nav-text">Reports</span>
                    </a>
                </li>
            </ul>
        </div>

    @elseif($current_role == 'clerk')
        <!-- Clerk Menu -->
        <div class="nav-section">
            <div class="nav-label">CLERK PANEL</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('clerk.residents.*') ? 'active' : '' }}">
                    <a href="{{ route('clerk.residents.index') }}">
                        <span class="nav-icon"><x-heroicon-o-users /></span>
                        <span class="nav-text">Residents</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('clerk.certificates.*') ? 'active' : '' }}">
                    <a href="{{ route('clerk.certificates.index') }}">
                        <span class="nav-icon"><x-heroicon-o-document-text /></span>
                        <span class="nav-text">Certificates</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('captain.blotters.*') ? 'active' : '' }}">
                    <a href="{{ route('captain.blotters.index') }}">
                        <span class="nav-icon"><x-heroicon-o-scale /></span>
                        <span class="nav-text">Blotter Cases</span>
                    </a>
                </li>
                <!-- <li class="nav-item {{ request()->routeIs('clerk.reports.*') ? 'active' : '' }}">
                    <a href="{{ route('clerk.reports.index') }}">
                        <span class="nav-icon"><x-heroicon-o-chart-bar /></span>
                        <span class="nav-text">Reports</span>
                    </a>
                </li> enable if needed -->
            </ul>
        </div>
    @endif

    <!-- Logout at Bottom -->
    <div class="sidebar-footer">
        <div class="nav-section">
            <ul class="nav-menu">
                <li class="nav-item logout-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">
                            <span class="nav-icon"><x-heroicon-o-arrow-left-on-rectangle /></span>
                            <span class="nav-text">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- System Version -->
        <div class="system-version">
            <span></span>
        </div>
    </div>
</nav>

<style>
.sidebar {
    width: 100%;
    background: white;
    color: #4a5568;
    display: flex;
    flex-direction: column;
    height: 100%;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    border-right: 1px solid #e2e8f0;
    box-shadow: 2px 0 8px rgba(0,0,0,0.02);
}

/* Sidebar Header */
.sidebar-header {
    padding: 20px 20px 16px;
    border-bottom: 1px solid #f1f5f9;
}

.app-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-icon {
    background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%);
    padding: 8px;
    border-radius: 12px;
}

.brand-icon svg {
    width: 28px;
    height: 28px;
}

.brand-text {
    display: flex;
    flex-direction: column;
}

.brand-name {
    font-weight: 700;
    font-size: 16px;
    color: #2d3748;
    letter-spacing: 0.3px;
}

.brand-location {
    font-size: 11px;
    color: #718096;
    margin-top: 2px;
}

/* User Card */
.user-card {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8fafc;
    margin: 16px 16px 8px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.user-avatar {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    color: white;
    text-transform: uppercase;
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.2);
}

.user-details {
    flex: 1;
}

.user-fullname {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #2d3748;
    margin-bottom: 4px;
}

.user-badge {
    display: inline-block;
    background: #e9ecef;
    color: #4a5568;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
    text-transform: capitalize;
}

/* Navigation Sections */
.nav-section {
    margin-bottom: 8px;
}

.nav-label {
    padding: 16px 20px 8px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #718096;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    padding: 2px 12px;
}

.nav-item a, .nav-item button {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    color: #4a5568;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.2s;
    width: 100%;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.nav-item a:hover {
    background: #f7fafc;
    color: #2d3748;
}

.nav-item.active a {
    background: #ebf4ff;
    color: #667eea;
}

.nav-icon {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    color: currentColor;
    flex-shrink: 0;
}

.nav-icon svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
}

.nav-text {
    flex: 1;
    text-align: left;
}

/* Logout Item */
.logout-item {
    margin-top: 8px;
    border-top: 1px solid #edf2f7;
    padding-top: 8px;
}

.logout-item button {
    color: #e53e3e;
}

.logout-item button:hover {
    background: #fff5f5;
    color: #c53030;
}

/* Sidebar Footer */
.sidebar-footer {
    margin-top: auto;
    padding: 16px 0 20px;
}

.system-version {
    padding: 12px 20px 0;
    font-size: 11px;
    color: #a0aec0;
    text-align: center;
    border-top: 1px solid #edf2f7;
    margin-top: 8px;
}

/* Custom Scrollbar */
.sidebar::-webkit-scrollbar {
    width: 4px;
}

.sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .user-card {
        margin: 8px 12px;
    }

    .nav-item {
        padding: 2px 8px;
    }
}
</style>
