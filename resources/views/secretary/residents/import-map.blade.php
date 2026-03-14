@extends('layouts.app')

@section('title', 'Map Import Columns')

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-map-signs" style="color: #667eea;"></i> Map Your Columns</h1>
                <p>Match your file columns to our database fields</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('secretary.residents.import') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Upload
                </a>
            </div>
        </div>

        <div class="filter-container" style="margin-bottom: 2rem;">
            <div class="filter-title">
                <i class="fas fa-info-circle" style="color: #667eea;"></i>
                Smart Column Detection
            </div>
            <div class="alert alert-info" style="margin: 0;">
                <i class="fas fa-magic"></i>
                <strong>We've automatically matched your columns based on the headers.</strong> Please review and adjust if needed.
            </div>
        </div>

        <div class="data-table">
            <div class="table-header">
                <h3><i class="fas fa-columns" style="color: #667eea;"></i> Column Mapping</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('secretary.residents.import.map') }}" method="POST" id="mappingForm">
                    @csrf
                    <input type="hidden" name="file_path" value="{{ $filePath }}">
                    <input type="hidden" name="has_header" value="1" id="hasHeaderInput">

                    <div class="table-responsive">
                        <table class="table" style="min-width: 800px;">
                            <thead>
                                <tr>
                                    <th>Your File Column</th>
                                    <th>Sample Data</th>
                                    <th>Map to Field</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($headers as $index => $header)
                                <tr>
                                    <td>
                                        <strong>{{ $header ?: 'Column ' . ($index + 1) }}</strong>
                                    </td>
                                    <td>
                                        @if(isset($sampleData[0][$index]))
                                            <code class="incident-type">{{ Str::limit($sampleData[0][$index], 30) }}</code>
                                        @else
                                            <em class="text-muted">No data</em>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="mapping[{{ $index }}]" class="filter-select" style="width: 100%;">
                                            <option value="skip">⏭️ Skip this column</option>
                                            <option value="first_name" {{ ($suggestedMapping[$index] ?? '') == 'first_name' ? 'selected' : '' }}>👤 First Name (Required)</option>
                                            <option value="last_name" {{ ($suggestedMapping[$index] ?? '') == 'last_name' ? 'selected' : '' }}>👤 Last Name (Required)</option>
                                            <option value="middle_name" {{ ($suggestedMapping[$index] ?? '') == 'middle_name' ? 'selected' : '' }}>👤 Middle Name</option>
                                            <option value="suffix" {{ ($suggestedMapping[$index] ?? '') == 'suffix' ? 'selected' : '' }}>👤 Suffix (Jr., Sr., III)</option>
                                            <option value="birthdate" {{ ($suggestedMapping[$index] ?? '') == 'birthdate' ? 'selected' : '' }}>📅 Birthdate (Required)</option>
                                            <option value="gender" {{ ($suggestedMapping[$index] ?? '') == 'gender' ? 'selected' : '' }}>⚥ Gender (Required)</option>
                                            <option value="civil_status" {{ ($suggestedMapping[$index] ?? '') == 'civil_status' ? 'selected' : '' }}>💍 Civil Status</option>
                                            <option value="purok" {{ ($suggestedMapping[$index] ?? '') == 'purok' ? 'selected' : '' }}>📍 Purok</option>
                                            <option value="contact_number" {{ ($suggestedMapping[$index] ?? '') == 'contact_number' ? 'selected' : '' }}>📞 Contact Number</option>
                                            <option value="email" {{ ($suggestedMapping[$index] ?? '') == 'email' ? 'selected' : '' }}>📧 Email</option>
                                            <option value="address" {{ ($suggestedMapping[$index] ?? '') == 'address' ? 'selected' : '' }}>🏠 Address</option>
                                            <option value="household_number" {{ ($suggestedMapping[$index] ?? '') == 'household_number' ? 'selected' : '' }}>🏘️ Household Number</option>
                                            <option value="is_voter" {{ ($suggestedMapping[$index] ?? '') == 'is_voter' ? 'selected' : '' }}>🗳️ Is Voter? (Yes/No)</option>
                                            <option value="is_senior" {{ ($suggestedMapping[$index] ?? '') == 'is_senior' ? 'selected' : '' }}>👴 Is Senior? (Yes/No)</option>
                                            <option value="is_pwd" {{ ($suggestedMapping[$index] ?? '') == 'is_pwd' ? 'selected' : '' }}>♿ Is PWD? (Yes/No)</option>
                                            <option value="is_4ps" {{ ($suggestedMapping[$index] ?? '') == 'is_4ps' ? 'selected' : '' }}>📋 Is 4Ps? (Yes/No)</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="barangay-detail-section" style="margin-top: 2rem;">
                        <div class="section-header">
                            <h3><i class="fas fa-eye" style="color: #667eea;"></i> Sample Data Preview (First 5 rows)</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table" style="min-width: 1000px;">
                                <thead>
                                    <tr>
                                        @foreach($headers as $header)
                                            <th>{{ $header ?: 'Column' }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sampleData as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{{ Str::limit($cell, 20) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Continue to Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<style>
/* Override any problematic styles */
.main-container {
    display: flex;
    min-height: calc(100vh - 70px);
    background: #f8fafc;
}

.content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
    margin-left: 0; /* Ensure no margin pushing content */
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.filter-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.alert-info {
    background: #dbeafe;
    border: 1px solid #3b82f6;
    color: #1e40af;
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.data-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    overflow: hidden;
}

.table-header {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.table-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    text-align: left;
    padding: 1rem;
    background: #f8fafc;
    color: #4a5568;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.filter-select {
    padding: 0.6rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.2s;
    background: white;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.incident-type {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #eef2ff;
    color: #667eea;
    border-radius: 20px;
    font-size: 0.85rem;
}

.barangay-detail-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.section-header {
    margin-bottom: 1rem;
}

.section-header h3 {
    color: #333;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-outline {
    background: white;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline:hover {
    background: #667eea;
    color: white;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
}

.text-muted {
    color: #999;
    font-size: 0.9rem;
}

code {
    font-family: monospace;
}

@media (max-width: 768px) {
    .content {
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .table td {
        padding: 0.75rem;
        font-size: 0.85rem;
    }

    .filter-select {
        font-size: 0.85rem;
        padding: 0.4rem;
    }
}
</style>
@endsection
