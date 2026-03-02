@extends('layouts.app')

@section('title', 'Pending Approvals')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Pending Approvals</h1>
            <p>Review and approve pending requests</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('captain.dashboard') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Pending Certificates -->
<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fas fa-file-alt"></i> Pending Certificate Approvals</h3>
    </div>
    <div class="card-body">
        @if($pendingCertificates->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Certificate #</th>
                            <th>Resident</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingCertificates as $cert)
                        <tr>
                            <td>{{ $cert->certificate_id }}</td>
                            <td>{{ $cert->resident->full_name ?? 'N/A' }}</td>
                            <td>{{ $cert->certificate_type }}</td>
                            <td>{{ Str::limit($cert->purpose, 30) }}</td>
                            <td>{{ $cert->created_at->format('M d, Y') }}</td>
                            <td>
                                <button class="btn-approve" onclick="showApproveModal(<?php echo $cert->id; ?>)">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn-reject" onclick="showRejectModal(<?php echo $cert->id; ?>)">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $pendingCertificates->links() }}
            </div>
        @else
            <p class="no-data">No pending certificate approvals</p>
        @endif
    </div>
</div>

    <!-- Pending Blotters -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-gavel"></i> Pending Blotter Cases</h3>
    </div>
    <div class="card-body">
        @if($pendingBlotters->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Blotter #</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Incident Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingBlotters as $blotter)
                        <tr>
                            <td>{{ $blotter->blotter_number }}</td>
                            <td>{{ $blotter->complainant->full_name ?? $blotter->complainant_name ?? 'N/A' }}</td>
                            <td>{{ $blotter->respondent_name }}</td>
                            <td>{{ $blotter->incident_type }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($blotter->status) }}">
                                    {{ $blotter->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('captain.blotters.show', $blotter) }}" class="btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $pendingBlotters->links() }}
            </div>
        @else
            <p class="no-data">No pending blotter cases</p>
        @endif
    </div>
</div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Approve Certificate</h3>
            <button type="button" class="close" onclick="closeApproveModal()">&times;</button>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            <div class="modal-body">
                <p>Are you sure you want to approve this certificate?</p>
                <div class="form-group">
                    <label for="remarks">Remarks (Optional)</label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Add any remarks..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeApproveModal()">Cancel</button>
                <button type="submit" class="btn-approve">Confirm Approve</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Reject Certificate</h3>
            <button type="button" class="close" onclick="closeRejectModal()">&times;</button>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="rejection_reason">Reason for Rejection <span class="required">*</span></label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn-reject">Confirm Reject</button>
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
}

.page-title h1 {
    color: #333;
    margin-bottom: 0.5rem;
}

.page-title p {
    color: #666;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #eef2ff;
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    margin: 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header h3 i {
    color: #667eea;
}

.card-body {
    padding: 1.5rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
}

.table tr:hover td {
    background: #f8fafc;
}

.btn-approve, .btn-reject, .btn-view {
    padding: 0.25rem 0.75rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-right: 0.5rem;
}

.btn-approve {
    background: #d4edda;
    color: #155724;
}

.btn-approve:hover {
    background: #c3e6cb;
}

.btn-reject {
    background: #fee2e2;
    color: #dc2626;
}

.btn-reject:hover {
    background: #fecaca;
}

.btn-view {
    background: #e9ecef;
    color: #495057;
    text-decoration: none;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-investigating {
    background: #cce5ff;
    color: #004085;
}

.status-hearings {
    background: #e2d5f1;
    color: #553c9a;
}

.status-settled {
    background: #d4edda;
    color: #155724;
}

.no-data {
    color: #999;
    text-align: center;
    padding: 2rem;
    margin: 0;
}

.pagination-wrapper {
    margin-top: 1.5rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
    animation: slideIn 0.3s;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.modal-header .close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.modal-header .close:hover {
    color: #333;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
}

.required {
    color: #dc2626;
}

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
function showApproveModal(id) {
    const form = document.getElementById('approveForm');
    form.action = "{{ url('captain/approvals/certificate') }}/" + id + "/approve";
    document.getElementById('approveModal').classList.add('show');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.remove('show');
    document.getElementById('remarks').value = '';
}

function showRejectModal(id) {
    const form = document.getElementById('rejectForm');
    form.action = "{{ url('captain/approvals/certificate') }}/" + id + "/reject";
    document.getElementById('rejectModal').classList.add('show');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('show');
    document.getElementById('rejection_reason').value = '';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}
</script>
@endpush
