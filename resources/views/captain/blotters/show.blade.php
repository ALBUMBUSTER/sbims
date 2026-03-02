@extends('layouts.app')

@section('title', 'Blotter Case Details')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Blotter Case Details</h1>
            <p>Case #: <?php echo $blotter->blotter_number; ?></p>
        </div>
        <div class="page-actions">
            <a href="<?php echo route('captain.blotters.index'); ?>" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>
    </div>

    <div class="details-container">
        <!-- Status Update Card -->
        <div class="status-card">
            <div class="status-header">
                <h3><i class="fas fa-edit"></i> Update Case Status</h3>
                <span class="status-badge status-<?php echo strtolower($blotter->status); ?>">
                    <?php echo $blotter->status; ?>
                </span>
            </div>
            <div class="status-body">
                <form action="<?php echo route('captain.blotters.status', $blotter); ?>" method="POST" class="status-form">
                    <?php echo csrf_field(); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Change Status</label>
                            <select name="status" id="status" class="form-control" onchange="toggleResolution(this.value)">
                                <option value="Pending" <?php echo $blotter->status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Investigating" <?php echo $blotter->status == 'Investigating' ? 'selected' : ''; ?>>Investigating</option>
                                <option value="Hearings" <?php echo $blotter->status == 'Hearings' ? 'selected' : ''; ?>>Hearings</option>
                                <option value="Settled" <?php echo $blotter->status == 'Settled' ? 'selected' : ''; ?>>Settled</option>
                                <option value="Unsolved" <?php echo $blotter->status == 'Unsolved' ? 'selected' : ''; ?>>Unsolved</option>
                                <option value="Dismissed" <?php echo $blotter->status == 'Dismissed' ? 'selected' : ''; ?>>Dismissed</option>
                            </select>
                        </div>
                        <div id="resolution-field" style="display: <?php echo $blotter->status == 'Settled' ? 'block' : 'none'; ?>;">
                            <div class="form-group">
                                <label for="resolution">Resolution <span class="required">*</span></label>
                                <textarea name="resolution" id="resolution" class="form-control" rows="2"><?php echo $blotter->resolution; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="settlement_date">Settlement Date</label>
                                <input type="date" name="settlement_date" id="settlement_date" class="form-control" value="<?php echo $blotter->settlement_date ? $blotter->settlement_date->format('Y-m-d') : ''; ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn-update">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Case Details Grid -->
        <div class="details-grid">
            <!-- Complainant Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <i class="fas fa-user"></i>
                    <h3>Complainant Information</h3>
                </div>
                <div class="detail-body">
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo $blotter->complainant_name; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?php echo $blotter->complainant->address ?? 'N/A'; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value"><?php echo $blotter->complainant->contact_number ?? 'N/A'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Respondent Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <i class="fas fa-user-friends"></i>
                    <h3>Respondent Information</h3>
                </div>
                <div class="detail-body">
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo $blotter->respondent_name; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?php echo $blotter->respondent_address; ?></span>
                    </div>
                </div>
            </div>

            <!-- Incident Details -->
            <div class="detail-card full-width">
                <div class="detail-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Incident Details</h3>
                </div>
                <div class="detail-body">
                    <div class="details-info-grid">
                        <div class="detail-row">
                            <span class="detail-label">Incident Type:</span>
                            <span class="detail-value"><?php echo $blotter->incident_type; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Incident Date:</span>
                            <span class="detail-value"><?php echo \Carbon\Carbon::parse($blotter->incident_date)->format('F d, Y'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Incident Location:</span>
                            <span class="detail-value"><?php echo $blotter->incident_location; ?></span>
                        </div>
                        <div class="detail-row full-width">
                            <span class="detail-label">Complaint Details:</span>
                            <span class="detail-value"><?php echo nl2br($blotter->complaint_details); ?></span>
                        </div>
                        <?php if($blotter->witnesses): ?>
                        <div class="detail-row full-width">
                            <span class="detail-label">Witnesses:</span>
                            <span class="detail-value"><?php echo nl2br($blotter->witnesses); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Resolution Details (if settled) -->
            <?php if($blotter->status == 'Settled' && $blotter->resolution): ?>
            <div class="detail-card full-width">
                <div class="detail-header">
                    <i class="fas fa-check-circle success"></i>
                    <h3>Resolution Details</h3>
                </div>
                <div class="detail-body">
                    <div class="details-info-grid">
                        <div class="detail-row full-width">
                            <span class="detail-label">Resolution:</span>
                            <span class="detail-value"><?php echo nl2br($blotter->resolution); ?></span>
                        </div>
                        <?php if($blotter->settlement_date): ?>
                        <div class="detail-row">
                            <span class="detail-label">Settlement Date:</span>
                            <span class="detail-value"><?php echo \Carbon\Carbon::parse($blotter->settlement_date)->format('F d, Y'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Timeline -->
            <div class="detail-card full-width">
                <div class="detail-header">
                    <i class="fas fa-clock"></i>
                    <h3>Case Timeline</h3>
                </div>
                <div class="detail-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo \Carbon\Carbon::parse($blotter->created_at)->format('M d, Y h:i A'); ?></div>
                            <div class="timeline-content">
                                <strong>Case Filed</strong> - Initial blotter report filed
                            </div>
                        </div>
                        <?php if($blotter->status != 'Pending' || $blotter->updated_at != $blotter->created_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo \Carbon\Carbon::parse($blotter->updated_at)->format('M d, Y h:i A'); ?></div>
                            <div class="timeline-content">
                                <strong>Last Updated</strong> - Status: <?php echo $blotter->status; ?>
                            </div>
                        </div>
                        <?php endif; ?>
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-header h3 i {
    color: #667eea;
}

.status-body {
    padding: 1.5rem;
}

.status-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.form-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
    width: 100%;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-size: 0.9rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
}

.btn-update {
    padding: 0.5rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    height: 38px;
}

.btn-update:hover {
    background: #5a67d8;
}

.required {
    color: #dc2626;
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-investigating { background: #cce5ff; color: #004085; }
.status-hearings { background: #e2d5f1; color: #553c9a; }
.status-settled { background: #d4edda; color: #155724; }
.status-unsolved { background: #fee2e2; color: #dc2626; }
.status-dismissed { background: #e2e8f0; color: #4a5568; }

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
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

.detail-header i {
    color: #667eea;
    font-size: 1.2rem;
}

.detail-header i.success {
    color: #10b981;
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

.detail-row.full-width {
    flex-direction: column;
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
</style>
@endpush

@push('scripts')
<script>
function toggleResolution(status) {
    var resolutionField = document.getElementById('resolution-field');
    var resolution = document.getElementById('resolution');
    var settlementDate = document.getElementById('settlement_date');

    if (status === 'Settled') {
        resolutionField.style.display = 'block';
        resolution.required = true;
        settlementDate.required = true;
    } else {
        resolutionField.style.display = 'none';
        resolution.required = false;
        settlementDate.required = false;
    }
}
</script>
@endpush
