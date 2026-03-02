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
            <a href="<?php echo route('captain.residents.index'); ?>" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>
    </div>

    <div class="resident-profile">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-circle fa-5x"></i>
            </div>
            <div class="profile-title">
                <h2><?php echo $resident->first_name . ' ' . $resident->last_name; ?></h2>
                <p>Resident ID: <?php echo $resident->resident_id; ?></p>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="profile-details">
            <div class="details-grid">
                <!-- Personal Information -->
                <div class="details-card">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    <div class="details-list">
                        <div class="detail-item">
                            <span class="detail-label">First Name:</span>
                            <span class="detail-value"><?php echo $resident->first_name; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Last Name:</span>
                            <span class="detail-value"><?php echo $resident->last_name; ?></span>
                        </div>
                        <?php if($resident->middle_name): ?>
                        <div class="detail-item">
                            <span class="detail-label">Middle Name:</span>
                            <span class="detail-value"><?php echo $resident->middle_name; ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <span class="detail-label">Gender:</span>
                            <span class="detail-value"><?php echo $resident->gender; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Birthdate:</span>
                            <span class="detail-value">
                                <?php if($resident->birthdate): ?>
                                    <?php echo \Carbon\Carbon::parse($resident->birthdate)->format('F d, Y'); ?>
                                    (<?php echo \Carbon\Carbon::parse($resident->birthdate)->age; ?> years old)
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Civil Status:</span>
                            <span class="detail-value"><?php echo $resident->civil_status; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="details-card">
                    <h3><i class="fas fa-phone"></i> Contact Information</h3>
                    <div class="details-list">
                        <div class="detail-item">
                            <span class="detail-label">Contact Number:</span>
                            <span class="detail-value"><?php echo $resident->contact_number ?? 'N/A'; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo $resident->email ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="details-card">
                    <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
                    <div class="details-list">
                        <div class="detail-item">
                            <span class="detail-label">Address:</span>
                            <span class="detail-value"><?php echo $resident->address; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Purok:</span>
                            <span class="detail-value"><?php echo $resident->purok; ?></span>
                        </div>
                        <?php if($resident->household_number): ?>
                        <div class="detail-item">
                            <span class="detail-label">Household #:</span>
                            <span class="detail-value"><?php echo $resident->household_number; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Special Status -->
                <div class="details-card">
                    <h3><i class="fas fa-star"></i> Special Status</h3>
                    <div class="status-grid">
                        <?php if($resident->is_voter): ?>
                            <span class="status-tag voter">Registered Voter</span>
                        <?php endif; ?>
                        <?php if($resident->is_senior): ?>
                            <span class="status-tag senior">Senior Citizen</span>
                        <?php endif; ?>
                        <?php if($resident->is_pwd): ?>
                            <span class="status-tag pwd">PWD</span>
                        <?php endif; ?>
                        <?php if($resident->is_4ps): ?>
                            <span class="status-tag fourps">4Ps Member</span>
                        <?php endif; ?>
                        <?php if(!$resident->is_voter && !$resident->is_senior && !$resident->is_pwd && !$resident->is_4ps): ?>
                            <span class="no-status">No special status</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- PWD Information (if applicable) -->
                <?php if($resident->is_pwd): ?>
                <div class="details-card full-width">
                    <h3><i class="fas fa-wheelchair"></i> PWD Information</h3>
                    <div class="details-list">
                        <div class="detail-item">
                            <span class="detail-label">PWD ID Number:</span>
                            <span class="detail-value"><?php echo $resident->pwd_id ?? 'N/A'; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Disability Type:</span>
                            <span class="detail-value"><?php echo $resident->disability_type ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #eef2ff;
}

/* Profile */
.resident-profile {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
    color: white;
}

.profile-avatar i {
    color: white;
    opacity: 0.9;
}

.profile-title h2 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.profile-title p {
    opacity: 0.9;
}

.profile-details {
    padding: 2rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.details-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
}

.details-card h3 {
    color: #333;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #667eea;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.details-card h3 i {
    color: #667eea;
}

.details-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-item {
    display: flex;
    align-items: baseline;
}

.detail-label {
    width: 120px;
    color: #666;
    font-size: 0.9rem;
}

.detail-value {
    color: #333;
    font-weight: 500;
    flex: 1;
}

/* Status Grid */
.status-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.status-tag {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-tag.voter {
    background: #d4edda;
    color: #155724;
}

.status-tag.senior {
    background: #cce5ff;
    color: #004085;
}

.status-tag.pwd {
    background: #fff3cd;
    color: #856404;
}

.status-tag.fourps {
    background: #e2d5f1;
    color: #553c9a;
}

.no-status {
    color: #999;
    font-style: italic;
}

.full-width {
    grid-column: 1 / -1;
}
</style>
@endpush
