@extends('layouts.app')

@section('title', 'Add New Resident')

@section('content')
<!-- Toast Notification -->
<div id="toast" class="toast">
    <div class="toast-content">
        <x-heroicon-o-check-circle class="toast-icon success" />
        <span id="toastMessage">Resident saved successfully!</span>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Review Resident Information</h3>
            <button type="button" class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="review-section">
                <h4>Personal Information</h4>
                <div class="review-grid" id="personalInfo"></div>
            </div>
            <div class="review-section" id="familyInfoReview" style="display: none;">
                <h4>Family Information</h4>
                <div class="review-grid" id="familyInfo"></div>
            </div>
            <div class="review-section">
                <h4>Status Information</h4>
                <div class="review-grid" id="statusInfo"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn-primary" id="confirmSubmit">Confirm & Save</button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Add New Resident</h1>
            <p>Create a new resident record</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to List
            </a>
        </div>
    </div>

    <div class="form-container">
        <form action="{{ route('secretary.residents.store') }}" method="POST" class="resident-form" id="createResidentForm">
            @csrf

            <!-- Basic Information Section -->
            <div class="form-section">
                <h2>Basic Information</h2>
                <div class="form-grid">
                    <!-- Resident ID (Auto-generated) -->
                    <div class="form-group">
                        <label for="resident_id">Resident ID(Auto Generated) <span class="required">*</span></label>
                        <div class="id-input-wrapper">
                            <input type="text"
                                   id="resident_id"
                                   name="resident_id"
                                   value="{{ $generatedId }}"
                                   class="form-control @error('resident_id') is-invalid @enderror"
                                   readonly>
                        </div>
                        @error('resident_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- First Name -->
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text"
                               id="first_name"
                               name="first_name"
                               value="{{ old('first_name') }}"
                               class="form-control @error('first_name') is-invalid @enderror"
                               required>
                        @error('first_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text"
                               id="last_name"
                               name="last_name"
                               value="{{ old('last_name') }}"
                               class="form-control @error('last_name') is-invalid @enderror"
                               required>
                        @error('last_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Middle Name -->
                    <div class="form-group">
                        <label for="middle_name">Middle Name(Optional)</label>
                        <input type="text"
                               id="middle_name"
                               name="middle_name"
                               value="{{ old('middle_name') }}"
                               class="form-control @error('middle_name') is-invalid @enderror">
                        @error('middle_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Birth Date with auto age calculation -->
                    <div class="form-group">
                        <label for="birthdate">Birth Date <span class="required">*</span></label>
                        <input type="date"
                               id="birthdate"
                               name="birthdate"
                               value="{{ old('birthdate') }}"
                               class="form-control @error('birthdate') is-invalid @enderror"
                               required
                               onchange="updateSeniorCitizenStatus()">
                        @error('birthdate')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Civil Status -->
                    <div class="form-group">
                        <label for="civil_status">Civil Status <span class="required">*</span></label>
                        <select id="civil_status" name="civil_status" class="form-control @error('civil_status') is-invalid @enderror" required onchange="toggleFamilyFields()">
                            <option value="">Select Status</option>
                            <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            <option value="Divorced" {{ old('civil_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        </select>
                        @error('civil_status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Family Information Section (Shows only when Married is selected) -->
            <div id="family_info_section" style="display: {{ old('civil_status') == 'Married' ? 'block' : 'none' }};">
                <div class="form-section">
                    <h2>Family Information</h2>
                    <div class="form-grid">
                        <!-- Spouse Name -->
                        <div class="form-group">
                            <label for="spouse_name">Spouse's Full Name</label>
                            <input type="text"
                                   id="spouse_name"
                                   name="spouse_name"
                                   value="{{ old('spouse_name') }}"
                                   class="form-control @error('spouse_name') is-invalid @enderror"
                                   placeholder="Enter spouse's full name">
                            <div class="help-text">
                                <i class="fas fa-heart"></i>
                                Enter the complete name of the spouse (First and Last Name)
                            </div>
                            @error('spouse_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Children Section -->
                    <div class="form-section" style="margin-top: 1rem; border-top: 1px dashed #e2e8f0; padding-top: 1rem;">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h4><i class="fas fa-child"></i> Children (Optional)</h4>
                            <button type="button" class="btn-add-child" onclick="addChild()">
                                <i class="fas fa-plus"></i> Add Child
                            </button>
                        </div>
                        <div id="children-container">
                            @php
                                $oldChildren = old('children', []);
                            @endphp
                            @if(count($oldChildren) > 0)
                                @foreach($oldChildren as $index => $child)
                                    <div class="child-card" id="child-card-{{ $index }}">
                                        <div class="child-header">
                                            <span class="child-number">Child {{ $index + 1 }}</span>
                                            <button type="button" class="btn-remove-child" data-child-id="{{ $index }}" onclick="removeChild({{ $index }})">
                                                <i class="fas fa-trash-alt"></i> Remove
                                            </button>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Child's Full Name *</label>
                                                <input type="text" name="children[{{ $index }}][name]" value="{{ $child['name'] ?? '' }}" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Birth Date</label>
                                                <input type="date" name="children[{{ $index }}][birthdate]" value="{{ $child['birthdate'] ?? '' }}" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select name="children[{{ $index }}][gender]" class="form-control">
                                                    <option value="">Select Gender</option>
                                                    <option value="Male" {{ ($child['gender'] ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                                    <option value="Female" {{ ($child['gender'] ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div id="no-children-message" class="help-text" style="color: #6c757d; text-align: center; padding: 1rem; {{ count($oldChildren) > 0 ? 'display: none;' : '' }}">
                            <i class="fas fa-info-circle"></i> No children added yet. Click "Add Child" to add family members.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="form-section">
                <h2>Contact Information</h2>
                <div class="form-grid">
                    <!-- Contact Number -->
                    <div class="form-group">
                        <label for="contact_number">Contact Number(Optional)</label>
                        <input type="tel"
                               id="contact_number"
                               name="contact_number"
                               value="{{ old('contact_number') }}"
                               class="form-control @error('contact_number') is-invalid @enderror"
                               maxlength="11"
                               pattern="[0-9]+"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                               title="Please enter 11-digit mobile number (e.g., 09123456789)">
                        <small class="form-text text-muted">Enter 11-digit mobile number (e.g., 09123456789)</small>
                        @error('contact_number')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address(Optional)</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Address Information Section -->
            <div class="form-section">
                <h2>Address Information</h2>
                <div class="form-grid">
                    <!-- Address -->
                    <div class="form-group full-width">
                        <label for="address">Complete Address <span class="required">*</span></label>
                        <textarea id="address"
                                  name="address"
                                  class="form-control @error('address') is-invalid @enderror"
                                  rows="2"
                                  required>{{ old('address') }}</textarea>
                        @error('address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Purok -->
                    <div class="form-group">
                        <label for="purok">Purok <span class="required">*</span></label>
                        <select id="purok"
                                name="purok"
                                class="form-control @error('purok') is-invalid @enderror"
                                required>
                            <option value="">Select Purok</option>
                            <option value="1" {{ old('purok') == '1' ? 'selected' : '' }}>Purok 1</option>
                            <option value="2" {{ old('purok') == '2' ? 'selected' : '' }}>Purok 2</option>
                            <option value="3" {{ old('purok') == '3' ? 'selected' : '' }}>Purok 3</option>
                            <option value="4" {{ old('purok') == '4' ? 'selected' : '' }}>Purok 4</option>
                            <option value="5" {{ old('purok') == '5' ? 'selected' : '' }}>Purok 5</option>
                            <option value="6" {{ old('purok') == '6' ? 'selected' : '' }}>Purok 6</option>
                            <option value="7" {{ old('purok') == '7' ? 'selected' : '' }}>Purok 7</option>
                        </select>
                        @error('purok')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Household Number -->
                    <div class="form-group">
                        <label for="household_number">Household Number</label>
                        <input type="text"
                               id="household_number"
                               name="household_number"
                               value="{{ old('household_number') }}"
                               class="form-control @error('household_number') is-invalid @enderror">
                        @error('household_number')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Special Status Section -->
            <div class="form-section">
                <h2>Special Status</h2>
                <div class="form-grid">
                    <!-- Voter Status -->
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_voter" value="1" {{ old('is_voter') ? 'checked' : '' }}>
                            <span>Registered Voter</span>
                        </label>
                    </div>

                    <!-- 4Ps Member -->
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_4ps" value="1" {{ old('is_4ps') ? 'checked' : '' }}>
                            <span>4Ps Member</span>
                        </label>
                    </div>

                    <!-- Senior Citizen - Auto-managed by birthdate -->
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_senior" value="1" {{ old('is_senior') ? 'checked' : '' }} id="is_senior_checkbox">
                            <span>Senior Citizen</span>
                        </label>
                        <small class="help-text" id="senior_eligibility_hint"></small>
                    </div>

                    <!-- PWD -->
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_pwd" value="1" {{ old('is_pwd') ? 'checked' : '' }} id="is_pwd_checkbox">
                            <span>Person with Disability (PWD)</span>
                        </label>
                    </div>
                </div>

                <!-- PWD Fields (hidden by default) -->
                <div id="pwd_fields_container" @if(old('is_pwd')) style="display: block; margin-top: 1rem;" @else style="display: none; margin-top: 1rem;" @endif>
                    <div class="form-grid">
                        <!-- PWD ID Number -->
                        <div class="form-group">
                            <label for="pwd_id">PWD ID Number <span class="required">*</span></label>
                            <input type="text"
                                   id="pwd_id"
                                   name="pwd_id"
                                   value="{{ old('pwd_id') }}"
                                   class="form-control @error('pwd_id') is-invalid @enderror"
                                   placeholder="Enter PWD ID number"
                                   maxlength="50">
                            <div class="help-text">
                                <i class="fas fa-info-circle"></i>
                                Enter the PWD ID number from the resident's official PWD card.
                            </div>
                            @error('pwd_id')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Disability Type with "Other" option -->
                        <div class="form-group">
                            <label for="disability_type">Type of Disability <span class="required">*</span></label>
                            <select id="disability_type" name="disability_type_display" class="form-control @error('disability_type') is-invalid @enderror" onchange="toggleDisabilityTypeInput()">
                                <option value="">Select disability type</option>
                                <option value="Physical Disability" {{ old('disability_type') == 'Physical Disability' ? 'selected' : '' }}>Physical Disability (Pisikal nga Pagkabaldado)</option>
                                <option value="Visual Impairment" {{ old('disability_type') == 'Visual Impairment' ? 'selected' : '' }}>Visual Impairment (Biswal nga Pagkabaldado)</option>
                                <option value="Hearing Impairment" {{ old('disability_type') == 'Hearing Impairment' ? 'selected' : '' }}>Hearing Impairment (Pakigbasa nga Pagkabaldado)</option>
                                <option value="Speech Impairment" {{ old('disability_type') == 'Speech Impairment' ? 'selected' : '' }}>Speech Impairment (Pagsulti nga Pagkabaldado)</option>
                                <option value="Intellectual Disability" {{ old('disability_type') == 'Intellectual Disability' ? 'selected' : '' }}>Intellectual Disability (Intelektwal nga Pagkabaldado)</option>
                                <option value="Learning Disability" {{ old('disability_type') == 'Learning Disability' ? 'selected' : '' }}>Learning Disability (Pagkat-on nga Pagkabaldado)</option>
                                <option value="Psychosocial Disability" {{ old('disability_type') == 'Psychosocial Disability' ? 'selected' : '' }}>Psychosocial Disability (Psikolohepikal ug sosyal nga Pagkabaldado)</option>
                                <option value="Multiple Disabilities" {{ old('disability_type') == 'Multiple Disabilities' ? 'selected' : '' }}>Multiple Disabilities (Multipleng Pagkabaldado)</option>
                                <option value="other">Other (specify below)</option>
                            </select>

                            <div id="other_disability_container" style="display: none; margin-top: 0.5rem;">
                                <input type="text"
                                       id="disability_type_other"
                                       name="disability_type_other_temp"
                                       value="{{ old('disability_type') }}"
                                       class="form-control @error('disability_type') is-invalid @enderror"
                                       placeholder="Please specify disability type">
                            </div>

                            <!-- Hidden input stores actual disability type value for submission -->
                            <input type="hidden" id="disability_type_hidden" name="disability_type" value="{{ old('disability_type') }}">

                            @error('disability_type')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-check class="icon-small" />
                    Save Resident
                </button>
                <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Helper text */
    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
        display: block;
    }

    .text-muted {
        color: #6c757d;
    }

    /* Container and Layout */
    .container-fluid { padding: 1.5rem; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
    .page-title h1 { color: #333; margin-bottom: 0.5rem; font-size: 1.8rem; }
    .page-title p { color: #666; font-size: 1rem; }

    /* Buttons */
    .btn-primary, .btn-secondary, .btn-search, .btn-icon, .btn-refresh {
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

    .btn-primary:hover { opacity: 0.9; color: white; }

    .btn-secondary {
        background: white;
        color: #667eea;
        border: 1px solid #667eea;
    }

    .btn-secondary:hover { background: #eef2ff; }

    .btn-search {
        background: #667eea;
        color: white;
    }

    .btn-search:hover { background: #5a67d8; }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        justify-content: center;
        color: #667eea;
        background: none;
    }

    .btn-icon:hover {
        background: #eef2ff;
        transform: translateY(-2px);
    }

    .btn-icon svg { width: 18px; height: 18px; }
    .delete-btn:hover { background: #fee2e2; color: #dc2626; }

    .btn-refresh {
        width: 42px;
        height: 42px;
        padding: 0;
        justify-content: center;
        background: #f8f9fa;
        border: 1px solid #e2e8f0;
        color: #667eea;
    }

    .btn-refresh:hover {
        background: #eef2ff;
        border-color: #667eea;
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

    .form-group { margin-bottom: 1rem; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 500;
    }

    .required { color: #dc2626; margin-left: 0.25rem; }

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

    .form-control.is-invalid { border-color: #dc2626; }
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

    /* Checkbox */
    .checkbox-group { display: flex; align-items: center; }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }

    .icon-small { width: 16px; height: 16px; }

    /* PWD Fields Container */
    #pwd_fields_container {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 5px;
        margin-top: 1rem;
    }

    /* ID Input Wrapper */
    .id-input-wrapper {
        display: flex;
        gap: 0.5rem;
    }

    .id-input-wrapper input { flex: 1; }

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
    }

    .toast-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }

    .toast-icon.success { color: #10b981; }
    .toast-icon.error { color: #dc2626; }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1002;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        animation: fadeIn 0.3s;
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        animation: slideIn 0.3s;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        background: white;
        border-radius: 10px 10px 0 0;
    }

    .modal-header h3 {
        color: #333;
        font-size: 1.25rem;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
        transition: color 0.3s;
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover { color: #666; }

    .modal-body { padding: 1.5rem; }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        position: sticky;
        bottom: 0;
        background: white;
        border-radius: 0 0 10px 10px;
    }

    .review-section { margin-bottom: 1.5rem; }

    .review-section h4 {
        color: #667eea;
        font-size: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #eef2ff;
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .review-item {
        background: #f8fafc;
        padding: 0.75rem;
        border-radius: 5px;
    }

    .review-item.full-width { grid-column: span 2; }

    .review-label {
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 0.25rem;
    }

    .review-value {
        font-weight: 600;
        color: #333;
        word-break: break-word;
    }

    /* Family Section Styles */
    .btn-add-child {
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

    .btn-add-child:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .child-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }

    .child-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .child-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .child-number {
        font-weight: 600;
        color: #10b981;
        font-size: 0.9rem;
    }

    .btn-remove-child {
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

    .btn-remove-child:hover {
        background: #fee2e2;
    }

    .section-header h4 {
        margin: 0;
        color: #333;
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-header h4 i {
        color: #10b981;
    }

    /* Help text */
    .help-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
        display: block;
    }

    /* Age Display */
    .age-display {
        font-size: 0.85rem;
        color: #667eea;
        margin-top: 0.25rem;
        font-weight: 500;
    }

    /* Animations */
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

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .child-card .form-row {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Real-time duplicate checking
let duplicateCheckTimeout = null;

function checkDuplicateResident() {
    const firstName = document.getElementById('first_name').value;
    const lastName = document.getElementById('last_name').value;
    const birthdate = document.getElementById('birthdate').value;
    const middleName = document.getElementById('middle_name').value;

    if (!firstName || !lastName || !birthdate) {
        return;
    }

    // Clear previous timeout
    if (duplicateCheckTimeout) {
        clearTimeout(duplicateCheckTimeout);
    }

    // Wait for user to stop typing before checking
    duplicateCheckTimeout = setTimeout(() => {
        fetch('{{ route("secretary.residents.check-duplicate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                middle_name: middleName,
                birthdate: birthdate
            })
        })
        .then(response => response.json())
        .then(data => {
            const duplicateAlert = document.getElementById('duplicate-alert');
            const submitButton = document.querySelector('#createResidentForm button[type="submit"]');

            if (data.exists) {
                if (!duplicateAlert) {
                    const alert = document.createElement('div');
                    alert.id = 'duplicate-alert';
                    alert.className = 'alert alert-warning';
                    alert.style.cssText = 'background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;';
                    alert.innerHTML = `
                        <strong>⚠️ Duplicate Resident Found!</strong><br>
                        A resident with the same name and birthdate already exists:<br>
                        • Name: ${data.resident.full_name}<br>
                        • Resident ID: ${data.resident.resident_id}<br>
                        • Age: ${data.resident.age} years old<br>
                        • Purok: ${data.resident.purok}<br><br>
                        Please check if this is the same person.
                    `;

                    const formContainer = document.querySelector('.form-container');
                    formContainer.insertBefore(alert, document.querySelector('#createResidentForm'));
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.style.opacity = '0.6';
                    submitButton.title = 'Duplicate resident detected';
                }
            } else {
                const duplicateAlert = document.getElementById('duplicate-alert');
                if (duplicateAlert) {
                    duplicateAlert.remove();
                }

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.style.opacity = '1';
                    submitButton.title = '';
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }, 500); // Wait 500ms after user stops typing
}

// Add event listeners for real-time duplicate checking
document.addEventListener('DOMContentLoaded', function() {
    const firstName = document.getElementById('first_name');
    const lastName = document.getElementById('last_name');
    const birthdate = document.getElementById('birthdate');
    const middleName = document.getElementById('middle_name');

    if (firstName) firstName.addEventListener('input', checkDuplicateResident);
    if (lastName) lastName.addEventListener('input', checkDuplicateResident);
    if (birthdate) birthdate.addEventListener('change', checkDuplicateResident);
    if (middleName) middleName.addEventListener('input', checkDuplicateResident);
});
// ============================================
// GLOBAL VARIABLES
// ============================================
let childIndex = {{ count(old('children', [])) }};

// ============================================
// AGE CALCULATION & SENIOR CITIZEN MANAGEMENT
// ============================================

/**
 * Calculate age from birthdate
 * @param {string} birthdate - Date in YYYY-MM-DD format
 * @returns {number|null} Age in years or null if invalid
 */
function calculateAge(birthdate) {
    if (!birthdate) return null;
    const today = new Date();
    const birthDate = new Date(birthdate);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

/**
 * Update senior citizen status based on birthdate
 * Auto-checks/unchecks and enables/disables the senior citizen checkbox
 */
function updateSeniorCitizenStatus() {
    const birthdateInput = document.getElementById('birthdate');
    const seniorCheckbox = document.getElementById('is_senior_checkbox');
    const seniorHint = document.getElementById('senior_eligibility_hint');

    if (!birthdateInput || !seniorCheckbox) return;

    const birthdate = birthdateInput.value;

    if (birthdate) {
        const age = calculateAge(birthdate);
        const isEligibleSenior = age >= 60;

        if (isEligibleSenior) {
            seniorCheckbox.disabled = false;
            if (seniorHint) {
                seniorHint.innerHTML = `<i class="fas fa-check-circle"></i> Resident is eligible for Senior Citizen status (${age} years old)`;
                seniorHint.style.color = '#10b981';
            }
            if (!seniorCheckbox.checked) seniorCheckbox.checked = true;
        } else {
            seniorCheckbox.checked = false;
            seniorCheckbox.disabled = true;
            if (seniorHint) {
                seniorHint.innerHTML = `<i class="fas fa-info-circle"></i> Senior Citizen status requires age 60+. Current age: ${age} years old.`;
                seniorHint.style.color = '#f59e0b';
            }
        }
    } else {
        seniorCheckbox.disabled = false;
        if (seniorHint) seniorHint.innerHTML = '';
    }
}

// ============================================
// FAMILY INFORMATION MANAGEMENT
// ============================================

/** Toggle family section visibility based on civil status */
function toggleFamilyFields() {
    const civilStatus = document.getElementById('civil_status').value;
    const familySection = document.getElementById('family_info_section');
    const familyReview = document.getElementById('familyInfoReview');

    if (familySection) {
        familySection.style.display = civilStatus === 'Married' ? 'block' : 'none';
        if (familyReview) familyReview.style.display = civilStatus === 'Married' ? 'block' : 'none';
    }
}

/** Add a new child entry to the form */
function addChild() {
    const container = document.getElementById('children-container');
    const noChildrenMsg = document.getElementById('no-children-message');
    const index = childIndex;

    const childCard = document.createElement('div');
    childCard.className = 'child-card';
    childCard.id = `child-card-${index}`;
    childCard.innerHTML = `
        <div class="child-header">
            <span class="child-number">Child ${index + 1}</span>
            <button type="button" class="btn-remove-child" data-child-id="${index}">
                <i class="fas fa-trash-alt"></i> Remove
            </button>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Child's Full Name *</label>
                <input type="text" name="children[${index}][name]" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Birth Date</label>
                <input type="date" name="children[${index}][birthdate]" class="form-control">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="children[${index}][gender]" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>
    `;

    container.appendChild(childCard);

    const removeBtn = childCard.querySelector('.btn-remove-child');
    if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            deleteChild(index);
        });
    }

    childIndex++;
    if (noChildrenMsg) noChildrenMsg.style.display = 'none';
}

/** Remove a child entry from the form */
function deleteChild(childId) {
    const childCard = document.getElementById(`child-card-${childId}`);
    const container = document.getElementById('children-container');
    const noChildrenMsg = document.getElementById('no-children-message');

    if (childCard) {
        childCard.remove();
        const remainingChildren = document.querySelectorAll('#children-container .child-card');
        childIndex = remainingChildren.length;

        remainingChildren.forEach((child, idx) => {
            const numberSpan = child.querySelector('.child-number');
            if (numberSpan) numberSpan.textContent = `Child ${idx + 1}`;

            const removeBtn = child.querySelector('.btn-remove-child');
            if (removeBtn) {
                const newRemoveBtn = removeBtn.cloneNode(true);
                removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
                newRemoveBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    deleteChild(idx);
                });
            }

            const inputs = child.querySelectorAll('input, select');
            inputs.forEach(input => {
                const oldName = input.getAttribute('name');
                if (oldName) {
                    const newName = oldName.replace(/children\[\d+\]/, `children[${idx}]`);
                    input.setAttribute('name', newName);
                }
            });

            child.id = `child-card-${idx}`;
        });

        if (container.children.length === 0 && noChildrenMsg) {
            noChildrenMsg.style.display = 'block';
        }
    }
}

/** Reinitialize existing children cards after page load */
function reinitializeExistingChildren() {
    const existingChildren = document.querySelectorAll('#children-container .child-card');
    existingChildren.forEach((child, idx) => {
        if (!child.id) child.id = `child-card-${idx}`;

        const removeBtn = child.querySelector('.btn-remove-child');
        if (removeBtn) {
            removeBtn.removeAttribute('onclick');
            const newRemoveBtn = removeBtn.cloneNode(true);
            removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
            newRemoveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                deleteChild(idx);
            });
        }

        const numberSpan = child.querySelector('.child-number');
        if (numberSpan) numberSpan.textContent = `Child ${idx + 1}`;

        const inputs = child.querySelectorAll('input, select');
        inputs.forEach(input => {
            const oldName = input.getAttribute('name');
            if (oldName && oldName.match(/children\[\d+\]/)) {
                const newName = oldName.replace(/children\[\d+\]/, `children[${idx}]`);
                input.setAttribute('name', newName);
            }
        });
    });
    childIndex = existingChildren.length;
}

// ============================================
// DISABILITY TYPE MANAGEMENT (with "Other" option)
// ============================================

/** Toggle between dropdown and text input for disability type */
function toggleDisabilityTypeInput() {
    const select = document.getElementById('disability_type');
    const otherContainer = document.getElementById('other_disability_container');
    const otherInput = document.getElementById('disability_type_other');
    const hiddenInput = document.getElementById('disability_type_hidden');

    if (!select || !otherContainer || !otherInput || !hiddenInput) return;

    if (select.value === 'other') {
        otherContainer.style.display = 'block';
        select.value = '';
        otherInput.required = true;
        otherInput.focus();
        hiddenInput.value = '';
        otherInput.oninput = () => { hiddenInput.value = otherInput.value; };
    } else {
        otherContainer.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
        hiddenInput.value = select.value;
    }
}

// ============================================
// REVIEW MODAL FUNCTIONS
// ============================================

/** Display form data in confirmation modal */
function populateReviewData(formData) {
    const personalInfo = document.getElementById('personalInfo');
    const age = formData.birthdate ? calculateAge(formData.birthdate) : null;

    personalInfo.innerHTML = `
        <div class="review-item"><div class="review-label">Resident ID</div><div class="review-value">${escapeHtml(formData.resident_id || '')}</div></div>
        <div class="review-item"><div class="review-label">First Name</div><div class="review-value">${escapeHtml(formData.first_name || '')}</div></div>
        <div class="review-item"><div class="review-label">Last Name</div><div class="review-value">${escapeHtml(formData.last_name || '')}</div></div>
        <div class="review-item"><div class="review-label">Middle Name</div><div class="review-value">${escapeHtml(formData.middle_name || 'N/A')}</div></div>
        <div class="review-item"><div class="review-label">Birthdate</div><div class="review-value">${formatDate(formData.birthdate) || ''}<span class="age-display" style="display: block; font-size: 0.85rem; color: #667eea;">Age: ${age !== null ? age : 'N/A'} years old</span></div></div>
        <div class="review-item"><div class="review-label">Gender</div><div class="review-value">${escapeHtml(formData.gender || '')}</div></div>
        <div class="review-item"><div class="review-label">Civil Status</div><div class="review-value">${escapeHtml(formData.civil_status || '')}</div></div>
        <div class="review-item full-width"><div class="review-label">Address</div><div class="review-value">${escapeHtml(formData.address || '')}</div></div>
        <div class="review-item"><div class="review-label">Purok</div><div class="review-value">${escapeHtml(formData.purok || '')}</div></div>
        <div class="review-item"><div class="review-label">Household #</div><div class="review-value">${escapeHtml(formData.household_number || 'N/A')}</div></div>
        <div class="review-item"><div class="review-label">Contact</div><div class="review-value">${escapeHtml(formData.contact_number || 'N/A')}</div></div>
        <div class="review-item"><div class="review-label">Email</div><div class="review-value">${escapeHtml(formData.email || 'N/A')}</div></div>
    `;

    const statusInfo = document.getElementById('statusInfo');
    const statuses = [];
    if (formData.is_voter && formData.is_voter !== '0') statuses.push('Registered Voter');
    if (formData.is_senior && formData.is_senior !== '0') statuses.push('Senior Citizen');
    if (formData.is_pwd && formData.is_pwd !== '0') statuses.push('PWD');
    if (formData.is_4ps && formData.is_4ps !== '0') statuses.push('4Ps Member');

    let pwdInfo = '';
    if (formData.is_pwd && formData.is_pwd !== '0') {
        let disabilityType = formData.disability_type || '';
        if (!disabilityType && formData.disability_type_other_temp) {
            disabilityType = formData.disability_type_other_temp;
        }
        pwdInfo = `
            <div class="review-item"><div class="review-label">PWD ID</div><div class="review-value">${escapeHtml(formData.pwd_id || 'N/A')}</div></div>
            <div class="review-item"><div class="review-label">Disability Type</div><div class="review-value">${escapeHtml(disabilityType || 'Not specified')}</div></div>
        `;
    }

    statusInfo.innerHTML = `
        <div class="review-item full-width"><div class="review-label">Status</div><div class="review-value">${statuses.length ? statuses.join(', ') : 'None'}</div></div>
        ${pwdInfo}
    `;

    // Family information review
    const familyInfo = document.getElementById('familyInfo');
    if (familyInfo) {
        let familyHtml = '';
        if (formData.spouse_name) {
            familyHtml += `<div class="review-item full-width"><div class="review-label">Spouse Name</div><div class="review-value">${escapeHtml(formData.spouse_name)}</div></div>`;
        }

        const childrenData = [];
        for (let i = 0; i < childIndex; i++) {
            const childName = formData[`children[${i}][name]`];
            if (childName) {
                const childBirthdate = formData[`children[${i}][birthdate]`] || 'N/A';
                const childGender = formData[`children[${i}][gender]`] || 'N/A';
                childrenData.push(`${childName} (${childGender}, born ${childBirthdate})`);
            }
        }

        if (childrenData.length > 0) {
            familyHtml += `<div class="review-item full-width"><div class="review-label">Children</div><div class="review-value">${childrenData.join('<br>')}</div></div>`;
        }

        if (familyHtml) {
            familyInfo.innerHTML = familyHtml;
            document.getElementById('familyInfoReview').style.display = 'block';
        } else {
            document.getElementById('familyInfoReview').style.display = 'none';
        }
    }
}

/** Show confirmation modal */
function showConfirmModal(formData) {
    populateReviewData(formData);
    document.getElementById('confirmModal').classList.add('show');
}

/** Close confirmation modal */
function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/** Escape HTML special characters */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/** Format date to readable format */
function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

/** Show toast notification */
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.querySelector('.toast-icon');

    if (!toast || !toastMessage) return;

    toastMessage.textContent = message;
    toast.style.borderLeftColor = type === 'success' ? '#10b981' : '#dc2626';

    if (toastIcon) {
        if (type === 'success') {
            toastIcon.classList.remove('error');
            toastIcon.classList.add('success');
        } else {
            toastIcon.classList.remove('success');
            toastIcon.classList.add('error');
        }
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// ============================================
// CONTACT NUMBER VALIDATION
// ============================================
const contactInput = document.getElementById('contact_number');
if (contactInput) {
    contactInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
    });
}

// ============================================
// PAGE INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize disability type fields
    const select = document.getElementById('disability_type');
    const otherContainer = document.getElementById('other_disability_container');
    const otherInput = document.getElementById('disability_type_other');
    const hiddenInput = document.getElementById('disability_type_hidden');
    const pwdCheckbox = document.getElementById('is_pwd_checkbox');
    const pwdContainer = document.getElementById('pwd_fields_container');

    if (select && otherContainer && otherInput && hiddenInput) {
        const oldValue = hiddenInput.value || select.value;
        const options = Array.from(select.options).map(opt => opt.value);

        if (oldValue && !options.includes(oldValue) && oldValue !== 'other') {
            select.value = 'other';
            otherContainer.style.display = 'block';
            otherInput.value = oldValue;
            otherInput.required = true;
            select.disabled = true;
            hiddenInput.value = oldValue;
        } else if (select.value === 'other') {
            otherContainer.style.display = 'block';
            otherInput.required = true;
            select.disabled = true;
        } else if (select.value && select.value !== 'other') {
            hiddenInput.value = select.value;
        }

        otherInput.addEventListener('input', () => { hiddenInput.value = otherInput.value; });
        select.addEventListener('change', () => { if (select.value !== 'other') hiddenInput.value = select.value; });
    }

    // Initialize other form features
    toggleFamilyFields();
    updateSeniorCitizenStatus();
    reinitializeExistingChildren();

    // Update child index for existing children
    const existingChildren = document.querySelectorAll('#children-container .child-card');
    if (existingChildren.length > 0) {
        childIndex = existingChildren.length;
        const noChildrenMsg = document.getElementById('no-children-message');
        if (noChildrenMsg) noChildrenMsg.style.display = 'none';
    }

    // PWD checkbox toggle
    if (pwdCheckbox && pwdContainer) {
        pwdCheckbox.addEventListener('change', function() {
            pwdContainer.style.display = this.checked ? 'block' : 'none';
            if (!this.checked && hiddenInput) {
                hiddenInput.value = '';
                if (select) select.value = '';
                if (otherInput) otherInput.value = '';
            }
        });
    }

    // Form submission with confirmation
    const createForm = document.getElementById('createResidentForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Update disability type before submission
            const selectField = document.getElementById('disability_type');
            const otherContainerField = document.getElementById('other_disability_container');
            const otherInputField = document.getElementById('disability_type_other');
            const hiddenInputField = document.getElementById('disability_type_hidden');

            if (hiddenInputField) {
                if (otherContainerField && otherContainerField.style.display === 'block' && otherInputField) {
                    hiddenInputField.value = otherInputField.value;
                } else if (selectField && selectField.value !== 'other') {
                    hiddenInputField.value = selectField.value;
                }
            }

            // Collect form data
            const formData = {};
            for (let element of this.elements) {
                if (element.name && element.type !== 'submit') {
                    formData[element.name] = element.type === 'checkbox' ? element.checked : element.value;
                }
            }

            showConfirmModal(formData);

            document.getElementById('confirmSubmit').onclick = () => {
                closeConfirmModal();
                if (hiddenInputField) {
                    if (otherContainerField && otherContainerField.style.display === 'block' && otherInputField) {
                        hiddenInputField.value = otherInputField.value;
                    } else if (selectField && selectField.value !== 'other') {
                        hiddenInputField.value = selectField.value;
                    }
                }
                createForm.submit();
            };
        });
    }

    // Close modal on outside click or Escape key
    window.addEventListener('click', (event) => {
        if (event.target === document.getElementById('confirmModal')) closeConfirmModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeConfirmModal();
    });
});
</script>

{{-- Session messages --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
@endif
@endpush
