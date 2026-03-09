<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Blotter;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
     * Display residents report.
     */
    public function residents(Request $request)
    {
        // Base query
        $query = Resident::query();

        // Apply date filters if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get residents with pagination
        $residents = $query->latest()->paginate(50);

        // Calculate statistics
        $statistics = [
            'total' => Resident::count(),
            'by_gender' => [
                'male' => Resident::where('gender', 'Male')->count(),
                'female' => Resident::where('gender', 'Female')->count(),
            ],
            'by_purok' => Resident::select('purok', DB::raw('count(*) as total'))
                ->groupBy('purok')
                ->orderBy('purok')
                ->get(),
            'by_civil_status' => Resident::select('civil_status', DB::raw('count(*) as total'))
                ->groupBy('civil_status')
                ->get(),
            'by_status' => [
                'voters' => Resident::where('is_voter', true)->count(),
                'seniors' => Resident::where('is_senior', true)->count(),
                'pwd' => Resident::where('is_pwd', true)->count(),
                '4ps' => Resident::where('is_4ps', true)->count(),
            ],
            'age_distribution' => $this->getAgeDistribution(),
        ];

        return view('secretary.reports.residents', compact('residents', 'statistics'));
    }

    /**
     * Display certificates report.
     */
    public function certificates(Request $request)
    {
        $query = Certificate::with('resident');

        // Apply date filters if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $certificates = $query->latest()->paginate(50);

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

        // Apply date filters if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $blotters = $query->latest()->paginate(50);

        $statistics = [
            'total' => Blotter::count(),
            'by_status' => [
                'pending' => Blotter::where('status', 'Pending')->count(),
                'ongoing' => Blotter::whereIn('status', ['Investigating', 'Hearings'])->count(),
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
                    'active' => Blotter::whereIn('status', ['Pending', 'Investigating', 'Hearings'])->count(),
                    'settled' => Blotter::where('status', 'Settled')->count(),
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
     * Export report to PDF/Excel.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:residents,certificates,blotter,summary',
            'format' => 'required|in:pdf,excel',
        ]);

        // This would handle export to PDF/Excel
        // For now, return with info message
        return redirect()->back()
            ->with('info', 'Export feature coming soon!');
    }

    /**
     * Get age distribution statistics.
     */
    private function getAgeDistribution()
    {
        $residents = Resident::all();
        $distribution = [
            '0-17' => 0,
            '18-30' => 0,
            '31-45' => 0,
            '46-60' => 0,
            '60+' => 0,
        ];

        foreach ($residents as $resident) {
            if ($resident->birthdate) {
                $age = $resident->birthdate->age;

                if ($age <= 17) {
                    $distribution['0-17']++;
                } elseif ($age <= 30) {
                    $distribution['18-30']++;
                } elseif ($age <= 45) {
                    $distribution['31-45']++;
                } elseif ($age <= 60) {
                    $distribution['46-60']++;
                } else {
                    $distribution['60+']++;
                }
            }
        }

        return $distribution;
    }

    /**
     * Get certificate monthly trend.
     */
    private function getCertificateMonthlyTrend()
    {
        return Certificate::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('count(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();
    }

    /**
     * Get blotter monthly trend.
     */
    private function getBlotterMonthlyTrend()
    {
        return Blotter::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('count(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();
    }

    /**
     * Get resolution rate.
     */
    private function getResolutionRate()
    {
        $total = Blotter::count();
        if ($total === 0) {
            return 0;
        }

        $settled = Blotter::where('status', 'Settled')->count();
        return round(($settled / $total) * 100, 2);
    }

    /**
     * Get residents monthly trend for summary.
     */
    private function getResidentsMonthlyTrend($year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[date('F', mktime(0, 0, 0, $i, 1))] = Resident::whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->count();
        }
        return $months;
    }

    /**
     * Get certificates monthly trend for summary.
     */
    private function getCertificatesMonthlyTrend($year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[date('F', mktime(0, 0, 0, $i, 1))] = Certificate::whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->count();
        }
        return $months;
    }

    /**
     * Get blotters monthly trend for summary.
     */
    private function getBlottersMonthlyTrend($year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[date('F', mktime(0, 0, 0, $i, 1))] = Blotter::whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->count();
        }
        return $months;
    }
    
}
