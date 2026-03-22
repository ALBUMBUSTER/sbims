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

            <div class="form-section">
                <h2>Basic Information</h2>

                <div class="form-grid">
                    <!-- Resident ID (Auto-generated) -->
                    <div class="form-group">
                        <label for="resident_id">Resident ID <span class="required">*</span></label>
                        <div class="id-input-wrapper">
                            <input type="text"
                                   id="resident_id"
                                   name="resident_id"
                                   value="{{ $generatedId }}"
                                   class="form-control @error('resident_id') is-invalid @enderror"
                                   readonly>
                            <button type="text" class="text-refresh" onclick="refreshResidentId()" title="Generate new ID">
                                <x-heroicon-o-arrow-path class="icon-small" />
                            </button>
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
                        <label for="middle_name">Middle Name</label>
                        <input type="text"
                               id="middle_name"
                               name="middle_name"
                               value="{{ old('middle_name') }}"
                               class="form-control @error('middle_name') is-invalid @enderror">
                        @error('middle_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Birth Date -->
                    <div class="form-group">
                        <label for="birthdate">Birth Date <span class="required">*</span></label>
                        <input type="date"
                               id="birthdate"
                               name="birthdate"
                               value="{{ old('birthdate') }}"
                               class="form-control @error('birthdate') is-invalid @enderror"
                               required>
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
                        <select id="civil_status" name="civil_status" class="form-control @error('civil_status') is-invalid @enderror" required>
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

            <div class="form-section">
                <h2>Contact Information</h2>

                <div class="form-grid">
                    <!-- Contact Number -->
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text"
                               id="contact_number"
                               name="contact_number"
                               value="{{ old('contact_number') }}"
                               class="form-control @error('contact_number') is-invalid @enderror">
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
                        <input type="text"
                               id="purok"
                               name="purok"
                               value="{{ old('purok') }}"
                               class="form-control @error('purok') is-invalid @enderror"
                               required>
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

                    <!-- Senior Citizen -->
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_senior" value="1" {{ old('is_senior') ? 'checked' : '' }}>
                            <span>Senior Citizen</span>
                        </label>
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
        <div class="form-group">
            <label for="pwd_id">PWD ID Number</label>
            <div class="id-input-wrapper">
                <input type="text"
                       id="pwd_id"
                       name="pwd_id"
                       value="{{ old('pwd_id', $generatedPwdId ?? '') }}"
                       class="form-control @error('pwd_id') is-invalid @enderror"
                       readonly>
            </div>
            @error('pwd_id')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="disability_type">Type of Disability</label>
            <input type="text"
                   id="disability_type"
                   name="disability_type"
                   value="{{ old('disability_type') }}"
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
/* All existing styles from both files combined and deduplicated */
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
</style>
@endpush

@push('scripts')
<script>
// Toast notification function
function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');
    let toastMessage = document.getElementById('toastMessage');
    let toastIcon = document.querySelector('.toast-icon');

    if (!toast || !toastMessage) return;

    toastMessage.textContent = message;

    if (type === 'success') {
        toast.style.borderLeftColor = '#10b981';
        if (toastIcon) {
            toastIcon.classList.remove('error');
            toastIcon.classList.add('success');
        }
    } else if (type === 'error') {
        toast.style.borderLeftColor = '#dc2626';
        if (toastIcon) {
            toastIcon.classList.remove('success');
            toastIcon.classList.add('error');
        }
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Modal functions
function showConfirmModal(formData) {
    populateReviewData(formData);
    document.getElementById('confirmModal').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

function populateReviewData(formData) {
    const personalInfo = document.getElementById('personalInfo');
    personalInfo.innerHTML = `
        <div class="review-item"><div class="review-label">Resident ID</div><div class="review-value">${escapeHtml(formData.resident_id || '')}</div></div>
        <div class="review-item"><div class="review-label">First Name</div><div class="review-value">${escapeHtml(formData.first_name || '')}</div></div>
        <div class="review-item"><div class="review-label">Last Name</div><div class="review-value">${escapeHtml(formData.last_name || '')}</div></div>
        <div class="review-item"><div class="review-label">Middle Name</div><div class="review-value">${escapeHtml(formData.middle_name || 'N/A')}</div></div>
        <div class="review-item"><div class="review-label">Birthdate</div><div class="review-value">${formatDate(formData.birthdate) || ''}</div></div>
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
        pwdInfo = `
            <div class="review-item"><div class="review-label">PWD ID</div><div class="review-value">${escapeHtml(formData.pwd_id || 'N/A')}</div></div>
            <div class="review-item"><div class="review-label">Disability Type</div><div class="review-value">${escapeHtml(formData.disability_type || 'N/A')}</div></div>
        `;
    }

    statusInfo.innerHTML = `
        <div class="review-item full-width"><div class="review-label">Status</div><div class="review-value">${statuses.length ? statuses.join(', ') : 'None'}</div></div>
        ${pwdInfo}
    `;
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

// Auto-generate ID refresh
function refreshResidentId() {
    fetch('{{ route("secretary.residents.generate-id") }}')
        .then(response => response.json())
        .then(data => document.getElementById('resident_id').value = data.id)
        .catch(error => console.error('Error:', error));
}

// Auto-generate PWD ID refresh
function refreshPwdId() {
    fetch('{{ route("secretary.residents.generate-pwd-id") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('pwd_id').value = data.id;
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to generate PWD ID', 'error');
        });
}
// Form submission with confirmation
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.getElementById('createResidentForm');

    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = {};
            for (let element of this.elements) {
                if (element.name && element.type !== 'submit') {
                    formData[element.name] = element.type === 'checkbox' ? element.checked : element.value;
                }
            }

            showConfirmModal(formData);

            document.getElementById('confirmSubmit').onclick = () => {
                closeConfirmModal();
                createForm.submit();
            };
        });
    }

    // Close modal when clicking outside or pressing Escape
    window.addEventListener('click', (event) => {
        if (event.target === document.getElementById('confirmModal')) {
            closeConfirmModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeConfirmModal();
    });

    // PWD fields toggle
    const pwdCheckbox = document.getElementById('is_pwd_checkbox');
    const pwdContainer = document.getElementById('pwd_fields_container');

    if (pwdCheckbox && pwdContainer) {
        pwdCheckbox.addEventListener('change', function() {
            pwdContainer.style.display = this.checked ? 'block' : 'none';
        });
    }
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
