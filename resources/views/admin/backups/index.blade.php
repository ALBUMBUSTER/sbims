@extends('layouts.app')

@section('title', 'Database Backup & Restore')

@push('styles')
<style>
    .backup-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 1.2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        text-align: center;
        border-left: 4px solid #667eea;
    }

    .stat-card h4 {
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 0.3rem;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #888;
    }

    .backup-creation-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        border: 2px solid #e2e8f0;
    }

    .backup-creation-card h3 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .warning-box {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .warning-box span {
        font-size: 1.2rem;
    }

    .warning-box p {
        color: #92400e;
        margin: 0;
        font-size: 0.95rem;
    }

    .backup-type-info {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .backup-type-info p {
        color: #0369a1;
        margin: 0;
        font-size: 0.9rem;
    }

    .backup-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-backup {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-backup-primary {
        background: #667eea;
        color: white;
    }

    .btn-backup-primary:hover {
        background: #5a67d8;
        transform: translateY(-1px);
    }

    .btn-backup-secondary {
        background: #edf2f7;
        color: #4a5568;
        border: 1px solid #cbd5e0;
    }

    .btn-backup-secondary:hover {
        background: #e2e8f0;
    }

    .backups-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .table-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .table-header h3 {
        color: #333;
        margin: 0;
    }

    .backup-count {
        font-size: 0.9rem;
        color: #666;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        padding: 1rem 1.5rem;
        text-align: left;
        background: #f8fafc;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }

    td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        color: #2d3748;
    }

    .backup-file {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .backup-filename {
        font-weight: 500;
        color: #2d3748;
    }

    .backup-path {
        font-size: 0.8rem;
        color: #718096;
        font-family: monospace;
    }

    .backup-actions-cell {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        font-size: 0.85rem;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-restore {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .btn-restore:hover {
        background: #a7f3d0;
    }

    .btn-download {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .btn-download:hover {
        background: #bfdbfe;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    .schedule-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .schedule-card h3 {
        color: #333;
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
    }

    .schedule-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #4a5568;
        font-size: 0.9rem;
    }

    .form-group select,
    .form-group input {
        width: 100%;
        padding: 0.6rem;
        border: 1px solid #cbd5e0;
        border-radius: 6px;
        font-size: 0.9rem;
        background: white;
    }

    .form-group input[type="time"] {
        padding: 0.5rem;
    }

    .form-actions {
        text-align: right;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .no-backups {
        text-align: center;
        padding: 3rem;
        color: #718096;
        font-style: italic;
    }

    .file-size {
        font-weight: 500;
        color: #4a5568;
    }

    .file-date {
        color: #718096;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Database Backup & Restore</h1>
                <p>Manage system backups and database restoration</p>
            </div>
        </div>

        <!-- Database Statistics -->
        <div class="backup-stats-grid">
            <div class="stat-card">
                <h4>Total Users</h4>
                <div class="stat-value">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">System Users</div>
            </div>
            <div class="stat-card">
                <h4>Total Residents</h4>
                <div class="stat-value">{{ $stats['total_residents'] ?? 0 }}</div>
                <div class="stat-label">Residents</div>
            </div>
            <div class="stat-card">
                <h4>Total Blotters</h4>
                <div class="stat-value">{{ $stats['total_blotters'] ?? 0 }}</div>
                <div class="stat-label">Blotter Cases</div>
            </div>
            <div class="stat-card">
                <h4>Total Certificates</h4>
                <div class="stat-value">{{ $stats['total_certificates'] ?? 0 }}</div>
                <div class="stat-label">Certificates</div>
            </div>
            <div class="stat-card">
                <h4>Total Logs</h4>
                <div class="stat-value">{{ $stats['total_logs'] ?? 0 }}</div>
                <div class="stat-label">Activity Logs</div>
            </div>
        </div>

        <!-- Create New Backup Section -->
        <div class="backup-creation-card">
            <h3>Create New Backup</h3>

            <div class="warning-box">
                <span>⚠️</span>
                <p><strong>Important:</strong> Create regular backups to prevent data loss.</p>
            </div>

            <div class="backup-type-info">
                <p>Backups include all database tables and data.</p>
            </div>

            <div class="backup-actions">
                <form action="{{ route('admin.backups.create') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="type" value="database">
                    <button type="submit" class="btn-backup btn-backup-primary">
                        <span>💾</span> Create Backup Now
                    </button>
                </form>

                <button type="button" class="btn-backup btn-backup-secondary" onclick="showScheduleModal()">
                    <span>⏰</span> Schedule Backup
                </button>
            </div>
        </div>

        <!-- Available Backups -->
        <div class="backups-table">
            <div class="table-header">
                <h3>Available Backups</h3>
                <div class="backup-count">{{ $backups->count() }} backup files</div>
            </div>

            @if($backups->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Backup File</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr>
                        <td>
                            <div class="backup-file">
                                <div class="backup-filename">{{ $backup->filename ?? 'Unknown' }}</div>
                                <div class="backup-path">{{ $backup->path ?? 'Unknown' }}</div>
                            </div>
                        </td>
                        <td class="file-size">{{ $backup->formatted_size ?? '0 KB' }}</td>
                        <td class="file-date">{{ $backup->created_at->format('M d, Y h:i A') ?? 'Unknown' }}</td>
                       <td>
    <div class="backup-actions-cell">
        @php
            // Safely get the backup ID
            $backupId = $backup->id ?? null;
        @endphp

        @if($backupId)
        <button type="button" class="btn-action btn-restore"
                onclick="confirmRestore('{{ $backupId }}')">
            <span>🔄</span> Restore
        </button>

        <a href="{{ route('admin.backups.download', $backupId) }}"
           class="btn-action btn-download">
            <span>📥</span> Download
        </a>

        <button type="button" class="btn-action btn-delete"
                onclick="confirmDelete('{{ $backupId }}')">
            <span>🗑️</span> Delete
        </button>
        @else
        <span class="text-muted" style="font-size: 0.85rem;">No actions available</span>
        @endif
    </div>
</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-backups">
                <p>No backup files found. Create your first backup above.</p>
            </div>
            @endif
        </div>

        <!-- Backup Schedule Settings -->
        <div class="schedule-card">
            <h3>Backup Schedule Settings</h3>

            <form action="{{ route('admin.backups.schedule') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="schedule-form">
                    <div class="form-group">
                        <label for="schedule_type">Schedule Type</label>
                        <select id="schedule_type" name="schedule_type">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="backup_time">Backup Time (Daily)</label>
                        <input type="time"
                               id="backup_time"
                               name="backup_time"
                               value="02:00"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="retention_days">Retention Period</label>
                        <input type="number"
                               id="retention_days"
                               name="retention_days"
                               value="30"
                               min="1" max="365"
                               required>
                        <small style="color: #666; font-size: 0.8rem;">days</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-backup btn-backup-primary">
                        <span>💾</span> Save Schedule
                    </button>
                </div>
            </form>
        </div>

        <!-- Last Backup Info -->
        @if(isset($stats['last_backup']))
        <div style="text-align: center; color: #666; font-size: 0.9rem; margin-top: 2rem; padding: 1rem; border-top: 1px solid #e2e8f0;">
            📊 <strong>Database Information:</strong>
            Last backup: {{ $stats['last_backup']->format('M d, Y h:i A') }}
        </div>
        @endif
    </main>
</div>

<!-- Restore Confirmation Modal -->
<div id="restoreModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 1rem; color: #dc2626;">⚠️ Restore Backup</h3>
        <p style="margin-bottom: 1.5rem; color: #4b5563;">
            <strong>Warning:</strong> Restoring will overwrite all current data with the backup data.
            This action cannot be undone. Are you sure you want to proceed?
        </p>
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="button" onclick="hideRestoreModal()" style="padding: 0.6rem 1.2rem; background: #e5e7eb; border: none; border-radius: 6px; cursor: pointer;">
                Cancel
            </button>
            <form id="restoreForm" method="POST" style="display: inline;">
                @csrf
                @method('POST')
                <button type="submit" style="padding: 0.6rem 1.2rem; background: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Yes, Restore Backup
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 1rem; color: #dc2626;">🗑️ Delete Backup</h3>
        <p style="margin-bottom: 1.5rem; color: #4b5563;">
            Are you sure you want to delete this backup file? This action cannot be undone.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="button" onclick="hideDeleteModal()" style="padding: 0.6rem 1.2rem; background: #e5e7eb; border: none; border-radius: 6px; cursor: pointer;">
                Cancel
            </button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" style="padding: 0.6rem 1.2rem; background: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Yes, Delete Backup
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentBackupId = null;

    function confirmRestore(backupId) {
        currentBackupId = backupId;
        document.getElementById('restoreForm').action = `/admin/backups/${backupId}/restore`;
        document.getElementById('restoreModal').style.display = 'flex';
    }

    function confirmDelete(backupId) {
        currentBackupId = backupId;
        document.getElementById('deleteForm').action = `/admin/backups/${backupId}`;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function hideRestoreModal() {
        document.getElementById('restoreModal').style.display = 'none';
        currentBackupId = null;
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        currentBackupId = null;
    }

    function showScheduleModal() {
        alert('Schedule backup functionality would be implemented here.\nFor now, use the schedule settings below.');
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.id === 'restoreModal') {
            hideRestoreModal();
        }
        if (event.target.id === 'deleteModal') {
            hideDeleteModal();
        }
    });

    // Set default time to 02:00 AM
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('backup_time').value = '02:00';
    });
</script>
@endpush
@endsection
