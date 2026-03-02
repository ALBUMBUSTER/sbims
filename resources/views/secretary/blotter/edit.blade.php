@extends('layouts.app')

@section('title', 'Edit Blotter Case')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Edit Blotter Case</h1>
            <p>Case #: {{ $blotter->blotter_number }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.blotter.show', $blotter) }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Details
            </a>
        </div>
    </div>

    <div class="form-container">
        <form action="{{ route('secretary.blotter.update', $blotter) }}" method="POST" class="blotter-form">
            @csrf
            @method('PUT')

            <!-- Complainant Information -->
            <div class="form-section">
                <h2>Complainant Information</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="complainant_id">Select Complainant <span class="required">*</span></label>
                        <select name="complainant_id" id="complainant_id" class="form-control @error('complainant_id') is-invalid @enderror" required>
                            <option value="">Select Resident</option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}" {{ old('complainant_id', $blotter->complainant_id) == $resident->id ? 'selected' : '' }}>
                                    {{ $resident->first_name }} {{ $resident->last_name }} - {{ $resident->address }}
                                </option>
                            @endforeach
                        </select>
                        @error('complainant_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Respondent Information -->
            <div class="form-section">
                <h2>Respondent Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="respondent_name">Respondent Name <span class="required">*</span></label>
                        <input type="text"
                               id="respondent_name"
                               name="respondent_name"
                               value="{{ old('respondent_name', $blotter->respondent_name) }}"
                               class="form-control @error('respondent_name') is-invalid @enderror"
                               required>
                        @error('respondent_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="respondent_address">Respondent Address <span class="required">*</span></label>
                        <textarea id="respondent_address"
                                  name="respondent_address"
                                  class="form-control @error('respondent_address') is-invalid @enderror"
                                  rows="2"
                                  required>{{ old('respondent_address', $blotter->respondent_address) }}</textarea>
                        @error('respondent_address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Incident Details -->
            <div class="form-section">
                <h2>Incident Details</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="incident_type">Incident Type <span class="required">*</span></label>
                        <select name="incident_type" id="incident_type" class="form-control @error('incident_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="Physical Injury" {{ old('incident_type', $blotter->incident_type) == 'Physical Injury' ? 'selected' : '' }}>Physical Injury</option>
                            <option value="Harassment" {{ old('incident_type', $blotter->incident_type) == 'Harassment' ? 'selected' : '' }}>Harassment</option>
                            <option value="Threat" {{ old('incident_type', $blotter->incident_type) == 'Threat' ? 'selected' : '' }}>Threat</option>
                            <option value="Property Dispute" {{ old('incident_type', $blotter->incident_type) == 'Property Dispute' ? 'selected' : '' }}>Property Dispute</option>
                            <option value="Boundary Dispute" {{ old('incident_type', $blotter->incident_type) == 'Boundary Dispute' ? 'selected' : '' }}>Boundary Dispute</option>
                            <option value="Domestic Issue" {{ old('incident_type', $blotter->incident_type) == 'Domestic Issue' ? 'selected' : '' }}>Domestic Issue</option>
                            <option value="Other" {{ old('incident_type', $blotter->incident_type) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('incident_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="incident_date">Incident Date <span class="required">*</span></label>
                        <input type="date"
                               id="incident_date"
                               name="incident_date"
                               value="{{ old('incident_date', $blotter->incident_date->format('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               class="form-control @error('incident_date') is-invalid @enderror"
                               required>
                        @error('incident_date')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="incident_location">Incident Location <span class="required">*</span></label>
                        <input type="text"
                               id="incident_location"
                               name="incident_location"
                               value="{{ old('incident_location', $blotter->incident_location) }}"
                               class="form-control @error('incident_location') is-invalid @enderror"
                               required>
                        @error('incident_location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="complaint_details">Complaint Details <span class="required">*</span></label>
                        <textarea id="complaint_details"
                                  name="complaint_details"
                                  class="form-control @error('complaint_details') is-invalid @enderror"
                                  rows="4"
                                  required>{{ old('complaint_details', $blotter->complaint_details) }}</textarea>
                        @error('complaint_details')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="witnesses">Witnesses</label>
                        <textarea id="witnesses"
                                  name="witnesses"
                                  class="form-control @error('witnesses') is-invalid @enderror"
                                  rows="2"
                                  placeholder="Enter names of witnesses (if any)">{{ old('witnesses', $blotter->witnesses) }}</textarea>
                        @error('witnesses')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status and Resolution -->
            <div class="form-section">
                <h2>Case Status</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required onchange="toggleResolutionFields()">
                            <option value="Pending" {{ old('status', $blotter->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Investigating" {{ old('status', $blotter->status) == 'Investigating' ? 'selected' : '' }}>Investigating</option>
                            <option value="Hearings" {{ old('status', $blotter->status) == 'Hearings' ? 'selected' : '' }}>Hearings</option>
                            <option value="Settled" {{ old('status', $blotter->status) == 'Settled' ? 'selected' : '' }}>Settled</option>
                            <option value="Unsolved" {{ old('status', $blotter->status) == 'Unsolved' ? 'selected' : '' }}>Unsolved</option>
                            <option value="Dismissed" {{ old('status', $blotter->status) == 'Dismissed' ? 'selected' : '' }}>Dismissed</option>
                        </select>
                        @error('status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

<div id="resolutionFields" data-show="{{ old('status', $blotter->status) == 'Settled' ? 'true' : 'false' }}" style="grid-column: 1 / -1;">                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="resolution">Resolution <span class="required">*</span></label>
                                <textarea id="resolution"
                                          name="resolution"
                                          class="form-control @error('resolution') is-invalid @enderror"
                                          rows="3">{{ old('resolution', $blotter->resolution) }}</textarea>
                                @error('resolution')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="settlement_date">Settlement Date</label>
                                <input type="date"
                                       id="settlement_date"
                                       name="settlement_date"
                                       value="{{ old('settlement_date', $blotter->settlement_date ? $blotter->settlement_date->format('Y-m-d') : '') }}"
                                       class="form-control @error('settlement_date') is-invalid @enderror">
                                @error('settlement_date')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-check class="icon-small" />
                    Update Case
                </button>
                <a href="{{ route('secretary.blotter.show', $blotter) }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Same styles as create view */
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

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.icon-small {
    width: 16px;
    height: 16px;
}
</style>
@endpush

@push('scripts')
<script>
function toggleResolutionFields() {
    const status = document.getElementById('status').value;
    const resolutionFields = document.getElementById('resolutionFields');
    const resolution = document.getElementById('resolution');
    const settlementDate = document.getElementById('settlement_date');

    if (status === 'Settled') {
        resolutionFields.style.display = 'block';
        resolution.required = true;
        settlementDate.required = true;
    } else {
        resolutionFields.style.display = 'none';
        resolution.required = false;
        settlementDate.required = false;
    }
}
</script>
@endpush
@push('scripts')
<script>
// Initialize resolution fields visibility
document.addEventListener('DOMContentLoaded', function() {
    const resolutionFields = document.getElementById('resolutionFields');
    if (resolutionFields.dataset.show === 'true') {
        resolutionFields.style.display = 'block';
    } else {
        resolutionFields.style.display = 'none';
    }
});

function toggleResolutionFields() {
    const status = document.getElementById('status').value;
    const resolutionFields = document.getElementById('resolutionFields');
    const resolution = document.getElementById('resolution');
    const settlementDate = document.getElementById('settlement_date');

    if (status === 'Settled') {
        resolutionFields.style.display = 'block';
        if (resolution) resolution.required = true;
        if (settlementDate) settlementDate.required = true;
    } else {
        resolutionFields.style.display = 'none';
        if (resolution) resolution.required = false;
        if (settlementDate) settlementDate.required = false;
    }
}
</script>
@endpush
