@extends('layouts.app')

@section('title', 'Resident Details')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Resident Details</h1>
            <p>Resident ID: {{ $resident->resident_id ?? 'N/A' }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to List
            </a>

            @if(!$resident->is_deceased)
            <a href="{{ route('secretary.residents.mark-deceased-form', $resident) }}" class="btn-deceased">
                <i class="fas fa-cross"></i>
                Mark as Deceased
            </a>
            @else
            {{-- <form action="{{ route('secretary.residents.undo-deceased', $resident) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-undo-deceased" onclick="return confirm('Are you sure you want to remove the deceased status? This action will restore the resident to active status.')">
                    <i class="fas fa-undo-alt"></i>
                    Undo Deceased Status
                </button>
            </form> --}}
            @endif

            <a href="{{ route('secretary.residents.edit', $resident) }}" class="btn-primary">
                <x-heroicon-o-pencil class="icon-small" />
                Edit Resident
            </a>
        </div>
    </div>

    <div class="details-grid">
        <!-- Personal Information -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-user class="detail-icon" />
                <h3>Personal Information</h3>
            </div>
            <div class="detail-body">
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value">
                        {{ $resident->first_name ?? '' }} {{ $resident->last_name ?? '' }}
                        @if($resident->middle_name)
                            <span class="middle-name">{{ $resident->middle_name[0] }}.</span>
                        @endif
                        @if($resident->suffix)
                            <span class="suffix">{{ $resident->suffix }}</span>
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Birth Date:</span>
                    <span class="detail-value">
                        @if($resident->birthdate)
                            {{ \Carbon\Carbon::parse($resident->birthdate)->format('F d, Y') }}
                            <span class="age-detail">({{ $resident->age }} years old)</span>
                        @else
                            <span class="text-muted">Not provided</span>
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Gender:</span>
                    <span class="detail-value">{{ $resident->gender ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Civil Status:</span>
                    <span class="detail-value">{{ $resident->civil_status ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Purok:</span>
                    <span class="detail-value">Purok {{ $resident->purok ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Household #:</span>
                    <span class="detail-value">{{ $resident->household_number ?? 'N/A' }}</span>
                </div>
                @if($resident->address)
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $resident->address }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Contact Information -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-phone class="detail-icon" />
                <h3>Contact Information</h3>
            </div>
            <div class="detail-body">
                <div class="detail-row">
                    <span class="detail-label">Contact Number:</span>
                    <span class="detail-value">{{ $resident->contact_number ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email Address:</span>
                    <span class="detail-value">{{ $resident->email ?? 'N/A' }}</span>
                </div>
                @if($resident->emergency_contact_name || $resident->emergency_contact_number)
                <div class="detail-row">
                    <span class="detail-label">Emergency Contact:</span>
                    <span class="detail-value">
                        @if($resident->emergency_contact_name)
                            <strong>{{ $resident->emergency_contact_name }}</strong>
                        @endif
                        @if($resident->emergency_contact_number)
                            <span class="contact-detail">({{ $resident->emergency_contact_number }})</span>
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Family Information -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-heart class="detail-icon" />
                <h3>Family Information</h3>
            </div>
            <div class="detail-body">
                @if($resident->civil_status === 'Married' && $resident->spouse)
                    <div class="detail-row">
                        <span class="detail-label">Spouse:</span>
                        <span class="detail-value">
                            <strong>{{ $resident->spouse->full_name }}</strong>
                            @if($resident->spouse->birthdate)
                                <span class="child-detail">(Born: {{ \Carbon\Carbon::parse($resident->spouse->birthdate)->format('M d, Y') }})</span>
                            @endif
                        </span>
                    </div>
                @endif

                @if($resident->children && $resident->children->count() > 0)
                    <div class="detail-row">
                        <span class="detail-label">Children:</span>
                        <div class="detail-value">
                            @foreach($resident->children as $child)
                                <div class="child-info">
                                    <i class="fas fa-child"></i> {{ $child->full_name }}
                                    @if($child->birthdate)
                                        <span class="child-detail">(Born: {{ \Carbon\Carbon::parse($child->birthdate)->format('M d, Y') }})</span>
                                    @endif
                                    @if($child->gender)
                                        <span class="child-detail">- {{ $child->gender }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if((!$resident->spouse || $resident->civil_status !== 'Married') && (!$resident->children || $resident->children->count() == 0))
                    <div class="detail-row">
                        <span class="detail-label"></span>
                        <span class="detail-value text-muted">No family information recorded</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Information -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-check-badge class="detail-icon" />
                <h3>Status Information</h3>
            </div>
            <div class="detail-body">
                <div class="status-badges-large">
                    @if($resident->is_deceased)
                    <div class="status-item">
                        <span class="badge badge-deceased">D</span>
                        <span>Deceased</span>
                        @if($resident->death_date)
                            <span class="badge-detail">(Died: {{ \Carbon\Carbon::parse($resident->death_date)->format('M d, Y') }})</span>
                        @endif
                    </div>
                    @endif

                    @if($resident->is_voter)
                        <div class="status-item">
                            <span class="badge badge-voter">V</span>
                            <span>Registered Voter</span>
                        </div>
                    @endif
                    @if($resident->is_senior)
                        <div class="status-item">
                            <span class="badge badge-senior">S</span>
                            <span>Senior Citizen</span>
                        </div>
                    @endif
                    @if($resident->is_pwd)
                        <div class="status-item">
                            <span class="badge badge-pwd">P</span>
                            <span>Person with Disability</span>
                            @if($resident->pwd_id)
                                <span class="badge-detail">(ID: {{ $resident->pwd_id }})</span>
                            @endif
                            @if($resident->disability_type)
                                <span class="badge-detail">- {{ $resident->disability_type }}</span>
                            @endif
                        </div>
                    @endif
                    @if($resident->is_4ps)
                        <div class="status-item">
                            <span class="badge badge-4ps">4Ps</span>
                            <span>4Ps Member</span>
                        </div>
                    @endif
                    @if(!$resident->is_deceased && !$resident->is_voter && !$resident->is_senior && !$resident->is_pwd && !$resident->is_4ps)
                        <div class="status-item">
                            <span class="text-muted">No special status</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Deceased Information Card - Only shows if resident is deceased -->
        @if($resident->is_deceased)
        <div class="detail-card deceased-card">
            <div class="detail-header">
                <i class="fas fa-cross detail-icon" style="color: #6b7280;"></i>
                <h3>Deceased Information</h3>
            </div>
            <div class="detail-body">
                <div class="detail-row">
                    <span class="detail-label">Date of Death:</span>
                    <span class="detail-value">
                        <strong>{{ $resident->death_date ? \Carbon\Carbon::parse($resident->death_date)->format('F d, Y') : 'N/A' }}</strong>
                        @if($resident->death_date)
<span class="age-detail">(Age at death: {{ (int) \Carbon\Carbon::parse($resident->birthdate)->diffInYears(\Carbon\Carbon::parse($resident->death_date)) }} years)</span>                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cause of Death:</span>
                    <span class="detail-value">{{ $resident->cause_of_death ?: 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Death Certificate No.:</span>
                    <span class="detail-value">{{ $resident->death_certificate_number ?: 'N/A' }}</span>
                </div>
                @if($resident->archived_reason && $resident->archived_reason !== 'Marked as deceased')
                <div class="detail-row">
                    <span class="detail-label">Archive Reason:</span>
                    <span class="detail-value">{{ $resident->archived_reason }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Archive Information - Shows if resident is archived -->
        @if($resident->archived_at && !$resident->is_deceased)
        <div class="detail-card archived-card">
            <div class="detail-header">
                <i class="fas fa-archive detail-icon" style="color: #f59e0b;"></i>
                <h3>Archive Information</h3>
            </div>
            <div class="detail-body">
                <div class="detail-row">
                    <span class="detail-label">Archived Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($resident->archived_at)->format('F d, Y h:i A') }}</span>
                </div>
                @if($resident->archived_reason)
                <div class="detail-row">
                    <span class="detail-label">Archive Reason:</span>
                    <span class="detail-value">{{ $resident->archived_reason }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- System Information -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-clock class="detail-icon" />
                <h3>System Information</h3>
            </div>
            <div class="detail-body">
                <div class="timeline">
                    @if($resident->created_at)
                    <div class="timeline-item">
                        <div class="timeline-date">{{ \Carbon\Carbon::parse($resident->created_at)->format('M d, Y h:i A') }}</div>
                        <div class="timeline-content">
                            <strong>Record Created</strong>
                            <span class="timeline-user">by {{ $resident->creator ? $resident->creator->name : 'System' }}</span>
                        </div>
                    </div>
                    @endif

                    @if($resident->updated_at && $resident->updated_at != $resident->created_at)
                    <div class="timeline-item">
                        <div class="timeline-date">{{ \Carbon\Carbon::parse($resident->updated_at)->format('M d, Y h:i A') }}</div>
                        <div class="timeline-content">
                            <strong>Last Updated</strong>
                        </div>
                    </div>
                    @endif

                    @if($resident->deleted_at && !$resident->is_deceased)
                    <div class="timeline-item">
                        <div class="timeline-date">{{ \Carbon\Carbon::parse($resident->deleted_at)->format('M d, Y h:i A') }}</div>
                        <div class="timeline-content">
                            <strong>Archived</strong>
                        </div>
                    </div>
                    @endif

                    @if($resident->deleted_at && $resident->is_deceased)
                    <div class="timeline-item">
                        <div class="timeline-date">{{ \Carbon\Carbon::parse($resident->deleted_at)->format('M d, Y h:i A') }}</div>
                        <div class="timeline-content">
                            <strong>Marked as Deceased & Archived</strong>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Deceased Card Styles */
.deceased-card {
    border-left: 4px solid #6b7280;
}

.deceased-card .detail-header {
    background: #f3f4f6;
}

.deceased-card .detail-header i,
.deceased-card .detail-icon {
    color: #6b7280;
}

/* Archived Card Styles */
.archived-card {
    border-left: 4px solid #f59e0b;
}

.archived-card .detail-header {
    background: #fef3c7;
}

.archived-card .detail-header i,
.archived-card .detail-icon {
    color: #f59e0b;
}

/* Button Styles */
.btn-deceased {
    background: #6b7280;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.btn-deceased:hover {
    background: #4b5563;
    color: white;
    transform: translateY(-1px);
}

.btn-undo-deceased {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.btn-undo-deceased:hover {
    background: #d97706;
    transform: translateY(-1px);
}

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
    color: white;
    transform: translateY(-1px);
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #eef2ff;
    transform: translateY(-1px);
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.detail-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-label {
    width: 140px;
    color: #666;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.detail-value {
    color: #333;
    font-weight: 500;
    flex: 1;
}

.middle-name, .suffix {
    color: #666;
    font-size: 0.9rem;
    margin-left: 0.25rem;
}

.age-detail {
    font-size: 0.85rem;
    color: #667eea;
    margin-left: 0.5rem;
    font-weight: normal;
}

.contact-detail {
    font-size: 0.85rem;
    color: #666;
    margin-left: 0.5rem;
}

.text-muted {
    color: #999;
    font-style: italic;
}

/* Child Information */
.child-info {
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.child-info:last-child {
    margin-bottom: 0;
}

.child-info i {
    color: #10b981;
    font-size: 0.9rem;
}

.child-detail {
    font-size: 0.8rem;
    color: #666;
    margin-left: 0.25rem;
    font-weight: normal;
}

.badge-detail {
    font-size: 0.75rem;
    color: #666;
    font-weight: normal;
    margin-left: 0.5rem;
}

/* Status Badges - Large version for show page */
.status-badges-large {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 1rem;
    font-weight: 600;
}

.badge-voter { background: #d4edda; color: #155724; }
.badge-senior { background: #cce5ff; color: #004085; }
.badge-pwd { background: #fff3cd; color: #856404; }
.badge-4ps {
    background: #e2d5f1;
    color: #553c9a;
    width: auto;
    padding: 0 12px;
    border-radius: 20px;
}
.badge-deceased {
    background: #6b7280;
    color: white;
    width: auto;
    padding: 0 12px;
    border-radius: 20px;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 1.5rem;
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
    left: -1.5rem;
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

.timeline-user {
    font-size: 0.8rem;
    color: #999;
    margin-left: 0.5rem;
}

.icon-small {
    width: 16px;
    height: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .details-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .page-actions {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .page-actions a,
    .page-actions form,
    .page-actions button {
        width: 100%;
        justify-content: center;
    }

    .detail-row {
        flex-direction: column;
    }

    .detail-label {
        width: 100%;
        margin-bottom: 0.25rem;
    }

    .child-info {
        flex-direction: column;
        align-items: flex-start;
    }

    .status-item {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Print Styles */
@media print {
    .page-actions,
    .btn-deceased,
    .btn-undo-deceased {
        display: none;
    }

    .detail-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Add Font Awesome if not already included
document.addEventListener('DOMContentLoaded', function() {
    // Check if Font Awesome is already loaded
    if (!document.querySelector('link[href*="font-awesome"]') && !document.querySelector('link[href*="fontawesome"]')) {
        const fontAwesome = document.createElement('link');
        fontAwesome.rel = 'stylesheet';
        fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
        document.head.appendChild(fontAwesome);
    }
});
</script>
@endpush
