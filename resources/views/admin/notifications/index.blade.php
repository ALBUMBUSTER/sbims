@extends('layouts.app')

@section('title', 'All Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>All Notifications</h1>
                    <p>View and manage all your notifications</p>
                </div>
                <div class="page-actions">
                    <form action="{{ route('notifications.clear-all') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Clear all notifications?')">
                            <i class="fas fa-trash"></i> Clear All
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
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
                                    <tr class="{{ !$notification->is_read ? 'table-primary' : '' }}">
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
                                            @if($notification->link)
                                                <a href="{{ $notification->link }}" class="btn btn-sm btn-link p-0">View Details</a>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $notification->type === 'info' ? 'info' : ($notification->type === 'success' ? 'success' : ($notification->type === 'warning' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($notification->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $notification->created_at->format('M d, Y h:i A') }}<br>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if(!$notification->is_read)
                                                <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Mark as read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this notification?')" title="Delete">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
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
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-primary {
    background-color: #f0f9ff !important;
    font-weight: 500;
}

.badge {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
}

.btn-group .btn {
    margin: 0 2px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.page-title h1 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.page-title p {
    margin: 0;
    color: #6c757d;
}

.card {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.pagination {
    margin-bottom: 0;
}
</style>
@endsection
