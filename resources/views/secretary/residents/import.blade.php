@extends('layouts.app')

@section('title', 'Import Residents')

@section('content')
<!-- Toast Notification -->
<div id="toast" class="toast">
    <div class="toast-content">
        <div class="toast-icon success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <span id="toastMessage">Import completed!</span>
    </div>
</div>

<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Import Resident Records</h1>
            <p>Import multiple residents from CSV file</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Residents
            </a>
        </div>
    </div>

    {{-- Success Results with Duplicate Handling --}}
    @if(session('import_success'))
    <div class="alert alert-success">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="alert-content">
            <h4>✅ Import Completed!</h4>
            <p>
                <strong>{{ session('import_count') }}</strong> residents imported successfully.
                @if(session('duplicate_count') > 0)
                    <br><span style="color: #856404;">⚠️ <strong>{{ session('duplicate_count') }}</strong> duplicate records skipped.</span>
                @endif
                @if(session('skipped_count') > (session('duplicate_count') ?? 0))
                    <br><span style="color: #dc2626;">❌ <strong>{{ session('skipped_count') - (session('duplicate_count') ?? 0) }}</strong> invalid records skipped.</span>
                @endif
            </p>
            <div class="alert-actions">
                <a href="{{ route('secretary.residents.index') }}" class="btn-success btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2s10 4.48 10 10z"></path>
                    </svg>
                    View Residents
                </a>
                <a href="{{ route('secretary.residents.import') }}" class="btn-outline btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="16 16 12 12 8 16"></polyline>
                        <line x1="12" y1="12" x2="12" y2="21"></line>
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Import Another File
                </a>
            </div>
        </div>
    </div>

    {{-- Duplicate Records Section --}}
    @if(session('duplicate_count') > 0 && session('duplicate_rows'))
    <div class="alert alert-warning" style="margin-top: 15px;">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="alert-content">
            <h4>⚠️ Duplicate Records Skipped ({{ session('duplicate_count') }})</h4>
            <p>The following records already exist in the database:</p>
            <div class="error-log" style="max-height: 150px; overflow-y: auto; background: white; padding: 10px; border-radius: 5px; margin-top: 10px;">
                @foreach(session('duplicate_rows') as $duplicate)
                    <div style="padding: 5px; border-bottom: 1px solid #ffeeba; color: #856404;">{{ $duplicate }}</div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Failed Records Section --}}
    @if(session('skipped_count') > 0 && session('failed_rows') && session('skipped_count') > (session('duplicate_count') ?? 0))
    <div class="alert alert-danger" style="margin-top: 15px;">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="alert-content">
            <h4>❌ Invalid Records ({{ session('skipped_count') - (session('duplicate_count') ?? 0) }})</h4>
            <p>The following records had validation errors and were skipped:</p>
            <div class="error-log" style="max-height: 150px; overflow-y: auto; background: white; padding: 10px; border-radius: 5px; margin-top: 10px;">
                @foreach(session('failed_rows') as $error)
                    @if(!str_contains($error, 'already exists'))
                        <div style="padding: 5px; border-bottom: 1px solid #f5c6cb; color: #721c24;">{{ $error }}</div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- All Duplicates Error Message --}}
    @if(session('error') && session('duplicate_count') > 0 && !session('import_success'))
    <div class="alert alert-warning">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="alert-content">
            <h4>⚠️ No New Records Imported</h4>
            <p>{{ session('error') }}</p>
            @if(session('duplicate_rows'))
                <div class="error-log" style="max-height: 150px; overflow-y: auto; background: white; padding: 10px; border-radius: 5px; margin-top: 10px;">
                    @foreach(session('duplicate_rows') as $duplicate)
                        <div style="padding: 5px; border-bottom: 1px solid #ffeeba; color: #856404;">{{ $duplicate }}</div>
                    @endforeach
                </div>
            @endif
            <div class="alert-actions" style="margin-top: 15px;">
                <a href="{{ route('secretary.residents.import') }}" class="btn-outline btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="16 16 12 12 8 16"></polyline>
                        <line x1="12" y1="12" x2="12" y2="21"></line>
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Try Another File
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Regular Error Message --}}
    @if(session('error') && !session('duplicate_count'))
    <div class="alert alert-danger">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="alert-content">
            <h4>❌ Import Failed</h4>
            <p>{{ session('error') }}</p>
            @if(session('failed_rows'))
                <div class="error-log" style="max-height: 150px; overflow-y: auto; background: white; padding: 10px; border-radius: 5px; margin-top: 10px;">
                    @foreach(session('failed_rows') as $error)
                        <div style="padding: 5px; border-bottom: 1px solid #f5c6cb; color: #721c24;">{{ $error }}</div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="alert-content">
            <h4>❌ Validation Error</h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Main Import Card --}}
    <div class="card">
        <div class="card-header">
            <h3>Upload CSV File</h3>
        </div>
        <div class="card-body">
            {{-- CSV Format Guide --}}
            <div class="format-guide">
                <div class="guide-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <h4>CSV Format Guide</h4>
                </div>
                <p>Your CSV file must have <strong>15 columns</strong> in this exact order:</p>

                <div class="guide-columns">
                    <div class="column-list">
                        <h5>Required Fields</h5>
                        <ol>
                            <li><strong>First Name</strong> <span class="required-badge">Required</span></li>
                            <li><strong>Last Name</strong> <span class="required-badge">Required</span></li>
                            <li>Middle Name</li>
                            <li><strong>Birthdate</strong> <span class="required-badge">Required</span></li>
                            <li><strong>Gender</strong> (Male/Female) <span class="required-badge">Required</span></li>
                            <li>Contact Number</li>
                            <li>Email</li>
                        </ol>
                    </div>
                    <div class="column-list">
                        <h5>Additional Fields</h5>
                        <ol start="8">
                            <li>Address</li>
                            <li>Purok</li>
                            <li>Household Number</li>
                            <li>Voter? (Yes/No)</li>
                            <li>Senior? (Yes/No)</li>
                            <li>PWD? (Yes/No)</li>
                            <li>4PS? (Yes/No)</li>
                            <li>Civil Status (Single/Married/Widowed/Divorced)</li>
                        </ol>
                    </div>
                </div>

                <div class="guide-notes">
                    <div class="note-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <div>
                            <strong>Date Formats Accepted:</strong>
                            <span>YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, DD-MM-YYYY</span>
                        </div>
                    </div>
                    <div class="note-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <div>
                            <strong>Gender:</strong>
                            <span>"Male", "Female", "M", "F", "Lalaki", "Babae"</span>
                        </div>
                    </div>
                    <div class="note-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <div>
                            <strong>Yes/No Fields:</strong>
                            <span>Use "Yes", "No", "Y", "N", "1", "0" (case-insensitive)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Import Form --}}
            <form method="POST" action="{{ route('secretary.residents.import.post') }}" enctype="multipart/form-data" class="import-form" id="importForm">
                @csrf

                <div class="form-group">
                    <label for="csv_file">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Select CSV File
                    </label>
                    <div class="file-input-wrapper">
                        <input type="file"
                               name="csv_file"
                               id="csv_file"
                               accept=".csv, .txt"
                               class="file-input @error('csv_file') is-invalid @enderror"
                               required>
                        <div class="file-input-preview" id="filePreview">
                            <span class="preview-placeholder">No file chosen</span>
                        </div>
                    </div>
                    <small class="text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="12" x2="12" y2="16"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        Only .csv files accepted (Max: 10MB)
                    </small>
                    @error('csv_file')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 16 12 12 8 16"></polyline>
                            <line x1="12" y1="12" x2="12" y2="21"></line>
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span id="btnText">Upload & Import</span>
                        <span id="btnSpinner" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="spinning">
                                <line x1="12" y1="2" x2="12" y2="6"></line>
                                <line x1="12" y1="18" x2="12" y2="22"></line>
                                <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                <line x1="2" y1="12" x2="6" y2="12"></line>
                                <line x1="18" y1="12" x2="22" y2="12"></line>
                                <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                            </svg>
                            Processing...
                        </span>
                    </button>
                    <a href="{{ route('secretary.residents.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Example Card --}}
    <div class="card example-card">
        <div class="card-header">
            <h3>Example CSV Format</h3>
        </div>
        <div class="card-body">
            <p>Here's an example of how your CSV should look (first row is headers):</p>

            <div class="table-container">
                <table class="example-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Middle Name</th>
                            <th>Birthdate</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Purok</th>
                            <th>Household</th>
                            <th>Voter</th>
                            <th>Senior</th>
                            <th>PWD</th>
                            <th>4PS</th>
                            <th>Civil Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Juan</td>
                            <td>Dela Cruz</td>
                            <td>Santos</td>
                            <td>1990-05-15</td>
                            <td>Male</td>
                            <td>09123456789</td>
                            <td>juan@email.com</td>
                            <td>Purok 1, Brgy. Libertad</td>
                            <td>1</td>
                            <td>101</td>
                            <td>Yes</td>
                            <td>No</td>
                            <td>No</td>
                            <td>No</td>
                            <td>Married</td>
                        </tr>
                        <tr>
                            <td>Maria</td>
                            <td>Santos</td>
                            <td>Reyes</td>
                            <td>1985-08-20</td>
                            <td>Female</td>
                            <td>09187654321</td>
                            <td>maria@email.com</td>
                            <td>Purok 2, Brgy. Libertad</td>
                            <td>2</td>
                            <td>202</td>
                            <td>Yes</td>
                            <td>No</td>
                            <td>No</td>
                            <td>Yes</td>
                            <td>Single</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="example-actions">
                <button type="button" class="btn-primary" onclick="downloadSampleCSV()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download Sample CSV
                </button>
                <button type="button" class="btn-secondary" onclick="copyCSVExample()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    Copy Example
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Add these new styles */
.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
}

.alert-warning .alert-icon svg {
    color: #856404;
}

/* Rest of your existing styles remain the same */
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
    flex-shrink: 0;
}

.toast-icon.success svg {
    width: 24px;
    height: 24px;
    color: #10b981;
}

.toast-icon.error svg {
    width: 24px;
    height: 24px;
    color: #dc2626;
}

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

.btn-primary, .btn-secondary, .btn-success, .btn-outline {
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
.btn-primary:hover:not(:disabled) { opacity: 0.9; color: white; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}
.btn-secondary:hover { background: #eef2ff; }

.btn-success {
    background: #10b981;
    color: white;
}
.btn-success:hover { background: #059669; }

.btn-outline {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
}
.btn-outline:hover { background: #f9fafb; }

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header {
    background: #f8fafc;
    padding: 15px 20px;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 20px;
}

.format-guide {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
}

.guide-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 15px;
}

.guide-header svg {
    width: 24px;
    height: 24px;
    color: #3b82f6;
}

.guide-header h4 {
    margin: 0;
    color: #1e293b;
    font-size: 1.1rem;
}

.guide-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin: 20px 0;
}

.column-list {
    background: white;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #667eea;
}

.column-list h5 {
    margin: 0 0 15px 0;
    color: #1e293b;
    font-size: 1rem;
}

.column-list ol {
    margin: 0;
    padding-left: 20px;
}

.column-list li {
    margin-bottom: 8px;
    line-height: 1.4;
    color: #374151;
}

.required-badge {
    background: #fee2e2;
    color: #dc2626;
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 12px;
    margin-left: 8px;
    font-weight: normal;
}

.guide-notes {
    background: #dbeafe;
    border-radius: 6px;
    padding: 15px;
    margin-top: 15px;
}

.note-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}

.note-item:last-child {
    margin-bottom: 0;
}

.note-item svg {
    width: 18px;
    height: 18px;
    color: #3b82f6;
    flex-shrink: 0;
    margin-top: 2px;
}

.note-item strong {
    color: #1e40af;
    margin-right: 5px;
}

.import-form {
    margin-top: 20px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 10px;
    font-weight: 500;
    color: #374151;
}

.form-group label svg {
    width: 16px;
    height: 16px;
    color: #667eea;
}

.file-input-wrapper {
    position: relative;
}

.file-input {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.file-input-preview {
    width: 100%;
    padding: 1rem;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    background: white;
    transition: all 0.3s;
    color: #6b7280;
    z-index: 1;
}

.file-input:hover + .file-input-preview {
    border-color: #667eea;
    background: #f8fafc;
}

.file-input-preview.has-file {
    border-color: #10b981;
    background: #f0fdf4;
    color: #065f46;
}

.preview-placeholder {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.text-muted {
    display: block;
    margin-top: 8px;
    color: #6b7280;
    font-size: 0.9em;
}

.text-muted svg {
    width: 14px;
    height: 14px;
    display: inline;
    margin-right: 4px;
    color: #6b7280;
}

.error-message {
    display: block;
    color: #dc2626;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.alert {
    display: flex;
    gap: 15px;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d1fae5;
    border: 1px solid #10b981;
    color: #065f46;
}

.alert-danger {
    background: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
}

.alert-icon {
    flex-shrink: 0;
}

.alert-icon svg {
    width: 24px;
    height: 24px;
}

.alert-success .alert-icon svg {
    color: #10b981;
}

.alert-danger .alert-icon svg {
    color: #dc2626;
}

.alert-content {
    flex: 1;
}

.alert-content h4 {
    margin: 0 0 10px 0;
    font-size: 1.1rem;
}

.alert-content p {
    margin: 0 0 10px 0;
}

.alert-content ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.alert-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.skipped-records {
    margin-top: 20px;
    padding: 15px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
}

.skipped-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 10px;
}

.skipped-header svg {
    width: 20px;
    height: 20px;
    color: #856404;
}

.skipped-header h5 {
    margin: 0;
    color: #856404;
    font-size: 1rem;
}

.error-log {
    max-height: 200px;
    overflow-y: auto;
    background: white;
    padding: 10px;
    border-radius: 5px;
    font-size: 0.9em;
}

.error-item {
    padding: 5px;
    border-bottom: 1px solid #ffeeba;
    color: #856404;
}

.error-item:last-child {
    border-bottom: none;
}

.example-card {
    margin-top: 30px;
}

.table-container {
    overflow-x: auto;
    margin: 20px 0;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.example-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85em;
    min-width: 1400px;
}

.example-table th {
    background: #f1f5f9;
    padding: 10px 6px;
    text-align: center;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.example-table td {
    padding: 8px 6px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
}

.example-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.example-table tbody tr:hover {
    background: #f1f5f9;
}

.example-actions {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.spinning {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

svg {
    display: inline-block;
    vertical-align: middle;
}

@media (max-width: 768px) {
    .guide-columns {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary, .btn-secondary, .btn-success, .btn-outline {
        width: 100%;
        justify-content: center;
    }

    .example-actions {
        flex-direction: column;
    }

    .alert {
        flex-direction: column;
        gap: 10px;
    }

    .alert-actions {
        flex-direction: column;
    }
}
</style>
@endpush

@push('scripts')
<script>
// File input preview
document.getElementById('csv_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.querySelector('.file-input-preview');
    const placeholder = preview.querySelector('.preview-placeholder');

    if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        placeholder.innerHTML = `📄 ${file.name} (${fileSize} MB)`;
        preview.classList.add('has-file');

        if (file.size > 10 * 1024 * 1024) {
            alert('File is too large! Maximum size is 10MB.');
            e.target.value = '';
            placeholder.innerHTML = 'No file chosen';
            preview.classList.remove('has-file');
        }
    } else {
        placeholder.innerHTML = 'No file chosen';
        preview.classList.remove('has-file');
    }
});

// Form submission loading state
document.getElementById('importForm')?.addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline-flex';
});

// Download sample CSV
function downloadSampleCSV() {
    const headers = ['First Name', 'Last Name', 'Middle Name', 'Birthdate', 'Gender', 'Contact', 'Email',
                    'Address', 'Purok', 'Household', 'Voter', 'Senior', 'PWD', '4PS', 'Civil Status'];

    const rows = [
        ['Juan', 'Dela Cruz', 'Santos', '1990-05-15', 'Male', '09123456789', 'juan@email.com',
         'Purok 1, Brgy. Libertad', '1', '101', 'Yes', 'No', 'No', 'No', 'Married'],
        ['Maria', 'Santos', 'Reyes', '1985-08-20', 'Female', '09187654321', 'maria@email.com',
         'Purok 2, Brgy. Libertad', '2', '202', 'Yes', 'No', 'No', 'Yes', 'Single'],
        ['Pedro', 'Gonzales', '', '1975-03-10', 'Male', '09234567890', '',
         'Purok 1, Brgy. Libertad', '1', '103', 'Yes', 'Yes', 'No', 'No', 'Widowed']
    ];

    let csvContent = headers.join(',') + '\n';

    rows.forEach(row => {
        const escapedRow = row.map(cell => {
            if (cell.includes(',') || cell.includes('"') || cell.includes('\n')) {
                return `"${cell.replace(/"/g, '""')}"`;
            }
            return cell;
        }).join(',');
        csvContent += escapedRow + '\n';
    });

    const blob = new Blob(["\xEF\xBB\xBF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sample_residents.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Copy CSV example to clipboard
function copyCSVExample() {
    const headers = ['First Name', 'Last Name', 'Middle Name', 'Birthdate', 'Gender', 'Contact', 'Email',
                    'Address', 'Purok', 'Household', 'Voter', 'Senior', 'PWD', '4PS', 'Civil Status'];

    const row1 = ['Juan', 'Dela Cruz', 'Santos', '1990-05-15', 'Male', '09123456789', 'juan@email.com',
                  'Purok 1, Brgy. Libertad', '1', '101', 'Yes', 'No', 'No', 'No', 'Married'];

    const row2 = ['Maria', 'Santos', 'Reyes', '1985-08-20', 'Female', '09187654321', 'maria@email.com',
                  'Purok 2, Brgy. Libertad', '2', '202', 'Yes', 'No', 'No', 'Yes', 'Single'];

    const example = headers.join(',') + '\n' +
                    row1.join(',') + '\n' +
                    row2.join(',');

    const textarea = document.createElement('textarea');
    textarea.value = example;
    document.body.appendChild(textarea);
    textarea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showToast('✅ CSV example copied to clipboard!', 'success');
        } else {
            showToast('❌ Failed to copy. Please try again.', 'error');
        }
    } catch (err) {
        console.error('Copy error:', err);
        showToast('❌ Copy failed. Please select and copy manually.', 'error');
    }

    document.body.removeChild(textarea);
}

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
            toastIcon.className = 'toast-icon success';
        }
    } else {
        toast.style.borderLeftColor = '#dc2626';
        if (toastIcon) {
            toastIcon.className = 'toast-icon error';
        }
    }

    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}
</script>

{{-- Session messages --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
@endif
@endpush
