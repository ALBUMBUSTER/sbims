@extends('layouts.app')

@section('title', 'Certificate Details')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Certificate Details</h1>
            <p>Certificate #: {{ $certificate->certificate_id }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.certificates.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to List
            </a>
            <a href="{{ route('secretary.certificates.edit', $certificate) }}" class="btn-primary">
                <x-heroicon-o-pencil class="icon-small" />
                Edit
            </a>
            <a href="{{ route('secretary.certificates.print', $certificate) }}" class="btn-secondary" target="_blank">
                <x-heroicon-o-printer class="icon-small" />
                Print
            </a>
        </div>
    </div>

    <div class="details-container">
        <!-- Status Update Card -->
        <div class="status-card">
            <div class="status-header">
                <h3>Process Certificate</h3>
                <span class="status-badge status-{{ strtolower($certificate->status) }}">
                    {{ $certificate->status }}
                </span>
            </div>
            <div class="status-body">
                <form action="{{ route('secretary.certificates.process', $certificate) }}" method="POST" class="status-form">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Update Status</label>
                            <select name="status" id="status" class="form-control" required onchange="toggleRejectionField()">
                                <option value="Approved" {{ $certificate->status == 'Approved' ? 'selected' : '' }}>Approve</option>
                                <option value="Released" {{ $certificate->status == 'Released' ? 'selected' : '' }}>Release</option>
                                <option value="Rejected" {{ $certificate->status == 'Rejected' ? 'selected' : '' }}>Reject</option>
                            </select>
                        </div>

                        <div id="rejectionField" style="display: none; width: 100%;">
                            <div class="form-group full-width">
                                <label for="rejection_reason">Rejection Reason <span class="required">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn-update-status">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Certificate Details Grid -->
        <div class="details-grid">
            <!-- Resident Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <x-heroicon-o-user class="detail-icon" />
                    <h3>Resident Information</h3>
                </div>
                <div class="detail-body">
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">{{ $certificate->resident->first_name }} {{ $certificate->resident->last_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">{{ $certificate->resident->address }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value">{{ $certificate->resident->contact_number ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Birthdate:</span>
                        <span class="detail-value">{{ $certificate->resident->birthdate ? \Carbon\Carbon::parse($certificate->resident->birthdate)->format('F d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Certificate Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <x-heroicon-o-document-text class="detail-icon" />
                    <h3>Certificate Information</h3>
                </div>
                <div class="detail-body">
                    <div class="detail-row">
                        <span class="detail-label">Certificate #:</span>
                        <span class="detail-value">{{ $certificate->certificate_id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value">{{ $certificate->certificate_type }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Request Date:</span>
                        <span class="detail-value">{{ $certificate->created_at ? $certificate->created_at->format('F d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Purpose:</span>
                        <span class="detail-value">{{ $certificate->purpose }}</span>
                    </div>
                </div>
            </div>

            <!-- Certificate Details -->
            <div class="detail-card">
                <div class="detail-header">
                    <x-heroicon-o-information-circle class="detail-icon" />
                    <h3>Status Details</h3>
                </div>
                <div class="detail-body">
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge status-{{ strtolower($certificate->status) }}">
                                {{ $certificate->status }}
                            </span>
                        </span>
                    </div>
                    @if($certificate->approved_at)
                    <div class="detail-row">
                        <span class="detail-label">Approved Date:</span>
                        <span class="detail-value">{{ $certificate->approved_at->format('F d, Y h:i A') }}</span>
                    </div>
                    @endif
                    @if($certificate->approver)
                    <div class="detail-row">
                        <span class="detail-label">Approved By:</span>
                        <span class="detail-value">{{ $certificate->approver->name ?? 'Unknown' }}</span>
                    </div>
                    @endif
                    @if($certificate->released_at)
                    <div class="detail-row">
                        <span class="detail-label">Released Date:</span>
                        <span class="detail-value">{{ $certificate->released_at->format('F d, Y h:i A') }}</span>
                    </div>
                    @endif
                    @if($certificate->issuer)
                    <div class="detail-row">
                        <span class="detail-label">Issued By:</span>
                        <span class="detail-value">{{ $certificate->issuer->name ?? 'Unknown' }}</span>
                    </div>
                    @endif
                    @if($certificate->rejected_at)
                    <div class="detail-row">
                        <span class="detail-label">Rejected Date:</span>
                        <span class="detail-value">{{ $certificate->rejected_at->format('F d, Y h:i A') }}</span>
                    </div>
                    @endif
                    @if($certificate->rejection_reason)
                    <div class="detail-row">
                        <span class="detail-label">Rejection Reason:</span>
                        <span class="detail-value">{{ $certificate->rejection_reason }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="detail-card full-width">
                <div class="detail-header">
                    <x-heroicon-o-clock class="detail-icon" />
                    <h3>Timeline</h3>
                </div>
                <div class="detail-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $certificate->created_at ? $certificate->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                            <div class="timeline-content">
                                <strong>Certificate Created</strong> - Status: Pending
                            </div>
                        </div>
                        @if($certificate->approved_at)
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $certificate->approved_at->format('M d, Y h:i A') }}</div>
                            <div class="timeline-content">
                                <strong>Certificate Approved</strong>
                            </div>
                        </div>
                        @endif
                        @if($certificate->released_at)
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $certificate->released_at->format('M d, Y h:i A') }}</div>
                            <div class="timeline-content">
                                <strong>Certificate Released</strong>
                            </div>
                        </div>
                        @endif
                        @if($certificate->rejected_at)
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $certificate->rejected_at->format('M d, Y h:i A') }}</div>
                            <div class="timeline-content">
                                <strong>Certificate Rejected</strong> - {{ $certificate->rejection_reason }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.container-fluid {
    padding: 1.5rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.8rem;
}

.page-title p {
    color: #666;
    font-size: 1rem;
}

/* Buttons */
.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #eef2ff;
}

/* Status Card */
.status-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.status-header {
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-header h3 {
    color: #333;
    font-size: 1.1rem;
    margin: 0;
}

.status-body {
    padding: 1.5rem;
}

.status-form {
    width: 100%;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group.full-width {
    width: 100%;
    flex: 0 0 100%;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-size: 0.9rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.95rem;
}

.btn-update-status {
    padding: 0.5rem 2rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
    height: 38px;
    align-self: flex-end;
}

.btn-update-status:hover {
    background: #5a67d8;
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #cce5ff;
    color: #004085;
}

.status-released {
    background: #d4edda;
    color: #155724;
}

.status-rejected {
    background: #fee2e2;
    color: #dc2626;
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-card.full-width {
    grid-column: 1 / -1;
}

.detail-header {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-icon {
    width: 20px;
    height: 20px;
    color: #667eea;
}

.detail-header h3 {
    color: #333;
    font-size: 1rem;
    margin: 0;
}

.detail-body {
    padding: 1.5rem;
}

.detail-row {
    display: flex;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-label {
    width: 120px;
    color: #666;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.detail-value {
    color: #333;
    font-weight: 500;
    flex: 1;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -2rem;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #667eea;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #667eea;
}

.timeline-date {
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.timeline-content {
    color: #333;
}

.timeline-content strong {
    color: #667eea;
}

.icon-small {
    width: 16px;
    height: 16px;
}
</style>
@endpush

@push('scripts')
<script>
function toggleRejectionField() {
    const status = document.getElementById('status').value;
    const rejectionField = document.getElementById('rejectionField');
    const rejectionReason = document.getElementById('rejection_reason');

    if (status === 'Rejected') {
        rejectionField.style.display = 'block';
        if (rejectionReason) rejectionReason.required = true;
    } else {
        rejectionField.style.display = 'none';
        if (rejectionReason) rejectionReason.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleRejectionField();
});
</script>
@endpush
