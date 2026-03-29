@extends('layouts.app')

@section('title', 'Blotter Case Details')

@section('content')
<div class="container-fluid">
    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="toast-content">
            <x-heroicon-o-check-circle class="toast-icon success" />
            <span id="toastMessage">Status updated successfully!</span>
        </div>
    </div>

    <div class="page-header">
        <div class="page-title">
            <h1>Blotter Case Details</h1>
            <p>Case #: {{ $blotter->case_id ?? 'N/A' }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.blotter.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to List
            </a>
            @if(auth()->user()->role_id != 4)
            <a href="{{ route('secretary.blotter.edit', $blotter) }}" class="btn-primary">
                <x-heroicon-o-pencil class="icon-small" />
                Edit Case
            </a>
            @endif
        </div>
    </div>

    <div class="details-container">
<!-- Status Update Card -->
<div class="status-card">
    <div class="status-header">
        <h3>Case Status</h3>
        <span class="status-badge status-{{ strtolower($blotter->status) }}">
            {{ $blotter->status }}
        </span>
    </div>
    <div class="status-body">
        @php
            $isClerk = auth()->user()->role_id == 4;
        @endphp

        @if($isClerk)
            <div class="status-readonly">
                <div class="current-status-display">
                    <span class="status-label">Current Status:</span>
                    <span class="status-badge status-{{ strtolower($blotter->status) }}">
                        {{ $blotter->status }}
                    </span>
                </div>
                @if($blotter->status == 'Referred' && $blotter->referred_reason)
                <div class="referred-reason-display">
                    <span class="status-label">Referred Reason:</span>
                    <span class="referred-reason">{{ $blotter->referred_reason }}</span>
                </div>
                @endif
                @if($blotter->status == 'Settled' && $blotter->resolution)
                <div class="resolution-display">
                    <span class="status-label">Resolution:</span>
                    <span class="resolution-text">{{ $blotter->resolution }}</span>
                </div>
                @endif
                <div class="clerk-notice">
                    <i class="fas fa-info-circle"></i>
                    <span>Status cannot be changed by clerk. Please contact secretary for status updates.</span>
                </div>
            </div>
        @else
            <form action="{{ route('secretary.blotter.status', $blotter) }}" method="POST" class="status-form">
                @csrf
                @method('PATCH')

                <div class="status-update-container">
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="status" value="Pending"
                                   {{ $blotter->status == 'Pending' ? 'checked' : '' }}
                                   onchange="toggleFields(this.value)">
                            <span class="radio-label status-pending-radio">Pending</span>
                        </label>

                        <label class="radio-option">
                            <input type="radio" name="status" value="Ongoing"
                                   {{ $blotter->status == 'Ongoing' ? 'checked' : '' }}
                                   onchange="toggleFields(this.value)">
                            <span class="radio-label status-ongoing-radio">Ongoing</span>
                        </label>

                        <label class="radio-option">
                            <input type="radio" name="status" value="Settled"
                                   {{ $blotter->status == 'Settled' ? 'checked' : '' }}
                                   onchange="toggleFields(this.value)">
                            <span class="radio-label status-settled-radio">Settled</span>
                        </label>

                        <label class="radio-option">
                            <input type="radio" name="status" value="Referred"
                                   {{ $blotter->status == 'Referred' ? 'checked' : '' }}
                                   onchange="toggleFields(this.value)">
                            <span class="radio-label status-referred-radio">Referred</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-update-status">
                        <x-heroicon-o-check-circle class="icon-small" />
                        Update Status
                    </button>
                </div>

                <!-- Resolution Field (for Settled status) -->
                <div id="resolution-field" class="resolution-container {{ $blotter->status == 'Settled' ? '' : 'hidden' }}">
                    <div class="resolution-grid">
                        <div class="form-group">
                            <label for="resolution">Resolution <span class="required">*</span></label>
                            <textarea name="resolution" id="resolution" class="form-control" rows="3"
                                {{ $blotter->status == 'Settled' ? '' : 'disabled' }}>{{ $blotter->resolution }}</textarea>
                            <small class="form-text text-muted">Describe how the case was resolved</small>
                        </div>
                        <div class="form-group">
                            <label for="resolved_date">Resolution Date</label>
                            <input type="date" name="resolved_date" id="resolved_date" class="form-control"
                                   value="{{ $blotter->resolved_date ? $blotter->resolved_date->format('Y-m-d') : '' }}"
                                   {{ $blotter->status == 'Settled' ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>

                <!-- Referred Reason Field (for Referred status) -->
                <div id="referred-field" class="referred-container {{ $blotter->status == 'Referred' ? '' : 'hidden' }}">
                    <div class="referred-grid">
                        <div class="form-group full-width">
                            <label for="referred_reason">Reason for Referral <span class="required">*</span></label>
                            <textarea name="referred_reason" id="referred_reason" class="form-control" rows="4"
                                {{ $blotter->status == 'Referred' ? '' : 'disabled' }}>{{ $blotter->referred_reason }}</textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                Specify why this case is being referred (e.g., "Beyond barangay jurisdiction", "Requires court action", "Parties requested referral", etc.)
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="referred_to">Referred To</label>
                            <select name="referred_to" id="referred_to" class="form-control"
                                    {{ $blotter->status == 'Referred' ? '' : 'disabled' }}>
                                <option value="">Select where referred</option>
                                <option value="Court" {{ $blotter->referred_to == 'Court' ? 'selected' : '' }}>Court of Law</option>
                                <option value="Police" {{ $blotter->referred_to == 'Police' ? 'selected' : '' }}>Philippine National Police (PNP)</option>
                                <option value="Municipal" {{ $blotter->referred_to == 'Municipal' ? 'selected' : '' }}>Municipal Government</option>
                                <option value="DPC" {{ $blotter->referred_to == 'DPC' ? 'selected' : '' }}>Department of Public Counsel</option>
                                <option value="DSWD" {{ $blotter->referred_to == 'DSWD' ? 'selected' : '' }}>Department of Social Welfare and Development</option>
                                <option value="Other" {{ $blotter->referred_to == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group" id="other_referred_container" style="display: none;">
                            <label for="other_referred">Please specify</label>
                            <input type="text" name="other_referred" id="other_referred" class="form-control"
                                   value="{{ $blotter->referred_to == 'Other' ? $blotter->referred_to_other : '' }}"
                                   {{ $blotter->status == 'Referred' ? '' : 'disabled' }}
                                   placeholder="Enter the specific agency or office">
                        </div>
                        <div class="form-group">
                            <label for="referred_date">Referral Date</label>
                            <input type="date" name="referred_date" id="referred_date" class="form-control"
                                   value="{{ $blotter->referred_date ? $blotter->referred_date->format('Y-m-d') : '' }}"
                                   {{ $blotter->status == 'Referred' ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
        <!-- Case Details Grid -->
        <div class="details-grid">
            <!-- Complainant Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <x-heroicon-o-user class="detail-icon" />
                    <h3>Complainant Information</h3>
                </div>
                <div class="detail-body">
                    @if($blotter->complainants->count() > 0)
                        @foreach($blotter->complainants as $index => $complainant)
                            <div class="party-detail-row {{ $index > 0 ? 'mt-3' : '' }}">
                                <div class="party-header">
                                    <span class="party-number">Complainant {{ $index + 1 }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value">{{ $complainant->name }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Address:</span>
                                    <span class="detail-value">{{ $complainant->address ?? 'N/A' }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Contact:</span>
                                    <span class="detail-value">{{ $complainant->contact_number ?? 'N/A' }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="detail-row">
                            <span class="detail-value">No complainant information available</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Respondent Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <x-heroicon-o-user-group class="detail-icon" />
                    <h3>Respondent Information</h3>
                </div>
                <div class="detail-body">
                    @if($blotter->respondents->count() > 0)
                        @foreach($blotter->respondents as $index => $respondent)
                            <div class="party-detail-row {{ $index > 0 ? 'mt-3' : '' }}">
                                <div class="party-header">
                                    <span class="party-number">Respondent {{ $index + 1 }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value">{{ $respondent->name }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Address:</span>
                                    <span class="detail-value">{{ $respondent->address ?? 'N/A' }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="detail-row">
                            <span class="detail-value">No respondent information available</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Witness Information (Optional) -->
            @if($blotter->witnesses->count() > 0)
            <div class="detail-card">
                <div class="detail-header">
                    <x-heroicon-o-eye class="detail-icon" />
                    <h3>Witness Information</h3>
                </div>
                <div class="detail-body">
                    @foreach($blotter->witnesses as $index => $witness)
                        <div class="party-detail-row {{ $index > 0 ? 'mt-3' : '' }}">
                            <div class="party-header">
                                <span class="party-number">Witness {{ $index + 1 }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value">{{ $witness->name }}</span>
                            </div>
                            @if($witness->additional_info)
                            <div class="detail-row full-width">
                                <span class="detail-label">Statement:</span>
                                <span class="detail-value">{{ $witness->additional_info }}</span>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Incident Details -->
            <div class="detail-card full-width">
                <div class="detail-header">
                    <x-heroicon-o-document-text class="detail-icon" />
                    <h3>Incident Details</h3>
                </div>
                <div class="detail-body">
                    <div class="details-info-grid">
                        <div class="detail-row">
                            <span class="detail-label">Incident Type:</span>
                            <span class="detail-value">{{ $blotter->incident_type ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Incident Date:</span>
                            <span class="detail-value">
                                @if($blotter->incident_date)
                                    {{ $blotter->incident_date->format('F d, Y') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Incident Location:</span>
                            <span class="detail-value">{{ $blotter->incident_location ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row full-width">
                            <span class="detail-label">Description:</span>
                            <span class="detail-value">{{ $blotter->description ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

<!-- Resolution Details (if settled) -->
@if($blotter->status == 'Settled' && !empty($blotter->resolution))
<div class="detail-card full-width">
    <div class="detail-header">
        <x-heroicon-o-check-circle class="detail-icon success" />
        <h3>Resolution Details</h3>
    </div>
    <div class="detail-body">
        <div class="details-info-grid">
            <div class="detail-row full-width">
                <span class="detail-label">Resolution:</span>
                <span class="detail-value">{{ $blotter->resolution }}</span>
            </div>
            @if($blotter->resolved_date)
            <div class="detail-row">
                <span class="detail-label">Resolution Date:</span>
                <span class="detail-value">
                    {{ $blotter->resolved_date->format('F d, Y') }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Referral Details (if referred) -->
@if($blotter->status == 'Referred' && !empty($blotter->referred_reason))
<div class="detail-card full-width">
    <div class="detail-header">
        <i class="fas fa-share-square" style="color: #f59e0b;"></i>
        <h3>Referral Details</h3>
    </div>
    <div class="detail-body">
        <div class="details-info-grid">
            <div class="detail-row full-width">
                <span class="detail-label">Reason for Referral:</span>
                <span class="detail-value">{{ $blotter->referred_reason }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Referred To:</span>
                <span class="detail-value">
                    @if($blotter->referred_to == 'Other')
                        {{ $blotter->referred_to_other }}
                    @else
                        {{ $blotter->referred_to }}
                    @endif
                </span>
            </div>
            @if($blotter->referred_date)
            <div class="detail-row">
                <span class="detail-label">Referral Date:</span>
                <span class="detail-value">
                    {{ $blotter->referred_date->format('F d, Y') }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

            <!-- Timeline -->
            <div class="detail-card full-width">
                <div class="detail-header">
                    <x-heroicon-o-clock class="detail-icon" />
                    <h3>Case Timeline</h3>
                </div>
                <div class="detail-body">
                    <div class="timeline">
                        @if($blotter->created_at)
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $blotter->created_at->format('M d, Y h:i A') }}</div>
                            <div class="timeline-content">
                                <strong>Case Filed</strong> - Initial blotter report filed
                            </div>
                        </div>
                        @endif

                        @if($blotter->updated_at && (!$blotter->created_at || $blotter->updated_at->ne($blotter->created_at)))
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $blotter->updated_at->format('M d, Y h:i A') }}</div>
                            <div class="timeline-content">
                                <strong>Last Updated</strong> - Status: {{ $blotter->status ?? 'N/A' }}
                            </div>
                        </div>
                        @endif

                        @if($blotter->status == 'Settled' && $blotter->resolved_date)
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $blotter->resolved_date->format('M d, Y h:i A') }}</div>
                            <div class="timeline-content">
                                <strong>Case Resolved</strong> - Case marked as settled
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ========== HEARING TRACKING SECTION ========== -->
            <div class="detail-card full-width">
                <div class="detail-header">
                    <i class="fas fa-gavel"></i>
                    <h3>Hearing Tracking</h3>
                </div>
                <div class="detail-body">
                    @if($blotter->status == 'Pending' || $blotter->status == 'Ongoing')
                        <div class="hearing-info">
                            <div class="hearing-stats">
                                <div class="stat-box">
                                    <span class="stat-label">Stage</span>
                                    <span class="stat-value">{{ ucfirst($blotter->hearing_stage) }}</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Hearings Missed</span>
                                    <span class="stat-value {{ $blotter->hearing_count >= 2 ? 'text-warning' : '' }}">
                                        {{ $blotter->hearing_count }} / 3
                                    </span>
                                </div>
                                @if($blotter->next_hearing_date)
                                <div class="stat-box">
                                    <span class="stat-label">Next Hearing</span>
                                    <span class="stat-value">
                                        {{ $blotter->next_hearing_date->format('M d, Y') }}
                                        @if($blotter->isHearingOverdue())
                                            <span class="badge-danger">Overdue!</span>
                                        @endif
                                    </span>
                                </div>
                                @endif
                                @if($blotter->deadline_date)
                                <div class="stat-box">
                                    <span class="stat-label">Stage Deadline</span>
                                    <span class="stat-value {{ $blotter->getDaysUntilDeadline() <= 3 ? 'text-danger' : '' }}">
                                        {{ $blotter->deadline_date->format('M d, Y') }}
                                        ({{ intval($blotter->getDaysUntilDeadline()) }} days left)
                                    </span>
                                </div>
                                @endif
                            </div>
                            @if(!$blotter->cfa_issued && $blotter->hearing_count < 3)
                            <div class="hearing-actions">
                                <button class="btn-record" onclick="openHearingModal()">
                                    <i class="fas fa-check-circle"></i> Record Hearing
                                </button>
                                <button class="btn-schedule" onclick="openScheduleModal()">
                                    <i class="fas fa-calendar-alt"></i> Schedule Hearing
                                </button>
                            </div>
                            @endif

                            @if($blotter->cfa_issued)
                            <div class="alert-cfa">
                                <i class="fas fa-file-alt"></i>
                                <strong>Certificate to File Action (CFA) Issued</strong>
                                on {{ $blotter->cfa_issued_date->format('M d, Y') }}
                                <br>This case may now be filed in regular court.
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="alert-info">
                            <i class="fas fa-info-circle"></i>
                            Hearing tracking is only available for pending or ongoing cases.
                        </div>
                    @endif
                </div>
            </div>
            <!-- ========== END HEARING TRACKING SECTION ========== -->
        </div>
    </div>
</div>

<!-- Record Hearing Modal -->
<div id="hearingModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Record Hearing</h3>
            <button type="button" class="close" onclick="closeHearingModal()">&times;</button>
        </div>
        <form action="{{ route('secretary.blotter.hearing.record', $blotter) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Did the Respondent attend?</label>
                    <select name="respondent_attended" class="form-control" required>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Did the Complainant attend?</label>
                    <select name="complainant_attended" class="form-control" required>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hearing Notes</label>
                    <textarea name="hearing_notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeHearingModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Hearing Record</button>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Hearing Modal -->
<div id="scheduleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Schedule Hearing</h3>
            <button type="button" class="close" onclick="closeScheduleModal()">&times;</button>
        </div>
        <form action="{{ route('secretary.blotter.hearing.schedule', $blotter) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Hearing Date</label>
                    <input type="date" name="hearing_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="hearing_notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeScheduleModal()">Cancel</button>
                <button type="submit" class="btn-primary">Schedule</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toast notification function
function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');
    let toastMessage = document.getElementById('toastMessage');
    let toastIcon = document.querySelector('.toast-icon');

    if (!toast || !toastMessage) return;

    toastMessage.textContent = message;

    if (type === 'success') {
        toast.style.borderLeftColor = '#10b981';
        if (toastIcon) {
            toastIcon.classList.remove('error');
            toastIcon.classList.add('success');
        }
    } else if (type === 'error') {
        toast.style.borderLeftColor = '#dc2626';
        if (toastIcon) {
            toastIcon.classList.remove('success');
            toastIcon.classList.add('error');
        }
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Hearing Modal Functions
function openHearingModal() {
    document.getElementById('hearingModal').style.display = 'flex';
}

function closeHearingModal() {
    document.getElementById('hearingModal').style.display = 'none';
}

function openScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'flex';
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}
</script>

{{-- Status update functions for non-clerk users --}}
@if(auth()->user()->role_id != 4)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedStatus = document.querySelector('input[name="status"]:checked');
    if (selectedStatus) {
        toggleFields(selectedStatus.value);
    }

    // Handle referred to dropdown for "Other" option
    const referredTo = document.getElementById('referred_to');
    const otherContainer = document.getElementById('other_referred_container');

    if (referredTo) {
        referredTo.addEventListener('change', function() {
            if (this.value === 'Other') {
                otherContainer.style.display = 'block';
            } else {
                otherContainer.style.display = 'none';
            }
        });

        // Initial check
        if (referredTo.value === 'Other') {
            otherContainer.style.display = 'block';
        }
    }
});

function toggleFields(status) {
    const resolutionField = document.getElementById('resolution-field');
    const referredField = document.getElementById('referred-field');
    const resolution = document.getElementById('resolution');
    const resolvedDate = document.getElementById('resolved_date');
    const referredReason = document.getElementById('referred_reason');
    const referredTo = document.getElementById('referred_to');
    const referredDate = document.getElementById('referred_date');
    const otherReferred = document.getElementById('other_referred');

    // Hide both fields first
    if (resolutionField) resolutionField.classList.add('hidden');
    if (referredField) referredField.classList.add('hidden');

    // Disable all fields
    if (resolution) {
        resolution.disabled = true;
        resolution.required = false;
    }
    if (resolvedDate) resolvedDate.disabled = true;
    if (referredReason) {
        referredReason.disabled = true;
        referredReason.required = false;
    }
    if (referredTo) referredTo.disabled = true;
    if (referredDate) referredDate.disabled = true;
    if (otherReferred) otherReferred.disabled = true;

    // Show and enable appropriate field based on status
    if (status === 'Settled') {
        if (resolutionField) resolutionField.classList.remove('hidden');
        if (resolution) {
            resolution.disabled = false;
            resolution.required = true;
        }
        if (resolvedDate) resolvedDate.disabled = false;
    } else if (status === 'Referred') {
        if (referredField) referredField.classList.remove('hidden');
        if (referredReason) {
            referredReason.disabled = false;
            referredReason.required = true;
        }
        if (referredTo) referredTo.disabled = false;
        if (referredDate) referredDate.disabled = false;
        if (otherReferred) otherReferred.disabled = false;
    }
}
</script>
@endif

{{-- Session messages --}}
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast("{{ session('success') }}", 'success');
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast("{{ session('error') }}", 'error');
});
</script>
@endif
@endpush

@push('styles')
<style>
    /* Referred Container Styles */
.referred-container {
    margin: 1rem 0;
    padding: 1.5rem;
    background: #fffbeb;
    border-radius: 8px;
    border-left: 4px solid #f59e0b;
    transition: all 0.3s;
}

.referred-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.referred-grid .full-width {
    grid-column: 1 / -1;
}

/* Referred reason display */
.referred-reason-display {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #fffbeb;
    border-radius: 8px;
    border-left: 4px solid #f59e0b;
}

.referred-reason {
    flex: 1;
    color: #92400e;
    line-height: 1.5;
}

.resolution-display {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f0fdf4;
    border-radius: 8px;
    border-left: 4px solid #10b981;
}

.resolution-text {
    flex: 1;
    color: #155724;
    line-height: 1.5;
}

/* Additional styles */
.form-text.text-muted {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.form-text.text-muted i {
    margin-right: 0.25rem;
}
    /* Stage Deadline Alerts */
.alert-deadline {
    background: #fee2e2;
    border-left: 4px solid #dc2626;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    color: #991b1b;
}

.alert-expired {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    color: #92400e;
}

.stage-info {
    background: #eef2ff;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    color: #1e40af;
    font-size: 0.85rem;
}

.stage-info i {
    margin-right: 0.5rem;
}

.extend-actions {
    margin-top: 1rem;
}

.btn-extend {
    padding: 0.5rem 1rem;
    background: #f59e0b;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.3s;
}

.btn-extend:hover {
    background: #d97706;
    transform: translateY(-1px);
}
.party-detail-row {
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.party-detail-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.party-header {
    margin-bottom: 0.5rem;
    padding-bottom: 0.25rem;
    border-bottom: 1px dashed #e2e8f0;
}

.party-number {
    font-weight: 600;
    color: #667eea;
    font-size: 0.85rem;
}

.mt-3 {
    margin-top: 1rem;
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

.status-readonly {
    padding: 1rem;
}

.current-status-display {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.status-label {
    font-weight: 600;
    color: #4b5563;
}

.clerk-notice {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #fef3c7;
    border-radius: 8px;
    color: #92400e;
    border-left: 4px solid #f59e0b;
}

.clerk-notice i {
    font-size: 1.2rem;
    color: #f59e0b;
}

.status-update-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    flex: 1;
    min-width: 300px;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem 0.75rem;
    border-radius: 5px;
    transition: all 0.2s;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.radio-option:hover {
    background: #eef2ff;
    border-color: #667eea;
}

.radio-option input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
}

.radio-label {
    font-size: 0.95rem;
    font-weight: 500;
}

.status-pending-radio { color: #856404; }
.status-ongoing-radio { color: #004085; }
.status-settled-radio { color: #155724; }
.status-referred-radio { color: #553c9a; }

.btn-update-status {
    padding: 0.6rem 1.8rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    font-weight: 500;
    white-space: nowrap;
    height: 42px;
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}

.btn-update-status:hover {
    background: #5a67d8;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(102, 126, 234, 0.4);
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-ongoing { background: #cce5ff; color: #004085; }
.status-settled { background: #d4edda; color: #155724; }
.status-referred { background: #e2d5f1; color: #553c9a; }

.resolution-container {
    margin: 1rem 0;
    padding: 1.5rem;
    background: #f0fdf4;
    border-radius: 8px;
    border-left: 4px solid #10b981;
    transition: all 0.3s;
}

.hidden {
    display: none !important;
}

.resolution-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
}

.form-group {
    flex: 1;
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
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.95rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control:disabled {
    background: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.7;
}

.required {
    color: #dc2626;
}

.icon-small {
    width: 16px;
    height: 16px;
}

/* Toast Notification */
.toast {
    visibility: hidden;
    min-width: 300px;
    background-color: white;
    color: #333;
    text-align: center;
    border-radius: 8px;
    padding: 1rem;
    position: fixed;
    z-index: 1001;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-left: 4px solid #10b981;
    animation: slideUp 0.3s;
}

.toast.show {
    visibility: visible;
    animation: slideUp 0.3s, fadeOut 0.3s 2.7s;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    justify-content: center;
}

.toast-icon {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.toast-icon.success { color: #10b981; }
.toast-icon.error { color: #dc2626; }

@keyframes slideUp {
    from { transform: translate(-50%, 20px); opacity: 0; }
    to { transform: translate(-50%, 0); opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; transform: translate(-50%, 0); }
    to { opacity: 0; transform: translate(-50%, -10px); }
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
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

.detail-icon.success { color: #10b981; }

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

/* ========== HEARING TRACKING STYLES ========== */
.hearing-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-box {
    background: #f8fafc;
    padding: 0.75rem;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #e2e8f0;
}

.stat-box .stat-label {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.stat-box .stat-value {
    display: block;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
}

.text-warning {
    color: #f59e0b;
}

.text-danger {
    color: #dc2626;
}

.badge-danger {
    background: #fee2e2;
    color: #dc2626;
    padding: 0.15rem 0.4rem;
    border-radius: 20px;
    font-size: 0.7rem;
    margin-left: 0.5rem;
}

.alert-cfa {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.alert-info {
    background: #eef2ff;
    border-left: 4px solid #3b82f6;
    padding: 0.75rem 1rem;
    border-radius: 8px;
}

.hearing-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.btn-record, .btn-schedule {
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.85rem;
    border: none;
}

.btn-record {
    background: #10b981;
    color: white;
}

.btn-schedule {
    background: #3b82f6;
    color: white;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal .modal-content {
    background: white;
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
    animation: modalFadeIn 0.3s ease;
}

.modal .modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal .modal-header h3 {
    margin: 0;
}

.modal .modal-header .close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.modal .modal-body {
    padding: 1.5rem;
}

.modal .modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.modal-footer .btn-secondary {
    background: #f3f4f6;
    color: #4b5563;
    border: none;
}

.modal-footer .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .status-update-container {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .radio-group {
        flex-direction: column;
        min-width: 100%;
    }

    .radio-option {
        width: 100%;
    }

    .btn-update-status {
        width: 100%;
        justify-content: center;
    }

    .resolution-grid {
        grid-template-columns: 1fr;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .detail-row {
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-label {
        width: 100%;
    }

    .hearing-stats {
        grid-template-columns: 1fr;
    }

    .hearing-actions {
        flex-direction: column;
    }
}
</style>
@endpush
