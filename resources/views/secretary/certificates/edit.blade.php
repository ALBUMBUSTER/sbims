@extends('layouts.app')

@section('title', 'Edit Certificate')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Edit Certificate</h1>
            <p>Certificate #: {{ $certificate->certificate_id }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.certificates.show', $certificate) }}" class="btn-secondary">
                <i class="fas fa-arrow-left icon-small"></i>
                Back to Details
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

        <form action="{{ route('secretary.certificates.update', $certificate) }}" method="POST" class="certificate-form">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h2>Certificate Information</h2>

                <div class="form-grid">
                    <!-- Select Resident -->
                    <div class="form-group full-width">
                        <label for="resident_id">Select Resident <span class="required">*</span></label>
                        <select name="resident_id" id="resident_id" class="form-control @error('resident_id') is-invalid @enderror" required>
                            <option value="">-- Select Resident --</option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}" {{ old('resident_id', $certificate->resident_id) == $resident->id ? 'selected' : '' }}>
                                    {{ $resident->first_name }} {{ $resident->last_name }} - {{ $resident->address }}
                                </option>
                            @endforeach
                        </select>
                        @error('resident_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Certificate Type -->
                    <div class="form-group">
                        <label for="certificate_type">Certificate Type <span class="required">*</span></label>
                        <select name="certificate_type" id="certificate_type" class="form-control @error('certificate_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="Clearance" {{ old('certificate_type', $certificate->certificate_type) == 'Clearance' ? 'selected' : '' }}>Barangay Clearance</option>
                            <option value="Indigency" {{ old('certificate_type', $certificate->certificate_type) == 'Indigency' ? 'selected' : '' }}>Certificate of Indigency</option>
                            <option value="Residency" {{ old('certificate_type', $certificate->certificate_type) == 'Residency' ? 'selected' : '' }}>Certificate of Residency</option>
                        </select>
                        @error('certificate_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required onchange="toggleRejectionField()">
                            <option value="Pending" {{ old('status', $certificate->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approved" {{ old('status', $certificate->status) == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Released" {{ old('status', $certificate->status) == 'Released' ? 'selected' : '' }}>Released</option>
                            <option value="Rejected" {{ old('status', $certificate->status) == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Purpose -->
                <div class="form-group full-width">
                    <label for="purpose">Purpose <span class="required">*</span></label>
                    <textarea id="purpose"
                              name="purpose"
                              class="form-control @error('purpose') is-invalid @enderror"
                              rows="3"
                              required>{{ old('purpose', $certificate->purpose) }}</textarea>
                    @error('purpose')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Rejection Reason (shown only when Rejected is selected) -->
<div id="rejectionField" class="form-group full-width"
     style="display: <?php echo old('status', $certificate->status) == 'Rejected' ? 'block' : 'none'; ?>;">
                       <label for="rejection_reason">
                        Rejection Reason
                        @if(old('status', $certificate->status) == 'Rejected')
                            <span class="required">*</span>
                        @endif
                    </label>
                    <textarea name="rejection_reason"
                              id="rejection_reason"
                              class="form-control @error('rejection_reason') is-invalid @enderror"
                              rows="2">{{ old('rejection_reason', $certificate->rejection_reason) }}</textarea>
                    @error('rejection_reason')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check icon-small"></i>
                    Update Certificate
                </button>
                <a href="{{ route('secretary.certificates.show', $certificate) }}" class="btn-secondary">
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
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
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

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.icon-small {
    font-size: 16px;
}
</style>
@endpush

@push('scripts')
<script>
function toggleRejectionField() {
    const status = document.getElementById('status').value;
    const rejectionField = document.getElementById('rejectionField');
    const rejectionReason = document.getElementById('rejection_reason');
    const requiredSpan = rejectionField ? rejectionField.querySelector('.required') : null;

    if (status === 'Rejected') {
        rejectionField.style.display = 'block';
        if (rejectionReason) rejectionReason.required = true;
    } else {
        rejectionField.style.display = 'none';
        if (rejectionReason) rejectionReason.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleRejectionField();
});
</script>
@endpush
