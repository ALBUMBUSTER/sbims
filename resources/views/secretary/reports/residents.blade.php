@extends('layouts.app')

@section('title', 'Residents Report')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Residents Report</h1>
            <p>Demographics and statistics</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('secretary.reports.index') }}" class="btn-secondary">
                <x-heroicon-o-arrow-left class="icon-small" />
                Back to Reports
            </a>
            <form action="{{ route('secretary.reports.export') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="type" value="residents">
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-document-arrow-down class="icon-small" />
                    Export PDF
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filters-section">
        <form action="{{ route('secretary.reports.residents') }}" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="date_from">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="filter-input">
            </div>
            <div class="filter-group">
                <label for="date_to">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="filter-input">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Generate</button>
                <a href="{{ route('secretary.reports.residents') }}" class="btn-clear">Reset</a>
            </div>
        </form>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <x-heroicon-o-users />
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Residents</span>
                <span class="stat-value">{{ $statistics['total'] }}</span>
            </div>
        </div>

        <div class="stat-card male">
            <div class="stat-icon">
                <x-heroicon-o-user />
            </div>
            <div class="stat-content">
                <span class="stat-label">Male</span>
                <span class="stat-value">{{ $statistics['by_gender']['male'] }}</span>
            </div>
        </div>

        <div class="stat-card female">
            <div class="stat-icon">
                <x-heroicon-o-user />
            </div>
            <div class="stat-content">
                <span class="stat-label">Female</span>
                <span class="stat-value">{{ $statistics['by_gender']['female'] }}</span>
            </div>
        </div>

        <div class="stat-card voters">
            <div class="stat-icon">
                <x-heroicon-o-check-badge />
            </div>
            <div class="stat-content">
                <span class="stat-label">Registered Voters</span>
                <span class="stat-value">{{ $statistics['by_status']['voters'] }}</span>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="details-grid">
        <!-- By Purok -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-map-pin class="detail-icon" />
                <h3>Distribution by Purok</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Purok</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statistics['by_purok'] as $purok)
                        <tr>
                            <td>Purok {{ $purok->purok }}</td>
                            <td>{{ $purok->total }}</td>
                            <td>{{ round(($purok->total / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- By Civil Status -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-heart class="detail-icon" />
                <h3>Civil Status</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statistics['by_civil_status'] as $status)
                        <tr>
                            <td>{{ $status->civil_status }}</td>
                            <td>{{ $status->total }}</td>
                            <td>{{ round(($status->total / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Age Distribution -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-calendar class="detail-icon" />
                <h3>Age Distribution</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Age Range</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statistics['age_distribution'] as $range => $count)
                        <tr>
                            <td>{{ $range }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ round(($count / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Special Status -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-star class="detail-icon" />
                <h3>Special Categories</h3>
            </div>
            <div class="detail-body">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Senior Citizens</td>
                            <td>{{ $statistics['by_status']['seniors'] }}</td>
                            <td>{{ round(($statistics['by_status']['seniors'] / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>PWD</td>
                            <td>{{ $statistics['by_status']['pwd'] }}</td>
                            <td>{{ round(($statistics['by_status']['pwd'] / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>4Ps Members</td>
                            <td>{{ $statistics['by_status']['4ps'] }}</td>
                            <td>{{ round(($statistics['by_status']['4ps'] / $statistics['total']) * 100, 1) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Residents List -->
    @if($residents->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Residents List</h3>
            <span class="record-count">Showing {{ $residents->count() }} records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Resident ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Birthdate</th>
                            <th>Age</th>
                            <th>Purok</th>
                            <th>Civil Status</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residents as $resident)
                        <tr>
                            <td>{{ $resident->resident_id }}</td>
                            <td>{{ $resident->first_name }} {{ $resident->last_name }}</td>
                            <td>{{ $resident->gender }}</td>
                            <td>{{ $resident->birthdate ? $resident->birthdate->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $resident->birthdate ? $resident->birthdate->age : 'N/A' }}</td>
                            <td>Purok {{ $resident->purok }}</td>
                            <td>{{ $resident->civil_status }}</td>
                            <td>{{ $resident->contact_number ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
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
    border: none;
    cursor: pointer;
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

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-size: 0.9rem;
    font-weight: 500;
}

.filter-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-filter {
    padding: 0.5rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-filter:hover {
    background: #5a67d8;
}

.btn-clear {
    padding: 0.5rem 1.5rem;
    background: #e2e8f0;
    color: #4a5568;
    text-decoration: none;
    border-radius: 5px;
}

.btn-clear:hover {
    background: #cbd5e0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card.total .stat-icon { background: #667eea; }
.stat-card.male .stat-icon { background: #3b82f6; }
.stat-card.female .stat-icon { background: #ec4899; }
.stat-card.voters .stat-icon { background: #10b981; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    display: block;
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-header {
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-icon {
    width: 20px;
    height: 20px;
    color: #667eea;
}

.detail-header h3 {
    color: #333;
    font-size: 1rem;
    margin: 0;
}

.detail-body {
    padding: 1rem;
}

/* Mini Table */
.mini-table {
    width: 100%;
    border-collapse: collapse;
}

.mini-table th {
    text-align: left;
    padding: 0.5rem;
    background: #f8f9fa;
    color: #555;
    font-size: 0.85rem;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}

.mini-table td {
    padding: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
    font-size: 0.9rem;
}

.mini-table tr:last-child td {
    border-bottom: none;
}

/* Card */
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-top: 2rem;
}

.card-header {
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    color: #333;
    font-size: 1.2rem;
    margin: 0;
}

.record-count {
    color: #666;
    font-size: 0.9rem;
}

.card-body {
    padding: 1.5rem;
}

/* Data Table */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #333;
}

.data-table tr:hover td {
    background: #f8fafc;
}

.icon-small {
    width: 16px;
    height: 16px;
}
</style>
@endpush
