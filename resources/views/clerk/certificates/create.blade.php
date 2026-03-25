{{-- @extends('layouts.app')

@section('title', 'Issue Certificate')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Issue New Certificate</h1>
            <p>Create a certificate request for a resident</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clerk.certificates.index') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Certificates
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('clerk.certificates.store') }}" method="POST" id="certificateForm">
                @csrf

                <div class="form-grid">
                    <!-- Resident Selection -->
                    <div class="form-group full-width">
                        <label for="resident_id">Resident <span class="required">*</span></label>
                        <select name="resident_id" id="resident_id" class="form-control @error('resident_id') is-invalid @enderror" required>
                            <option value="">Select Resident</option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}" {{ old('resident_id', request('resident_id')) == $resident->id ? 'selected' : '' }}>
                                    {{ $resident->full_name }} ({{ $resident->resident_id }}) - Purok {{ $resident->purok }}
                                </option>
                            @endforeach
                        </select>
                        @error('resident_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Certificate Type -->
                    <div class="form-group">
                        <label for="certificate_type">Certificate Type <span class="required">*</span></label>
                        <select name="certificate_type" id="certificate_type" class="form-control @error('certificate_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="Clearance" {{ old('certificate_type') == 'Clearance' ? 'selected' : '' }}>Barangay Clearance</option>
                            <option value="Indigency" {{ old('certificate_type') == 'Indigency' ? 'selected' : '' }}>Certificate of Indigency</option>
                            <option value="Residency" {{ old('certificate_type') == 'Residency' ? 'selected' : '' }}>Certificate of Residency</option>
                            <option value="Good Moral" {{ old('certificate_type') == 'Good Moral' ? 'selected' : '' }}>Certificate of Good Moral</option>
                        </select>
                        @error('certificate_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Purpose -->
                    <div class="form-group full-width">
                        <label for="purpose">Purpose <span class="required">*</span></label>
                        <textarea name="purpose" id="purpose" rows="4" class="form-control @error('purpose') is-invalid @enderror" required>{{ old('purpose') }}</textarea>
                        <small class="form-text text-muted">State the purpose of this certificate request</small>
                        @error('purpose')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Additional Fields based on certificate type (optional) -->
                    <div id="additionalFields" class="full-width" style="display: none;">
                        <!-- This can be populated with JavaScript based on certificate type -->
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Create Certificate
                    </button>
                    <a href="{{ route('clerk.certificates.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
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

.page-actions {
    display: flex;
    gap: 0.75rem;
}

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
    font-weight: 500;
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

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-body {
    padding: 2rem;
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

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.invalid-feedback {
    color: #dc2626;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.85rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary, .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // You can add JavaScript to show additional fields based on certificate type
    const certificateType = document.getElementById('certificate_type');
    const additionalFields = document.getElementById('additionalFields');

    certificateType.addEventListener('change', function() {
        // Show additional fields based on selection
        if (this.value === 'Indigency') {
            additionalFields.innerHTML = `
                <div class="form-group">
                    <label>Monthly Income</label>
                    <input type="text" name="monthly_income" class="form-control" placeholder="Enter monthly income">
                </div>
            `;
            additionalFields.style.display = 'block';
        } else {
            additionalFields.style.display = 'none';
        }
    });
});
</script>
@endpush --}}
