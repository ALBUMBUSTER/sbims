<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Blotter;
use App\Models\Certificate;
use App\Exports\BlotterReportExport;
use App\Exports\ResidentsReportExport;
use App\Exports\CertificatesReportExport;
use App\Exports\SummaryReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display reports index.
     */
    public function index()
    {
        return view('secretary.reports.index');
    }

    /**
     * Display residents report with category filtering.
     */
    public function residents(Request $request)
    {
        // Get the category filter
        $category = $request->get('category', 'all');

        // Base query for pagination (with filters applied)
        $query = Resident::query();

        // Apply category filter
        $query = $this->applyCategoryFilter($query, $category);

        // Apply date filters if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get filtered total count (for display)
        $filteredTotal = $query->count();

        // Get residents with pagination
        $residents = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Calculate statistics - use a fresh query without pagination
        $statsQuery = Resident::query();

        // Apply same filters to stats query
        $statsQuery = $this->applyCategoryFilter($statsQuery, $category);
        if ($request->filled('date_from')) {
            $statsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate('created_at', '<=', $request->date_to);
        }

        // Get all filtered residents for statistics (no pagination)
        $allFilteredResidents = $statsQuery->get();

        // Calculate statistics from all filtered residents
        $statistics = $this->calculateStatisticsFromCollection($allFilteredResidents, $filteredTotal);

        return view('secretary.reports.residents', compact('residents', 'statistics'));
    }

    /**
     * Calculate statistics from a collection of residents
     */
    private function calculateStatisticsFromCollection($residents, $filteredTotal)
    {
        // If no residents, return empty statistics
        if ($residents->isEmpty()) {
            return [
                'filtered_total' => 0,
                'total' => Resident::count(),
                'by_gender' => [
                    'male' => 0,
                    'female' => 0,
                    'other' => 0,
                ],
                'by_purok' => collect(),
                'by_civil_status' => collect(),
                'by_status' => [
                    'voters' => 0,
                    'seniors' => 0,
                    'pwd' => 0,
                    '4ps' => 0,
                ],
                'age_distribution' => [
                    '0-17' => 0,
                    '18-30' => 0,
                    '31-45' => 0,
                    '46-60' => 0,
                    '60+' => 0,
                ],
            ];
        }

        // Gender distribution
        $maleCount = $residents->where('gender', 'Male')->count();
        $femaleCount = $residents->where('gender', 'Female')->count();
        $otherCount = $residents->where('gender', 'Other')->count();

        // Civil status distribution using collection
        $civilStatusData = $residents->groupBy('civil_status')
            ->map(function($group, $status) {
                return (object) ['civil_status' => $status ?: 'Not Specified', 'total' => $group->count()];
            })
            ->values();

        // Age distribution
        $ageDistribution = [
            '0-17' => 0,
            '18-30' => 0,
            '31-45' => 0,
            '46-60' => 0,
            '60+' => 0,
        ];

        foreach ($residents as $resident) {
            if ($resident->birthdate) {
                $age = Carbon::parse($resident->birthdate)->age;

                if ($age <= 17) {
                    $ageDistribution['0-17']++;
                } elseif ($age <= 30) {
                    $ageDistribution['18-30']++;
                } elseif ($age <= 45) {
                    $ageDistribution['31-45']++;
                } elseif ($age <= 60) {
                    $ageDistribution['46-60']++;
                } else {
                    $ageDistribution['60+']++;
                }
            }
        }

        // Special status counts
        $seniorsCount = $residents->where('is_senior', true)->count();
        $pwdCount = $residents->where('is_pwd', true)->count();
        $fourPsCount = $residents->where('is_4ps', true)->count();
        $votersCount = $residents->where('is_voter', true)->count();

        // Purok distribution
        $purokData = $residents->groupBy('purok')
            ->map(function($group, $purok) {
                return (object) ['purok' => $purok, 'total' => $group->count()];
            })
            ->sortBy('purok')
            ->values();

        return [
            'filtered_total' => $filteredTotal,
            'total' => Resident::count(),
            'by_gender' => [
                'male' => $maleCount,
                'female' => $femaleCount,
                'other' => $otherCount,
            ],
            'by_purok' => $purokData,
            'by_civil_status' => $civilStatusData,
            'by_status' => [
                'voters' => $votersCount,
                'seniors' => $seniorsCount,
                'pwd' => $pwdCount,
                '4ps' => $fourPsCount,
            ],
            'age_distribution' => $ageDistribution,
        ];
    }

    /**
     * Apply category filter to query
     */
    private function applyCategoryFilter($query, $category)
    {
        switch ($category) {
            case 'senior':
                return $query->where('is_senior', true);
            case 'pwd':
                return $query->where('is_pwd', true);
            case 'voter':
                return $query->where('is_voter', true);
            case '4ps':
                return $query->where('is_4ps', true);
            case 'male':
                return $query->where('gender', 'Male');
            case 'female':
                return $query->where('gender', 'Female');
            case 'children':
                return $query->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) <= 17');
            case 'adult':
                return $query->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 59');
            case 'all':
            default:
                return $query;
        }
    }

    /**
     * Display certificates report.
     */
    public function certificates(Request $request)
    {
        $query = Certificate::with('resident');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $certificates = $query->latest()->paginate(50)->withQueryString();

        $statistics = [
            'total' => Certificate::count(),
            'by_status' => [
                'pending' => Certificate::where('status', 'Pending')->count(),
                'approved' => Certificate::where('status', 'Approved')->count(),
                'released' => Certificate::where('status', 'Released')->count(),
                'rejected' => Certificate::where('status', 'Rejected')->count(),
            ],
            'by_type' => Certificate::select('certificate_type', DB::raw('count(*) as total'))
                ->groupBy('certificate_type')
                ->get(),
            'monthly_trend' => $this->getCertificateMonthlyTrend(),
        ];

        return view('secretary.reports.certificates', compact('certificates', 'statistics'));
    }

    /**
     * Display blotter report.
     */
    public function blotter(Request $request)
    {
        $query = Blotter::with('complainant');

        if ($request->filled('date_from')) {
            $query->whereDate('incident_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('incident_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $blotters = $query->latest()->paginate(50)->withQueryString();

        $statistics = [
            'total' => Blotter::count(),
            'by_status' => [
                'pending' => Blotter::where('status', 'Pending')->count(),
                'ongoing' => Blotter::where('status', 'Ongoing')->count(),
                'settled' => Blotter::where('status', 'Settled')->count(),
                'referred' => Blotter::where('status', 'Referred')->count(),
            ],
            'by_type' => Blotter::select('incident_type', DB::raw('count(*) as total'))
                ->groupBy('incident_type')
                ->get(),
            'resolution_rate' => $this->getResolutionRate(),
            'monthly_trend' => $this->getBlotterMonthlyTrend(),
        ];

        return view('secretary.reports.blotter', compact('blotters', 'statistics'));
    }

    /**
     * Display summary report.
     */
    public function summary(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $statistics = [
            'year' => $year,
            'residents' => [
                'total' => Resident::count(),
                'new_this_year' => Resident::whereYear('created_at', $year)->count(),
                'by_gender' => [
                    'male' => Resident::where('gender', 'Male')->count(),
                    'female' => Resident::where('gender', 'Female')->count(),
                    'other' => Resident::where('gender', 'Other')->count(),
                ],
                'monthly' => $this->getResidentsMonthlyTrend($year),
            ],
            'certificates' => [
                'total' => Certificate::count(),
                'issued_this_year' => Certificate::whereYear('created_at', $year)->count(),
                'by_status' => [
                    'pending' => Certificate::where('status', 'Pending')->count(),
                    'approved' => Certificate::where('status', 'Approved')->count(),
                    'released' => Certificate::where('status', 'Released')->count(),
                    'rejected' => Certificate::where('status', 'Rejected')->count(),
                ],
                'monthly' => $this->getCertificatesMonthlyTrend($year),
            ],
            'blotters' => [
                'total' => Blotter::count(),
                'filed_this_year' => Blotter::whereYear('created_at', $year)->count(),
                'by_status' => [
                    'pending' => Blotter::where('status', 'Pending')->count(),
                    'ongoing' => Blotter::where('status', 'Ongoing')->count(),
                    'settled' => Blotter::where('status', 'Settled')->count(),
                    'referred' => Blotter::where('status', 'Referred')->count(),
                    'active' => Blotter::whereIn('status', ['Pending', 'Ongoing', 'Referred'])->count(),
                ],
                'monthly' => $this->getBlottersMonthlyTrend($year),
            ],
        ];

        return view('secretary.reports.summary', compact('statistics'));
    }

    /**
     * Generate reports based on criteria.
     */
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'report_type' => 'required|in:residents,blotters,certificates',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'status' => 'nullable|string'
            ]);

            $data = [];
            $reportType = $validated['report_type'];

            switch ($reportType) {
                case 'residents':
                    $data = Resident::whereBetween('created_at', [$validated['date_from'], $validated['date_to']])
                        ->orderBy('created_at', 'desc')
                        ->get();
                    break;
                case 'blotters':
                    $query = Blotter::with('complainant')
                        ->whereBetween('created_at', [$validated['date_from'], $validated['date_to']]);
                    if (!empty($validated['status'])) {
                        $query->where('status', $validated['status']);
                    }
                    $data = $query->orderBy('created_at', 'desc')->get();
                    break;
                case 'certificates':
                    $query = Certificate::with('resident')
                        ->whereBetween('created_at', [$validated['date_from'], $validated['date_to']]);
                    if (!empty($validated['status'])) {
                        $query->where('status', $validated['status']);
                    }
                    $data = $query->orderBy('created_at', 'desc')->get();
                    break;
            }

            return view('secretary.reports.results', compact('data', 'reportType', 'validated'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error generating report: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Export report to Excel.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:residents,certificates,blotter,summary',
            'format' => 'required|in:excel,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|string',
            'year' => 'nullable|integer',
            'category' => 'nullable|string'
        ]);

        $type = $validated['type'];
        $filters = $request->only(['date_from', 'date_to', 'status', 'year', 'category']);

        try {
            switch ($type) {
                case 'blotter':
                    return $this->exportBlotter($filters);
                case 'residents':
                    return $this->exportResidents($filters);
                case 'certificates':
                    return $this->exportCertificates($filters);
                case 'summary':
                    return $this->exportSummary($filters);
                default:
                    return redirect()->back()->with('error', 'Invalid export type');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error exporting report: ' . $e->getMessage());
        }
    }

    /**
     * Export blotter report to Excel
     */
    protected function exportBlotter($filters)
    {
        $statistics = $this->getBlotterStatisticsForExport($filters);
        $filename = 'blotter_report_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new BlotterReportExport($filters, $statistics), $filename);
    }

    /**
     * Export residents report to Excel with category filter
     */
    protected function exportResidents($filters)
    {
        $query = Resident::query();

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query = $this->applyCategoryFilter($query, $filters['category']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $residents = $query->orderBy('created_at', 'desc')->get();

        $statistics = [
            'filtered_total' => $residents->count(),
            'by_gender' => [
                'male' => $residents->where('gender', 'Male')->count(),
                'female' => $residents->where('gender', 'Female')->count(),
                'other' => $residents->where('gender', 'Other')->count(),
            ],
            'by_status' => [
                'voters' => $residents->where('is_voter', true)->count(),
                'seniors' => $residents->where('is_senior', true)->count(),
                'pwd' => $residents->where('is_pwd', true)->count(),
                '4ps' => $residents->where('is_4ps', true)->count(),
            ],
        ];

        $filterName = $this->getFilterDisplayName($filters['category'] ?? 'all');
        $filename = 'residents_report_' . $filterName . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ResidentsReportExport($residents, $statistics, $filters), $filename);
    }

    /**
     * Get display name for filter
     */
    private function getFilterDisplayName($filter)
    {
        $names = [
            'all' => 'all',
            'senior' => 'senior_citizens',
            'pwd' => 'pwd',
            'voter' => 'voters',
            '4ps' => '4ps_members',
            'male' => 'male',
            'female' => 'female',
            'children' => 'children_0_17',
            'adult' => 'adults_18_59',
        ];
        return $names[$filter] ?? $filter;
    }

    /**
     * Export certificates report to Excel
     */
    protected function exportCertificates($filters)
    {
        $statistics = $this->getCertificatesStatisticsForExport($filters);
        $filename = 'certificates_report_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new CertificatesReportExport($filters, $statistics), $filename);
    }

    /**
     * Export summary report to Excel
     */
    protected function exportSummary($filters)
    {
        $year = $filters['year'] ?? date('Y');
        $statistics = [
            'year' => $year,
            'residents' => [
                'total' => Resident::count(),
                'new_this_year' => Resident::whereYear('created_at', $year)->count(),
                'by_gender' => [
                    'male' => Resident::where('gender', 'Male')->count(),
                    'female' => Resident::where('gender', 'Female')->count(),
                    'other' => Resident::where('gender', 'Other')->count(),
                ],
                'monthly' => $this->getResidentsMonthlyTrend($year),
            ],
            'certificates' => [
                'total' => Certificate::count(),
                'issued_this_year' => Certificate::whereYear('created_at', $year)->count(),
                'by_status' => [
                    'pending' => Certificate::where('status', 'Pending')->count(),
                    'released' => Certificate::where('status', 'Released')->count(),
                ],
                'monthly' => $this->getCertificatesMonthlyTrend($year),
            ],
            'blotters' => [
                'total' => Blotter::count(),
                'filed_this_year' => Blotter::whereYear('created_at', $year)->count(),
                'by_status' => [
                    'active' => Blotter::whereIn('status', ['Pending', 'Ongoing', 'Referred'])->count(),
                    'settled' => Blotter::where('status', 'Settled')->count(),
                ],
                'monthly' => $this->getBlottersMonthlyTrend($year),
            ],
        ];
        $filename = 'summary_report_' . $year . '_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new SummaryReportExport($statistics), $filename);
    }

    /**
     * Get blotter statistics for export
     */
    protected function getBlotterStatisticsForExport($filters)
    {
        $query = Blotter::query();
        if (!empty($filters['date_from'])) {
            $query->whereDate('incident_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('incident_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        $total = $query->count();
        $byStatus = [
            'pending' => (clone $query)->where('status', 'Pending')->count(),
            'ongoing' => (clone $query)->where('status', 'Ongoing')->count(),
            'settled' => (clone $query)->where('status', 'Settled')->count(),
            'referred' => (clone $query)->where('status', 'Referred')->count(),
        ];
        $resolutionRate = $total > 0 ? round(($byStatus['settled'] / $total) * 100, 1) : 0;
        return ['total' => $total, 'by_status' => $byStatus, 'resolution_rate' => $resolutionRate];
    }

    /**
     * Get certificates statistics for export
     */
    protected function getCertificatesStatisticsForExport($filters)
    {
        $query = Certificate::query();
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        $total = $query->count();
        return [
            'total' => $total,
            'by_status' => [
                'pending' => (clone $query)->where('status', 'Pending')->count(),
                'approved' => (clone $query)->where('status', 'Approved')->count(),
                'released' => (clone $query)->where('status', 'Released')->count(),
                'rejected' => (clone $query)->where('status', 'Rejected')->count(),
            ],
        ];
    }

    private function getCertificateMonthlyTrend()
    {
        return Certificate::select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('count(*) as total'))
            ->groupBy('year', 'month')->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(6)->get();
    }

    private function getBlotterMonthlyTrend()
    {
        return Blotter::select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('count(*) as total'))
            ->groupBy('year', 'month')->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(6)->get();
    }

    private function getResolutionRate()
    {
        $total = Blotter::count();
        if ($total === 0) return 0;
        return round((Blotter::where('status', 'Settled')->count() / $total) * 100, 2);
    }

    private function getResidentsMonthlyTrend($year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[date('F', mktime(0, 0, 0, $i, 1))] = Resident::whereYear('created_at', $year)->whereMonth('created_at', $i)->count();
        }
        return $months;
    }

    private function getCertificatesMonthlyTrend($year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[date('F', mktime(0, 0, 0, $i, 1))] = Certificate::whereYear('created_at', $year)->whereMonth('created_at', $i)->count();
        }
        return $months;
    }

    private function getBlottersMonthlyTrend($year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[date('F', mktime(0, 0, 0, $i, 1))] = Blotter::whereYear('created_at', $year)->whereMonth('created_at', $i)->count();
        }
        return $months;
    }
}
