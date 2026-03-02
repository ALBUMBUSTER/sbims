@php
    $resident = $resident ?? null;
@endphp

<div class="form-grid">
    <!-- Resident ID (read-only) -->
    <div class="form-group">
        <label for="resident_id">Resident ID</label>
        <input type="text"
               name="resident_id"
               id="resident_id"
               value="{{ old('resident_id', $resident->resident_id ?? $generatedId ?? '') }}"
               class="form-control @error('resident_id') is-invalid @enderror"
               readonly>
        @error('resident_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- First Name -->
    <div class="form-group">
        <label for="first_name">First Name <span class="required">*</span></label>
        <input type="text"
               name="first_name"
               id="first_name"
               value="{{ old('first_name', $resident->first_name ?? '') }}"
               class="form-control @error('first_name') is-invalid @enderror"
               required>
        @error('first_name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Middle Name -->
    <div class="form-group">
        <label for="middle_name">Middle Name</label>
        <input type="text"
               name="middle_name"
               id="middle_name"
               value="{{ old('middle_name', $resident->middle_name ?? '') }}"
               class="form-control @error('middle_name') is-invalid @enderror">
        @error('middle_name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Last Name -->
    <div class="form-group">
        <label for="last_name">Last Name <span class="required">*</span></label>
        <input type="text"
               name="last_name"
               id="last_name"
               value="{{ old('last_name', $resident->last_name ?? '') }}"
               class="form-control @error('last_name') is-invalid @enderror"
               required>
        @error('last_name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Birthdate -->
    <div class="form-group">
        <label for="birthdate">Birthdate <span class="required">*</span></label>
        <input type="date"
               name="birthdate"
               id="birthdate"
               value="{{ old('birthdate', isset($resident->birthdate) ? $resident->birthdate->format('Y-m-d') : '') }}"
               class="form-control @error('birthdate') is-invalid @enderror"
               required>
        @error('birthdate')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Gender -->
    <div class="form-group">
        <label for="gender">Gender <span class="required">*</span></label>
        <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror" required>
            <option value="">Select Gender</option>
            <option value="Male" {{ old('gender', $resident->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ old('gender', $resident->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
        </select>
        @error('gender')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Civil Status -->
    <div class="form-group">
        <label for="civil_status">Civil Status <span class="required">*</span></label>
        <select name="civil_status" id="civil_status" class="form-control @error('civil_status') is-invalid @enderror" required>
            <option value="">Select Status</option>
            <option value="Single" {{ old('civil_status', $resident->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
            <option value="Married" {{ old('civil_status', $resident->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
            <option value="Widowed" {{ old('civil_status', $resident->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
            <option value="Divorced" {{ old('civil_status', $resident->civil_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
        </select>
        @error('civil_status')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Contact Number -->
    <div class="form-group">
        <label for="contact_number">Contact Number</label>
        <input type="text"
               name="contact_number"
               id="contact_number"
               value="{{ old('contact_number', $resident->contact_number ?? '') }}"
               class="form-control @error('contact_number') is-invalid @enderror">
        @error('contact_number')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Email -->
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email"
               name="email"
               id="email"
               value="{{ old('email', $resident->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror">
        @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Address -->
    <div class="form-group full-width">
        <label for="address">Address <span class="required">*</span></label>
        <textarea name="address"
                  id="address"
                  class="form-control @error('address') is-invalid @enderror"
                  rows="2"
                  required>{{ old('address', $resident->address ?? '') }}</textarea>
        @error('address')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Purok -->
    <div class="form-group">
        <label for="purok">Purok <span class="required">*</span></label>
        <input type="text"
               name="purok"
               id="purok"
               value="{{ old('purok', $resident->purok ?? '') }}"
               class="form-control @error('purok') is-invalid @enderror"
               required>
        @error('purok')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Household Number -->
    <div class="form-group">
        <label for="household_number">Household Number</label>
        <input type="text"
               name="household_number"
               id="household_number"
               value="{{ old('household_number', $resident->household_number ?? '') }}"
               class="form-control @error('household_number') is-invalid @enderror">
        @error('household_number')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Status Checkboxes -->
    <div class="form-group full-width">
        <label>Status Indicators</label>
        <div class="checkbox-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_voter" value="1" {{ old('is_voter', $resident->is_voter ?? false) ? 'checked' : '' }}>
                <span>Registered Voter</span>
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="is_senior" value="1" {{ old('is_senior', $resident->is_senior ?? false) ? 'checked' : '' }}>
                <span>Senior Citizen</span>
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="is_pwd" value="1" {{ old('is_pwd', $resident->is_pwd ?? false) ? 'checked' : '' }}>
                <span>PWD</span>
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="is_4ps" value="1" {{ old('is_4ps', $resident->is_4ps ?? false) ? 'checked' : '' }}>
                <span>4Ps Member</span>
            </label>
        </div>
    </div>

    <!-- PWD Fields (hidden by default) -->
    <div id="pwdFields" style="display: none;" class="full-width">
        <div class="form-group">
            <label for="pwd_id">PWD ID Number</label>
            <input type="text"
                   name="pwd_id"
                   id="pwd_id"
                   value="{{ old('pwd_id', $resident->pwd_id ?? '') }}"
                   class="form-control @error('pwd_id') is-invalid @enderror">
            @error('pwd_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="disability_type">Type of Disability</label>
            <input type="text"
                   name="disability_type"
                   id="disability_type"
                   value="{{ old('disability_type', $resident->disability_type ?? '') }}"
                   class="form-control @error('disability_type') is-invalid @enderror">
            @error('disability_type')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

@push('styles')
<style>
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
    font-weight: 500;
    color: #374151;
}

.form-group .required {
    color: #dc2626;
    margin-left: 0.25rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 5px;
    font-size: 0.95rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control.is-invalid {
    border-color: #dc2626;
}

.invalid-feedback {
    color: #dc2626;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-top: 0.5rem;
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

.checkbox-label span {
    color: #374151;
}

#pwdFields {
    margin-top: 1rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .checkbox-group {
        flex-direction: column;
        gap: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pwdCheckbox = document.querySelector('input[name="is_pwd"]');
    const pwdFields = document.getElementById('pwdFields');

    function togglePwdFields() {
        if (pwdCheckbox && pwdCheckbox.checked) {
            pwdFields.style.display = 'block';
        } else {
            pwdFields.style.display = 'none';
        }
    }

    if (pwdCheckbox) {
        pwdCheckbox.addEventListener('change', togglePwdFields);
        togglePwdFields(); // Initial state
    }
});
</script>
@endpush
