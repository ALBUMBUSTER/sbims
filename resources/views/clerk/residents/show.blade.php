@extends('layouts.app')

@section('title', 'Resident Details')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Resident Details</h1>
            <p>View resident information</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clerk.residents.index') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Residents
            </a>
        </div>
    </div>

    <div class="resident-profile">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                @if($resident->profile_photo)
                    <img src="{{ asset('storage/' . $resident->profile_photo) }}" alt="{{ $resident->full_name }}">
                @else
                    <div class="avatar-placeholder">
                        {{ strtoupper(substr($resident->first_name, 0, 1) . substr($resident->last_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="profile-title">
                <h2>{{ $resident->full_name }}</h2>
                <p class="resident-id-badge">ID: {{ $resident->resident_id }}</p>
                <div class="profile-status">
                    @if($resident->is_voter) <span class="status-tag voter">Registered Voter</span> @endif
                    @if($resident->is_senior) <span class="status-tag senior">Senior Citizen</span> @endif
                    @if($resident->is_pwd) <span class="status-tag pwd">PWD</span> @endif
                    @if($resident->is_4ps) <span class="status-tag fourps">4Ps Member</span> @endif
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="info-section">
            <h3><i class="fas fa-user"></i> Personal Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>First Name</label>
                    <p>{{ $resident->first_name }}</p>
                </div>
                <div class="info-item">
                    <label>Middle Name</label>
                    <p>{{ $resident->middle_name ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Last Name</label>
                    <p>{{ $resident->last_name }}</p>
                </div>
                <div class="info-item">
                    <label>Suffix</label>
                    <p>{{ $resident->suffix ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Birthdate</label>
                    <p>{{ $resident->birthdate ? $resident->birthdate->format('F d, Y') : 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Age</label>
                    <p>{{ $resident->age ?? 'N/A' }} years old</p>
                </div>
                <div class="info-item">
                    <label>Gender</label>
                    <p>{{ $resident->gender }}</p>
                </div>
                <div class="info-item">
                    <label>Civil Status</label>
                    <p>{{ $resident->civil_status }}</p>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="info-section">
            <h3><i class="fas fa-address-book"></i> Contact Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Address</label>
                    <p>{{ $resident->address ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Purok</label>
                    <p>Purok {{ $resident->purok ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Household Number</label>
                    <p>{{ $resident->household_number ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Contact Number</label>
                    <p>{{ $resident->contact_number ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <p>{{ $resident->email ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        @if($resident->is_pwd || $resident->is_4ps)
        <div class="info-section">
            <h3><i class="fas fa-clipboard-list"></i> Additional Information</h3>
            <div class="info-grid">
                @if($resident->is_pwd)
                <div class="info-item">
                    <label>PWD ID Number</label>
                    <p>{{ $resident->pwd_id ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Disability Type</label>
                    <p>{{ $resident->disability_type ?? 'N/A' }}</p>
                </div>
                @endif
                @if($resident->is_4ps)
                <div class="info-item">
                    <label>4Ps ID Number</label>
                    <p>{{ $resident->{'4ps_id'} ?? 'N/A' }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Emergency Contact -->
        @if($resident->emergency_contact_name || $resident->emergency_contact_number)
        <div class="info-section">
            <h3><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Contact Person</label>
                    <p>{{ $resident->emergency_contact_name ?? 'N/A' }}</p>
                </div>
                <div class="info-item">
                    <label>Contact Number</label>
                    <p>{{ $resident->emergency_contact_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions for Clerk -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="{{ route('clerk.certificates.create', ['resident_id' => $resident->id]) }}" class="btn-primary">
                    <i class="fas fa-file-medical"></i>
                    Issue Certificate
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

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #eef2ff;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-primary:hover {
    opacity: 0.9;
}

.resident-profile {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid white;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    color: white;
}

.profile-title h2 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.resident-id-badge {
    background: rgba(255,255,255,0.2);
    display: inline-block;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.profile-status {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.status-tag {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    background: rgba(255,255,255,0.2);
    color: white;
}

.info-section {
    padding: 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.info-section:last-child {
    border-bottom: none;
}

.info-section h3 {
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-section h3 i {
    color: #667eea;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item label {
    display: block;
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 0.3rem;
}

.info-item p {
    color: #333;
    font-size: 1rem;
    font-weight: 500;
}

.quick-actions {
    padding: 2rem;
    background: #f8fafc;
}

.quick-actions h3 {
    color: #333;
    margin-bottom: 1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .profile-status {
        justify-content: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-primary {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush
