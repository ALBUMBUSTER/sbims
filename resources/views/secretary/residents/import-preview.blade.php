@extends('layouts.app')

@section('title', 'Preview Import')

@section('content')
<div class="main-container">
    <main class="content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-eye" style="color: #667eea;"></i> Preview Import</h1>
                <p>Review your data before importing</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('secretary.residents.import') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Start Over
                </a>
            </div>
        </div>

        <!-- Statistics Cards - Shows import summary -->
        <div class="stats-grid">
            <!-- Valid Records Card -->
            <div class="stat-card">
                <div class="stat-icon" style="background: #10b981;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3>Valid Records</h3>
                    <div class="stat-value">{{ $stats['valid'] }}</div>
                    <span class="stat-label">Ready to import</span>
                </div>
            </div>

            <!-- Issues Found Card -->
            <div class="stat-card">
                <div class="stat-icon" style="background: #f59e0b;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-details">
                    <h3>Issues Found</h3>
                    <div class="stat-value">{{ $stats['invalid'] }}</div>
                    <span class="stat-label">Need attention</span>
                </div>
            </div>

            <!-- Total Rows Card -->
            <div class="stat-card">
                <div class="stat-icon" style="background: #667eea;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Rows</h3>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <span class="stat-label">In preview</span>
                </div>
            </div>
        </div>

        <!-- Permanent Warning Banner - Only shows when there are invalid records -->
        @if($stats['invalid'] > 0)
        <div class="permanent-warning">
            <div class="warning-header">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="warning-title">
                    <h3>⚠️ Issues Detected in {{ $stats['invalid'] }} Record(s)</h3>
                    <p>These records will be <strong>automatically skipped</strong> during import. Only valid records will be imported.</p>
                </div>
            </div>
            <div class="warning-actions">
                <button type="button" class="btn-view-issues" onclick="showAllErrors()">
                    <i class="fas fa-list"></i> View All Issues ({{ $stats['invalid'] }})
                </button>
            </div>
        </div>
        @endif

        <!-- Preview Table - Shows first 10 rows of data -->
        <div class="data-table">
            <div class="table-header">
                <h3><i class="fas fa-table" style="color: #667eea;"></i> Preview (First {{ count($preview['rows']) }} rows)</h3>
                <span class="badge">Total: {{ count($preview['rows']) }} rows</span>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th width="100">Status</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Birthdate</th>
                            <th>Gender</th>
                            <th>Civil Status</th>
                            <th>Purok</th>
                            <th>Contact</th>
                            <th width="60">Voter</th>
                            <th width="60">Senior</th>
                            <th width="60">PWD</th>
                            <th width="60">4Ps</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preview['rows'] as $row)
                        <tr class="{{ $row['is_valid'] ? '' : 'invalid-row' }}">
                            <td>{{ $row['row_number'] }}</td>
                            <td>
                                @if($row['is_valid'])
                                    <span class="badge badge-success">Valid</span>
                                @else
                                    <span class="badge badge-danger">Issues</span>
                                    <button type="button" class="btn-icon info-btn" onclick="showRowErrors({{ $row['row_number'] }})" title="View errors">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                @endif
                            </td>
                            <td>{{ $row['data']['first_name'] ?? '' }}</td>
                            <td>{{ $row['data']['last_name'] ?? '' }}</td>
                            <td>{{ $row['data']['birthdate'] ?? '' }}</td>
                            <td>{{ $row['data']['gender'] ?? '' }}</td>
                            <td>{{ $row['data']['civil_status'] ?? '' }}</td>
                            <td>{{ $row['data']['purok'] ?? '' }}</td>
                            <td>{{ $row['data']['contact_number'] ?? '' }}</td>
                            <td class="text-center">{{ isset($row['data']['is_voter']) && $row['data']['is_voter'] ? 'Yes' : 'No' }}</td>
                            <td class="text-center">{{ isset($row['data']['is_senior']) && $row['data']['is_senior'] ? 'Yes' : 'No' }}</td>
                            <td class="text-center">{{ isset($row['data']['is_pwd']) && $row['data']['is_pwd'] ? 'Yes' : 'No' }}</td>
                            <td class="text-center">{{ isset($row['data']['is_4ps']) && $row['data']['is_4ps'] ? 'Yes' : 'No' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Import Confirmation Form -->
            <form action="{{ route('secretary.residents.import.confirm') }}" method="POST" class="import-confirm-form">
                @csrf
                <!-- Hidden fields to pass data to confirmation -->
                <input type="hidden" name="file_path" value="{{ $file_path }}">
                <input type="hidden" name="has_header" value="{{ $has_header ? '1' : '0' }}">

                @foreach($mapping as $index => $field)
                    <input type="hidden" name="mapping[{{ $index }}]" value="{{ $field }}">
                @endforeach

                <!-- Action Buttons -->
                <div class="form-actions">
                    <a href="{{ route('secretary.residents.import') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" {{ $stats['valid'] == 0 ? 'disabled' : '' }}>
                        <i class="fas fa-check"></i> Import {{ $stats['valid'] }} Valid Record(s)
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- Error Details Modal - Shows detailed validation errors -->
<div id="errorModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Validation Errors</h3>
            <button type="button" class="modal-close" onclick="closeErrorModal()">&times;</button>
        </div>
        <div class="modal-body" id="errorDetails"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ==================== STATISTICS CARDS ==================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: all 0.3s;
    border: 1px solid rgba(0,0,0,0.05);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.stat-details {
    flex: 1;
}

.stat-details h3 {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: #999;
}

/* ==================== PERMANENT WARNING BANNER ==================== */
.permanent-warning {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 12px;
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
}

.warning-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.warning-icon i {
    font-size: 2rem;
    color: #856404;
}

.warning-title h3 {
    margin: 0 0 0.3rem 0;
    color: #856404;
    font-size: 1.2rem;
}

.warning-title p {
    margin: 0;
    color: #856404;
    opacity: 0.9;
}

.btn-view-issues {
    background: white;
    border: 2px solid #856404;
    color: #856404;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-view-issues:hover {
    background: #856404;
    color: white;
}

/* ==================== TABLE STYLES ==================== */
.data-table {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    overflow: hidden;
}

.table-header {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.badge {
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1300px;
}

.table th {
    text-align: left;
    padding: 1rem 1.5rem;
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 0.9rem;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.invalid-row {
    background: #fff5f5;
}

.invalid-row:hover {
    background: #fee2e2;
}

/* Status Badges */
.badge-success {
    background: #d4edda;
    color: #155724;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-danger {
    background: #fee2e2;
    color: #dc2626;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Info Button */
.info-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 6px;
    background: none;
    color: #3b82f6;
    cursor: pointer;
    font-size: 1rem;
    margin-left: 0.3rem;
    transition: all 0.2s;
}

.info-btn:hover {
    background: #e6f0ff;
    color: #2563eb;
}

/* ==================== FORM ACTIONS ==================== */
.import-confirm-form {
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
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

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #5a67d8;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

/* ==================== MODAL STYLES ==================== */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
    line-height: 1;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    padding: 1.5rem;
}

.modal-body ul {
    margin: 0.5rem 0 0 0;
    padding-left: 1.5rem;
}

.modal-body li {
    margin-bottom: 0.3rem;
    color: #dc2626;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    background: #f8fafc;
}

.text-center {
    text-align: center;
}

/* ==================== RESPONSIVE STYLES ==================== */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .permanent-warning {
        flex-direction: column;
        align-items: flex-start;
    }

    .warning-header {
        width: 100%;
    }

    .warning-actions {
        width: 100%;
    }

    .btn-view-issues {
        width: 100%;
        justify-content: center;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Store error data from the server
let errorData = @json($preview['rows']);

/**
 * Display errors for a specific row in the modal
 */
function showRowErrors(rowNumber) {
    const row = errorData.find(r => r.row_number === rowNumber);
    if (!row || !row.errors) return;

    // Build HTML for errors
    let html = `<h4 style="margin-bottom: 1rem; color: #333;">Row ${rowNumber} Errors:</h4><ul>`;
    Object.values(row.errors).forEach(error => {
        html += `<li style="margin-bottom: 0.5rem;">${error}</li>`;
    });
    html += '</ul>';

    // Show modal with errors
    document.getElementById('errorDetails').innerHTML = html;
    document.getElementById('errorModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/**
 * Display all validation errors in the modal
 */
function showAllErrors() {
    let html = '<h4 style="margin-bottom: 1rem; color: #333;">All Validation Errors:</h4>';
    let hasErrors = false;

    errorData.forEach(row => {
        if (row.errors && Object.keys(row.errors).length > 0) {
            hasErrors = true;
            html += `<h5 style="margin: 1rem 0 0.5rem; color: #856404;">Row ${row.row_number}:</h5><ul>`;
            Object.values(row.errors).forEach(error => {
                html += `<li style="margin-bottom: 0.3rem;">${error}</li>`;
            });
            html += '</ul>';
        }
    });

    if (!hasErrors) {
        html += '<p class="text-muted">No errors found.</p>';
    }

    // Show modal with all errors
    document.getElementById('errorDetails').innerHTML = html;
    document.getElementById('errorModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/**
 * Close the error modal
 */
function closeErrorModal() {
    document.getElementById('errorModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('errorModal').style.display === 'flex') {
        closeErrorModal();
    }
});

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('errorModal');
    if (e.target === modal) {
        closeErrorModal();
    }
});
</script>
@endpush
