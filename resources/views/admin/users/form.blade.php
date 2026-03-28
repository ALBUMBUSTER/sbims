@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@push('styles')
<style>
    .form-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        max-width: 800px;
        margin: 0 auto;
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

    .form-group label .required-star {
        color: #dc2626;
        margin-left: 0.25rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-group input.is-invalid,
    .form-group select.is-invalid {
        border-color: #dc2626;
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .security-section {
        margin-top: 2rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .security-section h3 {
        color: #333;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .security-section h3 i {
        color: #667eea;
    }

    .help-text {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .warning-message {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        color: #92400e;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .warning-message i {
        font-size: 1rem;
        color: #f59e0b;
    }

    .info-message {
        background: #dbeafe;
        border: 1px solid #3b82f6;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        color: #1e40af;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-message i {
        font-size: 1rem;
        color: #3b82f6;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>{{ isset($user) ? 'Edit User' : 'Create New User' }}</h1>
                <p>{{ isset($user) ? 'Update user information' : 'Add a new user to the system' }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>

        <div class="form-container">
            <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" id="userForm">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required-star">*</span></label>
                        <input type="text" id="username" name="username"
                               value="{{ old('username', $user->username ?? '') }}"
                               class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                               required
                               placeholder="Enter username">
                        @error('username')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', $user->email ?? '') }}"
                               class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="Enter email address">
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required-star">*</span></label>
                        <input type="text" id="full_name" name="full_name"
                               value="{{ old('full_name', $user->full_name ?? '') }}"
                               class="{{ $errors->has('full_name') ? 'is-invalid' : '' }}"
                               required
                               placeholder="Enter full name">
                        @error('full_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role <span class="required-star">*</span></label>
                        @php
                            $hasCaptain = \App\Models\User::where('role_id', 2)->where('is_active', true)->exists();
                            $currentUserRole = isset($user) ? $user->role_id : null;
                            $isEditingCaptain = isset($user) && $user->role_id == 2;
                        @endphp

                        <select id="role_id" name="role_id" required class="{{ $errors->has('role_id') ? 'is-invalid' : '' }}">
                            <option value="">Select Role</option>
                            @if($hasCaptain && !$isEditingCaptain)
                                <option value="1" {{ old('role_id', $user->role_id ?? '') == 1 ? 'selected' : '' }}>Admin</option>
                                <option value="3" {{ old('role_id', $user->role_id ?? '') == 3 ? 'selected' : '' }}>Secretary</option>
                                <option value="4" {{ old('role_id', $user->role_id ?? '') == 4 ? 'selected' : '' }}>Clerk</option>
                            @elseif($hasCaptain && $isEditingCaptain)
                                <option value="1" {{ old('role_id', $user->role_id ?? '') == 1 ? 'selected' : '' }}>Admin</option>
                                <option value="2" {{ old('role_id', $user->role_id ?? '') == 2 ? 'selected' : '' }}>Captain</option>
                                <option value="3" {{ old('role_id', $user->role_id ?? '') == 3 ? 'selected' : '' }}>Secretary</option>
                                <option value="4" {{ old('role_id', $user->role_id ?? '') == 4 ? 'selected' : '' }}>Clerk</option>
                            @else
                                <option value="1" {{ old('role_id', $user->role_id ?? '') == 1 ? 'selected' : '' }}>Admin</option>
                                <option value="2" {{ old('role_id', $user->role_id ?? '') == 2 ? 'selected' : '' }}>Captain</option>
                                <option value="3" {{ old('role_id', $user->role_id ?? '') == 3 ? 'selected' : '' }}>Secretary</option>
                                <option value="4" {{ old('role_id', $user->role_id ?? '') == 4 ? 'selected' : '' }}>Clerk</option>
                            @endif
                        </select>
                        @error('role_id')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Term End Date Field - Only for Captain (Required) -->
                <div class="form-row" id="termEndDateGroup" style="display: none;">
                    <div class="form-group">
                        <label for="term_end_date">Term End Date <span class="required-star" id="termEndDateRequired" style="display: none;">*</span></label>
                        <input type="date" id="term_end_date" name="term_end_date"
                               value="{{ old('term_end_date', isset($user) ? $user->term_end_date : '') }}"
                               class="form-control {{ $errors->has('term_end_date') ? 'is-invalid' : '' }}">
                        <div class="help-text">
                            <i class="fas fa-calendar-alt"></i>
                            Enter the date when this captain's term ends. The account will be automatically deactivated on this date.
                            <strong>This field is required for Captain role.</strong>
                        </div>
                        @error('term_end_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">{{ isset($user) ? 'New Password' : 'Password' }} <span class="required-star">{{ isset($user) ? '' : '*' }}</span></label>
                        <input type="password" id="password" name="password"
                               {{ isset($user) ? '' : 'required' }}
                               class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="{{ isset($user) ? 'Leave blank to keep current' : 'Enter password' }}">
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               placeholder="Confirm password">
                    </div>
                </div>

                <!-- Security Questions Section -->
                <div class="security-section">
                    <h3>
                        <i class="fas fa-shield-alt"></i>
                        Security Questions (For Password Recovery)
                    </h3>

                    <div class="form-group">
                        <label for="security_question">Security Question <span class="required-star">*</span></label>
                        <select id="security_question" name="security_question" required class="{{ $errors->has('security_question') ? 'is-invalid' : '' }}">
                            <option value="">Select a security question</option>
                            <option value="What is your mother's maiden name?" {{ old('security_question', $user->security_question ?? '') == "What is your mother's maiden name?" ? 'selected' : '' }}>What is your mother's maiden name?</option>
                            <option value="What was the name of your first pet?" {{ old('security_question', $user->security_question ?? '') == "What was the name of your first pet?" ? 'selected' : '' }}>What was the name of your first pet?</option>
                            <option value="What elementary school did you attend?" {{ old('security_question', $user->security_question ?? '') == "What elementary school did you attend?" ? 'selected' : '' }}>What elementary school did you attend?</option>
                            <option value="What is your favorite book?" {{ old('security_question', $user->security_question ?? '') == "What is your favorite book?" ? 'selected' : '' }}>What is your favorite book?</option>
                            <option value="What city were you born in?" {{ old('security_question', $user->security_question ?? '') == "What city were you born in?" ? 'selected' : '' }}>What city were you born in?</option>
                            <option value="What is your favorite color?" {{ old('security_question', $user->security_question ?? '') == "What is your favorite color?" ? 'selected' : '' }}>What is your favorite color?</option>
                            <option value="What is your email address?" {{ old('security_question', $user->security_question ?? '') == "What is your email address?" ? 'selected' : '' }}>What is your email address?</option>
                            <option value="When is your birthday?" {{ old('security_question', $user->security_question ?? '') == "When is your birthday?" ? 'selected' : '' }}>When is your birthday?</option>
                            <option value="What is your favorite food?" {{ old('security_question', $user->security_question ?? '') == "What is your favorite food?" ? 'selected' : '' }}>What is your favorite food?</option>
                            <option value="custom">Custom Question (type below)</option>
                        </select>
                        @error('security_question')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" id="customQuestionGroup" style="display: none;">
                        <label for="custom_question">Custom Question <span class="required-star">*</span></label>
                        <input type="text" id="custom_question" name="custom_question"
                               value="{{ old('custom_question') }}"
                               class="{{ $errors->has('custom_question') ? 'is-invalid' : '' }}"
                               placeholder="Type your custom question">
                        <div class="help-text">Enter your own security question</div>
                    </div>

                    <div class="form-group">
                        <label for="security_answer">Security Answer <span class="required-star">*</span></label>
                        <input type="text" id="security_answer" name="security_answer"
                               value="{{ old('security_answer') }}"
                               class="{{ $errors->has('security_answer') ? 'is-invalid' : '' }}"
                               required
                               placeholder="Enter your answer">
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            This will be encrypted and used to verify your identity if you forget your password.
                        </div>
                        @error('security_answer')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-outline" style="background: #667eea; color: white; border-color: #667eea;">
                        <i class="fas fa-save"></i>
                        {{ isset($user) ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const questionSelect = document.getElementById('security_question');
        const customQuestionGroup = document.getElementById('customQuestionGroup');
        const customQuestionInput = document.getElementById('custom_question');
        const roleSelect = document.getElementById('role_id');
        const termEndDateGroup = document.getElementById('termEndDateGroup');
        const termEndDateInput = document.getElementById('term_end_date');
        const termEndDateRequired = document.getElementById('termEndDateRequired');
        const form = document.getElementById('userForm');

        function toggleCustomQuestion() {
            if (questionSelect.value === 'custom') {
                customQuestionGroup.style.display = 'block';
                customQuestionInput.required = true;
            } else {
                customQuestionGroup.style.display = 'none';
                customQuestionInput.required = false;
            }
        }

        function toggleTermEndDateField() {
            if (roleSelect && termEndDateGroup) {
                if (roleSelect.value == '2') { // Captain role
                    termEndDateGroup.style.display = 'block';
                    termEndDateInput.required = true;
                    termEndDateRequired.style.display = 'inline';
                } else {
                    termEndDateGroup.style.display = 'none';
                    termEndDateInput.required = false;
                    termEndDateRequired.style.display = 'none';
                }
            }
        }

        // Add form validation for term end date
        function validateTermEndDate() {
            if (roleSelect.value == '2') {
                const termEndDate = termEndDateInput.value;
                if (!termEndDate) {
                    const errorDiv = termEndDateInput.parentElement.querySelector('.error-message');
                    if (!errorDiv) {
                        const newError = document.createElement('div');
                        newError.className = 'error-message';
                        newError.innerHTML = 'Term end date is required for Captain role.';
                        termEndDateInput.parentElement.appendChild(newError);
                        termEndDateInput.classList.add('is-invalid');
                    }
                    return false;
                } else {
                    // Remove error if exists
                    const existingError = termEndDateInput.parentElement.querySelector('.error-message');
                    if (existingError) existingError.remove();
                    termEndDateInput.classList.remove('is-invalid');
                    return true;
                }
            }
            return true;
        }

        // Add form submit validation
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validate term end date for captain
                if (roleSelect.value == '2') {
                    if (!termEndDateInput.value) {
                        e.preventDefault();
                        validateTermEndDate();
                        termEndDateInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                }

                // Handle custom question
                if (questionSelect.value === 'custom') {
                    const customQuestion = customQuestionInput.value.trim();
                    if (customQuestion) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'security_question';
                        hiddenInput.value = customQuestion;
                        questionSelect.disabled = true;
                        this.appendChild(hiddenInput);
                    } else {
                        e.preventDefault();
                        alert('Please enter your custom question');
                        customQuestionInput.focus();
                        return false;
                    }
                }
            });
        }

        questionSelect.addEventListener('change', toggleCustomQuestion);
        toggleCustomQuestion();

        roleSelect.addEventListener('change', function() {
            toggleTermEndDateField();
            // Clear error when changing role
            const existingError = termEndDateInput.parentElement.querySelector('.error-message');
            if (existingError) existingError.remove();
            termEndDateInput.classList.remove('is-invalid');
        });

        // Add real-time validation for term end date
        if (termEndDateInput) {
            termEndDateInput.addEventListener('change', function() {
                if (roleSelect.value == '2') {
                    validateTermEndDate();
                }
            });
        }

        toggleTermEndDateField();

        // Show error if term end date is missing on page load (for edit mode with captain)
        if (roleSelect.value == '2' && termEndDateInput && !termEndDateInput.value) {
            validateTermEndDate();
        }
    });
</script>
@endpush
