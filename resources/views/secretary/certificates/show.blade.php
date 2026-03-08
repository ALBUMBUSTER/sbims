@extends('layouts.app')

@section('title', 'Certificate Details')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Certificate Details</h1>
            <p>{{ $certificate->certificate_type }} - {{ $certificate->certificate_id }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.certificates.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Certificates
            </a>
            <!-- @if($certificate->status === 'Released')
            <a href="{{ route('secretary.certificates.print', $certificate) }}" class="btn-primary" target="_blank">
                <x-heroicon-o-printer class="icon-small" />
                Print Certificate
            </a> -->
            @endif
            <a href="{{ route('secretary.certificates.edit', $certificate) }}" class="btn-secondary">
                <x-heroicon-o-pencil class="icon-small" />
                Edit
            </a>
        </div>
    </div>

    <div class="certificate-details">
        <!-- Certificate Info Card -->
        <div class="info-card">
            <h3>Certificate Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Certificate #</label>
                    <p>{{ $certificate->certificate_id }}</p>
                </div>
                <div class="info-item">
                    <label>Type</label>
                    <p>{{ $certificate->certificate_type }}</p>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <p>
                        <span class="status-badge status-{{ strtolower($certificate->status) }}">
                            {{ $certificate->status }}
                        </span>
                    </p>
                </div>
                <div class="info-item">
                    <label>Request Date</label>
                    <p>{{ $certificate->created_at->format('F d, Y h:i A') }}</p>
                </div>
                <div class="info-item full-width">
                    <label>Purpose</label>
                    <p>{{ $certificate->purpose }}</p>
                </div>
            </div>
        </div>

        <!-- Resident Info Card -->
        <div class="info-card">
            <h3>Resident Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <p>{{ $certificate->resident->first_name }} {{ $certificate->resident->last_name }}</p>
                </div>
                <div class="info-item">
                    <label>Gender</label>
                    <p>{{ $certificate->resident->gender }}</p>
                </div>
                <div class="info-item">
                    <label>Civil Status</label>
                    <p>{{ $certificate->resident->civil_status ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Birthdate</label>
                    <p>{{ $certificate->resident->birthdate ? $certificate->resident->birthdate->format('F d, Y') : 'N/A' }}</p>
                </div>
                <div class="info-item full-width">
                    <label>Address</label>
                    <p>Purok {{ $certificate->resident->purok }}, {{ $certificate->resident->address }}</p>
                </div>
            </div>
        </div>

        <!-- Processing Info Card -->
        @if($certificate->approved_at || $certificate->released_at || $certificate->rejected_at)
        <div class="info-card">
            <h3>Processing Information</h3>
            <div class="info-grid">
                @if($certificate->approved_at)
                <div class="info-item">
                    <label>Approved Date</label>
                    <p>{{ $certificate->approved_at->format('F d, Y h:i A') }}</p>
                </div>
                <div class="info-item">
                    <label>Approved By</label>
                    <p>{{ $certificate->approver->name ?? 'N/A' }}</p>
                </div>
                @endif
                @if($certificate->released_at)
                <div class="info-item">
                    <label>Released Date</label>
                    <p>{{ $certificate->released_at->format('F d, Y h:i A') }}</p>
                </div>
                <div class="info-item">
                    <label>Released By</label>
                    <p>{{ $certificate->issuer->name ?? 'N/A' }}</p>
                </div>
                @endif
                @if($certificate->rejected_at)
                <div class="info-item full-width">
                    <label>Rejection Reason</label>
                    <p>{{ $certificate->rejection_reason }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
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

.icon-small {
    width: 16px;
    height: 16px;
}

.certificate-details {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-card h3 {
    color: #333;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #667eea;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    color: #666;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item p {
    color: #333;
    font-size: 1rem;
    font-weight: 500;
    margin: 0;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-approved { background: #cce5ff; color: #004085; }
.status-released { background: #d4edda; color: #155724; }
.status-rejected { background: #fee2e2; color: #dc2626; }

@media (max-width: 768px) {
    .page-actions {
        width: 100%;
    }

    .btn-primary, .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
