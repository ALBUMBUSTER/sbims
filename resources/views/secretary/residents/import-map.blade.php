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

                    <!-- Mapping Table with Horizontal Scroll -->
                    <div class="table-responsive-wrapper">
                        <div class="table-responsive">
                            <table class="table mapping-table">
                                <thead>
                                     <tr>
                                        <th style="min-width: 180px;">Your File Column</th>
                                        <th style="min-width: 200px;">Sample Data</th>
                                        <th style="min-width: 220px;">Map to Field</th>
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
                                                <code class="sample-data">{{ Str::limit($sampleData[0][$index], 30) }}</code>
                                            @else
                                                <em class="text-muted">No data</em>
                                            @endif
                                        </td>
                                        <td>
                                            <select name="mapping[{{ $index }}]" class="filter-select" style="width: 100%;">
                                                <option value="skip"><i class="fas fa-ban"></i> Skip this column</option>
                                                <option value="first_name" {{ ($suggestedMapping[$index] ?? '') == 'first_name' ? 'selected' : '' }}><i class="fas fa-user"></i> First Name (Required)</option>
                                                <option value="last_name" {{ ($suggestedMapping[$index] ?? '') == 'last_name' ? 'selected' : '' }}><i class="fas fa-user"></i> Last Name (Required)</option>
                                                <option value="middle_name" {{ ($suggestedMapping[$index] ?? '') == 'middle_name' ? 'selected' : '' }}><i class="fas fa-user"></i> Middle Name</option>
                                                <option value="suffix" {{ ($suggestedMapping[$index] ?? '') == 'suffix' ? 'selected' : '' }}><i class="fas fa-tag"></i> Suffix (Jr., Sr., III)</option>
                                                <option value="birthdate" {{ ($suggestedMapping[$index] ?? '') == 'birthdate' ? 'selected' : '' }}><i class="fas fa-calendar-alt"></i> Birthdate (Required)</option>
                                                <option value="gender" {{ ($suggestedMapping[$index] ?? '') == 'gender' ? 'selected' : '' }}><i class="fas fa-venus-mars"></i> Gender (Required)</option>
                                                <option value="civil_status" {{ ($suggestedMapping[$index] ?? '') == 'civil_status' ? 'selected' : '' }}><i class="fas fa-ring"></i> Civil Status</option>
                                                <option value="purok" {{ ($suggestedMapping[$index] ?? '') == 'purok' ? 'selected' : '' }}><i class="fas fa-map-marker-alt"></i> Purok</option>
                                                <option value="contact_number" {{ ($suggestedMapping[$index] ?? '') == 'contact_number' ? 'selected' : '' }}><i class="fas fa-phone"></i> Contact Number</option>
                                                <option value="email" {{ ($suggestedMapping[$index] ?? '') == 'email' ? 'selected' : '' }}><i class="fas fa-envelope"></i> Email</option>
                                                <option value="address" {{ ($suggestedMapping[$index] ?? '') == 'address' ? 'selected' : '' }}><i class="fas fa-home"></i> Address</option>
                                                <option value="household_number" {{ ($suggestedMapping[$index] ?? '') == 'household_number' ? 'selected' : '' }}><i class="fas fa-building"></i> Household Number</option>
                                                <option value="is_voter" {{ ($suggestedMapping[$index] ?? '') == 'is_voter' ? 'selected' : '' }}><i class="fas fa-check-circle"></i> Is Voter? (Yes/No)</option>
                                                <option value="is_senior" {{ ($suggestedMapping[$index] ?? '') == 'is_senior' ? 'selected' : '' }}><i class="fas fa-user-plus"></i> Is Senior? (Yes/No)</option>
                                                <option value="is_pwd" {{ ($suggestedMapping[$index] ?? '') == 'is_pwd' ? 'selected' : '' }}><i class="fas fa-wheelchair"></i> Is PWD? (Yes/No)</option>
                                                <option value="is_4ps" {{ ($suggestedMapping[$index] ?? '') == 'is_4ps' ? 'selected' : '' }}><i class="fas fa-hand-holding-heart"></i> Is 4Ps? (Yes/No)</option>
                                            </select>
                                        </td>
                                     </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sample Data Preview Section with Horizontal Scroll -->
                    <div class="barangay-detail-section" style="margin-top: 2rem;">
                        <div class="section-header">
                            <h3><i class="fas fa-eye" style="color: #667eea;"></i> Sample Data Preview (First 5 rows)</h3>
                            <span class="preview-hint"><i class="fas fa-arrows-left-right"></i> Scroll horizontally to see all columns</span>
                        </div>
                        <div class="table-responsive-wrapper preview-wrapper">
                            <div class="table-responsive preview-table-responsive">
                                <table class="table preview-table">
                                    <thead>
                                        <tr>
                                            @foreach($headers as $header)
                                                <th class="preview-th">{{ $header ?: 'Column ' . $loop->index + 1 }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sampleData as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td class="preview-td">{{ Str::limit($cell, 25) }}</td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
.main-container {
    display: flex;
    min-height: calc(100vh - 70px);
    background: #f8fafc;
}

.content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
    margin-left: 0;
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

/* Responsive Table Wrapper */
.table-responsive-wrapper {
    width: 100%;
    overflow-x: auto;
    margin-bottom: 1rem;
    border-radius: 8px;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Mapping Table Styles */
.mapping-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

.mapping-table th,
.mapping-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    vertical-align: middle;
}

.mapping-table th {
    background: #f8fafc;
    font-weight: 600;
    color: #4a5568;
}

.mapping-table tbody tr:hover {
    background: #f8fafc;
}

/* Sample Data Preview Section */
.barangay-detail-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.section-header {
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.section-header h3 {
    color: #333;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.preview-hint {
    font-size: 0.75rem;
    color: #718096;
    background: #edf2f7;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.preview-wrapper {
    margin-top: 0.5rem;
}

.preview-table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.preview-th {
    padding: 0.75rem 1rem;
    background: #f1f5f9;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.85rem;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.preview-td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.85rem;
    color: #334155;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.preview-td:hover {
    white-space: normal;
    word-break: break-all;
}

/* Filter Select */
.filter-select {
    padding: 0.6rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.2s;
    background: white;
    cursor: pointer;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Sample Data Code Block */
.sample-data {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #eef2ff;
    color: #667eea;
    border-radius: 20px;
    font-size: 0.85rem;
    font-family: monospace;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sample-data:hover {
    white-space: normal;
    word-break: break-all;
}

/* Buttons */
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

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .content {
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .mapping-table th,
    .mapping-table td {
        padding: 0.75rem;
        font-size: 0.85rem;
    }

    .filter-select {
        font-size: 0.8rem;
        padding: 0.4rem;
    }

    .preview-th,
    .preview-td {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection
