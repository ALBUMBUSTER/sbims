@extends('layouts.app')

@section('title', 'Create New Certificate')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Create New Certificate</h1>
            <p>Fill in the certificate details</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.certificates.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to List
            </a>
        </div>
    </div>

    <div class="form-container">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('secretary.certificates.store') }}" method="POST" class="certificate-form" id="certificateForm">
            @csrf

            <div class="form-section">
                <h2>Certificate Information</h2>

                <!-- Resident Search (Replaces the select dropdown) -->
                <div class="form-group full-width">
                    <label for="resident_search">Search Resident <span class="required">*</span></label>
                    <div class="search-container">
                        <input type="text"
                               id="resident_search"
                               placeholder="Start typing resident name or ID..."
                               autocomplete="off"
                               class="search-input form-control @error('resident_id') is-invalid @enderror"
                               value="{{ old('resident_search') }}">
                        <div class="search-results" id="resident_results"></div>
                    </div>
                    <input type="hidden" id="resident_id" name="resident_id" value="{{ old('resident_id') }}" required>
                    <div id="selected_resident" class="selected-resident" @if(old('resident_id')) style="display: block;" @else style="display: none;" @endif>
                        <div class="resident-info">
                            <strong>Selected:</strong>
                            <span id="selected_resident_name">
                                @if(old('resident_id'))
                                    @php
                                        $selectedResident = $residents->firstWhere('id', old('resident_id'));
                                    @endphp
                                    @if($selectedResident)
                                        {{ $selectedResident->first_name }} {{ $selectedResident->last_name }} ({{ $selectedResident->resident_id }}) - {{ $selectedResident->address }}
                                    @endif
                                @endif
                            </span>
                            <button type="button" class="btn-clear" onclick="clearResident()">×</button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Type at least 2 characters to search residents</small>
                    @error('resident_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Certificate Type -->
                <div class="form-group">
                    <label for="certificate_type">Certificate Type <span class="required">*</span></label>
                    <select name="certificate_type" id="certificate_type" class="form-control @error('certificate_type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="Clearance" {{ old('certificate_type') == 'Clearance' ? 'selected' : '' }}>Barangay Clearance</option>
                        <option value="Indigency" {{ old('certificate_type') == 'Indigency' ? 'selected' : '' }}>Certificate of Indigency</option>
                        <option value="Residency" {{ old('certificate_type') == 'Residency' ? 'selected' : '' }}>Certificate of Residency</option>
                    </select>
                    @error('certificate_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Purpose Selection -->
                <div class="form-group full-width">
                    <label for="purpose_category">Purpose Category</label>
                    <select id="purpose_category" class="form-control">
                        <option value="">Select Common Purpose</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                    <small class="form-text text-muted">Select a common purpose or type your own below</small>
                </div>

                <!-- Purpose -->
                <div class="form-group full-width">
                    <label for="purpose">Purpose <span class="required">*</span></label>
                    <textarea id="purpose"
                              name="purpose"
                              class="form-control @error('purpose') is-invalid @enderror"
                              rows="3"
                              placeholder="Type your purpose here or select from the common purposes above"
                              required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">State the purpose of requesting this certificate</small>
                </div>

                <!-- Transaction Fee -->
                <div class="form-group">
                    <label for="transaction_fee">Transaction Fee (₱)</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number"
                               id="transaction_fee"
                               name="transaction_fee"
                               class="form-control @error('transaction_fee') is-invalid @enderror"
                               value="{{ old('transaction_fee') }}"
                               step="0.01"
                               min="0"
                               placeholder="0.00">
                    </div>
                    @error('transaction_fee')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Enter the transaction fee amount (optional)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-primary" id="previewBtn">
                    <span class="preview-icon">👁️</span> Preview & Confirm
                </button>
                <button type="submit" class="btn-primary" style="display: none;" id="submitBtn">Create Certificate</button>
                <a href="{{ route('secretary.certificates.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal" style="display: none;">
    <div class="preview-modal-content">
        <div class="preview-header">
            <h3><span class="header-icon">📄</span> Certificate Preview</h3>
            <button type="button" class="preview-close">&times;</button>
        </div>

        <div class="preview-body">
            <div class="preview-alert">
                <span class="alert-icon">⚠️</span>
                <span>Please review all certificate details before submitting</span>
            </div>

            <div class="preview-sections">
                <div class="preview-section">
                    <h4>👤 Resident Information</h4>
                    <div class="preview-grid">
                        <div class="preview-item">
                            <span class="preview-label">Name:</span>
                            <span class="preview-value" id="preview_name"></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Resident ID:</span>
                            <span class="preview-value" id="preview_resident_id"></span>
                        </div>
                        <div class="preview-item full-width">
                            <span class="preview-label">Address:</span>
                            <span class="preview-value" id="preview_address"></span>
                        </div>
                    </div>
                </div>

                <div class="preview-section">
                    <h4>📋 Certificate Details</h4>
                    <div class="preview-grid">
                        <div class="preview-item">
                            <span class="preview-label">Certificate Type:</span>
                            <span class="preview-value highlight" id="preview_certificate_type"></span>
                        </div>
                        <div class="preview-item full-width">
                            <span class="preview-label">Purpose:</span>
                            <div class="preview-text" id="preview_purpose"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="preview-footer">
            <div class="preview-warning">
                <span class="warning-icon">⚠️</span>
                <span>This action will create a permanent certificate record.</span>
            </div>
            <div class="preview-actions">
                <button type="button" class="btn btn-outline edit-btn">✏️ Edit Details</button>
                <button type="button" class="btn btn-secondary cancel-btn">❌ Cancel</button>
                <button type="button" class="btn btn-primary confirm-preview-btn">✓ Confirm & Create</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>

.btn-add-new-inline:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-add-new-inline i {
    font-size: 0.9rem;
}
/* All your existing styles remain the same */
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
    cursor: pointer;
    border: none;
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

/* Alert */
.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.alert-danger ul {
    margin: 0;
    padding-left: 1.5rem;
}

/* Form Container */
.form-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 2rem;
}

.form-section {
    margin-bottom: 2rem;
}

.form-section h2 {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #667eea;
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

.form-group.full-width {
    grid-column: 1 / -1;
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

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

.error-message {
    display: block;
    color: #dc2626;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-text {
    display: block;
    color: #666;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.text-muted {
    color: #6c757d !important;
}

/* Input Group for Transaction Fee */
.input-group {
    display: flex;
    align-items: stretch;
}

.input-group-text {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f3f4f6;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-radius: 5px 0 0 5px;
    color: #4b5563;
    font-weight: 500;
}

.input-group .form-control {
    border-left: none;
    border-radius: 0 5px 5px 0;
}

.input-group .form-control:focus {
    border-left: none;
    outline: none;
    border-color: #667eea;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.icon-small {
    width: 16px;
    height: 16px;
}

/* Search styles */
.search-container {
    position: relative;
    width: 100%;
}

.search-input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.search-results.active {
    display: block;
}

.resident-option {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
}

.resident-option:hover {
    background-color: #f9fafb;
}

.resident-option.highlighted {
    background-color: #eef2ff;
}

.resident-option:last-child {
    border-bottom: none;
}

.resident-name {
    font-weight: 600;
    color: #111827;
}

.resident-details {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 2px;
}

.selected-resident {
    margin-top: 10px;
    padding: 10px;
    background-color: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
}

.resident-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.btn-clear {
    background: none;
    border: none;
    color: #ef4444;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.btn-clear:hover {
    background-color: #fee2e2;
}

.no-results {
    padding: 15px;
    text-align: center;
    color: #6b7280;
    font-style: italic;
}

.loading {
    padding: 15px;
    text-align: center;
    color: #6b7280;
}

.loading::after {
    content: '...';
    animation: dots 1.5s steps(4, end) infinite;
}

@keyframes dots {
    0%, 20% { content: '.'; }
    40% { content: '..'; }
    60%, 100% { content: '...'; }
}

/* Preview Modal Styles */
.preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.preview-modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease forwards;
    transform: translateY(20px);
}

@keyframes slideUp {
    to { transform: translateY(0); }
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
}

.preview-header h3 {
    margin: 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    border: none;
}

.preview-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 1.5rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.preview-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.preview-body {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
}

.preview-alert {
    background: #fffbeb;
    border: 1px solid #fbbf24;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #92400e;
}

.preview-sections {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.preview-section {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #667eea;
}

.preview-section h4 {
    margin: 0 0 15px 0;
    color: #1e293b;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    border: none;
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.preview-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.preview-item.full-width {
    grid-column: 1 / -1;
}

.preview-label {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 500;
}

.preview-value {
    color: #1f2937;
    font-weight: 500;
}

.preview-value.highlight {
    color: #667eea;
    font-weight: 600;
    font-size: 1.1rem;
}

.preview-text {
    background: #f1f5f9;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #94a3b8;
    margin-top: 5px;
    white-space: pre-wrap;
    line-height: 1.6;
}

.preview-footer {
    border-top: 1px solid #e5e7eb;
    padding: 20px 25px;
    background: #f9fafb;
    border-radius: 0 0 12px 12px;
}

.preview-warning {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #991b1b;
    font-size: 0.9rem;
}

.preview-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.preview-actions .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.btn-outline {
    background: white;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f3f4f6;
}

.loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease infinite;
    display: inline-block;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Error Toast */
.error-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #fee2e2;
    border: 1px solid #ef4444;
    border-radius: 8px;
    padding: 15px;
    min-width: 300px;
    max-width: 400px;
    z-index: 10000;
    animation: slideInRight 0.3s ease forwards;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.error-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.error-close {
    background: none;
    border: none;
    color: #991b1b;
    font-size: 1.2rem;
    cursor: pointer;
    margin-left: auto;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn-primary,
    .form-actions .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .preview-actions {
        flex-direction: column;
    }

    .preview-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Residents data for autocomplete
const residents = <?php echo json_encode($residents); ?>;

// Common purposes for each certificate type
const purposeOptions = {
    'Clearance': [
        'Employment application',
        'Local employment',
        'Overseas employment (OFW)',
        'Business permit application',
        'School enrollment',
        'Scholarship application',
        'Travel requirements',
        'Bank account opening',
        'Loan application',
        'Government ID application',
        'Driver\'s license application',
        'Passport application',
        'Visa application',
        'Civil service eligibility',
        'Job promotion',
        'Training/seminar attendance',
        'Police clearance requirement',
        'NBI clearance requirement',
        'SSS/GSIS requirements',
        'Pag-IBIG requirements',
        'PhilHealth requirements',
        'Voter\'s registration',
        'Marriage license application',
        'Court requirement',
        'Notarial document requirement'
    ],
    'Indigency': [
        'Medical assistance',
        'Hospital bill assistance',
        'Financial assistance from LGU',
        'Scholarship grant (financial aid)',
        'Social welfare programs (4Ps)',
        'Food assistance',
        'Burial assistance',
        'Medicine assistance',
        'Senior citizen benefits',
        'PWD benefits',
        'Solo parent benefits',
        'Disaster relief assistance',
        'Educational assistance',
        'Livelihood program application',
        'Housing assistance',
        'Utility bill discount application',
        'Legal aid qualification',
        'Free legal assistance',
        'Public attorney\'s office (PAO) assistance',
        'Health center services',
        'Government hospital charity ward',
        'DSWD programs',
        'Food stamp program',
        'Tulong Panghanapbuhay sa Ating Disadvantaged/Displaced Workers (TUPAD)',
        'Emergency cash assistance'
    ],
    'Residency': [
        'Proof of residency requirement',
        'Voter\'s registration',
        'School enrollment (transfer students)',
        'Local employment (proof of residency)',
        'Business registration',
        'Barangay ID application',
        'Tax identification number (TIN) application',
        'Postal ID application',
        'Driver\'s license (address verification)',
        'Voter\'s ID application',
        'Senior citizen ID application',
        'PWD ID application',
        'Utility connection application (water/electricity)',
        'Internet connection application',
        'Bank account address verification',
        'Loan application (address verification)',
        'Credit card application',
        'Insurance application',
        'Government service eligibility',
        'Residency verification for court cases',
        'Adoption requirements',
        'Foster care application',
        'Relocation/transfer verification',
        'Community tax certificate (cedula) application',
        'Barangay assembly participation'
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    // Setup search functionality
    setupResidentSearch();

    // Setup purpose dropdown based on certificate type
    setupPurposeDropdown();

    // Preview button click handler
    document.getElementById('previewBtn').addEventListener('click', function() {
        if (validateForm()) {
            showPreview();
        }
    });

    // Form submit handler
    document.getElementById('certificateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            showPreview();
        }
    });
});

function setupPurposeDropdown() {
    const certificateType = document.getElementById('certificate_type');
    const purposeCategory = document.getElementById('purpose_category');
    const purposeTextarea = document.getElementById('purpose');

    // Update purpose options when certificate type changes
    certificateType.addEventListener('change', function() {
        updatePurposeOptions(this.value, purposeCategory);
    });

    // Handle purpose category selection
    purposeCategory.addEventListener('change', function() {
        if (this.value) {
            // If "Other" is selected, clear the textarea and focus on it
            if (this.value === 'Other') {
                purposeTextarea.value = '';
                purposeTextarea.focus();
            } else {
                purposeTextarea.value = this.value;
            }
        }
    });

    // Initialize with existing value if any
    if (certificateType.value) {
        updatePurposeOptions(certificateType.value, purposeCategory);
        // If there's an old purpose value, try to select it in the dropdown
        const oldPurpose = '{{ old('purpose') }}';
        if (oldPurpose) {
            const options = purposeCategory.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === oldPurpose) {
                    options[i].selected = true;
                    purposeTextarea.value = oldPurpose;
                    break;
                }
            }
        }
    }
}

function updatePurposeOptions(type, purposeCategory) {
    // Clear existing options
    purposeCategory.innerHTML = '<option value="">Select Common Purpose</option>';

    if (type && purposeOptions[type]) {
        // Add purpose options for selected certificate type
        purposeOptions[type].forEach(purpose => {
            const option = document.createElement('option');
            option.value = purpose;
            option.textContent = purpose;
            purposeCategory.appendChild(option);
        });

        // Add "Other" option
        const otherOption = document.createElement('option');
        otherOption.value = 'Other';
        otherOption.textContent = '-- Other (Type below) --';
        purposeCategory.appendChild(otherOption);
    }
}

function setupResidentSearch() {
    const input = document.getElementById('resident_search');
    const results = document.getElementById('resident_results');
    const hiddenInput = document.getElementById('resident_id');
    const selectedDiv = document.getElementById('selected_resident');
    const selectedName = document.getElementById('selected_resident_name');

    let timeout = null;
    let currentFocus = -1;

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const searchTerm = this.value.trim().toLowerCase();

            if (searchTerm.length < 2) {
                results.classList.remove('active');
                return;
            }

            // Show loading
            results.innerHTML = '<div class="loading">Searching</div>';
            results.classList.add('active');

            // Filter residents
            const filtered = residents.filter(resident => {
                const fullName = (resident.first_name + ' ' + resident.last_name).toLowerCase();
                const residentId = resident.resident_id ? resident.resident_id.toLowerCase() : '';
                const address = resident.address ? resident.address.toLowerCase() : '';
                return fullName.includes(searchTerm) || residentId.includes(searchTerm) || address.includes(searchTerm);
            });

            // Store filtered residents for keyboard navigation
            window.filteredResidents = filtered;

            // Display results
            if (filtered.length > 0) {
                results.innerHTML = '';
                filtered.forEach((resident, index) => {
                    const option = document.createElement('div');
                    option.className = 'resident-option';
                    option.setAttribute('data-index', index);
                    option.innerHTML = `
                        <div class="resident-name">${resident.first_name} ${resident.last_name} ${resident.suffix ? resident.suffix : ''}</div>
                        <div class="resident-details">
                            ID: ${resident.resident_id} |
                            Address: ${resident.address}, Purok ${resident.purok}
                        </div>
                    `;

                    option.addEventListener('click', function() {
                        selectResident(resident);
                    });

                    option.addEventListener('mouseenter', function() {
                        removeHighlight();
                        this.classList.add('highlighted');
                        currentFocus = index;
                    });

                    results.appendChild(option);
                });
            } else {
                // Show "No residents found" with inline
                results.innerHTML = `
                    <div class="no-results">
                        <div class="no-results-message">No residents found</div>
                        <a href="{{ route('secretary.residents.create') }}" target="_blank" class="btn-add-new-inline">
                        </a>
                    </div>
                `;
            }
        }, 300);
    });

    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        const options = results.querySelectorAll('.resident-option');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            if (currentFocus >= options.length) currentFocus = 0;
            updateHighlight(options, currentFocus);
            options[currentFocus]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            if (currentFocus < 0) currentFocus = options.length - 1;
            updateHighlight(options, currentFocus);
            options[currentFocus]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter' && currentFocus > -1) {
            e.preventDefault();
            const selectedOption = options[currentFocus];
            if (selectedOption) {
                const residentIndex = selectedOption.getAttribute('data-index');
                const resident = window.filteredResidents ? window.filteredResidents[residentIndex] : null;
                if (resident) {
                    selectResident(resident);
                }
            }
        }
    });

    // Close results when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.remove('active');
        }
    });

    function selectResident(resident) {
        hiddenInput.value = resident.id;
        selectedName.textContent = `${resident.first_name} ${resident.last_name} (${resident.resident_id}) - ${resident.address}, Purok ${resident.purok}`;
        selectedDiv.style.display = 'block';
        input.value = '';
        results.classList.remove('active');

        // Store filtered residents for keyboard navigation
        window.filteredResidents = null;
    }

    function updateHighlight(options, index) {
        removeHighlight();
        if (options[index]) {
            options[index].classList.add('highlighted');
        }
    }

    function removeHighlight() {
        document.querySelectorAll('.resident-option.highlighted').forEach(el => {
            el.classList.remove('highlighted');
        });
    }
}

function clearResident() {
    document.getElementById('resident_id').value = '';
    document.getElementById('selected_resident').style.display = 'none';
    document.getElementById('resident_search').focus();
}

function validateForm() {
    // Validate resident is selected
    const residentId = document.getElementById('resident_id').value;
    if (!residentId) {
        showError('Please select a resident from the search results.');
        document.getElementById('resident_search').focus();
        return false;
    }

    // Validate certificate type
    const certificateType = document.getElementById('certificate_type').value;
    if (!certificateType) {
        showError('Please select certificate type.');
        document.getElementById('certificate_type').focus();
        return false;
    }

    // Validate purpose
    const purpose = document.getElementById('purpose').value.trim();
    if (!purpose) {
        showError('Please enter the purpose of the certificate.');
        document.getElementById('purpose').focus();
        return false;
    }

    return true;
}

function showError(message) {
    // Remove existing error toast
    const existingToast = document.querySelector('.error-toast');
    if (existingToast) {
        existingToast.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-toast';
    errorDiv.innerHTML = `
        <div class="error-content">
            <span class="error-icon">⚠️</span>
            <span class="error-message">${message}</span>
            <button class="error-close">&times;</button>
        </div>
    `;

    document.body.appendChild(errorDiv);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            document.body.removeChild(errorDiv);
        }
    }, 5000);

    // Close button handler
    errorDiv.querySelector('.error-close').addEventListener('click', () => {
        document.body.removeChild(errorDiv);
    });
}

function showPreview() {
    // Get selected resident data
    const residentId = document.getElementById('resident_id').value;
    const resident = residents.find(r => r.id == residentId);

    if (!resident) return;

    // Get form values
    const certificateType = document.getElementById('certificate_type').value;
    const certificateTypeText = document.getElementById('certificate_type').options[document.getElementById('certificate_type').selectedIndex].text;
    const purpose = document.getElementById('purpose').value;
    const transactionFee = document.getElementById('transaction_fee').value;

    // Update preview
    document.getElementById('preview_name').textContent = `${resident.first_name} ${resident.last_name} ${resident.suffix || ''}`;
    document.getElementById('preview_resident_id').textContent = resident.resident_id;
    document.getElementById('preview_address').textContent = `${resident.address}, Purok ${resident.purok}`;
    document.getElementById('preview_certificate_type').textContent = certificateTypeText;
    document.getElementById('preview_purpose').textContent = purpose;

    // Add transaction fee to preview
    const previewSections = document.querySelector('.preview-sections');
    const existingFeeRow = document.getElementById('preview_fee_row');

    if (transactionFee && transactionFee > 0) {
        const feeHtml = `
            <div class="preview-section" id="preview_fee_row">
                <h4>💰 Payment Information</h4>
                <div class="preview-grid">
                    <div class="preview-item">
                        <span class="preview-label">Transaction Fee:</span>
                        <span class="preview-value highlight">₱ ${parseFloat(transactionFee).toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;

        if (existingFeeRow) {
            existingFeeRow.outerHTML = feeHtml;
        } else {
            previewSections.insertAdjacentHTML('beforeend', feeHtml);
        }
    } else if (existingFeeRow) {
        existingFeeRow.remove();
    }

    // Show modal
    const modal = document.getElementById('previewModal');
    modal.style.display = 'flex';

    // Setup modal event listeners
    setupPreviewModal(modal);
}

function setupPreviewModal(modal) {
    // Close handlers
    const closeModal = () => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    modal.querySelector('.preview-close').addEventListener('click', closeModal);
    modal.querySelector('.cancel-btn').addEventListener('click', closeModal);
    modal.querySelector('.edit-btn').addEventListener('click', closeModal);

    // Click outside modal
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Confirm button
    modal.querySelector('.confirm-preview-btn').addEventListener('click', function() {
        const btn = this;
        btn.innerHTML = '<span class="loading-spinner"></span> Creating...';
        btn.disabled = true;

        // Disable other buttons
        modal.querySelectorAll('button').forEach(b => b.disabled = true);

        // Submit form
        setTimeout(() => {
            document.getElementById('certificateForm').submit();
        }, 500);
    });

    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}
</script>
@endpush
