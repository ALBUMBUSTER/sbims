@php
    use App\Models\Notification;
    $notification_count = auth()->check() ? Notification::where('user_id', auth()->id())->where('is_read', false)->count() : 0;
    $unread_notifications = []; // Keep this empty for now since we're loading via AJAX
@endphp
<header class="header">
    <div class="logo-container">
        <div class="logo">
            <img src="{{ asset('assets/img/logo1.png') }}" alt="BL Logo" class="logo-img">
        </div>

        <div class="system-title">
            <h1>SBIMS-PRO</h1>
            <p>Brgy. Libertad, Isabel, Leyte</p>
        </div>
    </div>

    <div class="user-menu">
        <!-- Notification Dropdown (simplified for now) -->
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
                    @if(count($unread_notifications) > 0)
                        @foreach($unread_notifications as $notification)
                        <div class="notification-item">
                            <div class="notification-type type-{{ $notification['type'] ?? 'info' }}"></div>
                            <div class="notification-content">
                                <strong>{{ $notification['title'] ?? 'Notification' }}</strong>
                                <p>{{ $notification['message'] ?? 'No message' }}</p>
                                <small>{{ $notification['time'] ?? 'Just now' }}</small>
                            </div>
                            @if(isset($notification['link']))
                                <a href="{{ $notification['link'] }}" class="notification-link">View</a>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="no-notifications">
                            <p>No new notifications</p>
                        </div>
                    @endif
                </div>
                <div class="notification-footer">
<a href="{{ route('notifications.index') }}">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="user-profile">
            <span>{{ auth()->user()->full_name }} ({{ ucfirst(auth()->user()->role) }})</span>
            <div class="user-dropdown">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; padding: 0;">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<style>
.logo-img {
    height: 70px;
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
}

.notification-item:hover {
    background: #f8fafc;
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

.notification-link {
    background: #667eea;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 4px;
    font-size: 0.8rem;
    text-decoration: none;
    margin-left: 0.5rem;
    flex-shrink: 0;
}

.notification-link:hover {
    background: #5a6fd8;
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
</style>

<script>
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
            html += `
                <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="notification-type type-${notification.type}"></div>
                    <div class="notification-content" onclick="markAsRead(${notification.id}, '${notification.link}')">
                        <strong>${notification.title}</strong>
                        <p>${notification.message}</p>
                        <small>${notification.time_ago}</small>
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

function markAsRead(id, link) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(() => {
        // Reload notifications
        loadNotifications();

        // Redirect if link exists and not '#'
        if (link && link !== '#') {
            window.location.href = link;
        }
    })
    .catch(error => console.error('Error marking as read:', error));
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
    .then(() => {
        loadNotifications();
    })
    .catch(error => console.error('Error marking all as read:', error));
}
</script>
