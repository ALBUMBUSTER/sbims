<div class="notification-wrapper" x-data="notificationDropdown()" x-init="init()">
    <button class="notification-btn" @click="toggle()">
        <i class="fas fa-bell"></i>
        <span class="notification-badge" x-show="unreadCount > 0" x-text="unreadCount"></span>
    </button>

    <div class="notification-dropdown" x-show="open" @click.away="open = false" x-cloak>
        <div class="notification-header">
            <h3>Notifications</h3>
            <div class="notification-actions">
                <button class="mark-all-read" @click="markAllAsRead()" x-show="unreadCount > 0" title="Mark all as read">
                    <i class="fas fa-check-double"></i>
                </button>
                <button class="clear-all" @click="clearAll()" title="Clear all">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

        <div class="notification-list">
            <template x-for="notification in notifications" :key="notification.id">
                <div class="notification-item" :class="{ 'unread': !notification.is_read }">
                    <a :href="notification.link || '#'" @click.prevent="markAsRead(notification)">
                        <div class="notification-icon" :style="{ background: notification.color + '20', color: notification.color }">
                            <i :class="notification.icon"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title" x-text="notification.title"></div>
                            <div class="notification-message" x-text="notification.message"></div>
                            <div class="notification-time" x-text="notification.time_ago"></div>
                        </div>
                        <button class="notification-delete" @click.stop="deleteNotification(notification.id)">
                            <i class="fas fa-times"></i>
                        </button>
                    </a>
                </div>
            </template>

            <div class="notification-empty" x-show="notifications.length === 0">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications</p>
            </div>
        </div>

        <div class="notification-footer">
            <a href="/modules/notifications/index.php" class="view-all">
                View All Notifications
            </a>
        </div>
    </div>
</div>

<script>
function notificationDropdown() {
    return {
        open: false,
        notifications: [],
        unreadCount: <?php echo isset($unreadNotificationsCount) ? $unreadNotificationsCount : 0; ?>,

        init() {
            this.fetchNotifications();
            // Poll for new notifications every 30 seconds
            setInterval(() => this.fetchNotifications(), 30000);
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        fetchNotifications() {
            fetch('/modules/notifications/get_recent.php')
                .then(response => response.json())
                .then(data => {
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                })
                .catch(error => console.error('Error fetching notifications:', error));
        },

        markAsRead(notification) {
            if (!notification.is_read) {
                fetch('/modules/notifications/mark_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + notification.id
                })
                .then(response => response.json())
                .then(() => {
                    notification.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);

                    if (notification.link) {
                        window.location.href = notification.link;
                    }
                })
                .catch(error => console.error('Error marking as read:', error));
            } else if (notification.link) {
                window.location.href = notification.link;
            }
        },

        markAllAsRead() {
            fetch('/modules/notifications/mark_all_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(() => {
                this.notifications.forEach(n => n.is_read = true);
                this.unreadCount = 0;
            })
            .catch(error => console.error('Error marking all as read:', error));
        },

        deleteNotification(id) {
            if (confirm('Delete this notification?')) {
                fetch('/modules/notifications/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                    this.unreadCount = this.notifications.filter(n => !n.is_read).length;
                })
                .catch(error => console.error('Error deleting notification:', error));
            }
        },

        clearAll() {
            if (confirm('Delete all notifications?')) {
                fetch('/modules/notifications/clear_all.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(() => {
                    this.notifications = [];
                    this.unreadCount = 0;
                })
                .catch(error => console.error('Error clearing notifications:', error));
            }
        }
    }
}
</script>

<style>
.notification-wrapper {
    position: relative;
    display: inline-block;
}

.notification-btn {
    background: none;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    position: relative;
    color: #6b7280;
    font-size: 1.2rem;
    transition: color 0.2s;
}

.notification-btn:hover {
    color: #4361ee;
}

.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #ef4444;
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: -10px;
    width: 380px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    margin-top: 10px;
    z-index: 1000;
    overflow: hidden;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
}

.notification-header h3 {
    margin: 0;
    font-size: 1rem;
    color: #1f2937;
    font-weight: 600;
}

.notification-actions {
    display: flex;
    gap: 8px;
}

.notification-actions button {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.notification-actions button:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.notification-item.unread {
    background: #f0f9ff;
}

.notification-item:hover {
    background: #f9fafb;
}

.notification-item a {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px 20px;
    text-decoration: none;
    color: inherit;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 3px;
    font-size: 0.95rem;
}

.notification-message {
    color: #6b7280;
    font-size: 0.85rem;
    margin-bottom: 5px;
    line-height: 1.4;
    word-break: break-word;
}

.notification-time {
    color: #9ca3af;
    font-size: 0.75rem;
}

.notification-delete {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    opacity: 0;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.notification-item:hover .notification-delete {
    opacity: 1;
}

.notification-delete:hover {
    color: #ef4444;
    background: #fee2e2;
}

.notification-empty {
    padding: 40px 20px;
    text-align: center;
    color: #9ca3af;
}

.notification-empty i {
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.5;
}

.notification-empty p {
    margin: 0;
    font-size: 0.95rem;
}

.notification-footer {
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
    background: #f9fafb;
}

.view-all {
    color: #4361ee;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.2s;
}

.view-all:hover {
    color: #1f2937;
    text-decoration: underline;
}

[x-cloak] {
    display: none !important;
}

@media (max-width: 480px) {
    .notification-dropdown {
        position: fixed;
    top: 60px;
        right: 10px;
        left: 10px;
        width: auto;
        max-width: none;
    }
}

/* Table styles from your old system */
tr.unread {
    background-color: #f0f9ff;
    font-weight: 500;
}

tr.read {
    opacity: 0.7;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-primary {
    background-color: #4361ee;
    color: white;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}
</style>
