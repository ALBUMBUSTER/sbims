@extends('layouts.app')

@section('title', 'Barangay Information')

@push('styles')
<style>
    .barangay-form-container {
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
        font-size: 0.95rem;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 1rem;
        transition: border-color 0.3s;
        background: #f8fafc;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .required-field::after {
        content: " *";
        color: #dc2626;
    }

    .logo-preview {
        margin-top: 1rem;
        text-align: center;
    }

    .logo-preview img {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        padding: 5px;
        background: white;
    }

    .current-logo {
        margin-bottom: 1rem;
    }

    .current-logo-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-reset {
        background: transparent;
        border: 1px solid #d1d5db;
        color: #666;
    }

    .btn-reset:hover {
        background: #f8fafc;
        color: #333;
    }

    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .form-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #667eea;
    }

    .form-header h2 {
        color: #333;
        margin-bottom: 0.5rem;
    }

    .form-header p {
        color: #666;
        font-size: 0.95rem;
    }

    .current-info {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .current-info h3 {
        color: #0369a1;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .info-item {
        display: flex;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px dashed #cbd5e1;
    }

    .info-label {
        font-weight: 500;
        color: #475569;
        width: 200px;
        flex-shrink: 0;
    }

    .info-value {
        color: #1e293b;
        flex: 1;
    }

    .empty-value {
        color: #94a3b8;
        font-style: italic;
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Barangay Information</h1>
                <p>Update barangay details and officials</p>
            </div>
        </div>

        <!-- Current Information Display -->
        <div class="current-info">
            <h3>📋 Current Barangay Information</h3>

            <div class="info-item">
                <div class="info-label">Barangay Name:</div>
                <div class="info-value">{{ $barangayInfo->barangay_name ?? 'Not set' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Barangay Captain:</div>
                <div class="info-value {{ empty($barangayInfo->barangay_captain) ? 'empty-value' : '' }}">
                    {{ $barangayInfo->barangay_captain ?? 'Not set' }}
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Barangay Secretary:</div>
                <div class="info-value {{ empty($barangayInfo->barangay_secretary) ? 'empty-value' : '' }}">
                    {{ $barangayInfo->barangay_secretary ?? 'Not set' }}
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Contact Number:</div>
                <div class="info-value {{ empty($barangayInfo->contact_number) ? 'empty-value' : '' }}">
                    {{ $barangayInfo->contact_number ?? 'Not set' }}
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Complete Address:</div>
                <div class="info-value">{{ $barangayInfo->address ?? 'Not set' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Email Address:</div>
                <div class="info-value {{ empty($barangayInfo->email) ? 'empty-value' : '' }}">
                    {{ $barangayInfo->email ?? 'Not set' }}
                </div>
            </div>

            @if($barangayInfo->logo_path)
            <div class="info-item">
                <div class="info-label">Current Logo:</div>
                <div class="info-value">
                    <img src="{{ Storage::url($barangayInfo->logo_path) }}"
                         alt="Barangay Logo"
                         style="max-width: 100px; border-radius: 5px;">
                </div>
            </div>
            @endif
        </div>

        <!-- Update Form -->
        <div class="barangay-form-container">
            <div class="form-header">
                <h2>Update Barangay Information</h2>
                <p>Fill in the details below to update barangay information</p>
            </div>

            <form action="{{ route('admin.barangay.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="barangay_name" class="required-field">Barangay Name</label>
                    <input type="text"
                           id="barangay_name"
                           name="barangay_name"
                           value="{{ old('barangay_name', $barangayInfo->barangay_name ?? '') }}"
                           required
                           placeholder="Enter barangay name">
                    @error('barangay_name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="barangay_captain">Barangay Captain</label>
                    <input type="text"
                           id="barangay_captain"
                           name="barangay_captain"
                           value="{{ old('barangay_captain', $barangayInfo->barangay_captain ?? '') }}"
                           placeholder="Enter barangay captain's name">
                    @error('barangay_captain')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="barangay_secretary">Barangay Secretary</label>
                    <input type="text"
                           id="barangay_secretary"
                           name="barangay_secretary"
                           value="{{ old('barangay_secretary', $barangayInfo->barangay_secretary ?? '') }}"
                           placeholder="Enter barangay secretary's name">
                    @error('barangay_secretary')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text"
                           id="contact_number"
                           name="contact_number"
                           value="{{ old('contact_number', $barangayInfo->contact_number ?? '') }}"
                           placeholder="e.g., 09123456789">
                    @error('contact_number')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address" class="required-field">Complete Address</label>
                    <textarea id="address"
                              name="address"
                              required
                              placeholder="Enter complete address (e.g., Libertad, Isabel, Leyte)">{{ old('address', $barangayInfo->address ?? '') }}</textarea>
                    @error('address')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $barangayInfo->email ?? '') }}"
                           placeholder="Enter email address">
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="logo">Barangay Logo</label>
                    <input type="file"
                           id="logo"
                           name="logo"
                           accept="image/*">
                    @error('logo')
                        <div class="error-message">{{ $message }}</div>
                    @enderror

                    @if($barangayInfo->logo_path)
                    <div class="current-logo">
                        <div class="current-logo-label">Current Logo:</div>
                        <div class="logo-preview">
                            <img src="{{ Storage::url($barangayInfo->logo_path) }}"
                                 alt="Current Barangay Logo"
                                 id="currentLogoPreview">
                        </div>
                    </div>
                    @endif

                    <div class="logo-preview" id="newLogoPreview" style="display: none;">
                        <div class="current-logo-label">New Logo Preview:</div>
                        <img id="logoPreviewImage"
                             src="#"
                             alt="New Logo Preview"
                             style="max-width: 150px;">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-outline btn-reset">
                        <span>🔄</span> Reset
                    </button>
                    <button type="submit" class="btn btn-outline" style="background: #667eea; color: white; border-color: #667eea;">
                        <span>💾</span> Update Information
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

@push('scripts')
<script>
    // Logo preview functionality
    document.getElementById('logo').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('newLogoPreview');
        const previewImage = document.getElementById('logoPreviewImage');
        const currentLogo = document.getElementById('currentLogoPreview');

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                previewImage.src = e.target.result;
                preview.style.display = 'block';

                // Hide current logo preview if exists
                if (currentLogo) {
                    currentLogo.parentElement.parentElement.style.display = 'none';
                }
            }

            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';

            // Show current logo again if exists
            if (currentLogo) {
                currentLogo.parentElement.parentElement.style.display = 'block';
            }
        }
    });

    // Reset form button functionality
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        // Hide new logo preview
        document.getElementById('newLogoPreview').style.display = 'none';

        // Show current logo if exists
        const currentLogo = document.getElementById('currentLogoPreview');
        if (currentLogo) {
            currentLogo.parentElement.parentElement.style.display = 'block';
        }

        // Clear file input
        document.getElementById('logo').value = '';
    });
</script>
@endpush
@endsection
