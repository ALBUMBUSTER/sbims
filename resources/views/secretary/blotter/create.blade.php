@extends('layouts.app')

@section('title', 'Add Blotter Case')

@section('content')
<div class="main-container">
    <div class="content">
        <div class="page-header">
            <h1>Add Blotter Case</h1>
            <p>Record new barangay dispute or incident</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-container">
            <form method="POST" action="{{ route('secretary.blotter.store') }}" id="blotterForm">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="case_id">Case ID</label>
                        <input type="text" id="case_id" name="case_id" value="{{ $case_id ?? '' }}" readonly style="background-color: #f3f4f6;">
                        <small>Automatically generated upon save</small>
                    </div>
                </div>

                <h3>Complainant Information</h3>
                <div class="form-group">
                    <label for="complainant_search">Search Complainant *</label>
                    <div class="search-container">
                        <input type="text" id="complainant_search" placeholder="Start typing resident name..."
                               autocomplete="off" class="search-input" value="{{ old('complainant_search') }}">
                        <div class="search-results" id="complainant_results"></div>
                    </div>
                    <input type="hidden" id="complainant_id" name="complainant_id" value="{{ old('complainant_id') }}" required>
                    <div id="selected_complainant" class="selected-resident" @if(old('complainant_id')) style="display: block;" @else style="display: none;" @endif>
                        <div class="resident-info">
                            <strong>Selected:</strong>
                            <span id="selected_complainant_name">
                                @if(old('complainant_id'))
                                    @php
                                        $selectedComplainant = $residents->firstWhere('id', old('complainant_id'));
                                    @endphp
                                    @if($selectedComplainant)
                                        {{ $selectedComplainant->first_name }} {{ $selectedComplainant->last_name }} ({{ $selectedComplainant->resident_id }})
                                    @endif
                                @endif
                            </span>
                            <button type="button" class="btn-clear" onclick="clearComplainant()">×</button>
                        </div>
                    </div>
                    <small>Type at least 2 characters to search residents</small>
                </div>

                <h3>Respondent Information</h3>

                <div class="form-group">
                    <label for="respondent_search">Search Respondent Resident (Optional)</label>
                    <div class="search-container">
                        <input type="text" id="respondent_search" placeholder="Start typing resident name..."
                               autocomplete="off" class="search-input">
                        <div class="search-results" id="respondent_results"></div>
                    </div>
                    <input type="hidden" id="respondent_resident_id" name="respondent_resident_id" value="{{ old('respondent_resident_id') }}">
                    <div id="selected_respondent" class="selected-resident" @if(old('respondent_resident_id')) style="display: block;" @else style="display: none;" @endif>
                        <div class="resident-info">
                            <strong>Selected:</strong>
                            <span id="selected_respondent_name">
                                @if(old('respondent_resident_id'))
                                    @php
                                        $selectedRespondent = $residents->firstWhere('id', old('respondent_resident_id'));
                                    @endphp
                                    @if($selectedRespondent)
                                        {{ $selectedRespondent->first_name }} {{ $selectedRespondent->last_name }} ({{ $selectedRespondent->resident_id }})
                                    @endif
                                @endif
                            </span>
                            <button type="button" class="btn-clear" onclick="clearRespondent()">×</button>
                        </div>
                    </div>
                    <small>Selecting a resident will auto-fill name and address below</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="respondent_name">Respondent Name *</label>
                        <input type="text" id="respondent_name" name="respondent_name" value="{{ old('respondent_name') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="respondent_address">Respondent Address *</label>
                    <textarea id="respondent_address" name="respondent_address" rows="2" placeholder="Complete address of the respondent" required>{{ old('respondent_address') }}</textarea>
                </div>

                <h3>Incident Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="incident_type">Incident Type *</label>
                        <select id="incident_type" name="incident_type" required onchange="toggleOtherIncident()">
                            <option value="">Select Type</option>
                            <option value="Boundary Dispute" {{ old('incident_type') == 'Boundary Dispute' ? 'selected' : '' }}>Boundary Dispute</option>
                            <option value="Noise Complaint" {{ old('incident_type') == 'Noise Complaint' ? 'selected' : '' }}>Noise Complaint</option>
                            <option value="Property Damage" {{ old('incident_type') == 'Property Damage' ? 'selected' : '' }}>Property Damage</option>
                            <option value="Physical Altercation" {{ old('incident_type') == 'Physical Altercation' ? 'selected' : '' }}>Physical Altercation</option>
                            <option value="Theft" {{ old('incident_type') == 'Theft' ? 'selected' : '' }}>Theft</option>
                            <option value="Trespassing" {{ old('incident_type') == 'Trespassing' ? 'selected' : '' }}>Trespassing</option>
                            <option value="Verbal Argument" {{ old('incident_type') == 'Verbal Argument' ? 'selected' : '' }}>Verbal Argument</option>
                            <option value="Other" {{ old('incident_type') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="incident_date">Incident Date *</label>
                        <input type="date" id="incident_date" name="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                        <small>Must be today or earlier</small>
                    </div>

                    <div class="form-group">
                        <label for="incident_time">Incident Time *</label>
                        <input type="time" id="incident_time" name="incident_time" value="{{ old('incident_time', date('H:i')) }}" required>
                    </div>
                </div>

                <!-- Other Incident Type Input (Hidden by default) -->
                <div class="form-group" id="other_incident_container" @if(old('incident_type') == 'Other') style="display: block; margin-top: -10px;" @else style="display: none; margin-top: -10px;" @endif>
                    <label for="other_incident_type">Specify Incident Type *</label>
                    <input type="text" id="other_incident_type" name="other_incident_type" value="{{ old('other_incident_type') }}" placeholder="Please specify the incident type" style="margin-top: 5px;">
                </div>

                <div class="form-group">
                    <label for="incident_location">Incident Location *</label>
                    <textarea id="incident_location" name="incident_location" rows="2" placeholder="Exact location where the incident occurred" required>{{ old('incident_location') }}</textarea>
                </div>

                <!-- Case Description - Using 'description' to match database -->
                <div class="form-group">
                    <label for="description">Case Description *</label>
                    <textarea id="description" name="description" rows="5" placeholder="Detailed description of the incident, including what happened, who was involved, and any witnesses..." required>{{ old('description') }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="previewBtn">
                        <span class="preview-icon">👁️</span> Preview & Confirm
                    </button>
                    <button type="submit" class="btn btn-primary">Add Blotter Case</button>
                    <a href="{{ route('secretary.blotter.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.main-container {
    display: flex;
    min-height: 100vh;
    background: #f4f7fa;
}

.content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 2rem;
}

.page-header p {
    color: #666;
    font-size: 1.1rem;
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #fee2e2;
    border: 1px solid #ef4444;
    color: #dc2626;
}

.form-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

.form-group input[type="text"],
.form-group input[type="date"],
.form-group input[type="time"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.85rem;
}

h3 {
    color: #333;
    margin: 2rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
}

h3:first-of-type {
    margin-top: 0;
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

/* Updated No Results with Inline Button */
.no-results {
    padding: 15px;
    text-align: center;
    color: #6b7280;
    font-style: italic;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.no-results-message {
    margin-bottom: 5px;
}

.btn-add-new-inline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s;
    width: auto;
    min-width: 160px;
    border: none;
    cursor: pointer;
}

.btn-add-new-inline:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-add-new-inline i {
    font-size: 0.9rem;
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

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
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

.btn-outline {
    background: white;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f3f4f6;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
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
    opacity: 0;
    animation: fadeIn 0.3s ease forwards;
}

@keyframes fadeIn {
    to { opacity: 1; }
}

.preview-modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 900px;
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
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
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
    gap: 25px;
}

.preview-section {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #f97316;
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
    color: #f97316;
    font-weight: 600;
    font-size: 1.1rem;
}

.preview-value.warning {
    color: #dc2626;
    font-weight: 600;
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
    padding: 12px 25px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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
    .content {
        padding: 1rem;
    }

    .form-container {
        padding: 1rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
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

<script>
// Residents data for autocomplete
const residents = <?php echo json_encode($residents); ?>;

// Set default date and time to current
document.addEventListener('DOMContentLoaded', function() {
    // Initialize other incident type visibility
    toggleOtherIncident();

    // Setup search functionality
    setupSearch('complainant_search', 'complainant_results', 'complainant_id', 'selected_complainant', 'selected_complainant_name', true);
    setupSearch('respondent_search', 'respondent_results', 'respondent_resident_id', 'selected_respondent', 'selected_respondent_name', false);

    // Add preview button functionality
    document.getElementById('previewBtn').addEventListener('click', function() {
        console.log('Preview button clicked');
        if (!validateBlotterForm()) {
            console.log('Validation failed');
            return;
        }
        console.log('Showing preview...');
        showBlotterPreview();
    });

    // Form submit handler
    document.getElementById('blotterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');

        if (!validateBlotterForm()) {
            console.log('Validation failed');
            return;
        }

        // If preview is showing, use it for confirmation
        if (document.querySelector('.preview-modal')) {
            const confirmBtn = document.querySelector('.confirm-preview-btn');
            if (confirmBtn) {
                confirmBtn.click();
            }
        } else {
            showBlotterPreview();
        }
    });
});

function setupSearch(inputId, resultsId, hiddenId, selectedDivId, selectedNameId, isRequired) {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    const hiddenInput = document.getElementById(hiddenId);
    const selectedDiv = document.getElementById(selectedDivId);
    const selectedName = document.getElementById(selectedNameId);

    let timeout = null;

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
                return fullName.includes(searchTerm) || residentId.includes(searchTerm);
            });

            // Display results
            if (filtered.length > 0) {
                results.innerHTML = '';
                filtered.forEach(resident => {
                    const option = document.createElement('div');
                    option.className = 'resident-option';
                    option.innerHTML = `
                        <div class="resident-name">${resident.first_name} ${resident.last_name}</div>
                        <div class="resident-details">
                            ID: ${resident.resident_id} |
                            Address: ${resident.address}, Purok ${resident.purok}
                        </div>
                    `;

                    option.addEventListener('click', function() {
                        // Set the hidden input
                        hiddenInput.value = resident.id;

                        // Show selected resident
                        selectedName.textContent = `${resident.first_name} ${resident.last_name} (${resident.resident_id})`;
                        selectedDiv.style.display = 'block';

                        // Clear search input
                        input.value = '';
                        results.classList.remove('active');

                        // For respondent, auto-fill name and address
                        if (inputId === 'respondent_search') {
                            document.getElementById('respondent_name').value = `${resident.first_name} ${resident.last_name}`;
                            document.getElementById('respondent_address').value = `${resident.address}, Purok ${resident.purok}`;

                            // Visual feedback
                            document.getElementById('respondent_name').style.borderColor = '#10b981';
                            document.getElementById('respondent_address').style.borderColor = '#10b981';

                            setTimeout(() => {
                                document.getElementById('respondent_name').style.borderColor = '';
                                document.getElementById('respondent_address').style.borderColor = '';
                            }, 2000);
                        }
                    });

                    results.appendChild(option);
                });
            } else {
                // Show "No residents found" with inline Add New Resident button
                results.innerHTML = `
                    <div class="no-results">
                        <div class="no-results-message">No residents found</div>
                        <a href="{{ route('secretary.residents.create') }}" target="_blank" class="btn-add-new-inline">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add New Resident</span>
                        </a>
                    </div>
                `;
            }
        }, 300);
    });

    // Close results when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.remove('active');
        }
    });

    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        const options = results.querySelectorAll('.resident-option');
        let currentIndex = -1;

        options.forEach((option, index) => {
            if (option.classList.contains('highlighted')) {
                currentIndex = index;
            }
        });

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentIndex < options.length - 1) {
                if (currentIndex >= 0) {
                    options[currentIndex].classList.remove('highlighted');
                }
                currentIndex++;
                options[currentIndex].classList.add('highlighted');
                options[currentIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentIndex > 0) {
                options[currentIndex].classList.remove('highlighted');
                currentIndex--;
                options[currentIndex].classList.add('highlighted');
                options[currentIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'Enter' && currentIndex >= 0) {
            e.preventDefault();
            options[currentIndex].click();
        }
    });
}

function clearComplainant() {
    document.getElementById('complainant_id').value = '';
    document.getElementById('selected_complainant').style.display = 'none';
}

function clearRespondent() {
    document.getElementById('respondent_resident_id').value = '';
    document.getElementById('selected_respondent').style.display = 'none';
    document.getElementById('respondent_name').value = '';
    document.getElementById('respondent_address').value = '';
}

function toggleOtherIncident() {
    const incidentType = document.getElementById('incident_type').value;
    const otherContainer = document.getElementById('other_incident_container');
    const otherInput = document.getElementById('other_incident_type');

    if (incidentType === 'Other') {
        otherContainer.style.display = 'block';
        otherInput.required = true;
        setTimeout(() => {
            otherInput.focus();
        }, 100);
    } else {
        otherContainer.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
}

// Clear respondent auto-fill if user manually edits
document.getElementById('respondent_name').addEventListener('input', function() {
    const respondentId = document.getElementById('respondent_resident_id').value;
    if (respondentId) {
        const resident = residents.find(r => r.id == respondentId);
        if (resident && this.value !== `${resident.first_name} ${resident.last_name}`) {
            clearRespondent();
        }
    }
});

document.getElementById('respondent_address').addEventListener('input', function() {
    const respondentId = document.getElementById('respondent_resident_id').value;
    if (respondentId) {
        const resident = residents.find(r => r.id == respondentId);
        if (resident && this.value !== `${resident.address}, Purok ${resident.purok}`) {
            clearRespondent();
        }
    }
});

// Form validation - Using 'description' to match database
function validateBlotterForm() {
    // Validate complainant is selected
    const complainantId = document.getElementById('complainant_id').value;
    if (!complainantId) {
        showError('Please select a complainant from the search results.');
        document.getElementById('complainant_search').focus();
        return false;
    }

    // Validate respondent name
    const respondentName = document.getElementById('respondent_name').value;
    if (!respondentName.trim()) {
        showError('Please enter respondent name.');
        document.getElementById('respondent_name').focus();
        return false;
    }

    // Validate incident type
    const incidentType = document.getElementById('incident_type').value;
    const otherInput = document.getElementById('other_incident_type');

    if (!incidentType) {
        showError('Please select incident type.');
        document.getElementById('incident_type').focus();
        return false;
    }

    if (incidentType === 'Other') {
        if (!otherInput.value.trim()) {
            showError('Please specify the incident type.');
            otherInput.focus();
            return false;
        }
    }

    // Validate incident date is not in future
    const incidentDate = new Date(document.getElementById('incident_date').value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (incidentDate > today) {
        showError('Incident date cannot be in the future.');
        document.getElementById('incident_date').focus();
        return false;
    }

    // Validate incident date is not too far in past (5 years max)
    const fiveYearsAgo = new Date();
    fiveYearsAgo.setFullYear(fiveYearsAgo.getFullYear() - 5);

    if (incidentDate < fiveYearsAgo) {
        if (!confirm('Incident date appears to be more than 5 years ago. Is this correct?')) {
            document.getElementById('incident_date').focus();
            return false;
        }
    }

    // Validate required fields - Using 'description' to match database
    const requiredFields = [
        {id: 'incident_location', label: 'Incident Location'},
        {id: 'description', label: 'Case Description'}
    ];

    for (const field of requiredFields) {
        const fieldElement = document.getElementById(field.id);
        if (!fieldElement.value.trim()) {
            showError(`Please fill in ${field.label}`);
            fieldElement.focus();
            return false;
        }
    }

    return true;
}

// Helper function to show error messages
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

// Function to show blotter preview - Using 'description' to match database
function showBlotterPreview() {
    // Get complainant info
    const complainantId = document.getElementById('complainant_id').value;
    const complainant = residents.find(r => r.id == complainantId);
    const complainantName = complainant ?
        `${complainant.first_name} ${complainant.last_name} (${complainant.resident_id})` :
        'Not selected';
    const complainantAddress = complainant ?
        `${complainant.address}, Purok ${complainant.purok}` :
        'Not available';

    // Get the incident type
    const incidentTypeSelect = document.getElementById('incident_type');
    const otherIncidentInput = document.getElementById('other_incident_type');

    let finalIncidentType = incidentTypeSelect.value;
    if (finalIncidentType === 'Other' && otherIncidentInput) {
        finalIncidentType = otherIncidentInput.value || 'Other (not specified)';
    }

    // Get case ID from the input field
    const caseId = document.getElementById('case_id').value || 'Will be generated upon save';

    // Collect all form data - Using 'description' to match database
    const formData = {
        complainant_name: complainantName,
        complainant_address: complainantAddress,
        respondent_name: document.getElementById('respondent_name').value,
        respondent_address: document.getElementById('respondent_address').value,
        incident_type: finalIncidentType,
        incident_date: document.getElementById('incident_date').value,
        incident_time: document.getElementById('incident_time').value,
        incident_location: document.getElementById('incident_location').value,
        description: document.getElementById('description').value,
        case_id: caseId
    };

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'preview-modal active';
    modal.innerHTML = `
        <div class="preview-modal-content">
            <div class="preview-header">
                <h3><span class="header-icon">⚖️</span> Blotter Case Preview</h3>
                <button type="button" class="preview-close">&times;</button>
            </div>

            <div class="preview-body">
                <div class="preview-alert">
                    <span class="alert-icon">⚠️</span>
                    <span>Please review all case details before submitting</span>
                </div>

                <div class="preview-sections">
                    <div class="preview-section">
                        <h4>📋 Case Information</h4>
                        <div class="preview-grid">
                            <div class="preview-item">
                                <span class="preview-label">Case ID:</span>
                                <span class="preview-value highlight">${formData.case_id}</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Incident Type:</span>
                                <span class="preview-value">${formData.incident_type}</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Incident Date:</span>
                                <span class="preview-value">${formData.incident_date}</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Incident Time:</span>
                                <span class="preview-value">${formData.incident_time}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-section">
                        <h4>👤 Complainant Information</h4>
                        <div class="preview-grid">
                            <div class="preview-item">
                                <span class="preview-label">Complainant:</span>
                                <span class="preview-value">${formData.complainant_name}</span>
                            </div>
                            <div class="preview-item full-width">
                                <span class="preview-label">Address:</span>
                                <span class="preview-value">${formData.complainant_address}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-section">
                        <h4>👥 Respondent Information</h4>
                        <div class="preview-grid">
                            <div class="preview-item">
                                <span class="preview-label">Respondent Name:</span>
                                <span class="preview-value">${formData.respondent_name}</span>
                            </div>
                            <div class="preview-item full-width">
                                <span class="preview-label">Respondent Address:</span>
                                <span class="preview-value">${formData.respondent_address || 'Not provided'}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-section">
                        <h4>📍 Incident Location</h4>
                        <div class="preview-grid">
                            <div class="preview-item full-width">
                                <span class="preview-label">Where it happened:</span>
                                <div class="preview-text">${formData.incident_location}</div>
                            </div>
                        </div>
                    </div>

                    <div class="preview-section">
                        <h4>📝 Case Description</h4>
                        <div class="preview-grid">
                            <div class="preview-item full-width">
                                <span class="preview-label">Detailed Description:</span>
                                <div class="preview-text">${formData.description}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="preview-footer">
                <div class="preview-warning">
                    <span class="warning-icon">⚠️</span>
                    <span>This action will create a permanent blotter record. Blotter cases are official documents and cannot be easily modified.</span>
                </div>
                <div class="preview-actions">
                    <button type="button" class="btn btn-outline edit-btn">
                        <span class="btn-icon">✏️</span> Edit Details
                    </button>
                    <button type="button" class="btn btn-secondary cancel-btn">
                        <span class="btn-icon">❌</span> Cancel
                    </button>
                    <button type="button" class="btn btn-primary confirm-preview-btn">
                        <span class="btn-icon">⚖️</span> Confirm & File Case
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add event listeners
    modal.querySelector('.preview-close').addEventListener('click', closePreview);
    modal.querySelector('.cancel-btn').addEventListener('click', closePreview);
    modal.querySelector('.edit-btn').addEventListener('click', closePreview);

    modal.querySelector('.confirm-preview-btn').addEventListener('click', function() {
        const btn = this;
        btn.innerHTML = '<span class="loading-spinner"></span> Filing Case...';
        btn.disabled = true;

        // Disable other buttons
        modal.querySelectorAll('button').forEach(b => b.disabled = true);

        // Submit form after a short delay
        setTimeout(() => {
            document.getElementById('blotterForm').submit();
        }, 800);
    });

    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closePreview();
        }
    });

    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closePreview() {
    const modal = document.querySelector('.preview-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}
</script>
@endsection
