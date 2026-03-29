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

                <!-- ========== COMPLAINANTS SECTION (MULTIPLE) ========== -->
                <div class="section-header">
                    <h3><i class="fas fa-user"></i> Complainants <span class="required">*</span></h3>
                    <button type="button" class="btn-add-party" onclick="addParty('complainant')">
                        <i class="fas fa-plus"></i> Add Complainant
                    </button>
                </div>
                <div id="complainants-container">
                    @php
                        $oldComplainants = old('complainants', [['name' => '', 'address' => '', 'contact' => '', 'resident_id' => '']]);
                    @endphp
                    @foreach($oldComplainants as $index => $complainant)
                        <div class="party-card complainant-card" data-index="{{ $index }}">
                            <div class="party-header">
                                <span class="party-number">Complainant {{ $index + 1 }}</span>
                                @if($index > 0)
                                    <button type="button" class="btn-remove-party" onclick="removeParty(this)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                @endif
                            </div>
                            <div class="party-fields">
                                <div class="form-group">
                                    <label>Search Resident *</label>
                                    <div class="search-container">
                                        <input type="text" class="search-input party-search" placeholder="Start typing resident name..."
                                               autocomplete="off" data-type="complainant" data-index="{{ $index }}" value="{{ $complainant['name'] ?? '' }}">
                                        <div class="search-results" data-type="complainant" data-index="{{ $index }}"></div>
                                    </div>
                                    <input type="hidden" name="complainants[{{ $index }}][resident_id]" class="resident-id" value="{{ $complainant['resident_id'] ?? '' }}">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Full Name *</label>
                                        <input type="text" name="complainants[{{ $index }}][name]" class="party-name" value="{{ $complainant['name'] ?? '' }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="complainants[{{ $index }}][address]" class="party-address" rows="2">{{ $complainant['address'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="text" name="complainants[{{ $index }}][contact]" class="party-contact" value="{{ $complainant['contact'] ?? '' }}" maxlength="11">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- ========== RESPONDENTS SECTION (MULTIPLE) ========== -->
                <div class="section-header">
                    <h3><i class="fas fa-user-friends"></i> Respondents <span class="required">*</span></h3>
                    <button type="button" class="btn-add-party" onclick="addParty('respondent')">
                        <i class="fas fa-plus"></i> Add Respondent
                    </button>
                </div>
                <div id="respondents-container">
                    @php
                        $oldRespondents = old('respondents', [['name' => '', 'address' => '', 'resident_id' => '']]);
                    @endphp
                    @foreach($oldRespondents as $index => $respondent)
                        <div class="party-card respondent-card" data-index="{{ $index }}">
                            <div class="party-header">
                                <span class="party-number">Respondent {{ $index + 1 }}</span>
                                @if($index > 0)
                                    <button type="button" class="btn-remove-party" onclick="removeParty(this)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                @endif
                            </div>
                            <div class="party-fields">
                                <div class="form-group">
                                    <label>Search Resident *</label>
                                    <div class="search-container">
                                        <input type="text" class="search-input party-search" placeholder="Start typing resident name..."
                                               autocomplete="off" data-type="respondent" data-index="{{ $index }}" value="{{ $respondent['name'] ?? '' }}">
                                        <div class="search-results" data-type="respondent" data-index="{{ $index }}"></div>
                                    </div>
                                    <input type="hidden" name="respondents[{{ $index }}][resident_id]" class="resident-id" value="{{ $respondent['resident_id'] ?? '' }}">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Full Name *</label>
                                        <input type="text" name="respondents[{{ $index }}][name]" class="party-name" value="{{ $respondent['name'] ?? '' }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address *</label>
                                        <textarea name="respondents[{{ $index }}][address]" class="party-address" rows="2" required>{{ $respondent['address'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
{{-- <!-- ========== WITNESSES SECTION (OPTIONAL) ========== -->
<div class="section-header">
    <h3><i class="fas fa-eye"></i> Witnesses <span class="optional">(Optional)</span></h3>
    <button type="button" class="btn-add-party" onclick="addParty('witness')">
        <i class="fas fa-plus"></i> Add Witness
    </button>
</div>
<div id="witnesses-container">
    @php
        $oldWitnesses = old('witnesses', []);
    @endphp
    @if(count($oldWitnesses) > 0)
        @foreach($oldWitnesses as $index => $witness)
            <div class="party-card witness-card" data-index="{{ $index }}">
                <div class="party-header">
                    <span class="party-number">Witness {{ $index + 1 }}</span>
                    <button type="button" class="btn-remove-party" onclick="removeParty(this)">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                <div class="party-fields">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="witnesses[{{ $index }}][name]" value="{{ $witness['name'] ?? '' }}" required>
                    </div>
                    <div class="form-group">
                        <label>Statement</label>
                        <textarea name="witnesses[{{ $index }}][statement]" rows="2">{{ $witness['statement'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div> --}}

                <!-- Incident Details -->
                <h3>Incident Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="incident_type">Incident Type *</label>
                        <select id="incident_type" name="incident_type" required onchange="toggleOtherIncident()">
                            <option value="">Select Type</option>
                            <option value="Boundary Dispute" {{ old('incident_type') == 'Boundary Dispute' ? 'selected' : '' }}>Boundary Dispute(Ang panaglalis sa yuta)</option>
                            <option value="Noise Complaint" {{ old('incident_type') == 'Noise Complaint' ? 'selected' : '' }}>Noise Complaint(Reklamo sa Kasaba)</option>
                            <option value="Property Damage" {{ old('incident_type') == 'Property Damage' ? 'selected' : '' }}>Property Damage(Kadaot sa Lugar)</option>
                            <option value="Physical Altercation" {{ old('incident_type') == 'Physical Altercation' ? 'selected' : '' }}>Physical Altercation(pisikal nga panaglalis)</option>
                            <option value="Theft" {{ old('incident_type') == 'Theft' ? 'selected' : '' }}>Theft(Pagpangawat)</option>
                            <option value="Trespassing" {{ old('incident_type') == 'Trespassing' ? 'selected' : '' }}>Trespassing(Paglapas)</option>
                            <option value="Verbal Argument" {{ old('incident_type') == 'Verbal Argument' ? 'selected' : '' }}>Verbal Argument(Verbal nga Argumento)</option>
                            <option value="Other" {{ old('incident_type') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="incident_date">Incident Date *</label>
                        <input type="date" id="incident_date" name="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="incident_time">Incident Time *</label>
                        <input type="time" id="incident_time" name="incident_time" value="{{ old('incident_time', date('H:i')) }}" required>
                    </div>
                </div>

                <!-- Other Incident Type Input (Hidden by default) -->
                <div class="form-group" id="other_incident_container" @if(old('incident_type') == 'Other') style="display: block;" @else style="display: none;" @endif>
                    <label for="other_incident_type">Specify Incident Type *</label>
                    <input type="text" id="other_incident_type" name="other_incident_type" value="{{ old('other_incident_type') }}" placeholder="Please specify the incident type">
                </div>

                <div class="form-group">
                    <label for="incident_location">Incident Location *</label>
                    <textarea id="incident_location" name="incident_location" rows="2" placeholder="Exact location where the incident occurred" required>{{ old('incident_location') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="description">Case Description *</label>
                    <textarea id="description" name="description" rows="5" placeholder="Detailed description of the incident, including what happened, who was involved, and any witnesses..." required>{{ old('description') }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="previewBtn">
                        <span class="preview-icon">👁️</span> Preview & Confirm
                    </button>
                    {{-- <button type="submit" class="btn btn-primary">Add Blotter Case</button> --}}
                    <a href="{{ route('secretary.blotter.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* All existing styles remain exactly as you have them */
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

/* ========== ADDITIONAL STYLES FOR MULTIPLE PARTIES ========== */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 2rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.section-header h3 {
    margin: 0;
    padding: 0;
    border: none;
    font-size: 1.25rem;
}

.optional {
    font-size: 0.85rem;
    font-weight: normal;
    color: #6b7280;
}

.btn-add-party {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    transition: all 0.3s;
}

.btn-add-party:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.party-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s;
}

.party-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.party-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.party-number {
    font-weight: 600;
    color: #374151;
}

.btn-remove-party {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.btn-remove-party:hover {
    background: #fee2e2;
}

.party-fields {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.party-fields .form-row {
    margin-bottom: 0;
}

.party-fields .form-group {
    margin-bottom: 0;
}

.complainant-card {
    border-left: 4px solid #3b82f6;
}

.respondent-card {
    border-left: 4px solid #ef4444;
}

.witness-card {
    border-left: 4px solid #10b981;
}

/* Party search results */
.party-card .search-container {
    position: relative;
}

.party-card .search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.party-card .search-results.active {
    display: block;
}

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .party-fields .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<script>
    // ========== TEXT FORMATTING FUNCTIONS ==========
// Format text to Title Case (first letter of each word capitalized)
function formatFormalText(text) {
    if (!text) return '';

    // Split into sentences for better formatting
    let sentences = text.split(/(?<=[.!?])\s+/);

    let formattedSentences = sentences.map(sentence => {
        // Split into words
        let words = sentence.toLowerCase().split(/\s+/);

        // Capitalize first letter of each word
        let formattedWords = words.map(word => {
            // Handle special cases (acronyms, abbreviations)
            if (word.toUpperCase() === word && word.length > 1) {
                return word; // Keep acronyms as is
            }

            // Handle hyphenated words
            if (word.includes('-')) {
                return word.split('-').map(part =>
                    part.charAt(0).toUpperCase() + part.slice(1)
                ).join('-');
            }

            // Capitalize first letter, rest lowercase
            return word.charAt(0).toUpperCase() + word.slice(1);
        });

        return formattedWords.join(' ');
    });

    return formattedSentences.join(' ');
}

// Format location address (specific format for addresses)
function formatLocationText(text) {
    if (!text) return '';

    // Capitalize first letter of each word
    let words = text.toLowerCase().split(/\s+/);
    let formattedWords = words.map(word => {
        // Common words that should stay lowercase in addresses
        const lowercaseWords = ['of', 'and', 'the', 'in', 'on', 'at', 'by', 'for'];

        if (lowercaseWords.includes(word) && words.indexOf(word) > 0) {
            return word;
        }

        return word.charAt(0).toUpperCase() + word.slice(1);
    });

    return formattedWords.join(' ');
}

// Auto-format all text inputs in the form
function setupAutoFormatting() {
    // Format purpose/description fields
    const descriptionField = document.getElementById('description');
    const incidentLocationField = document.getElementById('incident_location');
    const otherIncidentField = document.getElementById('other_incident_type');

    // Format description on blur
    if (descriptionField) {
        descriptionField.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatFormalText(this.value);
            }
        });
    }

    // Format incident location on blur
    if (incidentLocationField) {
        incidentLocationField.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatLocationText(this.value);
            }
        });
    }

    // Format other incident type on blur
    if (otherIncidentField) {
        otherIncidentField.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatFormalText(this.value);
            }
        });
    }

    // Format party names (complainants, respondents, witnesses)
    const partyNameFields = document.querySelectorAll('.party-name');
    partyNameFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatFormalText(this.value);
            }
        });
    });

    // Format party addresses
    const partyAddressFields = document.querySelectorAll('.party-address');
    partyAddressFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatLocationText(this.value);
            }
        });
    });

    // Format witness statements
    const witnessStatementFields = document.querySelectorAll('textarea[name*="[statement]"]');
    witnessStatementFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatFormalText(this.value);
            }
        });
    });
}

// Format all text inputs before preview/submission
function formatAllTextInputs() {
    const form = document.getElementById('blotterForm');
    const textInputs = form.querySelectorAll('input[type="text"], textarea');

    textInputs.forEach(input => {
        if (input.id === 'incident_date' || input.id === 'incident_time') {
            return; // Skip date and time fields
        }

        if (input.value.trim()) {
            if (input.id === 'incident_location' || input.classList.contains('party-address')) {
                input.value = formatLocationText(input.value);
            } else {
                input.value = formatFormalText(input.value);
            }
        }
    });
}
// Residents data for autocomplete
const residents = <?php echo json_encode($residents); ?>;

// Party index counters - track how many of each type exist
// Get the actual count of existing parties from the DOM
let complainantCount = document.querySelectorAll('#complainants-container .party-card').length;
let respondentCount = document.querySelectorAll('#respondents-container .party-card').length;
let witnessCount = document.querySelectorAll('#witnesses-container .party-card').length;

let partyIndexCounters = {
    complainant: complainantCount,
    respondent: respondentCount,
    witness: witnessCount
};

console.log('Initial counts:', partyIndexCounters); // Debug to see initial counts

function addParty(type) {
    const container = document.getElementById(`${type}s-container`);
    const index = partyIndexCounters[type]++;

    const partyCard = document.createElement('div');
    partyCard.className = `party-card ${type}-card`;
    partyCard.dataset.index = index;

    if (type === 'complainant') {
        partyCard.innerHTML = `
            <div class="party-header">
                <span class="party-number">Complainant ${index + 1}</span>
                <button type="button" class="btn-remove-party" onclick="removeParty(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="party-fields">
                <div class="form-group">
                    <label>Search Resident *</label>
                    <div class="search-container">
                        <input type="text" class="search-input party-search" placeholder="Start typing resident name..."
                               autocomplete="off" data-type="${type}" data-index="${index}">
                        <div class="search-results" data-type="${type}" data-index="${index}"></div>
                    </div>
                    <input type="hidden" name="${type}s[${index}][resident_id]" class="resident-id" value="">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="${type}s[${index}][name]" class="party-name" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="${type}s[${index}][address]" class="party-address" rows="2"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="${type}s[${index}][contact]" class="party-contact" maxlength="11">
                </div>
            </div>
        `;
    } else if (type === 'respondent') {
        partyCard.innerHTML = `
            <div class="party-header">
                <span class="party-number">Respondent ${index + 1}</span>
                <button type="button" class="btn-remove-party" onclick="removeParty(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="party-fields">
                <div class="form-group">
                    <label>Search Resident *</label>
                    <div class="search-container">
                        <input type="text" class="search-input party-search" placeholder="Start typing resident name..."
                               autocomplete="off" data-type="${type}" data-index="${index}">
                        <div class="search-results" data-type="${type}" data-index="${index}"></div>
                    </div>
                    <input type="hidden" name="${type}s[${index}][resident_id]" class="resident-id" value="">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="${type}s[${index}][name]" class="party-name" required>
                    </div>
                    <div class="form-group">
                        <label>Address *</label>
                        <textarea name="${type}s[${index}][address]" class="party-address" rows="2" required></textarea>
                    </div>
                </div>
            </div>
        `;
    } else if (type === 'witness') {
        partyCard.innerHTML = `
            <div class="party-header">
                <span class="party-number">Witness ${index + 1}</span>
                <button type="button" class="btn-remove-party" onclick="removeParty(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="party-fields">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="witnesses[${index}][name]" class="party-name" required>
                </div>
                <div class="form-group">
                    <label>Statement</label>
                    <textarea name="witnesses[${index}][statement]" rows="2"></textarea>
                </div>
            </div>
        `;
    }

    container.appendChild(partyCard);

    // Setup search for the new party
    if (type !== 'witness') {
        const searchInput = partyCard.querySelector('.party-search');
        const resultsDiv = partyCard.querySelector('.search-results');
        setupPartySearch(searchInput, resultsDiv);
    }

    // Add formatting for new party fields
    const nameField = partyCard.querySelector('.party-name');
    const addressField = partyCard.querySelector('.party-address');

    if (nameField) {
        nameField.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatFormalText(this.value);
            }
        });
    }

    if (addressField) {
        addressField.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatLocationText(this.value);
            }
        });
    }
}
function removeParty(button) {
    const partyCard = button.closest('.party-card');
    partyCard.remove();
}

function setupPartySearch(input, resultsDiv) {
    let timeout = null;
    const type = input.dataset.type;
    const index = input.dataset.index;

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const searchTerm = this.value.trim().toLowerCase();

            if (searchTerm.length < 2) {
                resultsDiv.classList.remove('active');
                return;
            }

            resultsDiv.innerHTML = '<div class="loading">Searching...</div>';
            resultsDiv.classList.add('active');

            const filtered = residents.filter(resident => {
                const fullName = (resident.first_name + ' ' + resident.last_name).toLowerCase();
                const residentId = resident.resident_id ? resident.resident_id.toLowerCase() : '';
                return fullName.includes(searchTerm) || residentId.includes(searchTerm);
            });

            if (filtered.length > 0) {
                resultsDiv.innerHTML = '';
                filtered.forEach(resident => {
                    const option = document.createElement('div');
                    option.className = 'resident-option';
                    option.innerHTML = `
                        <div class="resident-name">${resident.first_name} ${resident.last_name}</div>
                        <div class="resident-details">
                            ID: ${resident.resident_id} | Address: ${resident.address}, Purok ${resident.purok}
                        </div>
                    `;

                    option.addEventListener('click', function() {
                        const hiddenInput = document.querySelector(`input[name="${type}s[${index}][resident_id]"]`);
                        const nameInput = document.querySelector(`input[name="${type}s[${index}][name]"]`);
                        const addressInput = document.querySelector(`textarea[name="${type}s[${index}][address]"]`);

                        hiddenInput.value = resident.id;
                        nameInput.value = `${resident.first_name} ${resident.last_name}`;
                        if (addressInput) {
                            addressInput.value = `${resident.address}, Purok ${resident.purok}`;
                        }

                        input.value = '';
                        resultsDiv.classList.remove('active');
                    });

                    resultsDiv.appendChild(option);
                });
            } else {
                resultsDiv.innerHTML = `
                    <div class="no-results">
                        <div class="no-results-message">No residents found</div>
                        <a href="{{ route('secretary.residents.create') }}" target="_blank" class="btn-add-new-inline">
                            <i class="fas fa-plus-circle"></i> Add New Resident
                        </a>
                    </div>
                `;
            }
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.classList.remove('active');
        }
    });
}

function toggleOtherIncident() {
    const incidentType = document.getElementById('incident_type').value;
    const otherContainer = document.getElementById('other_incident_container');
    const otherInput = document.getElementById('other_incident_type');

    if (incidentType === 'Other') {
        otherContainer.style.display = 'block';
        otherInput.required = true;
    } else {
        otherContainer.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
}

function validateBlotterForm() {
    // Validate at least one complainant
    const complainants = document.querySelectorAll('#complainants-container .party-card');
    if (complainants.length === 0) {
        showError('Please add at least one complainant.');
        return false;
    }

    // Validate at least one respondent
    const respondents = document.querySelectorAll('#respondents-container .party-card');
    if (respondents.length === 0) {
        showError('Please add at least one respondent.');
        return false;
    }

    // Validate each complainant has name
    for (let i = 0; i < complainants.length; i++) {
        const nameInput = complainants[i].querySelector('input[name*="[name]"]');
        if (!nameInput.value.trim()) {
            showError(`Please enter name for Complainant ${i + 1}.`);
            nameInput.focus();
            return false;
        }
    }

    // Validate each respondent has name and address
    for (let i = 0; i < respondents.length; i++) {
        const nameInput = respondents[i].querySelector('input[name*="[name]"]');
        const addressInput = respondents[i].querySelector('textarea[name*="[address]"]');

        if (!nameInput.value.trim()) {
            showError(`Please enter name for Respondent ${i + 1}.`);
            nameInput.focus();
            return false;
        }
        if (!addressInput.value.trim()) {
            showError(`Please enter address for Respondent ${i + 1}.`);
            addressInput.focus();
            return false;
        }
    }

    // Validate incident details
    const incidentDate = document.getElementById('incident_date').value;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const incidentDateObj = new Date(incidentDate);
    incidentDateObj.setHours(0, 0, 0, 0);

    if (incidentDateObj > today) {
        showError('Incident date cannot be in the future.');
        return false;
    }

    const incidentType = document.getElementById('incident_type').value;
    if (!incidentType) {
        showError('Please select incident type.');
        return false;
    }

    if (incidentType === 'Other') {
        const otherInput = document.getElementById('other_incident_type');
        if (!otherInput.value.trim()) {
            showError('Please specify the incident type.');
            otherInput.focus();
            return false;
        }
    }

    // Validate required fields
    const requiredFields = [
        {id: 'incident_location', label: 'Incident Location'},
        {id: 'description', label: 'Case Description'}
    ];

    for (const field of requiredFields) {
        const fieldElement = document.getElementById(field.id);
        if (!fieldElement.value.trim()) {
            showError(`Please fill in ${field.label}.`);
            fieldElement.focus();
            return false;
        }
    }

    return true;
}

function showError(message) {
    const existingToast = document.querySelector('.error-toast');
    if (existingToast) existingToast.remove();

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

    setTimeout(() => {
        if (errorDiv.parentNode) errorDiv.remove();
    }, 5000);

    errorDiv.querySelector('.error-close').addEventListener('click', () => errorDiv.remove());
}

// Initialize existing party searches and form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Setup search for existing parties
    document.querySelectorAll('.party-search').forEach(searchInput => {
        const resultsDiv = searchInput.closest('.search-container').querySelector('.search-results');
        setupPartySearch(searchInput, resultsDiv);
    });

    // Setup auto-formatting
    setupAutoFormatting();
        // Add formatting to existing party fields
    document.querySelectorAll('.party-name').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatFormalText(this.value);
            }
        });
    });

    document.querySelectorAll('.party-address').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatLocationText(this.value);
            }
        });
    });

    document.querySelectorAll('textarea[name*="[statement]"]').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = formatFormalText(this.value);
            }
        });
    });
    toggleOtherIncident();

    // Form submit handler
    document.getElementById('blotterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Format all text before submission
        formatAllTextInputs();
        if (validateBlotterForm()) {
            this.submit();
        }
    });

    // Preview button handler
    document.getElementById('previewBtn').addEventListener('click', function() {
        formatAllTextInputs();
        if (validateBlotterForm()) {
            showBlotterPreview();
        }
    });
});

function showBlotterPreview() {
    // Format all text before showing preview
    formatAllTextInputs();
    // Get all complainants
    const complainants = [];
    document.querySelectorAll('#complainants-container .party-card').forEach(card => {
        const name = card.querySelector('input[name*="[name]"]').value;
        const address = card.querySelector('textarea[name*="[address]"]')?.value || '';
        const contact = card.querySelector('input[name*="[contact]"]')?.value || '';
        if (name) complainants.push({ name, address, contact });
    });

    // Get all respondents
    const respondents = [];
    document.querySelectorAll('#respondents-container .party-card').forEach(card => {
        const name = card.querySelector('input[name*="[name]"]').value;
        const address = card.querySelector('textarea[name*="[address]"]').value;
        if (name) respondents.push({ name, address });
    });

    // Get witnesses
    const witnesses = [];
    document.querySelectorAll('#witnesses-container .party-card').forEach(card => {
        const name = card.querySelector('input[name*="[name]"]').value;
        const statement = card.querySelector('textarea[name*="[statement]"]')?.value || '';
        if (name) witnesses.push({ name, statement });
    });

    // Get incident details
    const incidentType = document.getElementById('incident_type').value;
    const otherIncident = document.getElementById('other_incident_type').value;
    const finalIncidentType = incidentType === 'Other' ? otherIncident : incidentType;
    const incidentDate = document.getElementById('incident_date').value;
    const incidentTime = document.getElementById('incident_time').value;
    const incidentLocation = document.getElementById('incident_location').value;
    const description = document.getElementById('description').value;
    const caseId = document.getElementById('case_id').value || 'Will be generated upon save';

    // Format date
    const formattedDate = new Date(incidentDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

    // Build complainant HTML
    let complainantHtml = '';
    complainants.forEach((c, i) => {
        complainantHtml += `
            <div class="preview-item full-width" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #e5e7eb;">
                <div style="font-weight: 600; color: #3b82f6; margin-bottom: 5px;">Complainant ${i + 1}</div>
                <div><strong>Name:</strong> ${escapeHtml(c.name)}</div>
                <div><strong>Address:</strong> ${escapeHtml(c.address) || 'N/A'}</div>
                <div><strong>Contact:</strong> ${escapeHtml(c.contact) || 'N/A'}</div>
            </div>
        `;
    });

    // Build respondent HTML
    let respondentHtml = '';
    respondents.forEach((r, i) => {
        respondentHtml += `
            <div class="preview-item full-width" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #e5e7eb;">
                <div style="font-weight: 600; color: #ef4444; margin-bottom: 5px;">Respondent ${i + 1}</div>
                <div><strong>Name:</strong> ${escapeHtml(r.name)}</div>
                <div><strong>Address:</strong> ${escapeHtml(r.address)}</div>
            </div>
        `;
    });

    // Build witness HTML
    let witnessHtml = '';
    if (witnesses.length > 0) {
        witnesses.forEach((w, i) => {
            witnessHtml += `
                <div class="preview-item full-width" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #e5e7eb;">
                    <div style="font-weight: 600; color: #10b981; margin-bottom: 5px;">Witness ${i + 1}</div>
                    <div><strong>Name:</strong> ${escapeHtml(w.name)}</div>
                    <div><strong>Statement:</strong> ${escapeHtml(w.statement) || 'N/A'}</div>
                </div>
            `;
        });
    }

    const modalHtml = `
        <div class="preview-modal active">
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
                                    <span class="preview-value highlight">${escapeHtml(caseId)}</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Incident Type:</span>
                                    <span class="preview-value">${escapeHtml(finalIncidentType)}</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Incident Date:</span>
                                    <span class="preview-value">${formattedDate}</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Incident Time:</span>
                                    <span class="preview-value">${escapeHtml(incidentTime)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-section">
                            <h4>👤 Complainant Information</h4>
                            <div class="preview-grid">
                                ${complainantHtml || '<div class="preview-item full-width">No complainants added</div>'}
                            </div>
                        </div>
                        <div class="preview-section">
                            <h4>👥 Respondent Information</h4>
                            <div class="preview-grid">
                                ${respondentHtml || '<div class="preview-item full-width">No respondents added</div>'}
                            </div>
                        </div>
                        ${witnessHtml ? `<div class="preview-section">
                            <h4>👁️ Witness Information</h4>
                            <div class="preview-grid">
                                ${witnessHtml}
                            </div>
                        </div>` : ''}
                        <div class="preview-section">
                            <h4>📍 Incident Location</h4>
                            <div class="preview-grid">
                                <div class="preview-item full-width">
                                    <div class="preview-text">${escapeHtml(incidentLocation)}</div>
                                </div>
                            </div>
                        </div>
                        <div class="preview-section">
                            <h4>📝 Case Description</h4>
                            <div class="preview-grid">
                                <div class="preview-item full-width">
                                    <div class="preview-text">${escapeHtml(description)}</div>
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
                        <button type="button" class="btn btn-outline edit-btn">✏️ Edit Details</button>
                        <button type="button" class="btn btn-secondary cancel-btn">❌ Cancel</button>
                        <button type="button" class="btn btn-primary confirm-preview-btn">⚖️ Confirm & File Case</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = document.querySelector('.preview-modal');

    modal.querySelector('.preview-close').addEventListener('click', closePreview);
    modal.querySelector('.cancel-btn').addEventListener('click', closePreview);
    modal.querySelector('.edit-btn').addEventListener('click', closePreview);
    modal.querySelector('.confirm-preview-btn').addEventListener('click', function() {
        document.getElementById('blotterForm').submit();
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closePreview();
    });
    document.body.style.overflow = 'hidden';
}

function closePreview() {
    const modal = document.querySelector('.preview-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
