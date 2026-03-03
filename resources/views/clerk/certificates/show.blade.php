@extends('layouts.app')

@section('title', 'Certificate Details')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Certificate Details</h1>
            <p>View certificate information</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clerk.certificates.index') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Certificates
            </a>
            <a href="{{ route('clerk.certificates.print', $certificate) }}" class="btn-primary" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Print Certificate
            </a>
        </div>
    </div>

    <div class="certificate-details">
        <!-- Status Banner -->
        <div class="status-banner status-{{ strtolower($certificate->status) }}">
            <div class="status-icon">
                @if($certificate->status == 'Pending')
                    <i class="fas fa-clock"></i>
                @elseif($certificate->status == 'Approved')
                    <i class="fas fa-check-circle"></i>
                @elseif($certificate->status == 'Released')
                    <i class="fas fa-check-double"></i>
                @elseif($certificate->status == 'Rejected')
                    <i class="fas fa-times-circle"></i>
                @endif
            </div>
            <div class="status-text">
                <h3>{{ $certificate->status }}</h3>
                <p>
                    @if($certificate->status == 'Pending')
                        Waiting for approval
                    @elseif($certificate->status == 'Approved')
                        Approved on {{ $certificate->approved_at ? $certificate->approved_at->format('M d, Y h:i A') : '' }}
                    @elseif($certificate->status == 'Released')
                        Released on {{ $certificate->released_at ? $certificate->released_at->format('M d, Y h:i A') : '' }}
                    @elseif($certificate->status == 'Rejected')
                        Rejected on {{ $certificate->rejected_at ? $certificate->rejected_at->format('M d, Y h:i A') : '' }}
                    @endif
                </p>
            </div>
        </div>

        <!-- Certificate Info -->
        <div class="info-grid">
            <!-- Left Column -->
            <div class="info-card">
                <h3><i class="fas fa-certificate"></i> Certificate Information</h3>
                <div class="info-list">
                    <div class="info-item">
                        <label>Certificate Number</label>
                        <p class="cert-number">{{ $certificate->certificate_id }}</p>
                    </div>
                    <div class="info-item">
                        <label>Type</label>
                        <p>{{ $certificate->certificate_type }}</p>
                    </div>
                    <div class="info-item">
                        <label>Purpose</label>
                        <p class="purpose-text">{{ $certificate->purpose }}</p>
                    </div>
                    <div class="info-item">
                        <label>Date Requested</label>
                        <p>{{ $certificate->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Resident Information</h3>
                <div class="info-list">
                    <div class="info-item">
                        <label>Full Name</label>
                        <p>{{ $certificate->resident->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item">
                        <label>Resident ID</label>
                        <p>{{ $certificate->resident->resident_id ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item">
                        <label>Address</label>
                        <p>{{ $certificate->resident->address ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item">
                        <label>Purok</label>
                        <p>Purok {{ $certificate->resident->purok ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item">
                        <label>Contact</label>
                        <p>{{ $certificate->resident->contact_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Processing Details -->
            <div class="info-card full-width">
                <h3><i class="fas fa-history"></i> Processing Details</h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-icon created">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>Certificate Created</h4>
                            <p>{{ $certificate->created_at->format('F d, Y h:i A') }}</p>
                            <small>by Clerk</small>
                        </div>
                    </div>

                    @if($certificate->approved_at)
                    <div class="timeline-item">
                        <div class="timeline-icon approved">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>Certificate Approved</h4>
                            <p>{{ $certificate->approved_at->format('F d, Y h:i A') }}</p>
                            @if($certificate->approver)
                                <small>by {{ $certificate->approver->name }}</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($certificate->released_at)
                    <div class="timeline-item">
                        <div class="timeline-icon released">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>Certificate Released</h4>
                            <p>{{ $certificate->released_at->format('F d, Y h:i A') }}</p>
                            @if($certificate->issuer)
                                <small>by {{ $certificate->issuer->name }}</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($certificate->rejected_at)
                    <div class="timeline-item">
                        <div class="timeline-icon rejected">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>Certificate Rejected</h4>
                            <p>{{ $certificate->rejected_at->format('F d, Y h:i A') }}</p>
                            <p class="rejection-reason"><strong>Reason:</strong> {{ $certificate->rejection_reason }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions for Clerk -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="{{ route('clerk.certificates.print', $certificate) }}" class="btn-primary" target="_blank">
                    <i class="fas fa-print"></i>
                    Print Certificate
                </a>
                <a href="{{ route('clerk.certificates.create', ['resident_id' => $certificate->resident_id]) }}" class="btn-secondary">
                    <i class="fas fa-plus"></i>
                    New Certificate for Same Resident
                </a>
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

.page-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    cursor: pointer;
    border: none;
    font-weight: 500;
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

.certificate-details {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.status-banner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem 2rem;
    color: white;
}

.status-banner.status-pending { background: #f59e0b; }
.status-banner.status-approved { background: #10b981; }
.status-banner.status-released { background: #3b82f6; }
.status-banner.status-rejected { background: #ef4444; }

.status-icon i {
    font-size: 2.5rem;
}

.status-text h3 {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.status-text p {
    opacity: 0.9;
    font-size: 0.95rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
}

.info-card {
    background: #f8fafc;
    border-radius: 8px;
    padding: 1.5rem;
}

.info-card.full-width {
    grid-column: 1 / -1;
}

.info-card h3 {
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
}

.info-card h3 i {
    color: #667eea;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item label {
    color: #666;
    font-size: 0.85rem;
    font-weight: 500;
}

.info-item p {
    color: #333;
    font-size: 1rem;
    font-weight: 500;
}

.cert-number {
    font-family: monospace;
    font-size: 1.1rem;
    color: #667eea;
}

.purpose-text {
    background: white;
    padding: 0.75rem;
    border-radius: 5px;
    border: 1px solid #e2e8f0;
    font-weight: normal !important;
    line-height: 1.5;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 3rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: -3rem;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 1;
}

.timeline-icon.created { background: #667eea; }
.timeline-icon.approved { background: #10b981; }
.timeline-icon.released { background: #3b82f6; }
.timeline-icon.rejected { background: #ef4444; }

.timeline-icon i {
    font-size: 1rem;
}

.timeline-content {
    background: white;
    padding: 1rem;
    border-radius: 5px;
    border: 1px solid #e2e8f0;
}

.timeline-content h4 {
    color: #333;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.timeline-content p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.timeline-content small {
    color: #999;
    font-size: 0.8rem;
}

.rejection-reason {
    color: #ef4444 !important;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed #e2e8f0;
}

.quick-actions {
    padding: 2rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.quick-actions h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
    }

    .status-banner {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }

    .timeline {
        padding-left: 2rem;
    }

    .timeline-icon {
        left: -2rem;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-primary, .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush
