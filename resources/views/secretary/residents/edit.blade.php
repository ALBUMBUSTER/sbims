@extends('layouts.app')

@section('title', 'Edit Resident')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Edit Resident</h1>
            <p>Update resident information</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to List
            </a>
        </div>
    </div>

    <div class="form-container">
        {{-- Display validation errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <h4>Please fix the following errors:</h4>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Display success/error messages --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('secretary.residents.update', $resident) }}" method="POST" class="resident-form">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h2>Personal Information</h2>

                <div class="form-grid">
                    {{-- Resident ID (hidden or read-only) --}}
                    <input type="hidden" name="resident_id" value="{{ $resident->resident_id }}">

                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text"
                               id="first_name"
                               name="first_name"
                               value="{{ old('first_name', $resident->first_name) }}"
                               class="form-control @error('first_name') is-invalid @enderror"
                               required>
                        @error('first_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text"
                               id="last_name"
                               name="last_name"
                               value="{{ old('last_name', $resident->last_name) }}"
                               class="form-control @error('last_name') is-invalid @enderror"
                               required>
                        @error('last_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text"
                               id="middle_name"
                               name="middle_name"
                               value="{{ old('middle_name', $resident->middle_name) }}"
                               class="form-control @error('middle_name') is-invalid @enderror">
                        @error('middle_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="birthdate">Birth Date <span class="required">*</span></label>
                        <input type="date"
                               id="birthdate"
                               name="birthdate"
                               value="{{ old('birthdate', $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->format('Y-m-d') : '') }}"
                               class="form-control @error('birthdate') is-invalid @enderror"
                               required>
                        @error('birthdate')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender', $resident->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $resident->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $resident->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="civil_status">Civil Status <span class="required">*</span></label>
                        <select id="civil_status" name="civil_status" class="form-control @error('civil_status') is-invalid @enderror" required>
                            <option value="">Select Status</option>
                            <option value="Single" {{ old('civil_status', $resident->civil_status) == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('civil_status', $resident->civil_status) == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Widowed" {{ old('civil_status', $resident->civil_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            <option value="Divorced" {{ old('civil_status', $resident->civil_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        </select>
                        @error('civil_status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Address Information</h2>

<div class="form-group">
    <label for="purok">Purok <span class="required">*</span></label>
    <select id="purok"
            name="purok"
            class="form-control @error('purok') is-invalid @enderror"
            required>
        <option value="">Select Purok</option>
        <option value="1" {{ old('purok', $resident->purok) == '1' ? 'selected' : '' }}>Purok 1</option>
        <option value="2" {{ old('purok', $resident->purok) == '2' ? 'selected' : '' }}>Purok 2</option>
        <option value="3" {{ old('purok', $resident->purok) == '3' ? 'selected' : '' }}>Purok 3</option>
        <option value="4" {{ old('purok', $resident->purok) == '4' ? 'selected' : '' }}>Purok 4</option>
        <option value="5" {{ old('purok', $resident->purok) == '5' ? 'selected' : '' }}>Purok 5</option>
        <option value="6" {{ old('purok', $resident->purok) == '6' ? 'selected' : '' }}>Purok 6</option>
        <option value="7" {{ old('purok', $resident->purok) == '7' ? 'selected' : '' }}>Purok 7</option>
    </select>
    @error('purok')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

                    <div class="form-group">
                        <label for="household_number">Household Number</label>
                        <input type="text"
                               id="household_number"
                               name="household_number"
                               value="{{ old('household_number', $resident->household_number) }}"
                               class="form-control @error('household_number') is-invalid @enderror">
                        @error('household_number')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Complete Address <span class="required">*</span></label>
                        <textarea id="address"
                                  name="address"
                                  class="form-control @error('address') is-invalid @enderror"
                                  rows="3"
                                  required>{{ old('address', $resident->address) }}</textarea>
                        @error('address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Contact Information</h2>

                <div class="form-grid">
                    <!-- Contact Number with Validation -->
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="tel"
                               id="contact_number"
                               name="contact_number"
                               value="{{ old('contact_number', $resident->contact_number) }}"
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

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email', $resident->email) }}"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Status Information</h2>

                <div class="form-grid">
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox"
                                   name="is_voter"
                                   value="1"
                                   {{ old('is_voter', $resident->is_voter) ? 'checked' : '' }}>
                            <span>Registered Voter</span>
                        </label>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox"
                                   name="is_senior"
                                   value="1"
                                   {{ old('is_senior', $resident->is_senior) ? 'checked' : '' }}>
                            <span>Senior Citizen</span>
                        </label>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox"
                                   name="is_4ps"
                                   value="1"
                                   {{ old('is_4ps', $resident->is_4ps) ? 'checked' : '' }}>
                            <span>4Ps Member</span>
                        </label>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox"
                                   name="is_pwd"
                                   value="1"
                                   id="is_pwd_checkbox"
                                   {{ old('is_pwd', $resident->is_pwd) ? 'checked' : '' }}
                                   onchange="togglePwdFields(this.checked)">
                            <span>PWD</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- PWD Fields - show only if PWD is checked --}}
            <div id="pwd_fields" class="{{ old('is_pwd', $resident->is_pwd ?? false) ? '' : 'hidden' }}">
                <div class="form-section">
                    <h2>PWD Information</h2>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="pwd_id">PWD ID Number</label>
                            <input type="text"
                                   id="pwd_id"
                                   name="pwd_id"
                                   value="{{ old('pwd_id', $resident->pwd_id) }}"
                                   class="form-control @error('pwd_id') is-invalid @enderror">
                            @error('pwd_id')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="disability_type">Type of Disability</label>
                            <input type="text"
                                   id="disability_type"
                                   name="disability_type"
                                   value="{{ old('disability_type', $resident->disability_type) }}"
                                   class="form-control @error('disability_type') is-invalid @enderror">
                            @error('disability_type')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-check class="icon-small" />
                    Update Resident
                </button>
                <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePwdFields(isChecked) {
    const pwdFields = document.getElementById('pwd_fields');
    if (pwdFields) {
        pwdFields.style.display = isChecked ? 'block' : 'none';
    }
}

// Contact number validation
document.addEventListener('DOMContentLoaded', function() {
    const contactInput = document.getElementById('contact_number');
    if (contactInput) {
        contactInput.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            // Limit to 11 characters
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });
    }
});
</script>
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
    cursor: pointer;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
    color: white;
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
    margin: 0.5rem 0 0 1.5rem;
}

.alert-danger li {
    margin-bottom: 0.25rem;
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

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
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
    min-height: 100px;
}

.error-message {
    display: block;
    color: #dc2626;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

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

/* Checkbox */
.checkbox-group {
    display: flex;
    align-items: center;
}

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

.icon-small {
    width: 16px;
    height: 16px;
}

.hidden {
    display: none;
}
</style>
@endpush
