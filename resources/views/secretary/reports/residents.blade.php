@extends('layouts.app')

@section('title', 'Residents Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
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
            <form action="{{ route('secretary.reports.export') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="residents">
                <input type="hidden" name="format" value="excel">
                @if(request('date_from'))
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                @endif
                @if(request('date_to'))
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                @endif
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button type="submit" class="btn-primary">
                    <i class="fas fa-file-excel icon-small"></i>
                    Export to Excel
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Section with Category Filter -->
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
            <div class="filter-group">
                <label for="category">Filter by Category</label>
                <select name="category" id="category" class="filter-input" onchange="this.form.submit()">
                    <option value="all" {{ request('category') == 'all' || !request('category') ? 'selected' : '' }}>All Residents</option>
                    <option value="senior" {{ request('category') == 'senior' ? 'selected' : '' }}>Senior Citizens (60+)</option>
                    <option value="pwd" {{ request('category') == 'pwd' ? 'selected' : '' }}>Persons with Disability (PWD)</option>
                    <option value="voter" {{ request('category') == 'voter' ? 'selected' : '' }}>Registered Voters</option>
                    <option value="4ps" {{ request('category') == '4ps' ? 'selected' : '' }}>4Ps Members</option>
                    <option value="male" {{ request('category') == 'male' ? 'selected' : '' }}>Male Residents</option>
                    <option value="female" {{ request('category') == 'female' ? 'selected' : '' }}>Female Residents</option>
                    <option value="children" {{ request('category') == 'children' ? 'selected' : '' }}>Children (0-17 years)</option>
                    <option value="adult" {{ request('category') == 'adult' ? 'selected' : '' }}>Adults (18-59 years)</option>
                </select>
            </div>
            <div class="filter-actions">
                <a href="{{ route('secretary.reports.residents') }}" class="btn-clear">Reset</a>
            </div>
        </form>
    </div>

    <!-- Category Info Badge -->
    @if(request('category') && request('category') != 'all')
    <div class="category-info">
        <i class="fas fa-filter"></i>
        <span>Showing:
            @switch(request('category'))
                @case('senior') <strong>Senior Citizens (60 years and above)</strong> @break
                @case('pwd') <strong>Persons with Disability (PWD)</strong> @break
                @case('voter') <strong>Registered Voters</strong> @break
                @case('4ps') <strong>4Ps Members</strong> @break
                @case('male') <strong>Male Residents</strong> @break
                @case('female') <strong>Female Residents</strong> @break
                @case('children') <strong>Children (0-17 years old)</strong> @break
                @case('adult') <strong>Adults (18-59 years old)</strong> @break
            @endswitch
        </span>
        <span class="category-count">{{ $statistics['filtered_total'] ?? $statistics['total'] }} residents found</span>
    </div>
    @endif

    <!-- Key Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon"><x-heroicon-o-users /></div>
            <div class="stat-content">
                <span class="stat-label">
                    @if(request('category') && request('category') != 'all')
                        Filtered Residents
                    @else
                        Total Residents
                    @endif
                </span>
                <span class="stat-value">{{ $statistics['filtered_total'] ?? $statistics['total'] }}</span>
            </div>
        </div>
        <div class="stat-card male">
            <div class="stat-icon"><x-heroicon-o-user /></div>
            <div class="stat-content">
                <span class="stat-label">Male</span>
                <span class="stat-value">{{ $statistics['by_gender']['male'] }}</span>
            </div>
        </div>
        <div class="stat-card female">
            <div class="stat-icon"><x-heroicon-o-user /></div>
            <div class="stat-content">
                <span class="stat-label">Female</span>
                <span class="stat-value">{{ $statistics['by_gender']['female'] }}</span>
            </div>
        </div>
        @if(($statistics['by_gender']['other'] ?? 0) > 0)
        <div class="stat-card other">
            <div class="stat-icon"><x-heroicon-o-user /></div>
            <div class="stat-content">
                <span class="stat-label">Other</span>
                <span class="stat-value">{{ $statistics['by_gender']['other'] }}</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Gender Distribution Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-venus-mars"></i> Gender Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="genderChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot male"></span> Male:</span>
                    <span class="stat-value">{{ $statistics['by_gender']['male'] }} ({{ round(($statistics['by_gender']['male'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot female"></span> Female:</span>
                    <span class="stat-value">{{ $statistics['by_gender']['female'] }} ({{ round(($statistics['by_gender']['female'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
                @if(($statistics['by_gender']['other'] ?? 0) > 0)
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot other"></span> Other:</span>
                    <span class="stat-value">{{ $statistics['by_gender']['other'] }} ({{ round(($statistics['by_gender']['other'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Civil Status Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-heart"></i> Civil Status</h3>
            </div>
            <div class="chart-body">
                <canvas id="civilStatusChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @foreach($statistics['by_civil_status'] as $status)
                @php
                    $dotClass = match($status->civil_status) {
                        'Single' => 'single',
                        'Married' => 'married',
                        'Widowed' => 'widowed',
                        'Divorced' => 'divorced',
                        default => 'default'
                    };
                @endphp
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot {{ $dotClass }}"></span> {{ $status->civil_status }}:</span>
                    <span class="stat-value">{{ $status->total }} ({{ round(($status->total / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Age Distribution Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-calendar"></i> Age Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="ageChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                @foreach($statistics['age_distribution'] as $range => $count)
                @php
                    $dotClass = match($range) {
                        '0-17' => 'age-0-17',
                        '18-30' => 'age-18-30',
                        '31-45' => 'age-31-45',
                        '46-60' => 'age-46-60',
                        '60+' => 'age-60-plus',
                        default => 'default'
                    };
                @endphp
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot {{ $dotClass }}"></span> {{ $range }}:</span>
                    <span class="stat-value">{{ $count }} ({{ round(($count / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Special Categories Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-star"></i> Special Categories</h3>
            </div>
            <div class="chart-body">
                <canvas id="specialChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-mini-table">
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot senior"></span> Senior Citizens:</span>
                    <span class="stat-value">{{ $statistics['by_status']['seniors'] }} ({{ round(($statistics['by_status']['seniors'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot pwd"></span> PWD:</span>
                    <span class="stat-value">{{ $statistics['by_status']['pwd'] }} ({{ round(($statistics['by_status']['pwd'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label"><span class="legend-dot fourps"></span> 4Ps Members:</span>
                    <span class="stat-value">{{ $statistics['by_status']['4ps'] }} ({{ round(($statistics['by_status']['4ps'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Purok Distribution Bar Chart -->
    <div class="chart-card full-width">
        <div class="chart-header">
            <h3><i class="fas fa-map-pin"></i> Population by Purok</h3>
            <div class="chart-total">Total: {{ $statistics['filtered_total'] ?? $statistics['total'] }} residents</div>
        </div>
        <div class="chart-body">
            <canvas id="purokChart" width="800" height="300"></canvas>
        </div>
        <div class="purok-stats">
            @foreach($statistics['by_purok'] as $purok)
            <div class="purok-stat-item">
                <span class="purok-label">Purok {{ $purok->purok }}</span>
                <span class="purok-value">{{ $purok->total }} residents</span>
                <div class="purok-bar">
                    <div class="purok-bar-fill" style="width: {{ round(($purok->total / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%;"></div>
                </div>
                <span class="purok-percentage">{{ round(($purok->total / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Detailed Data Tables - Responsive 2x2 Grid Layout -->
    <div class="details-grid">
        <!-- Top Left: Distribution by Purok -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-map-pin class="detail-icon" />
                <h3>Distribution by Purok</h3>
            </div>
            <div class="detail-body">
                <div class="table-responsive">
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
                                <td data-label="Purok">Purok {{ $purok->purok }}</td>
                                <td data-label="Count">{{ $purok->total }}</td>
                                <td data-label="Percentage">{{ round(($purok->total / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%</td>
                             </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Right: Civil Status -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-heart class="detail-icon" />
                <h3>Civil Status</h3>
            </div>
            <div class="detail-body">
                <div class="table-responsive">
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
                                <td data-label="Status">{{ $status->civil_status }}</td>
                                <td data-label="Count">{{ $status->total }}</td>
                                <td data-label="Percentage">{{ round(($status->total / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%</td>
                             </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bottom Left: Age Distribution -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-calendar class="detail-icon" />
                <h3>Age Distribution</h3>
            </div>
            <div class="detail-body">
                <div class="table-responsive">
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
                                <td data-label="Age Range">{{ $range }}</td>
                                <td data-label="Count">{{ $count }}</td>
                                <td data-label="Percentage">{{ round(($count / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%</td>
                             </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bottom Right: Special Categories -->
        <div class="detail-card">
            <div class="detail-header">
                <x-heroicon-o-star class="detail-icon" />
                <h3>Special Categories</h3>
            </div>
            <div class="detail-body">
                <div class="table-responsive">
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
                                <td data-label="Category">Senior Citizens</td>
                                <td data-label="Count">{{ $statistics['by_status']['seniors'] }}</td>
                                <td data-label="Percentage">{{ round(($statistics['by_status']['seniors'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%</td>
                             </tr>
                             <tr>
                                <td data-label="Category">PWD</td>
                                <td data-label="Count">{{ $statistics['by_status']['pwd'] }}</td>
                                <td data-label="Percentage">{{ round(($statistics['by_status']['pwd'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%</td>
                             </tr>
                             <tr>
                                <td data-label="Category">4Ps Members</td>
                                <td data-label="Count">{{ $statistics['by_status']['4ps'] }}</td>
                                <td data-label="Percentage">{{ round(($statistics['by_status']['4ps'] / ($statistics['filtered_total'] ?? $statistics['total'])) * 100, 1) }}%</td>
                             </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ==================== */
/* Container & Layout   */
/* ==================== */
.container-fluid {
    padding: 1.5rem;
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
}

/* ==================== */
/* Page Header          */
/* ==================== */
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
    margin: 0;
}

/* ==================== */
/* Page Actions         */
/* ==================== */
.page-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.page-actions form {
    margin: 0;
    padding: 0;
}

/* ==================== */
/* Buttons              */
/* ==================== */
.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border-radius: 5px;
    font-size: 0.85rem;
    font-weight: 500;
    min-height: 40px;
    line-height: 1;
    box-sizing: border-box;
    white-space: nowrap;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}
.btn-secondary:hover {
    background: #eef2ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.icon-small {
    width: 16px;
    height: 16px;
    display: inline-block;
    vertical-align: middle;
}

/* ==================== */
/* Filter Section       */
/* ==================== */
.filters-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
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
    flex-wrap: wrap;
}
.btn-filter {
    padding: 0.5rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    min-height: 38px;
}
.btn-filter:hover { background: #5a67d8; }
.btn-clear {
    padding: 0.5rem 1.5rem;
    background: #e2e8f0;
    color: #4a5568;
    text-decoration: none;
    border-radius: 5px;
    min-height: 38px;
    display: inline-flex;
    align-items: center;
}
.btn-clear:hover { background: #cbd5e0; }

/* ==================== */
/* Category Info Badge  */
/* ==================== */
.category-info {
    background: #eef2ff;
    border-left: 4px solid #667eea;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.category-info i {
    color: #667eea;
}
.category-info span {
    color: #4a5568;
    font-size: 0.9rem;
}
.category-count {
    background: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    color: #667eea;
}

/* ==================== */
/* Statistics Cards     */
/* ==================== */
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
.stat-card.other .stat-icon { background: #6b7280; }
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}
.stat-icon svg {
    width: 24px;
    height: 24px;
    color: white;
}
.stat-content {
    flex: 1;
    min-width: 0;
}
.stat-label {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.stat-value {
    display: block;
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

/* ==================== */
/* Charts Grid          */
/* ==================== */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.chart-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    width: 100%;
}
.chart-card.full-width { grid-column: span 2; }
.chart-header {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.chart-header h3 {
    margin: 0;
    font-size: 1rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.chart-header h3 i { color: #667eea; }
.chart-body {
    padding: 1.5rem;
    position: relative;
    height: 250px;
    width: 100%;
}

/* ==================== */
/* Chart Mini Table - Statistics Below Charts */
/* ==================== */
.chart-mini-table {
    padding: 0 1.5rem 1.5rem 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
    border-top: 1px dashed #e2e8f0;
    margin-top: 0.5rem;
    padding-top: 1.5rem;
}
.chart-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.6rem 1rem;
    background: #f8fafc;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.chart-stat-item:hover {
    background: #f1f5f9;
    transform: translateY(-1px);
}
.chart-stat-item .stat-label {
    color: #4a5568;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}
.chart-stat-item .stat-value {
    color: #2d3748;
    font-weight: 600;
    font-size: 0.95rem;
    background: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    min-width: 70px;
    text-align: center;
}

/* ==================== */
/* Legend Dots - Color Palette */
/* ==================== */
.legend-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}
/* Gender Status colors */
.legend-dot.male { background: #3b82f6; }
.legend-dot.female { background: #ec4899; }
.legend-dot.other { background: #6b7280; }
/* Civil Status colors */
.legend-dot.single { background: #3b82f6; }
.legend-dot.married { background: #10b981; }
.legend-dot.widowed { background: #8b5cf6; }
.legend-dot.divorced { background: #f59e0b; }
.legend-dot.default { background: #94a3b8; }
/* Age Distribution colors */
.legend-dot.age-0-17 { background: #3b82f6; }
.legend-dot.age-18-30 { background: #10b981; }
.legend-dot.age-31-45 { background: #f59e0b; }
.legend-dot.age-46-60 { background: #8b5cf6; }
.legend-dot.age-60-plus { background: #ec4899; }
/* Special Categories colors */
.legend-dot.senior { background: #3b82f6; }
.legend-dot.pwd { background: #10b981; }
.legend-dot.fourps { background: #f59e0b; }

/* ==================== */
/* Chart Total Badge    */
/* ==================== */
.chart-total {
    font-weight: 600;
    color: #667eea;
    background: #eef2ff;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    white-space: nowrap;
}

/* ==================== */
/* Purok Stats          */
/* ==================== */
.purok-stats {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}
.purok-stat-item {
    display: grid;
    grid-template-columns: 80px 100px 1fr 60px;
    align-items: center;
    gap: 1rem;
    font-size: 0.9rem;
}
.purok-label {
    font-weight: 600;
    color: #333;
    white-space: nowrap;
}
.purok-value {
    color: #667eea;
    font-weight: 500;
    white-space: nowrap;
}
.purok-bar {
    height: 20px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    width: 100%;
}
.purok-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 10px;
    transition: width 0.3s;
}
.purok-percentage {
    font-weight: 600;
    color: #333;
    text-align: right;
    white-space: nowrap;
}

/* ==================== */
/* Details Grid - 2x2 Layout */
/* ==================== */
.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-top: 2rem;
}
.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: fit-content;
    max-height: 450px;
    width: 100%;
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
    flex-shrink: 0;
}
.detail-header h3 {
    color: #333;
    font-size: 1rem;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.detail-body {
    padding: 1rem;
    flex: 1;
    overflow-y: auto;
    width: 100%;
}

/* ==================== */
/* Table Responsive */
/* ==================== */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* ==================== */
/* Mini Table           */
/* ==================== */
.mini-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    min-width: 250px;
}
.mini-table th {
    text-align: left;
    padding: 0.75rem 0.5rem;
    background: #f8f9fa;
    color: #555;
    font-size: 0.8rem;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 1;
}
.mini-table td {
    padding: 0.6rem 0.5rem;
    border-bottom: 1px solid #edf2f7;
    color: #4a5568;
}
.mini-table tr:last-child td { border-bottom: none; }
.mini-table tbody tr:hover { background: #f7fafc; }

/* ==================== */
/* Responsive Design    */
/* ==================== */
@media (max-width: 1024px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    .chart-card.full-width {
        grid-column: span 1;
    }
    .purok-stat-item {
        grid-template-columns: 80px 100px 1fr 60px;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .details-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .purok-stat-item {
        grid-template-columns: 70px 80px 1fr 50px;
        gap: 0.5rem;
        font-size: 0.85rem;
    }
    .chart-mini-table {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    /* Make tables stack on mobile */
    .mini-table thead {
        display: none;
    }
    .mini-table,
    .mini-table tbody,
    .mini-table tr,
    .mini-table td {
        display: block;
        width: 100%;
    }
    .mini-table tr {
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 0.5rem;
    }
    .mini-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        border-bottom: 1px dashed #edf2f7;
        text-align: right;
    }
    .mini-table td:last-child {
        border-bottom: none;
    }
    .mini-table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #555;
        text-align: left;
        padding-right: 1rem;
    }
    .mini-table td:last-child::before {
        font-weight: 600;
    }
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    .page-actions form {
        width: 100%;
    }
    .page-actions .btn-primary,
    .page-actions .btn-secondary {
        width: 100%;
        justify-content: center;
    }
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-group {
        width: 100%;
    }
    .filter-actions {
        width: 100%;
    }
    .btn-filter,
    .btn-clear {
        flex: 1;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .purok-stat-item {
        grid-template-columns: 1fr;
        gap: 0.3rem;
    }
    .purok-label,
    .purok-value,
    .purok-percentage {
        text-align: left;
    }
    .purok-bar {
        width: 100%;
    }
    .chart-mini-table {
        grid-template-columns: 1fr;
    }
    .chart-stat-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .chart-stat-item .stat-value {
        width: 100%;
    }
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .chart-total {
        align-self: flex-start;
    }
}

/* Small height screens */
@media (max-height: 700px) {
    .detail-card {
        max-height: 350px;
    }
}

/* Landscape mode on phones */
@media (max-width: 896px) and (orientation: landscape) {
    .details-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .detail-card {
        max-height: 300px;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Safely get total count for percentage calculations
    const totalResidents = {{ $statistics['filtered_total'] ?? $statistics['total'] }};

    /* ==================== */
    /* Gender Chart         */
    /* ==================== */
    const genderCtx = document.getElementById('genderChart');
    if (genderCtx) {
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female', 'Other'],
                datasets: [{
                    data: [
                        {{ $statistics['by_gender']['male'] ?? 0 }},
                        {{ $statistics['by_gender']['female'] ?? 0 }},
                        {{ $statistics['by_gender']['other'] ?? 0 }}
                    ],
                    backgroundColor: ['#3b82f6', '#ec4899', '#6b7280'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    /* ==================== */
    /* Civil Status Chart   */
    /* ==================== */
    const civilCtx = document.getElementById('civilStatusChart');
    if (civilCtx) {
        new Chart(civilCtx, {
            type: 'pie',
            data: {
                labels: [@foreach($statistics['by_civil_status'] as $status) '{{ $status->civil_status }}', @endforeach],
                datasets: [{
                    data: [@foreach($statistics['by_civil_status'] as $status) {{ $status->total }}, @endforeach],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    /* ==================== */
    /* Age Distribution Chart */
    /* ==================== */
    const ageCtx = document.getElementById('ageChart');
    if (ageCtx) {
        new Chart(ageCtx, {
            type: 'pie',
            data: {
                labels: [@foreach($statistics['age_distribution'] as $range => $count) '{{ $range }}', @endforeach],
                datasets: [{
                    data: [@foreach($statistics['age_distribution'] as $count) {{ $count }}, @endforeach],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    /* ==================== */
    /* Special Categories Chart */
    /* ==================== */
    const specialCtx = document.getElementById('specialChart');
    if (specialCtx) {
        new Chart(specialCtx, {
            type: 'pie',
            data: {
                labels: ['Senior Citizens', 'PWD', '4Ps Members'],
                datasets: [{
                    data: [
                        {{ $statistics['by_status']['seniors'] ?? 0 }},
                        {{ $statistics['by_status']['pwd'] ?? 0 }},
                        {{ $statistics['by_status']['4ps'] ?? 0 }}
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    /* ==================== */
    /* Purok Bar Chart      */
    /* ==================== */
    const purokCtx = document.getElementById('purokChart');
    if (purokCtx) {
        new Chart(purokCtx, {
            type: 'bar',
            data: {
                labels: [@foreach($statistics['by_purok'] as $purok) 'Purok {{ $purok->purok }}', @endforeach],
                datasets: [{
                    label: 'Number of Residents',
                    data: [@foreach($statistics['by_purok'] as $purok) {{ $purok->total }}, @endforeach],
                    backgroundColor: '#667eea',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' },
                        ticks: { stepSize: 20 }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
