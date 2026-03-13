@extends('layouts.app')

@section('title', 'Database Backup & Restore')

@push('styles')
<style>
    /* Toast Notification */
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

    .toast-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        justify-content: center;
    }

    .toast-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }

    .toast-icon.success {
        color: #10b981;
    }

    .toast-icon.error {
        color: #dc2626;
    }

    .toast-icon.info {
        color: #3b82f6;
    }

    @keyframes slideUp {
        from {
            transform: translate(-50%, 20px);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translate(-50%, 0);
        }
        to {
            opacity: 0;
            transform: translate(-50%, -10px);
        }
    }

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
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .backup-creation-card h3 i {
        color: #667eea;
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

    .warning-box i {
        font-size: 1.2rem;
        color: #d97706;
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

    .btn-backup i {
        font-size: 1rem;
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
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-header h3 i {
        color: #667eea;
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

    th i {
        margin-right: 0.5rem;
        color: #667eea;
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
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .backup-filename i {
        color: #667eea;
        font-size: 0.9rem;
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

    .btn-action i {
        font-size: 0.8rem;
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
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .schedule-card h3 i {
        color: #667eea;
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
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .form-group label i {
        color: #667eea;
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

    .text-muted {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.85rem;
    }

    /* Retention Info Box */
    .retention-info {
        background: #e8f5e9;
        border-left: 4px solid #10b981;
        padding: 0.8rem 1rem;
        border-radius: 4px;
        margin: 0.5rem 0 1rem 0;
        font-size: 0.85rem;
        color: #065f46;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .retention-info i {
        color: #10b981;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        max-width: 500px;
        width: 90%;
    }

    .modal-content h3 {
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-content h3.warning {
        color: #dc2626;
    }

    .modal-content h3 i {
        font-size: 1.2rem;
    }

    .modal-content p {
        margin-bottom: 1.5rem;
        color: #4b5563;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .modal-actions button {
        padding: 0.6rem 1.2rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .btn-cancel {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-cancel:hover {
        background: #d1d5db;
    }

    .btn-confirm {
        background: #dc2626;
        color: white;
    }

    .btn-confirm:hover {
        background: #b91c1c;
    }

    .btn-confirm i {
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<!-- Toast Notification -->
<div id="toast" class="toast">
    <div class="toast-content">
        <i class="fas fa-check-circle toast-icon success"></i>
        <span id="toastMessage">Operation completed successfully!</span>
    </div>
</div>

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

        <!-- Success/Error Messages from Session -->
        @if(session('success'))
            <script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));</script>
        @endif
        @if(session('error'))
            <script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
        @endif

        <!-- Create New Backup Section -->
        <div class="backup-creation-card">
            <h3><i class="fas fa-database"></i> Create New Backup</h3>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <p><strong>Important:</strong> Create regular backups to prevent data loss.</p>
            </div>

            <div class="backup-type-info">
                <p><i class="fas fa-info-circle"></i> Backups include all database tables and data.</p>
            </div>

            <div class="backup-actions">
                <form action="{{ route('admin.backups.create') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="type" value="database">
                    <button type="submit" class="btn-backup btn-backup-primary">
                        <i class="fas fa-save"></i> Create Backup Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Backup Schedule Settings -->
        <div class="schedule-card">
            <h3><i class="fas fa-clock"></i> Backup Schedule Settings</h3>

            @php
                $settings = app(\App\Services\BackupService::class)->getScheduleSettings();
            @endphp

            <form action="{{ route('admin.backups.schedule') }}" method="POST" id="scheduleForm">
                @csrf
                @method('PUT')

                <div class="schedule-form">
                    <div class="form-group">
                        <label for="schedule_type"><i class="fas fa-calendar"></i> Schedule Type</label>
                        <select id="schedule_type" name="schedule_type">
                            <option value="daily" {{ $settings['schedule_type'] == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $settings['schedule_type'] == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $settings['schedule_type'] == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="backup_time"><i class="fas fa-hourglass"></i> Backup Time</label>
                        <input type="time"
                               id="backup_time"
                               name="backup_time"
                               value="{{ $settings['backup_time'] }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="retention_days"><i class="fas fa-calendar-day"></i> Retention Period (Days)</label>
                        <input type="number"
                               id="retention_days"
                               name="retention_days"
                               value="{{ $settings['retention_days'] }}"
                               min="1" max="365"
                               required>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                        <label for="backup_enabled" style="margin-bottom: 0;">
                            <i class="fas fa-power-off"></i> Enable Scheduled Backups
                        </label>
                        <input type="checkbox"
                               id="backup_enabled"
                               name="backup_enabled"
                               value="1"
                               {{ $settings['backup_enabled'] ? 'checked' : '' }}
                               style="width: auto;">
                    </div>
                </div>
                <div class="schedule-info" style="background: #f0f9ff; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    @if($settings['next_backup_run'])
                        <p><i class="fas fa-clock"></i> <strong>Next scheduled backup:</strong> {{ \Carbon\Carbon::parse($settings['next_backup_run'])->format('M d, Y h:i A') }}</p>
                    @endif
                    @if($settings['last_backup_run'])
                        <p><i class="fas fa-history"></i> <strong>Last backup:</strong> {{ \Carbon\Carbon::parse($settings['last_backup_run'])->format('M d, Y h:i A') }}</p>
                    @endif
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-backup btn-backup-primary">
                        <i class="fas fa-save"></i> Save Schedule
                    </button>
                </div>
            </form>
        </div>

        <!-- Available Backups -->
        <div class="backups-table">
            <div class="table-header">
                <h3><i class="fas fa-archive"></i> Available Backups</h3>
                <div class="backup-count">{{ $backups->count() }} backup files</div>
            </div>

            @if($backups->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-file-archive"></i> Backup File</th>
                        <th><i class="fas fa-weight-hanging"></i> Size</th>
                        <th><i class="fas fa-calendar-alt"></i> Created</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr>
                        <td>
                            <div class="backup-file">
                                <div class="backup-filename">
                                    <i class="fas fa-database"></i> {{ $backup->filename ?? 'Unknown' }}
                                </div>
                            </div>
                        </td>
                        <td class="file-size">{{ $backup->formatted_size ?? '0 KB' }}</td>
                        <td class="file-date">{{ $backup->created_at->format('M d, Y h:i A') ?? 'Unknown' }}</td>
                       <td>
                            <div class="backup-actions-cell">
                                @if($backup->id)
                                <button type="button" class="btn-action btn-restore"
                                        onclick="confirmRestore('{{ $backup->id }}')">
                                    <i class="fas fa-undo-alt"></i> Restore
                                </button>

                                <a href="{{ route('admin.backups.download', $backup->id) }}"
                                   class="btn-action btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>

                                <button type="button" class="btn-action btn-delete"
                                        onclick="confirmDelete('{{ $backup->id }}')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-backups">
                <i class="fas fa-database fa-3x" style="color: #e2e8f0; margin-bottom: 1rem;"></i>
                <p>No backup files found. Create your first backup above.</p>
            </div>
            @endif
        </div>
    </main>
</div>

<!-- Restore Confirmation Modal -->
<div id="restoreModal" class="modal">
    <div class="modal-content">
        <h3 class="warning"><i class="fas fa-exclamation-triangle"></i> Restore Backup</h3>
        <p>
            <strong>Warning:</strong> Restoring will overwrite all current data with the backup data.
            This action cannot be undone. Are you sure you want to proceed?
        </p>
        <div class="modal-actions">
            <button type="button" onclick="hideRestoreModal()" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </button>
            <form id="restoreForm" method="POST" style="display: inline;">
                @csrf
                @method('POST')
                <button type="submit" class="btn-confirm" style="background: #059669;">
                    <i class="fas fa-check"></i> Yes, Restore Backup
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3 class="warning"><i class="fas fa-trash-alt"></i> Delete Backup</h3>
        <p>
            Are you sure you want to delete this backup file? This action cannot be undone.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="hideDeleteModal()" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-confirm">
                    <i class="fas fa-check"></i> Yes, Delete Backup
                </button>
            </form>
        </div>
    </div>
</div>

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
                toastIcon.classList.remove('error', 'info');
                toastIcon.classList.add('success');
            }
        } else if (type === 'error') {
            toast.style.borderLeftColor = '#dc2626';
            if (toastIcon) {
                toastIcon.classList.remove('success', 'info');
                toastIcon.classList.add('error');
            }
        } else {
            toast.style.borderLeftColor = '#3b82f6';
            if (toastIcon) {
                toastIcon.classList.remove('success', 'error');
                toastIcon.classList.add('info');
            }
        }

        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    let currentBackupId = null;

    function confirmRestore(backupId) {
        currentBackupId = backupId;
        document.getElementById('restoreForm').action = `/admin/backups/${backupId}/restore`;
        document.getElementById('restoreModal').classList.add('active');
    }

    function confirmDelete(backupId) {
        currentBackupId = backupId;
        document.getElementById('deleteForm').action = `/admin/backups/${backupId}`;
        document.getElementById('deleteModal').classList.add('active');
    }

    function hideRestoreModal() {
        document.getElementById('restoreModal').classList.remove('active');
        currentBackupId = null;
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        currentBackupId = null;
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

</script>
@endpush
@endsection
