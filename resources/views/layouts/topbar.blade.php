@php
    use App\Models\Notification;
    $notification_count = auth()->check() ? Notification::where('user_id', auth()->id())->where('is_read', false)->count() : 0;
    $unread_notifications = []; // Keep this empty for now since we're loading via AJAX
@endphp
<header class="header">
    <div class="logo-container">
        <div class="logo">
            <img src="{{ asset('assets/img/logo..png') }}" alt="BL Logo" class="logo-img">
        </div>

        <div class="system-title">
            <h1>SBIMS-PRO</h1>
            <p>Brgy. Libertad, Isabel, Leyte</p>
        </div>
    </div>

    <div class="user-menu">
        <!-- Notification Dropdown (Keep your existing structure) -->
        <div class="notification-dropdown">
            <div class="notification-icon" id="notificationIcon">
                <span>🔔</span>
                @if($notification_count > 0)
                    <span class="notification-badge" id="notificationBadge">{{ $notification_count }}</span>
                @endif
            </div>
            <div class="notification-panel" id="notificationPanel">
                <div class="notification-header">
                    <h4>Notifications</h4>
                    @if($notification_count > 0)
                        <button class="mark-all-read" onclick="markAllNotificationsAsRead()">Mark all as read</button>
                    @endif
                </div>
                <div class="notification-list" id="notificationList">
                    <div class="no-notifications">
                        <p>Loading notifications...</p>
                    </div>
                </div>
                <div class="notification-footer">
                    <a href="{{ route('notifications.index') }}" class="view-all-link">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- User Profile (Keep your existing structure) -->
        <div class="user-profile">
            <span>{{ auth()->user()->full_name ?? auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</span>
            <div class="user-dropdown">
                {{-- <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; padding: 0;">
                        Logout
                    </button>
                </form> --}}
            </div>
        </div>
    </div>
</header>

<!-- Custom Warning Modal -->
<div id="notificationConfirmModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Confirm Action</h3>
        </div>
        <div class="modal-body" id="modalMessage">
            Are you sure you want to proceed?
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel" onclick="closeModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="modal-btn confirm" id="notificationConfirmModal">
                <i class="fas fa-check"></i> Confirm
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="toast-container"></div>

<style>
/* Keep ALL your existing styles exactly as they are */
.logo-img {
    height: 50px;
    width: auto;
    display: block;
}

.notification-dropdown {
    position: relative;
}

.notification-panel {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    display: none;
    z-index: 1000;
    margin-top: 10px;
}

.notification-panel.show {
    display: block;
}

.notification-header {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h4 {
    margin: 0;
    color: #333;
}

.mark-all-read {
    background: none;
    border: none;
    color: #667eea;
    cursor: pointer;
    font-size: 0.9rem;
    padding: 0.3rem 0.5rem;
    border-radius: 4px;
}

.mark-all-read:hover {
    background: #eef2ff;
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
    cursor: pointer;
    position: relative;
}

.notification-item:hover {
    background: #f8fafc;
}

.notification-item.unread {
    background: #f0f9ff;
}

.notification-item.unread:hover {
    background: #e6f2ff;
}

.notification-type {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.8rem;
    margin-top: 0.5rem;
    flex-shrink: 0;
}

.type-info { background: #3b82f6; }
.type-warning { background: #f59e0b; }
.type-success { background: #10b981; }
.type-danger { background: #ef4444; }

.notification-content {
    flex: 1;
}

.notification-content strong {
    display: block;
    color: #333;
    font-size: 0.95rem;
    margin-bottom: 0.3rem;
}

.notification-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
}

.notification-content small {
    color: #94a3b8;
    font-size: 0.8rem;
    margin-top: 0.3rem;
    display: block;
}

.notification-actions {
    display: flex;
    gap: 5px;
    margin-left: 10px;
    flex-shrink: 0;
}

.notification-action-btn {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    border: none;
    background: transparent;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.notification-action-btn:hover {
    background: #e2e8f0;
}

.notification-action-btn.mark-read:hover {
    color: #10b981;
}

.notification-action-btn.delete:hover {
    color: #ef4444;
}

/* Enhanced View All Link */
.view-all-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.view-all-link:hover {
    background: #eef2ff;
    transform: translateY(-1px);
}

.view-all-link i {
    margin-right: 5px;
}

.no-notifications {
    padding: 2rem;
    text-align: center;
    color: #666;
}

.notification-footer {
    padding: 0.8rem 1rem;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.notification-footer a {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
}

.notification-footer a:hover {
    text-decoration: underline;
}

.notification-panel::before {
    content: '';
    position: absolute;
    top: -6px;
    right: 15px;
    width: 12px;
    height: 12px;
    background: white;
    transform: rotate(45deg);
    box-shadow: -2px -2px 5px rgba(0,0,0,0.05);
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
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.3s ease;
}

.custom-modal .modal-header {
    padding: 1.5rem 1.5rem 0.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.custom-modal .modal-header i {
    font-size: 2rem;
    color: #f59e0b;
}

.custom-modal .modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.3rem;
    font-weight: 600;
}

.custom-modal .modal-body {
    padding: 1rem 1.5rem;
    color: #666;
    font-size: 1rem;
    line-height: 1.5;
}

.custom-modal .modal-footer {
    padding: 1.5rem;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.custom-modal .modal-btn {
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    border: none;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
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
    background: #667eea;
    color: white;
}

.custom-modal .modal-btn.confirm:hover {
    background: #5a67d8;
}

/* Toast Container */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10001;
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
    color: #333;
    font-size: 0.95rem;
}

.toast-message {
    color: #666;
    font-size: 0.85rem;
}

.toast-close {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 1.2rem;
    padding: 0 5px;
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
</style>

<script>
// Modal functionality
let currentAction = null;
let currentId = null;

function showModal(message, action, id) {
    const modal = document.getElementById('notificationConfirmModal');
    const modalMessage = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('notificationConfirmModal');

    modalMessage.textContent = message;
    currentAction = action;
    currentId = id;

    modal.classList.add('show');

    // Remove any existing event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    // Add new event listener
    newConfirmBtn.addEventListener('click', function() {
        if (currentAction === 'delete') {
            deleteNotification(currentId);
        } else if (currentAction === 'clearAll') {
            clearAllNotifications();
        }
        closeModal();
    });
}

function closeModal() {
    const modal = document.getElementById('notificationConfirmModal');
    modal.classList.remove('show');
    currentAction = null;
    currentId = null;
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('notificationConfirmModal');
    if (event.target === modal) {
        closeModal();
    }
});

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
    toast.offsetHeight; // Force reflow

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

// Notification functionality
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationPanel = document.getElementById('notificationPanel');

    if (notificationIcon && notificationPanel) {
        // Toggle notification panel
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationPanel.classList.toggle('show');
            if (notificationPanel.classList.contains('show')) {
                loadNotifications();
            }
        });

        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationPanel.contains(e.target) && !notificationIcon.contains(e.target)) {
                notificationPanel.classList.remove('show');
            }
        });
    }

    // Load notifications on page load
    loadNotifications();

    // Load notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    // Get current user ID from Laravel
const userId = {{ auth()->user()->id }};

// Initialize Echo for real-time notifications
if (typeof Echo !== 'undefined') {
    console.log('Echo initialized, listening for notifications for user:', userId);

    Echo.private('notifications.' + userId)
        .listen('.notification.received', (e) => {
            console.log('Real-time notification received:', e);

            // Update notification badge
            const badge = document.getElementById('notificationBadge');
            let currentCount = badge ? parseInt(badge.textContent) || 0 : 0;
            currentCount++;

            if (badge) {
                badge.textContent = currentCount;
                badge.style.display = 'inline';
            } else {
                // Create badge if it doesn't exist
                const icon = document.querySelector('.notification-icon');
                const newBadge = document.createElement('span');
                newBadge.className = 'notification-badge';
                newBadge.id = 'notificationBadge';
                newBadge.textContent = '1';
                icon.appendChild(newBadge);
            }

            // Show toast notification (if you have a toast function)
            if (typeof showToast === 'function') {
                showToast(e.title, e.message, e.type);
            } else {
                // Fallback alert
                console.log('New notification:', e.title, e.message);
            }

            // Update notification list if panel is open
            const panel = document.getElementById('notificationPanel');
            if (panel && panel.classList.contains('show')) {
                // Reload notifications if you have a loadNotifications function
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            }
        });
} else {
    console.error('Echo is not defined. Make sure Laravel Echo is properly installed.');
}
});

function loadNotifications() {
    fetch('/notifications/recent')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.unread_count);
            updateNotificationList(data.notifications);
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    const notificationIcon = document.querySelector('.notification-icon span:first-child');

    if (count > 0) {
        if (badge) {
            badge.textContent = count;
        } else {
            // Create badge if it doesn't exist
            const newBadge = document.createElement('span');
            newBadge.className = 'notification-badge';
            newBadge.id = 'notificationBadge';
            newBadge.textContent = count;
            notificationIcon.parentNode.appendChild(newBadge);
        }
    } else {
        if (badge) {
            badge.remove();
        }
    }
}

function updateNotificationList(notifications) {
    const list = document.getElementById('notificationList');
    const header = document.querySelector('.notification-header');
    const markAllBtn = header?.querySelector('.mark-all-read');

    if (!list) return;

    let html = '';

    if (notifications.length > 0) {
        notifications.forEach(notification => {
            const isUnread = !notification.is_read;
            const displayLink = notification.link && notification.link !== '#' ? notification.link : '#';

            html += `
                <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="notification-type type-${notification.type}"></div>
                    <div class="notification-content" onclick="handleNotificationClick(${notification.id}, '${displayLink}')">
                        <strong>${notification.title}</strong>
                        <p>${notification.message}</p>
                        <small>${notification.time_ago}</small>
                    </div>
                    <div class="notification-actions">
                        ${!notification.is_read ? `
                            <button class="notification-action-btn mark-read" onclick="event.stopPropagation(); markAsRead(${notification.id})" title="Mark as read">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        <button class="notification-action-btn delete" onclick="event.stopPropagation(); showModal('Are you sure you want to delete this notification?', 'delete', ${notification.id})" title="Delete">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        // Show mark all button
        if (markAllBtn) markAllBtn.style.display = 'inline-block';
    } else {
        html = `
            <div class="no-notifications">
                <p>No new notifications</p>
            </div>
        `;
        // Hide mark all button
        if (markAllBtn) markAllBtn.style.display = 'none';
    }

    list.innerHTML = html;
}

function handleNotificationClick(id, link) {
    // Mark as read first
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(() => {
        loadNotifications();
        // Redirect if link exists and not '#'
        if (link && link !== '#') {
            window.location.href = link;
        }
    })
    .catch(error => console.error('Error marking as read:', error));
}

function markAsRead(id) {
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
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking as read:', error);
        showToast('Error', 'Failed to mark notification as read', 'error');
    });
}

function deleteNotification(id) {
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
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error deleting notification:', error);
        showToast('Error', 'Failed to delete notification', 'error');
    });
}

function markAllNotificationsAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'All notifications marked as read', 'success', 5000);
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
        showToast('Error', 'Failed to mark all as read', 'error', 5000);
    });
}

function clearAllNotifications() {
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
            loadNotifications();
            // Also reload if on notifications page
            if (window.location.pathname.includes('/notifications')) {
                setTimeout(() => location.reload(), 1000);
            }
        }
    })
    .catch(error => {
        console.error('Error clearing notifications:', error);
        showToast('Error', 'Failed to clear notifications', 'error', 5000);
    });
}
</script>
