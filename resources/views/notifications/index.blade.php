@extends('layouts.app')

@section('title', 'All Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header with improved button placement -->
            <div class="notifications-header">
                <div class="header-left">
                    <a href="{{ route('dashboard') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <div class="header-title">
                        <i class="fas fa-bell"></i>
                        <h1>All Notifications</h1>
                    </div>
                </div>
                <div class="header-right">
                    <button type="button" class="btn-clear-all" onclick="showClearAllModal()">
                        <i class="fas fa-trash-alt"></i>
                        <span>Clear All</span>
                    </button>
                </div>
            </div>

            <!-- Main Card -->
            <div class="notifications-card">
                <div class="card-body">
                    <div id="notificationsContainer">
                        @include('notifications.partials.list', ['notifications' => $notifications])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Warning Modal for Single Delete -->
<div id="deleteModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i>
            <h3 id="deleteModalTitle">Delete Notification</h3>
        </div>
        <div class="modal-body" id="deleteModalMessage">
            Are you sure you want to delete this notification? This action cannot be undone.
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="modal-btn confirm" style="background: #dc2626;" id="confirmDeleteBtn">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Custom Warning Modal for Clear All -->
<div id="clearAllModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i>
            <h3>Clear All Notifications</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to clear all notifications?</p>
            <p class="text-danger"><strong>This action cannot be undone!</strong></p>
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel" onclick="closeClearAllModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="modal-btn confirm" style="background: #dc2626;" id="confirmClearAllBtn">
                <i class="fas fa-trash-alt"></i> Clear All
            </button>
        </div>
    </div>
</div>

<!-- Custom Warning Modal for Mark as Read (Optional - if you want warning for mark as read) -->
<div id="markReadModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
            <h3>Mark as Read</h3>
        </div>
        <div class="modal-body" id="markReadModalMessage">
            Mark this notification as read?
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel" onclick="closeMarkReadModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="modal-btn confirm" style="background: #3b82f6;" id="confirmMarkReadBtn">
                <i class="fas fa-check"></i> Mark as Read
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="toast-container"></div>

<style>
/* Reset margins for Laravel layout */
.container-fluid {
    padding: 1.5rem;
    background: #f8fafc;
    width: 100%;
}

/* Header Styles */
.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    width: 100%;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.btn-back {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.3s;
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-back i {
    font-size: 0.9rem;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-title i {
    font-size: 1.5rem;
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    padding: 0.6rem;
    border-radius: 10px;
}

.header-title h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.btn-clear-all {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background: white;
    color: #dc2626;
    border: 1px solid #dc2626;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-clear-all:hover {
    background: #dc2626;
    color: white;
}

/* Main Card */
.notifications-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    overflow: hidden;
    width: 100%;
}

.card-body {
    padding: 1.5rem;
}

/* Table Styles */
.table-primary {
    background-color: #f0f9ff !important;
}

.badge {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
}

.btn-group .btn {
    margin: 0 2px;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #64748b;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem;
    background: #f8fafc;
}

.table td {
    vertical-align: middle;
    padding: 1rem;
    color: #1e293b;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
    gap: 0.25rem;
}

.pagination .page-item {
    list-style: none;
}

.pagination .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    border-radius: 6px;
    background: white;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination .page-link:hover {
    background: #f1f5f9;
    border-color: #667eea;
}

.pagination .active .page-link {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* Custom Modal Styles */
.custom-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.custom-modal.show {
    display: flex;
}

.custom-modal .modal-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.3s ease;
}

.custom-modal .modal-header {
    padding: 1.5rem 1.5rem 0.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.custom-modal .modal-header i {
    font-size: 2rem;
}

.custom-modal .modal-header h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 600;
}

.custom-modal .modal-body {
    padding: 1rem 1.5rem;
    color: #64748b;
    font-size: 1rem;
    line-height: 1.5;
}

.custom-modal .modal-body .text-danger {
    color: #dc2626;
    font-weight: 600;
    margin-top: 0.5rem;
}

.custom-modal .modal-footer {
    padding: 1.5rem;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.custom-modal .modal-btn {
    padding: 0.7rem 1.4rem;
    border-radius: 8px;
    border: none;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.custom-modal .modal-btn.cancel {
    background: #f1f5f9;
    color: #64748b;
}

.custom-modal .modal-btn.cancel:hover {
    background: #e2e8f0;
}

.custom-modal .modal-btn.confirm {
    color: white;
}

.custom-modal .modal-btn.confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* Toast Container */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    min-width: 300px;
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    border-left: 4px solid;
}

.toast.success { border-left-color: #10b981; }
.toast.error { border-left-color: #ef4444; }
.toast.info { border-left-color: #3b82f6; }
.toast.warning { border-left-color: #f59e0b; }

.toast-icon {
    font-size: 1.2rem;
}

.toast.success .toast-icon { color: #10b981; }
.toast.error .toast-icon { color: #ef4444; }
.toast.info .toast-icon { color: #3b82f6; }
.toast.warning .toast-icon { color: #f59e0b; }

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
}

.toast-message {
    color: #64748b;
    font-size: 0.85rem;
}

.toast-close {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 1.2rem;
    padding: 0 5px;
    transition: color 0.2s;
}

.toast-close:hover {
    color: #666;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }

    .notifications-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .header-left {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        width: 100%;
    }

    .btn-back {
        width: 100%;
        justify-content: center;
    }

    .header-title {
        width: 100%;
        justify-content: flex-start;
    }

    .btn-clear-all {
        width: 100%;
        justify-content: center;
    }

    .table-responsive {
        overflow-x: auto;
    }
}
</style>
@endsection

@push('scripts')
<script>
// Disable browser notifications
if ('Notification' in window) {
    window.Notification = {
        permission: 'denied',
        requestPermission: function() {
            return Promise.resolve('denied');
        }
    };
}

// Toast notification system
function showToast(title, message, type = 'info', duration = 5000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const icons = {
        success: '✓',
        error: '✗',
        warning: '⚠',
        info: 'ℹ'
    };

    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || icons.info}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;

    container.appendChild(toast);
    toast.offsetHeight;

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, duration);
}

// ==================== DELETE MODAL FUNCTIONS ====================
function showDeleteModal(id) {
    currentDeleteId = id;
    const modal = document.getElementById('deleteModal');
    modal.classList.add('show');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('show');
    currentDeleteId = null;
}

// ==================== CLEAR ALL MODAL FUNCTIONS ====================
function showClearAllModal() {
    const modal = document.getElementById('clearAllModal');
    modal.classList.add('show');
}

function closeClearAllModal() {
    const modal = document.getElementById('clearAllModal');
    modal.classList.remove('show');
}

// ==================== MARK READ MODAL FUNCTIONS ====================
function showMarkReadModal(id) {
    currentMarkReadId = id;
    const modal = document.getElementById('markReadModal');
    const messageEl = document.getElementById('markReadModalMessage');
    messageEl.textContent = 'Mark this notification as read?';
    modal.classList.add('show');
}

function closeMarkReadModal() {
    const modal = document.getElementById('markReadModal');
    modal.classList.remove('show');
    currentMarkReadId = null;
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const deleteModal = document.getElementById('deleteModal');
    const clearAllModal = document.getElementById('clearAllModal');
    const markReadModal = document.getElementById('markReadModal');

    if (event.target === deleteModal) {
        closeDeleteModal();
    }
    if (event.target === clearAllModal) {
        closeClearAllModal();
    }
    if (event.target === markReadModal) {
        closeMarkReadModal();
    }
});

// ==================== ACTION FUNCTIONS ====================

// Mark as read function with UI update
function markAsRead(id) {
    // Find the row
    const row = document.querySelector(`tr[data-id="${id}"]`);

    // Show loading state
    const markBtn = row?.querySelector('.btn-primary');
    if (markBtn) {
        const originalHtml = markBtn.innerHTML;
        markBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        markBtn.disabled = true;
    }

    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Notification marked as read', 'success');

            // Update UI immediately
            if (row) {
                // Remove unread styling
                row.classList.remove('table-primary');

                // Update status badge
                const statusCell = row.querySelector('td:first-child .badge');
                if (statusCell) {
                    statusCell.className = 'badge bg-secondary';
                    statusCell.textContent = 'Read';
                }

                // Remove the mark as read button
                const actionsCell = row.querySelector('td:last-child .btn-group');
                if (actionsCell) {
                    const markReadBtn = actionsCell.querySelector('.btn-primary');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                }
            }
        }
    })
    .catch(error => {
        console.error('Error marking as read:', error);
        showToast('Error', 'Failed to mark notification as read', 'error');

        // Reset button if error
        if (markBtn) {
            markBtn.innerHTML = '<i class="fas fa-check"></i>';
            markBtn.disabled = false;
        }
    });
}

// Delete notification function
function deleteNotification(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);

    // Show loading state on delete button
    const deleteBtn = row?.querySelector('.btn-danger');
    if (deleteBtn) {
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;
    }

    fetch(`/notifications/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Notification deleted', 'success');

            // Remove row with animation
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';

                setTimeout(() => {
                    row.remove();

                    // Check if table is empty
                    if (document.querySelectorAll('tbody tr').length === 0) {
                        // Show empty state
                        const container = document.getElementById('notificationsContainer');
                        if (container) {
                            container.innerHTML = `
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <h4>No Notifications</h4>
                                    <p class="text-muted">You don't have any notifications yet.</p>
                                </div>
                            `;
                        }
                    }
                }, 300);
            }
        }
    })
    .catch(error => {
        console.error('Error deleting notification:', error);
        showToast('Error', 'Failed to delete notification', 'error');

        // Reset button if error
        if (deleteBtn) {
            deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
            deleteBtn.disabled = false;
        }
    });
}

// Clear all notifications function
function clearAllNotifications() {
    const clearBtn = document.querySelector('.btn-clear-all');

    // Show loading state
    if (clearBtn) {
        clearBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
        clearBtn.disabled = true;
    }

    fetch('/notifications/clear-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'All notifications cleared', 'success', 5000);

            // Update UI immediately
            const container = document.getElementById('notificationsContainer');
            if (container) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h4>No Notifications</h4>
                        <p class="text-muted">You don't have any notifications yet.</p>
                    </div>
                `;
            }
        }
    })
    .catch(error => {
        console.error('Error clearing notifications:', error);
        showToast('Error', 'Failed to clear notifications', 'error', 5000);
    })
    .finally(() => {
        // Reset button
        if (clearBtn) {
            clearBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Clear All';
            clearBtn.disabled = false;
        }
    });
}

// ==================== EVENT LISTENERS ====================
document.addEventListener('DOMContentLoaded', function() {
    // Add data-id attribute to each row
    document.querySelectorAll('tbody tr').forEach(row => {
        // Try to get ID from the delete form
        const deleteForm = row.querySelector('form[action*="delete"]');
        if (deleteForm) {
            const matches = deleteForm.action.match(/\/(\d+)$/);
            if (matches && matches[1]) {
                const id = matches[1];
                row.setAttribute('data-id', id);
            }
        }
    });

    // Override mark as read form submissions
    document.querySelectorAll('form[action*="read"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const matches = this.action.match(/\/(\d+)\/read/);
            if (matches && matches[1]) {
                showMarkReadModal(matches[1]);
            }
        });
    });

    // Override delete form submissions
    document.querySelectorAll('form[action*="delete"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const matches = this.action.match(/\/(\d+)$/);
            if (matches && matches[1]) {
                showDeleteModal(matches[1]);
            }
        });
    });

    // Confirm Delete Button
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (currentDeleteId) {
            deleteNotification(currentDeleteId);
            closeDeleteModal();
        }
    });

    // Confirm Clear All Button
    document.getElementById('confirmClearAllBtn')?.addEventListener('click', function() {
        clearAllNotifications();
        closeClearAllModal();
    });

    // Confirm Mark Read Button
    document.getElementById('confirmMarkReadBtn')?.addEventListener('click', function() {
        if (currentMarkReadId) {
            markAsRead(currentMarkReadId);
            closeMarkReadModal();
        }
    });

    // Override clear all button
    const clearAllBtn = document.querySelector('.btn-clear-all');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showClearAllModal();
        });
    }
});
</script>
@endpush
