@extends('layouts.app')

@section('title', 'Mark Resident as Deceased')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-cross" style="color: #6b7280;"></i> Mark Resident as Deceased</h1>
            <p>Record death information for {{ $resident->full_name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.residents.show', $resident) }}" class="btn-secondary">
                <i class="fas fa-arrow-left icon-small"></i>
                Back to Profile
            </a>
        </div>
    </div>

    <div class="form-container">
        <!-- Permanent Warning Alert - This stays until acknowledged -->
        <div id="permanentWarning" class="alert-warning-permanent" style="margin-bottom: 1.5rem;">
            <div class="alert-header">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>IMPORTANT: Legal Documentation Required</strong>
            </div>
            <div class="alert-body">
                <p>Marking a resident as deceased is a permanent legal action. Please ensure you have:</p>
                <ul>
                    <li><i class="fas fa-file-alt"></i> Official Death Certificate</li>
                    <li><i class="fas fa-calendar-check"></i> Verified Date of Death</li>
                    <li><i class="fas fa-stethoscope"></i> Medical Certificate or Legal Cause of Death</li>
                    <li><i class="fas fa-user-check"></i> Verification from authorized personnel</li>
                </ul>
                <p class="warning-text"><strong>This action cannot be undone without administrative approval and proper documentation.</strong></p>
            </div>
        </div>

        <div class="info-card" style="background: #e6f7ff; border: 1px solid #91d5ff; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <div class="info-header" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <i class="fas fa-info-circle" style="color: #1890ff;"></i>
                <strong style="color: #0050b3;">Resident Information</strong>
            </div>
            <div class="info-body">
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 0.5rem;">
                    <div><strong>Full Name:</strong> {{ $resident->full_name }}</div>
                    <div><strong>Resident ID:</strong> {{ $resident->resident_id }}</div>
                    <div><strong>Age:</strong> {{ $resident->age }} years old</div>
                    <div><strong>Birthdate:</strong> {{ \Carbon\Carbon::parse($resident->birthdate)->format('F d, Y') }}</div>
                    <div><strong>Purok:</strong> Purok {{ $resident->purok }}</div>
                    <div><strong>Civil Status:</strong> {{ $resident->civil_status }}</div>
                </div>
            </div>
        </div>

        <form action="{{ route('secretary.residents.mark-deceased', $resident) }}" method="POST" id="deceasedForm">
            @csrf

            <div class="form-section">
                <h2>Death Information</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="death_date">Date of Death <span class="required">*</span></label>
                        <input type="date"
                               id="death_date"
                               name="death_date"
                               value="{{ old('death_date') }}"
                               class="form-control @error('death_date') is-invalid @enderror"
                               max="{{ date('Y-m-d') }}"
                               required>
                        <small class="form-text text-muted">Must be on or before today</small>
                        @error('death_date')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="death_certificate_number">Death Certificate Number <span class="required">*</span></label>
                        <input type="text"
                               id="death_certificate_number"
                               name="death_certificate_number"
                               value="{{ old('death_certificate_number') }}"
                               class="form-control @error('death_certificate_number') is-invalid @enderror"
                               placeholder="e.g., 2024-00123"
                               required>
                        <small class="form-text text-muted">Official death certificate number from PSA/Local Civil Registry</small>
                        @error('death_certificate_number')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="cause_of_death">Cause of Death <span class="required">*</span></label>
                    <textarea id="cause_of_death"
                              name="cause_of_death"
                              rows="4"
                              class="form-control @error('cause_of_death') is-invalid @enderror"
                              placeholder="e.g., Cardiac Arrest, Pneumonia, Accident, etc."
                              required>{{ old('cause_of_death') }}</textarea>
                    <small class="form-text text-muted">Provide the official medical cause of death</small>
                    @error('cause_of_death')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="remarks">Additional Remarks (Optional)</label>
                    <textarea id="remarks"
                              name="remarks"
                              rows="3"
                              class="form-control"
                              placeholder="Any additional information about the death..."></textarea>
                    <small class="form-text text-muted">E.g., Place of death, attending physician, etc.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-primary" id="openConfirmModalBtn" style="background: #6b7280;">
                    <i class="fas fa-cross icon-small"></i>
                    Mark as Deceased
                </button>
                <a href="{{ route('secretary.residents.show', $resident) }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Permanent Confirmation Modal (won't disappear until user acts) -->
<div id="permanentConfirmModal" class="confirm-modal-permanent" style="display: none;">
    <div class="confirm-modal-permanent-content">
        <div class="modal-header-permanent">
            <div class="modal-icon-permanent">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2>Permanent Action Confirmation</h2>
        </div>
        <div class="modal-body-permanent">
            <div class="warning-banner">
                <i class="fas fa-gavel"></i>
                <strong>LEGAL NOTICE: This action is permanent and legally binding</strong>
            </div>

            <div class="confirmation-checklist">
                <h3>Please confirm the following:</h3>
                <label class="checklist-item">
                    <input type="checkbox" id="confirmDeathCert" class="confirm-checkbox">
                    <span>I have the official Death Certificate with the number provided</span>
                </label>
                <label class="checklist-item">
                    <input type="checkbox" id="confirmDate" class="confirm-checkbox">
                    <span>The date of death is verified and correct</span>
                </label>
                <label class="checklist-item">
                    <input type="checkbox" id="confirmCause" class="confirm-checkbox">
                    <span>The cause of death is accurately documented</span>
                </label>
                <label class="checklist-item">
                    <input type="checkbox" id="confirmLegal" class="confirm-checkbox">
                    <span>I understand this action will permanently archive this resident record</span>
                </label>
                <label class="checklist-item">
                    <input type="checkbox" id="confirmAuthority" class="confirm-checkbox">
                    <span>I am authorized to make this declaration</span>
                </label>
            </div>

            <div class="verification-section">
                <p><strong>Type "CONFIRM" in the box below to verify:</strong></p>
                <input type="text"
                       id="verificationText"
                       class="verification-input"
                       placeholder="Type CONFIRM to proceed"
                       autocomplete="off">
            </div>

            <div class="legal-statement">
                <i class="fas fa-balance-scale"></i>
                <p>Under Philippine law, providing false information about a person's death is punishable by law.
                I certify that the information provided is true and correct to the best of my knowledge.</p>
            </div>
        </div>
        <div class="modal-footer-permanent">
            <button type="button" class="btn-cancel-permanent" id="closeConfirmModalBtn">Cancel</button>
            <button type="button" class="btn-confirm-permanent" id="permanentConfirmBtn" disabled>Confirm & Mark as Deceased</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Form Container */
.form-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 2rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.form-section h2 {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 500;
}

.required {
    color: #dc2626;
    margin-left: 0.25rem;
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
}

.form-control.is-invalid {
    border-color: #dc2626;
}

.error-message {
    display: block;
    color: #dc2626;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-text {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
    display: block;
}

/* Permanent Warning Alert */
.alert-warning-permanent {
    background: #fff3cd;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    overflow: hidden;
}

.alert-header {
    background: #fef3c7;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #fde68a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    color: #92400e;
}

.alert-header i {
    font-size: 1.2rem;
}

.alert-body {
    padding: 1rem 1.5rem;
    color: #78350f;
}

.alert-body ul {
    margin: 0.75rem 0;
    padding-left: 1.5rem;
}

.alert-body li {
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-body li i {
    width: 20px;
    color: #f59e0b;
}

.warning-text {
    color: #dc2626;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #fde68a;
}

/* Permanent Confirmation Modal */
.confirm-modal-permanent {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(5px);
}

.confirm-modal-permanent-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 550px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header-permanent {
    padding: 1.5rem;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
    background: #fef3c7;
}

.modal-icon-permanent {
    font-size: 3rem;
    color: #f59e0b;
    margin-bottom: 0.5rem;
}

.modal-header-permanent h2 {
    color: #92400e;
    font-size: 1.3rem;
    margin: 0;
}

.modal-body-permanent {
    padding: 1.5rem;
}

.warning-banner {
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #991b1b;
}

.warning-banner i {
    font-size: 1.2rem;
}

.confirmation-checklist {
    margin-bottom: 1.5rem;
}

.confirmation-checklist h3 {
    font-size: 1rem;
    color: #333;
    margin-bottom: 1rem;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    margin-bottom: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}

.checklist-item:hover {
    background: #f1f5f9;
}

.checklist-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checklist-item span {
    flex: 1;
    cursor: pointer;
}

.verification-section {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.verification-section p {
    margin-bottom: 0.5rem;
    color: #333;
}

.verification-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
    text-align: center;
    letter-spacing: 1px;
    font-weight: bold;
}

.verification-input:focus {
    outline: none;
    border-color: #f59e0b;
}

.legal-statement {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: #666;
    border-left: 3px solid #f59e0b;
}

.legal-statement i {
    color: #f59e0b;
    font-size: 1rem;
}

.modal-footer-permanent {
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    border-top: 1px solid #e2e8f0;
}

.btn-cancel-permanent {
    padding: 0.75rem 1.5rem;
    background: #f3f4f6;
    color: #4b5563;
    border: none;
    border-radius: 5px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-cancel-permanent:hover {
    background: #e5e7eb;
}

.btn-confirm-permanent {
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-confirm-permanent:enabled {
    background: #dc2626;
}

.btn-confirm-permanent:enabled:hover {
    background: #b91c1c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-confirm-permanent:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
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
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
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

.icon-small {
    font-size: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .form-container {
        padding: 1rem;
    }

    .modal-footer-permanent {
        flex-direction: column;
    }

    .btn-cancel-permanent,
    .btn-confirm-permanent {
        width: 100%;
        justify-content: center;
    }

    .checklist-item {
        flex-wrap: wrap;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const openModalBtn = document.getElementById('openConfirmModalBtn');
    const confirmModal = document.getElementById('permanentConfirmModal');
    const closeModalBtn = document.getElementById('closeConfirmModalBtn');
    const confirmBtn = document.getElementById('permanentConfirmBtn');
    const verificationInput = document.getElementById('verificationText');
    const checkboxes = document.querySelectorAll('.confirm-checkbox');
    const deceasedForm = document.getElementById('deceasedForm');

    let allCheckboxesChecked = false;
    let verificationCorrect = false;

    // Function to check if all conditions are met
    function updateConfirmButtonState() {
        // Check all checkboxes
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        allCheckboxesChecked = allChecked;

        // Check verification text
        verificationCorrect = verificationInput.value.toUpperCase() === 'CONFIRM';

        // Enable button only if both conditions are met
        confirmBtn.disabled = !(allCheckboxesChecked && verificationCorrect);

        // Visual feedback
        if (allCheckboxesChecked && verificationCorrect) {
            confirmBtn.style.background = '#dc2626';
        } else {
            confirmBtn.style.background = '#6b7280';
        }
    }

    // Add event listeners to checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateConfirmButtonState);
    });

    // Add event listener to verification input
    verificationInput.addEventListener('input', updateConfirmButtonState);

    // Open modal
    openModalBtn.addEventListener('click', function(e) {
        e.preventDefault();

        // Reset modal state
        checkboxes.forEach(cb => cb.checked = false);
        verificationInput.value = '';
        updateConfirmButtonState();

        confirmModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Focus on first checkbox
        setTimeout(() => {
            checkboxes[0].focus();
        }, 100);
    });

    // Close modal
    function closeModal() {
        confirmModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    closeModalBtn.addEventListener('click', closeModal);

    // Confirm and submit form
    confirmBtn.addEventListener('click', function() {
        if (allCheckboxesChecked && verificationCorrect) {
            // Show a final loading state
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            confirmBtn.disabled = true;

            // Submit the form
            deceasedForm.submit();
        }
    });

    // Close modal when clicking outside
    confirmModal.addEventListener('click', function(e) {
        if (e.target === confirmModal) {
            closeModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && confirmModal.style.display === 'flex') {
            closeModal();
        }
    });

    // Prevent accidental form submission
    deceasedForm.addEventListener('submit', function(e) {
        e.preventDefault();
        openModalBtn.click();
    });
});
</script>
@endpush
