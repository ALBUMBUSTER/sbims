@extends('layouts.app')

@section('title', 'Edit Blotter Case')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Edit Blotter Case</h1>
            <p>Case #: {{ $blotter->case_id }}</p>
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
                        <select name="complainant_id" id="complainant_id" required>
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
                        @php
                            $isClerk = auth()->user()->role_id == 4;
                        @endphp

                        @if($isClerk)
                            {{-- Clerk sees read-only incident type --}}
                            <input type="text"
                                   id="incident_type"
                                   value="{{ old('incident_type', $blotter->incident_type) }}"
                                   class="form-control readonly-field"
                                   readonly>
                            <input type="hidden" name="incident_type" value="{{ old('incident_type', $blotter->incident_type) }}">
                        @else
                            {{-- Secretary/Admin sees editable dropdown --}}
                            <select name="incident_type" id="incident_type" class="form-control @error('incident_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="Physical Injury" {{ old('incident_type', $blotter->incident_type) == 'Physical Injury' ? 'selected' : '' }}>Physical Injury</option>
                                <option value="Harassment" {{ old('incident_type', $blotter->incident_type) == 'Harassment' ? 'selected' : '' }}>Harassment</option>
                                <option value="Threat" {{ old('incident_type', $blotter->incident_type) == 'Threat' ? 'selected' : '' }}>Threat</option>
                                <option value="Property Dispute" {{ old('incident_type', $blotter->incident_type) == 'Property Dispute' ? 'selected' : '' }}>Property Dispute</option>
                                <option value="Boundary Dispute" {{ old('incident_type', $blotter->incident_type) == 'Boundary Dispute' ? 'selected' : '' }}>Boundary Dispute</option>
                                <option value="Domestic Issue" {{ old('incident_type', $blotter->incident_type) == 'Domestic Issue' ? 'selected' : '' }}>Domestic Issue</option>
                                <option value="Theft" {{ old('incident_type', $blotter->incident_type) == 'Theft' ? 'selected' : '' }}>Theft</option>
                                <option value="Trespassing" {{ old('incident_type', $blotter->incident_type) == 'Trespassing' ? 'selected' : '' }}>Trespassing</option>
                                <option value="Verbal Argument" {{ old('incident_type', $blotter->incident_type) == 'Verbal Argument' ? 'selected' : '' }}>Verbal Argument</option>
                                <option value="Physical Altercation" {{ old('incident_type', $blotter->incident_type) == 'Physical Altercation' ? 'selected' : '' }}>Physical Altercation</option>
                                <option value="Other" {{ old('incident_type', $blotter->incident_type) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        @endif
                        @error('incident_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="incident_date">Incident Date <span class="required">*</span></label>
                        @if($isClerk)
                            {{-- Clerk sees read-only incident date --}}
                            <input type="text"
                                   id="incident_date"
                                   value="{{ old('incident_date', $blotter->incident_date ? \Carbon\Carbon::parse($blotter->incident_date)->format('F d, Y') : '') }}"
                                   class="form-control readonly-field"
                                   readonly>
                            <input type="hidden" name="incident_date" value="{{ old('incident_date', $blotter->incident_date ? $blotter->incident_date->format('Y-m-d') : '') }}">
                        @else
                            {{-- Secretary/Admin sees editable date picker --}}
                            <input type="date"
                                   id="incident_date"
                                   name="incident_date"
                                   value="{{ old('incident_date', $blotter->incident_date ? $blotter->incident_date->format('Y-m-d') : '') }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="form-control @error('incident_date') is-invalid @enderror"
                                   required>
                        @endif
                        @error('incident_date')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="incident_location">Incident Location <span class="required">*</span></label>
                        @if($isClerk)
                            {{-- Clerk sees read-only location --}}
                            <input type="text"
                                   id="incident_location"
                                   value="{{ old('incident_location', $blotter->incident_location) }}"
                                   class="form-control readonly-field"
                                   readonly>
                            <input type="hidden" name="incident_location" value="{{ old('incident_location', $blotter->incident_location) }}">
                        @else
                            {{-- Secretary/Admin sees editable input --}}
                            <input type="text"
                                   id="incident_location"
                                   name="incident_location"
                                   value="{{ old('incident_location', $blotter->incident_location) }}"
                                   class="form-control @error('incident_location') is-invalid @enderror"
                                   required>
                        @endif
                        @error('incident_location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description <span class="required">*</span></label>
                        @if($isClerk)
                            {{-- Clerk sees read-only description --}}
                            <textarea id="description"
                                      class="form-control readonly-field"
                                      rows="4"
                                      readonly>{{ old('description', $blotter->description) }}</textarea>
                            <input type="hidden" name="description" value="{{ old('description', $blotter->description) }}">
                        @else
                            {{-- Secretary/Admin sees editable textarea --}}
                            <textarea id="description"
                                      name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4"
                                      required>{{ old('description', $blotter->description) }}</textarea>
                        @endif
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status and Resolution -->
            <div class="form-section">
                <h2>Case Status</h2>
                <div class="form-grid">
                    <!-- Status Field -->
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>

                        @php
                            $currentStatus = old('status', $blotter->status);
                        @endphp

                        @if($isClerk)
                            {{-- Clerk sees read-only status --}}
                            <input type="text"
                                   id="status"
                                   value="{{ $currentStatus }}"
                                   class="form-control readonly-field"
                                   readonly>
                            <input type="hidden" name="status" value="{{ $currentStatus }}">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Status cannot be changed by clerk. Please contact secretary for status updates.
                            </small>
                        @else
                            {{-- Secretary/Admin sees full editable dropdown --}}
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required onchange="toggleResolutionFields()">
                                <option value="Pending" {{ $currentStatus == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Ongoing" {{ $currentStatus == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="Investigating" {{ $currentStatus == 'Investigating' ? 'selected' : '' }}>Investigating</option>
                                <option value="Hearings" {{ $currentStatus == 'Hearings' ? 'selected' : '' }}>Hearings</option>
                                <option value="Settled" {{ $currentStatus == 'Settled' ? 'selected' : '' }}>Settled</option>
                                <option value="Referred" {{ $currentStatus == 'Referred' ? 'selected' : '' }}>Referred</option>
                                <option value="Dismissed" {{ $currentStatus == 'Dismissed' ? 'selected' : '' }}>Dismissed</option>
                            </select>
                        @endif

                        @error('status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Resolution Fields - Show when status is Settled -->
                    @php
                        $showResolution = !$isClerk && (old('status', $blotter->status) == 'Settled');
                    @endphp

                    <div id="resolutionFields" class="full-width" style="display: {{ $showResolution ? 'block' : 'none' }};">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="resolution">Resolution <span class="required" id="resolutionRequired">*</span></label>
                                @if($isClerk)
                                    {{-- Clerk sees read-only resolution --}}
                                    <textarea id="resolution"
                                              class="form-control readonly-field"
                                              rows="3"
                                              readonly>{{ old('resolution', $blotter->resolution) }}</textarea>
                                    <input type="hidden" name="resolution" value="{{ old('resolution', $blotter->resolution) }}">
                                @else
                                    {{-- Secretary/Admin sees editable textarea --}}
                                    <textarea id="resolution"
                                              name="resolution"
                                              class="form-control @error('resolution') is-invalid @enderror"
                                              rows="3">{{ old('resolution', $blotter->resolution) }}</textarea>
                                @endif
                                @error('resolution')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="resolved_date">Resolution Date</label>
                                @if($isClerk)
                                    {{-- Clerk sees read-only date --}}
                                    <input type="text"
                                           id="resolved_date"
                                           value="{{ old('resolved_date', $blotter->resolved_date ? \Carbon\Carbon::parse($blotter->resolved_date)->format('F d, Y') : '') }}"
                                           class="form-control readonly-field"
                                           readonly>
                                    <input type="hidden" name="resolved_date" value="{{ old('resolved_date', $blotter->resolved_date ? $blotter->resolved_date->format('Y-m-d') : '') }}">
                                @else
                                    {{-- Secretary/Admin sees editable date picker --}}
                                    <input type="date"
                                           id="resolved_date"
                                           name="resolved_date"
                                           value="{{ old('resolved_date', $blotter->resolved_date ? $blotter->resolved_date->format('Y-m-d') : '') }}"
                                           class="form-control @error('resolved_date') is-invalid @enderror">
                                @endif
                                @error('resolved_date')
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

/* ========== FIX: PROPER CURSOR STYLES ========== */

/* Base form control styles */
.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

/* Editable fields - Normal pointer cursor */
.form-control:not([readonly]):not(:disabled) {
    cursor: pointer !important;
    background-color: #ffffff;
}

/* Editable fields on hover - Visual feedback */
.form-control:not([readonly]):not(:disabled):hover {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Editable fields on focus */
.form-control:not([readonly]):not(:disabled):focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

/* Readonly fields - Not-allowed cursor */
.form-control[readonly],
.form-control:disabled,
.readonly-field {
    cursor: not-allowed !important;
    background-color: #f8f9fa;
    opacity: 0.9;
    pointer-events: auto;
}

/* Select element specific */
select.form-control:not([readonly]):not(:disabled) {
    cursor: pointer !important;
}

select.form-control:not([readonly]):not(:disabled):hover {
    border-color: #667eea;
}

/* Textarea specific */
textarea.form-control:not([readonly]):not(:disabled) {
    cursor: text !important;
}

textarea.form-control:not([readonly]):not(:disabled):hover {
    border-color: #667eea;
}

/* Form group ensures no cursor inheritance issues */
.form-group {
    cursor: default;
}

/* Invalid field styling */
.form-control.is-invalid {
    border-color: #dc2626;
}

/* Error message */
.error-message {
    display: block;
    color: #dc2626;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Helper text */
.text-muted {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.text-muted i {
    margin-right: 0.25rem;
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
    const statusSelect = document.getElementById('status');
    const resolutionFields = document.getElementById('resolutionFields');
    const resolutionTextarea = document.getElementById('resolution');
    const resolutionRequired = document.getElementById('resolutionRequired');

    if (statusSelect && resolutionFields) {
        if (statusSelect.value === 'Settled') {
            resolutionFields.style.display = 'block';
            if (resolutionTextarea) {
                resolutionTextarea.required = true;
            }
            if (resolutionRequired) {
                resolutionRequired.style.display = 'inline';
            }
        } else {
            resolutionFields.style.display = 'none';
            if (resolutionTextarea) {
                resolutionTextarea.required = false;
            }
            if (resolutionRequired) {
                resolutionRequired.style.display = 'none';
            }
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleResolutionFields();
});
</script>
@endpush
