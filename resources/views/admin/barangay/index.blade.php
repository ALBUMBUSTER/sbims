@extends('layouts.app')

@section('title', 'Barangay Information')

@push('styles')
<!-- Remove Font Awesome CDN since it's already in layouts.app -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .barangay-stats-container {
        margin-bottom: 2rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #eef2f6;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.residents { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-icon.blotters { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-icon.certificates { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-icon.officials { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .stat-icon.households { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .stat-icon.monthly { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }

    .stat-icon i {
        color: white;
        font-size: 2rem;
    }

    .stat-details {
        flex: 1;
        margin-left: 1rem;
    }

    .stat-label {
        color: #718096;
        font-size: 0.9rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        color: #2d3748;
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-subtitle {
        color: #a0aec0;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .barangay-detail-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #667eea;
    }

    .section-header h3 {
        color: #333;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }

    .section-header h3 i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    .section-header .badge {
        background: #e2e8f0;
        color: #4a5568;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }

    .info-box {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
    }

    .info-box-header {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed #cbd5e0;
    }

    .info-box-header i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #edf2f7;
    }

    .info-list-item:last-child {
        border-bottom: none;
    }

    .info-list-label {
        color: #718096;
    }

    .info-list-label i {
        margin-right: 0.5rem;
        color: #a0aec0;
        font-size: 0.9rem;
    }

    .info-list-value {
        font-weight: 500;
        color: #2d3748;
    }

    .trend-up { color: #48bb78; }
    .trend-down { color: #f56565; }

    .empty-state {
        text-align: center;
        padding: 2rem;
        background: #f7fafc;
        border-radius: 8px;
        color: #a0aec0;
    }

    .update-note {
        background: #ebf8ff;
        border-left: 4px solid #4299e1;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        color: #2c5282;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-edit-stats {
        background: #48bb78;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-edit-stats i {
        font-size: 1rem;
    }

    .btn-edit-stats:hover {
        background: #38a169;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .modal-header h4 {
        margin: 0;
        color: #2d3748;
    }

    .modal-header h4 i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #a0aec0;
    }

    .modal-close:hover {
        color: #718096;
    }

    .stat-input-group {
        margin-bottom: 1rem;
    }

    .stat-input-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #4a5568;
        font-weight: 500;
    }

    .stat-input-group label i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    .stat-input-group input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 1rem;
    }

    .stat-input-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .barangay-form-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        max-width: 800px;
        margin: 2rem auto 0;
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

    .form-group label i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    .required-field::after {
        content: " *";
        color: #dc2626;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group textarea,
    .form-group input[type="number"] {
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

    .btn-primary {
        background: #667eea;
        color: white;
        border: 1px solid #667eea;
    }

    .btn-primary:hover {
        background: #5a67d8;
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

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #eef2f6;
    }

    .chart-card.wide {
        grid-column: span 2;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .chart-header h4 {
        margin: 0;
        color: #2d3748;
        font-size: 1rem;
        font-weight: 600;
    }

    .chart-header i {
        color: #667eea;
        margin-right: 0.5rem;
    }

    .chart-container {
        position: relative;
        height: 250px;
        width: 100%;
    }

    .chart-container.small {
        height: 200px;
    }

    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #e2e8f0;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #4a5568;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 4px;
    }

    .stat-highlight {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .chart-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .chart-tab {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: white;
        color: #4a5568;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chart-tab:hover {
        background: #f7fafc;
    }

    .chart-tab.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .chart-tab i {
        margin-right: 0.25rem;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .alert-success {
        background: #c6f6d5;
        border: 1px solid #9ae6b4;
        color: #22543d;
    }

    .alert-danger {
        background: #fed7d7;
        border: 1px solid #feb2b2;
        color: #742a2a;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }

        .chart-card.wide {
            grid-column: span 1;
        }

        .section-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .update-note {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-info-circle"></i> Barangay Information</h1>
                <p>Comprehensive barangay overview and statistics</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Barangay Statistics Dashboard -->
        <div class="barangay-stats-container">
            <div class="stats-grid">
                <!-- Total Residents Card -->
                <div class="stat-card">
                    <div class="stat-icon residents">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">Total Residents</div>
                        <div class="stat-value">{{ number_format($statistics['total_residents'] ?? 0) }}</div>
                        <div class="stat-subtitle">
                            <span class="trend-up"><i class="fas fa-arrow-up"></i> {{ $statistics['new_residents_month'] ?? 0 }}</span> this month
                        </div>
                    </div>
                </div>

                <!-- Total Blotter Cases Card -->
                <div class="stat-card">
                    <div class="stat-icon blotters">
                        <i class="fas fa-scale-balanced"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">Blotter Cases</div>
                        <div class="stat-value">{{ number_format($statistics['total_blotters'] ?? 0) }}</div>
                        <div class="stat-subtitle">
                            <span style="color: #ed8936;"><i class="fas fa-clock"></i> {{ $statistics['pending_blotters'] ?? 0 }} pending</span> |
                            <span style="color: #48bb78;"><i class="fas fa-check-circle"></i> {{ $statistics['settled_blotters'] ?? 0 }} settled</span>
                        </div>
                    </div>
                </div>

                <!-- Total Certificates Card -->
                <div class="stat-card">
                    <div class="stat-icon certificates">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">Certificates Issued</div>
                        <div class="stat-value">{{ number_format($statistics['total_certificates'] ?? 0) }}</div>
                        <div class="stat-subtitle">
                            <i class="fas fa-calendar"></i> {{ $statistics['certificates_month'] ?? 0 }} this month
                        </div>
                    </div>
                </div>

                <!-- Total Households Card -->
                <div class="stat-card">
                    <div class="stat-icon households">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">Total Households</div>
                        <div class="stat-value">{{ number_format($statistics['total_households'] ?? 0) }}</div>
                        <div class="stat-subtitle">
                            <i class="fas fa-users"></i> ~{{ number_format($statistics['avg_household_size'] ?? 0) }} avg. members
                        </div>
                    </div>
                </div>

                <!-- Barangay Officials Card -->
                <div class="stat-card">
                    <div class="stat-icon officials">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">Barangay Officials</div>
                        <div class="stat-value">{{ number_format($statistics['total_officials'] ?? 0) }}</div>
                        <div class="stat-subtitle">
                            <i class="fas fa-user-check"></i> {{ $statistics['active_officials'] ?? 0 }} active
                        </div>
                    </div>
                </div>

                <!-- Monthly Transactions Card -->
                <div class="stat-card">
                    <div class="stat-icon monthly">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">This Month's Transactions</div>
                        <div class="stat-value">{{ number_format($statistics['monthly_transactions'] ?? 0) }}</div>
                        <div class="stat-subtitle">
                            <i class="fas fa-file-alt"></i> {{ $statistics['monthly_certificates'] ?? 0 }} certificates
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics Section -->
            <div class="barangay-detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-chart-pie"></i> Detailed Barangay Statistics</h3>
                    <div class="badge"><i class="fas fa-clock"></i> Last updated: {{ now()->format('F d, Y h:i A') }}</div>
                </div>

                <div class="info-grid">
                    <!-- Resident Demographics -->
                    <div class="info-box">
                        <div class="info-box-header"><i class="fas fa-users"></i> Resident Demographics</div>
                        <ul class="info-list">
                            @php
                                $totalResidents = max($statistics['total_residents'] ?? 1, 1);
                            @endphp
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-mars"></i> Male</span>
                                <span class="info-list-value">{{ number_format($statistics['male_residents'] ?? 0) }} ({{ round(($statistics['male_residents'] ?? 0) / $totalResidents * 100) }}%)</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-venus"></i> Female</span>
                                <span class="info-list-value">{{ number_format($statistics['female_residents'] ?? 0) }} ({{ round(($statistics['female_residents'] ?? 0) / $totalResidents * 100) }}%)</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-user-tie"></i> Senior Citizens</span>
                                <span class="info-list-value">{{ number_format($statistics['senior_citizens'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-wheelchair"></i> PWD</span>
                                <span class="info-list-value">{{ number_format($statistics['pwd'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-hand-holding-heart"></i> 4Ps Beneficiaries</span>
                                <span class="info-list-value">{{ number_format($statistics['four_ps'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-check-double"></i> Registered Voters</span>
                                <span class="info-list-value">{{ number_format($statistics['registered_voters'] ?? 0) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Blotter Cases by Status -->
                    <div class="info-box">
                        <div class="info-box-header"><i class="fas fa-scale-balanced"></i> Blotter Cases</div>
                        <ul class="info-list">
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-clock"></i> Pending</span>
                                <span class="info-list-value">{{ number_format($statistics['pending_blotters'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-search"></i> Investigating</span>
                                <span class="info-list-value">{{ number_format($statistics['investigating_blotters'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-gavel"></i> Hearings</span>
                                <span class="info-list-value">{{ number_format($statistics['hearing_blotters'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-check-circle"></i> Settled</span>
                                <span class="info-list-value">{{ number_format($statistics['settled_blotters'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item" style="border-top: 1px dashed #cbd5e0; margin-top: 0.5rem; padding-top: 0.75rem;">
                                <span class="info-list-label"><i class="fas fa-calendar-alt"></i> This Month</span>
                                <span class="info-list-value">{{ number_format($statistics['monthly_blotters'] ?? 0) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Certificates by Type -->
                    <div class="info-box">
                        <div class="info-box-header"><i class="fas fa-file-alt"></i> Certificates Issued</div>
                        <ul class="info-list">
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-id-card"></i> Barangay Clearance</span>
                                <span class="info-list-value">{{ number_format($statistics['clearance_certificates'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-hand-holding-heart"></i> Indigency</span>
                                <span class="info-list-value">{{ number_format($statistics['indigency_certificates'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-map-pin"></i> Residency</span>
                                <span class="info-list-value">{{ number_format($statistics['residency_certificates'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-star"></i> Good Moral</span>
                                <span class="info-list-value">{{ number_format($statistics['good_moral_certificates'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-file"></i> Others</span>
                                <span class="info-list-value">{{ number_format($statistics['other_certificates'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item" style="border-top: 1px dashed #cbd5e0; margin-top: 0.5rem; padding-top: 0.75rem;">
                                <span class="info-list-label"><i class="fas fa-hourglass-half"></i> Pending</span>
                                <span class="info-list-value">{{ number_format($statistics['pending_certificates'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-check-double"></i> Released</span>
                                <span class="info-list-value">{{ number_format($statistics['released_certificates'] ?? 0) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Barangay Officials -->
                    <div class="info-box">
                        <div class="info-box-header"><i class="fas fa-id-badge"></i> Barangay Officials</div>
                        <ul class="info-list">
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-crown"></i> Barangay Captain</span>
                                <span class="info-list-value">{{ $barangayInfo->barangay_captain ?? 'Not set' }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-pen-fancy"></i> Barangay Secretary</span>
                                <span class="info-list-value">{{ $barangayInfo->barangay_secretary ?? 'Not set' }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-coins"></i> Barangay Treasurer</span>
                                <span class="info-list-value">{{ $statistics['barangay_treasurer'] ?? 'Not set' }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-users-between-lines"></i> Kagawads</span>
                                <span class="info-list-value">{{ number_format($statistics['kagawads_count'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-child"></i> SK Chairman</span>
                                <span class="info-list-value">{{ $statistics['sk_chairman'] ?? 'Not set' }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-shield-halved"></i> Barangay Tanods</span>
                                <span class="info-list-value">{{ number_format($statistics['tanods_count'] ?? 0) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Purok Distribution -->
                    <div class="info-box">
                        <div class="info-box-header"><i class="fas fa-map-marker-alt"></i> Purok Distribution</div>
                        <ul class="info-list">
                            @forelse(($statistics['purok_distribution'] ?? []) as $purok => $count)
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-map-pin"></i> Purok {{ $purok }}</span>
                                <span class="info-list-value">{{ number_format($count) }} residents</span>
                            </li>
                            @empty
                            <li class="empty-state"><i class="fas fa-exclamation-triangle"></i> No purok data available</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Monthly Summary -->
                    <div class="info-box">
                        <div class="info-box-header"><i class="fas fa-chart-line"></i> This Month's Summary</div>
                        <ul class="info-list">
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-user-plus"></i> New Residents</span>
                                <span class="info-list-value">{{ number_format($statistics['new_residents_month'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-plus-circle"></i> New Blotter Cases</span>
                                <span class="info-list-value">{{ number_format($statistics['monthly_blotters'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-file-circle-plus"></i> Certificates Issued</span>
                                <span class="info-list-value">{{ number_format($statistics['monthly_certificates'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item">
                                <span class="info-list-label"><i class="fas fa-check-circle"></i> Settled Cases</span>
                                <span class="info-list-value">{{ number_format($statistics['monthly_settled'] ?? 0) }}</span>
                            </li>
                            <li class="info-list-item" style="border-top: 1px dashed #cbd5e0; margin-top: 0.5rem; padding-top: 0.75rem;">
                                <span class="info-list-label"><i class="fas fa-chart-line"></i> Total Transactions</span>
                                <span class="info-list-value">{{ number_format($statistics['monthly_transactions'] ?? 0) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Note for Statistics -->
        @if(auth()->user() && (auth()->user()->role_id == 1 || auth()->user()->role_id == 2))
        <div class="update-note">
            <span><i class="fas fa-info-circle"></i> <strong>Note:</strong> Statistics are automatically updated from your records.</span>
            <button class="btn-edit-stats" onclick="openStatsModal()">
                <i class="fas fa-pen-to-square"></i> Edit Statistics Manually
            </button>
        </div>
        @endif

        <!-- Data Visualization Section -->
        <div class="barangay-detail-section">
            <div class="section-header">
                <h3><i class="fas fa-chart-pie"></i> Barangay Data Visualization</h3>
                <div class="badge"><i class="fas fa-sync-alt"></i> Real-time data</div>
            </div>

            <!-- Chart Tabs for different views -->
            <div class="chart-tabs">
                <button class="chart-tab active" onclick="switchChartView(event, 'demographics')">
                    <i class="fas fa-users"></i> Demographics
                </button>
                <button class="chart-tab" onclick="switchChartView(event, 'certificates')">
                    <i class="fas fa-file-alt"></i> Certificates
                </button>
                <button class="chart-tab" onclick="switchChartView(event, 'blotters')">
                    <i class="fas fa-scale-balanced"></i> Blotter Cases
                </button>
                <button class="chart-tab" onclick="switchChartView(event, 'purok')">
                    <i class="fas fa-map-pin"></i> Purok Distribution
                </button>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid" id="chartsGrid">
                <!-- Gender Distribution Chart -->
                <div class="chart-card" data-chart="demographics">
                    <div class="chart-header">
                        <h4><i class="fas fa-venus-mars"></i> Gender Distribution</h4>
                        <span class="stat-highlight">{{ round(($statistics['male_residents'] ?? 0) / max($statistics['total_residents'] ?? 1, 1) * 100) }}% Male</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #4299e1;"></div>
                            <span>Male: {{ number_format($statistics['male_residents'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ed64a6;"></div>
                            <span>Female: {{ number_format($statistics['female_residents'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Resident Categories Chart -->
                <div class="chart-card" data-chart="demographics">
                    <div class="chart-header">
                        <h4><i class="fas fa-tags"></i> Resident Categories</h4>
                        <span class="stat-highlight">{{ $statistics['senior_citizens'] ?? 0 }} Seniors</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoriesChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #48bb78;"></div>
                            <span>Senior: {{ number_format($statistics['senior_citizens'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ecc94b;"></div>
                            <span>PWD: {{ number_format($statistics['pwd'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #9f7aea;"></div>
                            <span>4Ps: {{ number_format($statistics['four_ps'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #f56565;"></div>
                            <span>Voters: {{ number_format($statistics['registered_voters'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Certificate Types Chart -->
                <div class="chart-card" data-chart="certificates">
                    <div class="chart-header">
                        <h4><i class="fas fa-file-alt"></i> Certificates by Type</h4>
                        <span class="stat-highlight">{{ $statistics['certificates_month'] ?? 0 }} this month</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="certificatesChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #4299e1;"></div>
                            <span>Clearance: {{ number_format($statistics['clearance_certificates'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #48bb78;"></div>
                            <span>Indigency: {{ number_format($statistics['indigency_certificates'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ecc94b;"></div>
                            <span>Residency: {{ number_format($statistics['residency_certificates'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #9f7aea;"></div>
                            <span>Good Moral: {{ number_format($statistics['good_moral_certificates'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Blotter Status Chart -->
                <div class="chart-card" data-chart="blotters">
                    <div class="chart-header">
                        <h4><i class="fas fa-scale-balanced"></i> Blotter Cases by Status</h4>
                        <span class="stat-highlight">{{ $statistics['monthly_blotters'] ?? 0 }} new this month</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="blotterChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #f56565;"></div>
                            <span>Pending: {{ number_format($statistics['pending_blotters'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ecc94b;"></div>
                            <span>Investigating: {{ number_format($statistics['investigating_blotters'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #9f7aea;"></div>
                            <span>Hearings: {{ number_format($statistics['hearing_blotters'] ?? 0) }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #48bb78;"></div>
                            <span>Settled: {{ number_format($statistics['settled_blotters'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trends Chart (Line Chart) -->
                <div class="chart-card wide" data-chart="all">
                    <div class="chart-header">
                        <h4><i class="fas fa-chart-line"></i> Monthly Trends</h4>
                        <span class="stat-highlight">{{ $statistics['monthly_transactions'] ?? 0 }} total transactions</span>
                    </div>
                    <div class="chart-container small">
                        <canvas id="trendsChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #4299e1;"></div>
                            <span>New Residents</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #48bb78;"></div>
                            <span>Certificates</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #f56565;"></div>
                            <span>Blotter Cases</span>
                        </div>
                    </div>
                </div>

                <!-- Purok Distribution Chart (Bar Chart) -->
                <div class="chart-card wide" data-chart="purok">
                    <div class="chart-header">
                        <h4><i class="fas fa-map-pin"></i> Population by Purok</h4>
                        <span class="stat-highlight">{{ count($statistics['purok_distribution'] ?? []) }} Puroks</span>
                    </div>
                    <div class="chart-container small">
                        <canvas id="purokChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Form -->
        <!-- <div class="barangay-form-container">
            <div class="form-header">
                <h2><i class="fas fa-edit"></i> Update Barangay Information</h2>
                <p>Fill in the details below to update barangay contact information</p>
            </div>

            <form action="{{ route('admin.barangay.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="barangay_name" class="required-field"><i class="fas fa-building"></i> Barangay Name</label>
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
                    <label for="barangay_captain"><i class="fas fa-crown"></i> Barangay Captain</label>
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
                    <label for="barangay_secretary"><i class="fas fa-pen-fancy"></i> Barangay Secretary</label>
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
                    <label for="contact_number"><i class="fas fa-phone"></i> Contact Number</label>
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
                    <label for="address" class="required-field"><i class="fas fa-map-marker-alt"></i> Complete Address</label>
                    <textarea id="address"
                              name="address"
                              required
                              placeholder="Enter complete address (e.g., Libertad, Isabel, Leyte)">{{ old('address', $barangayInfo->address ?? '') }}</textarea>
                    @error('address')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
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
                    <label for="logo"><i class="fas fa-image"></i> Barangay Logo</label>
                    <input type="file"
                           id="logo"
                           name="logo"
                           accept="image/*">
                    @error('logo')
                        <div class="error-message">{{ $message }}</div>
                    @enderror

                    @if(!empty($barangayInfo->logo_path))
                    <div class="current-logo" id="currentLogoContainer">
                        <div class="current-logo-label"><i class="fas fa-image"></i> Current Logo:</div>
                        <div class="logo-preview">
                            <img src="{{ Storage::url($barangayInfo->logo_path) }}"
                                 alt="Current Barangay Logo"
                                 id="currentLogoPreview">
                        </div>
                    </div>
                    @endif

                    <div class="logo-preview" id="newLogoPreview" style="display: none;">
                        <div class="current-logo-label"><i class="fas fa-image"></i> New Logo Preview:</div>
                        <img id="logoPreviewImage"
                             src="#"
                             alt="New Logo Preview"
                             style="max-width: 150px;">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-outline btn-reset">
                        <i class="fas fa-undo-alt"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Information
                    </button>
                </div>
            </form>
        </div> -->
    </main>
</div>

<!-- Manual Statistics Edit Modal -->
@if(auth()->user() && (auth()->user()->role_id == 1 || auth()->user()->role_id == 2))
<div class="modal" id="statsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="fas fa-chart-simple"></i> Edit Barangay Statistics</h4>
            <button class="modal-close" onclick="closeStatsModal()">&times;</button>
        </div>
        <form id="statsForm" action="{{ route('admin.barangay.update-stats') }}" method="POST">
            @csrf
            @method('PUT')

            <p style="color: #718096; margin-bottom: 1rem;"><i class="fas fa-info-circle"></i> You can manually override statistics here. Leave blank to use auto-calculated values.</p>

            <div class="stat-input-group">
                <label for="manual_total_residents"><i class="fas fa-users"></i> Total Residents</label>
                <input type="number" id="manual_total_residents" name="manual_total_residents"
                       value="{{ session('manual_stats.total_residents', '') }}"
                       placeholder="Auto: {{ number_format($statistics['total_residents'] ?? 0) }}">
            </div>

            <div class="stat-input-group">
                <label for="manual_total_blotters"><i class="fas fa-scale-balanced"></i> Total Blotter Cases</label>
                <input type="number" id="manual_total_blotters" name="manual_total_blotters"
                       value="{{ session('manual_stats.total_blotters', '') }}"
                       placeholder="Auto: {{ number_format($statistics['total_blotters'] ?? 0) }}">
            </div>

            <div class="stat-input-group">
                <label for="manual_total_certificates"><i class="fas fa-file-alt"></i> Total Certificates</label>
                <input type="number" id="manual_total_certificates" name="manual_total_certificates"
                       value="{{ session('manual_stats.total_certificates', '') }}"
                       placeholder="Auto: {{ number_format($statistics['total_certificates'] ?? 0) }}">
            </div>

            <div class="stat-input-group">
                <label for="manual_total_households"><i class="fas fa-home"></i> Total Households</label>
                <input type="number" id="manual_total_households" name="manual_total_households"
                       value="{{ session('manual_stats.total_households', '') }}"
                       placeholder="Auto: {{ number_format($statistics['total_households'] ?? 0) }}">
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-outline btn-reset" onclick="closeStatsModal()"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Manual Overrides</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Global variables for charts
    let charts = {};

    // Initialize charts when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        setupEventListeners();
    });

    function initializeCharts() {
        // Wait for Chart.js
        if (typeof Chart === 'undefined') {
            console.warn('Waiting for Chart.js...');
            setTimeout(initializeCharts, 100);
            return;
        }

        // Destroy existing charts
        Object.values(charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        charts = {};

        // Get statistics data safely
        const stats = getStatisticsData();

        // Create charts
        createGenderChart(stats);
        createCategoriesChart(stats);
        createCertificatesChart(stats);
        createBlotterChart(stats);
        createTrendsChart(stats);
        createPurokChart(stats);
    }

    function getStatisticsData() {
        // Safely extract statistics from PHP
        const statistics = JSON.parse('{!! addslashes(json_encode($statistics ?? [])) !!}');

        // Helper function to safely get numeric value
        const getStat = (key, defaultValue = 0) => {
            const value = statistics[key];
            const num = parseInt(value);
            return isNaN(num) ? defaultValue : Math.max(0, num);
        };

        return {
            male: getStat('male_residents'),
            female: getStat('female_residents'),
            senior: getStat('senior_citizens'),
            pwd: getStat('pwd'),
            fourPs: getStat('four_ps'),
            voters: getStat('registered_voters'),
            total: getStat('total_residents', 1),
            clearance: getStat('clearance_certificates'),
            indigency: getStat('indigency_certificates'),
            residency: getStat('residency_certificates'),
            moral: getStat('good_moral_certificates'),
            other: getStat('other_certificates'),
            pending: getStat('pending_blotters'),
            investigating: getStat('investigating_blotters'),
            hearings: getStat('hearing_blotters'),
            settled: getStat('settled_blotters'),
            newRes: getStat('new_residents_month'),
            monthCerts: getStat('monthly_certificates'),
            monthBlotters: getStat('monthly_blotters'),
            purokLabels: Object.keys(statistics.purok_distribution || {}),
            purokData: Object.values(statistics.purok_distribution || {})
        };
    }

    function createGenderChart(stats) {
        const ctx = document.getElementById('genderChart')?.getContext('2d');
        if (!ctx) return;

        charts.gender = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [stats.male, stats.female],
                    backgroundColor: ['#4299e1', '#ed64a6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = stats.male + stats.female;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function createCategoriesChart(stats) {
        const ctx = document.getElementById('categoriesChart')?.getContext('2d');
        if (!ctx) return;

        const others = Math.max(0, stats.total - stats.senior - stats.pwd - stats.fourPs - stats.voters);

        charts.categories = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Senior Citizens', 'PWD', '4Ps', 'Registered Voters', 'Others'],
                datasets: [{
                    data: [stats.senior, stats.pwd, stats.fourPs, stats.voters, others],
                    backgroundColor: ['#48bb78', '#ecc94b', '#9f7aea', '#f56565', '#a0aec0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = stats.total;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function createCertificatesChart(stats) {
        const ctx = document.getElementById('certificatesChart')?.getContext('2d');
        if (!ctx) return;

        charts.certificates = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Clearance', 'Indigency', 'Residency', 'Good Moral', 'Others'],
                datasets: [{
                    data: [stats.clearance, stats.indigency, stats.residency, stats.moral, stats.other],
                    backgroundColor: ['#4299e1', '#48bb78', '#ecc94b', '#9f7aea', '#f56565'],
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw.toLocaleString()} certificates`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    function createBlotterChart(stats) {
        const ctx = document.getElementById('blotterChart')?.getContext('2d');
        if (!ctx) return;

        charts.blotter = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Investigating', 'Hearings', 'Settled'],
                datasets: [{
                    data: [stats.pending, stats.investigating, stats.hearings, stats.settled],
                    backgroundColor: ['#f56565', '#ecc94b', '#9f7aea', '#48bb78'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = stats.pending + stats.investigating + stats.hearings + stats.settled;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function createTrendsChart(stats) {
        const ctx = document.getElementById('trendsChart')?.getContext('2d');
        if (!ctx) return;

        // Generate weekly distribution
        const weeklyResidents = Array(4).fill().map(() => Math.round(stats.newRes / 4));
        const weeklyCerts = Array(4).fill().map(() => Math.round(stats.monthCerts / 4));
        const weeklyBlotters = Array(4).fill().map(() => Math.round(stats.monthBlotters / 4));

        charts.trends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [
                    {
                        label: 'New Residents',
                        data: weeklyResidents,
                        borderColor: '#4299e1',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        pointBackgroundColor: '#4299e1'
                    },
                    {
                        label: 'Certificates',
                        data: weeklyCerts,
                        borderColor: '#48bb78',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        pointBackgroundColor: '#48bb78'
                    },
                    {
                        label: 'Blotter Cases',
                        data: weeklyBlotters,
                        borderColor: '#f56565',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        pointBackgroundColor: '#f56565'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    function createPurokChart(stats) {
        const ctx = document.getElementById('purokChart')?.getContext('2d');
        if (!ctx || !stats.purokLabels?.length) return;

        charts.purok = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: stats.purokLabels.map(label => `Purok ${label}`),
                datasets: [{
                    data: stats.purokData,
                    backgroundColor: '#667eea',
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw.toLocaleString()} residents`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    function switchChartView(event, view) {
        // Update active tab
        document.querySelectorAll('.chart-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        event.target.classList.add('active');

        // Show/hide charts based on view
        document.querySelectorAll('[data-chart]').forEach(chart => {
            const chartView = chart.dataset.chart;
            if (view === 'all' || chartView === view || chartView === 'all') {
                chart.style.display = 'block';
            } else {
                chart.style.display = 'none';
            }
        });
    }

    function setupEventListeners() {
        // Logo preview
        const logoInput = document.getElementById('logo');
        if (logoInput) {
            logoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                const preview = document.getElementById('newLogoPreview');
                const previewImage = document.getElementById('logoPreviewImage');
                const currentLogo = document.getElementById('currentLogoContainer');

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        preview.style.display = 'block';
                        if (currentLogo) currentLogo.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none';
                    if (currentLogo) currentLogo.style.display = 'block';
                }
            });
        }

        // Reset form
        const resetBtn = document.querySelector('button[type="reset"]');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                const newPreview = document.getElementById('newLogoPreview');
                const currentLogo = document.getElementById('currentLogoContainer');
                const logoInput = document.getElementById('logo');

                if (newPreview) newPreview.style.display = 'none';
                if (currentLogo) currentLogo.style.display = 'block';
                if (logoInput) logoInput.value = '';
            });
        }

        // Modal close on outside click
        // window.addEventListener('click', function(event) {
        //     const modal = document.getElementById('statsModal');
        //     if (event.target === modal) {
        //         closeStatsModal();
        //     }
        // });
    }

    // Modal functions
    function openStatsModal() {
        const modal = document.getElementById('statsModal');
        if (modal) modal.classList.add('active');
    }

    function closeStatsModal() {
        const modal = document.getElementById('statsModal');
        if (modal) modal.classList.remove('active');
    }

    // Make functions globally available
    window.switchChartView = switchChartView;
    window.openStatsModal = openStatsModal;
    window.closeStatsModal = closeStatsModal;
</script>
@endpush
