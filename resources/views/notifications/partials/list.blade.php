@if($notifications->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Notification</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $notification)
                <tr class="{{ !$notification->is_read ? 'table-primary' : '' }}" data-id="{{ $notification->id }}">
                    <td>
                        @if(!$notification->is_read)
                            <span class="badge bg-primary">Unread</span>
                        @else
                            <span class="badge bg-secondary">Read</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $notification->title }}</strong>
                        <p class="mb-0 text-muted">{{ $notification->message }}</p>
                        @if($notification->link && $notification->link != '#')
                            <a href="{{ $notification->link }}" class="btn btn-sm btn-link p-0">View Details</a>
                        @endif
                    </td>
                    <td>
                        @php
                            $badgeClass = match($notification->type) {
                                'success' => 'bg-success',
                                'warning' => 'bg-warning',
                                'danger' => 'bg-danger',
                                default => 'bg-info'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($notification->type) }}
                        </span>
                    </td>
                    <td>
                        {{ $notification->created_at->format('M d, Y h:i A') }}<br>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </td>
                    <td>
                        <div class="action-buttons">
                            @if(!$notification->is_read)
                            <button type="button" class="action-btn mark-read" onclick="markAsRead({{ $notification->id }})" title="Mark as read">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            @endif
                            <button type="button" class="action-btn delete" onclick="showDeleteModal({{ $notification->id }})" title="Delete notification">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
        <h4>No Notifications</h4>
        <p class="text-muted">You don't have any notifications yet.</p>
    </div>
@endif

<style>
.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: transparent;
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.3s, height 0.3s;
}

.action-btn:hover::before {
    width: 100px;
    height: 100px;
}

.action-btn i {
    font-size: 1.2rem;
    position: relative;
    z-index: 1;
}

/* Mark as Read Button - Green Theme */
.action-btn.mark-read {
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
}

.action-btn.mark-read:hover {
    background: #10b981;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.action-btn.mark-read:active {
    transform: translateY(0);
}

/* Delete Button - Red Theme */
.action-btn.delete {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}

.action-btn.delete:hover {
    background: #ef4444;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.action-btn.delete:active {
    transform: translateY(0);
}

/* Loading state for buttons */
.action-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.action-btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Disabled state */
.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Tooltip styles */
.action-btn {
    position: relative;
}

.action-btn:hover::after {
    content: attr(title);
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    color: white;
    font-size: 0.7rem;
    padding: 4px 8px;
    border-radius: 4px;
    white-space: nowrap;
    z-index: 10;
    pointer-events: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Hide ripple when showing tooltip */
.action-btn:hover::before {
    display: none;
}

/* Responsive */
@media (max-width: 768px) {
    .action-buttons {
        gap: 4px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
    }

    .action-btn i {
        font-size: 1rem;
    }

    .action-btn:hover::after {
        display: none; /* Hide tooltips on mobile */
    }
}
</style>

<script>
// Add loading states to action buttons
document.addEventListener('DOMContentLoaded', function() {
    // This will be handled by the existing functions in the parent view
    // We're just adding visual feedback here
});

// Optional: Add loading state when mark as read is clicked
function markAsReadWithLoading(id) {
    const button = event.currentTarget;
    const originalHtml = button.innerHTML;

    // Show loading state
    button.classList.add('loading');
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    // Call the original function
    markAsRead(id);

    // Note: The original markAsRead function should handle resetting the button
    // If it fails, you may want to add error handling to reset it
}
</script>
