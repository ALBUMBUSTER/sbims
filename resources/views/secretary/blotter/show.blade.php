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
            <a href="{{ route('secretary.blotter.edit', $blotter) }}" class="btn-primary">
                <x-heroicon-o-pencil class="icon-small" />
                Edit Case
            </a>
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
                <form action="{{ route('secretary.blotter.status', $blotter) }}" method="POST" class="status-form">
                    @csrf
                    @method('PATCH')

                    <div class="status-update-container">
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="status" value="Pending"
                                       {{ $blotter->status == 'Pending' ? 'checked' : '' }}
                                       onchange="toggleResolution(this.value)">
                                <span class="radio-label status-pending-radio">Pending</span>
                            </label>

                            <label class="radio-option">
                                <input type="radio" name="status" value="Ongoing"
                                       {{ $blotter->status == 'Ongoing' ? 'checked' : '' }}
                                       onchange="toggleResolution(this.value)">
                                <span class="radio-label status-ongoing-radio">Ongoing</span>
                            </label>

                            <label class="radio-option">
                                <input type="radio" name="status" value="Settled"
                                       {{ $blotter->status == 'Settled' ? 'checked' : '' }}
                                       onchange="toggleResolution(this.value)">
                                <span class="radio-label status-settled-radio">Settled</span>
                            </label>

                            <label class="radio-option">
                                <input type="radio" name="status" value="Referred"
                                       {{ $blotter->status == 'Referred' ? 'checked' : '' }}
                                       onchange="toggleResolution(this.value)">
                                <span class="radio-label status-referred-radio">Referred</span>
                            </label>
                        </div>

                        <button type="submit" class="btn-update-status">
                            <x-heroicon-o-check-circle class="icon-small" />
                            Update Status
                        </button>
                    </div>

                    <div id="resolution-field" class="resolution-container {{ $blotter->status == 'Settled' ? '' : 'hidden' }}">
                        <div class="resolution-grid">
                            <div class="form-group">
                                <label for="resolution">Resolution <span class="required">*</span></label>
                                <textarea name="resolution" id="resolution" class="form-control" rows="3"
                                    {{ $blotter->status == 'Settled' ? '' : 'disabled' }}>{{ $blotter->resolution }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="resolved_date">Resolution Date</label>
                                <input type="date" name="resolved_date" id="resolved_date" class="form-control"
                                       value="{{ $blotter->resolved_date ? $blotter->resolved_date->format('Y-m-d') : '' }}"
                                       {{ $blotter->status == 'Settled' ? '' : 'disabled' }}>
                            </div>
                        </div>
                    </div>
                </form>
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
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">{{ $blotter->complainant->first_name ?? '' }} {{ $blotter->complainant->last_name ?? '' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">{{ $blotter->complainant->address ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value">{{ $blotter->complainant->contact_number ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Respondent Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <x-heroicon-o-user-group class="detail-icon" />
                    <h3>Respondent Information</h3>
                </div>
                <div class="detail-body">
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">{{ $blotter->respondent_name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">{{ $blotter->respondent_address ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

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
        </div>
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

document.addEventListener('DOMContentLoaded', function() {
    const resolutionField = document.getElementById('resolution-field');
    const radioButtons = document.querySelectorAll('input[name="status"]');

    // Set initial state
    const selectedStatus = document.querySelector('input[name="status"]:checked');
    if (selectedStatus) {
        toggleResolution(selectedStatus.value);
    }

    // Add change event to all radio buttons
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleResolution(this.value);
        });
    });
});

function toggleResolution(status) {
    const resolutionField = document.getElementById('resolution-field');
    const resolution = document.getElementById('resolution');
    const resolvedDate = document.getElementById('resolved_date');

    if (resolutionField) {
        if (status === 'Settled') {
            resolutionField.classList.remove('hidden');
            if (resolution) {
                resolution.disabled = false;
                resolution.required = true;
            }
            if (resolvedDate) {
                resolvedDate.disabled = false;
                resolvedDate.required = false;
            }
        } else {
            resolutionField.classList.add('hidden');
            if (resolution) {
                resolution.disabled = true;
                resolution.required = false;
            }
            if (resolvedDate) {
                resolvedDate.disabled = true;
                resolvedDate.required = false;
            }
        }
    }
}
</script>

{{-- Session messages - separated from main script to avoid Blade/JS conflicts --}}
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

/* Status Update Container */
.status-update-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

/* Radio Button Group */
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

/* Radio Label Colors */
.status-pending-radio {
    color: #856404;
}

.status-ongoing-radio {
    color: #004085;
}

.status-settled-radio {
    color: #155724;
}

.status-referred-radio {
    color: #553c9a;
}

/* Update Status Button */
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

.status-ongoing {
    background: #cce5ff;
    color: #004085;
}

.status-settled {
    background: #d4edda;
    color: #155724;
}

.status-referred {
    background: #e2d5f1;
    color: #553c9a;
}

/* Resolution Container */
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

/* Required field */
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

.toast-icon.success {
    color: #10b981;
}

.toast-icon.error {
    color: #dc2626;
}

@keyframes slideUp {
    from {
        transform: translate(-50%, 20px);
        opacity: 0;
    }
    to {
        transform: translate(-50%, 0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translate(-50%, 0);
    }
    to {
        opacity: 0;
        transform: translate(-50%, -10px);
    }
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

.detail-icon.success {
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
}
</style>
@endpush
