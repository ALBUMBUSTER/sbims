<?php
// app/Http/Controllers/Clerk/ClerkReportController.php

namespace App\Http\Controllers\Clerk;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClerkReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if user is clerk
     */
    private function isClerk()
    {
        if (Auth::user()->role_id != 4) {
            abort(403, 'Unauthorized access. This area is for Clerk only.');
        }
    }

    /**
     * Display reports index
     */
    public function index()
    {
        $this->isClerk(); // Role check
        return view('clerk.reports.index');
    }

    /**
     * Generate residents report
     */
    public function residents(Request $request)
    {
        $this->isClerk(); // Role check

        $query = Resident::query();

        // Apply filters
        if ($request->filled('purok')) {
            $query->where('purok', $request->purok);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('civil_status')) {
            $query->where('civil_status', $request->civil_status);
        }

        if ($request->filled('age_range')) {
            switch ($request->age_range) {
                case 'children':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= 12');
                    break;
                case 'teen':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 13 AND 19');
                    break;
                case 'adult':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 20 AND 59');
                    break;
                case 'senior':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= 60');
                    break;
            }
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $residents = $query->get();

        // Summary statistics
        $summary = [
            'total' => $residents->count(),
            'male' => $residents->where('gender', 'Male')->count(),
            'female' => $residents->where('gender', 'Female')->count(),
            'by_purok' => $residents->groupBy('purok')->map->count(),
            'by_civil_status' => $residents->groupBy('civil_status')->map->count(),
        ];

        // Get unique puroks for filter
        $puroks = Resident::distinct('purok')->whereNotNull('purok')->pluck('purok');

        return view('clerk.reports.residents', compact('residents', 'summary', 'puroks'));
    }

    /**
     * Generate certificates report
     */
    public function certificates(Request $request)
    {
        $this->isClerk(); // Role check

        $query = Certificate::with('resident');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('certificate_type')) {
            $query->where('certificate_type', $request->certificate_type);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $certificates = $query->get();

        // Summary statistics
        $summary = [
            'total' => $certificates->count(),
            'pending' => $certificates->where('status', 'Pending')->count(),
            'processing' => $certificates->where('status', 'Processing')->count(),
            'approved' => $certificates->where('status', 'Approved')->count(),
            'released' => $certificates->where('status', 'Released')->count(),
            'rejected' => $certificates->where('status', 'Rejected')->count(),
            'by_type' => $certificates->groupBy('certificate_type')->map->count(),
        ];

        // Get unique certificate types for filter
        $certificateTypes = Certificate::distinct('certificate_type')->pluck('certificate_type');
        $statuses = ['Pending', 'Processing', 'Approved', 'Released', 'Rejected'];

        return view('clerk.reports.certificates', compact('certificates', 'summary', 'certificateTypes', 'statuses'));
    }

    /**
     * Generate summary report
     */
    public function summary()
    {
        $this->isClerk(); // Role check

        // Resident statistics
        $residentStats = [
            'total' => Resident::count(),
            'male' => Resident::where('gender', 'Male')->count(),
            'female' => Resident::where('gender', 'Female')->count(),
            'by_purok' => Resident::select('purok', DB::raw('count(*) as total'))
                ->groupBy('purok')
                ->get()
                ->pluck('total', 'purok')
                ->toArray(),
            'by_civil_status' => Resident::select('civil_status', DB::raw('count(*) as total'))
                ->groupBy('civil_status')
                ->get()
                ->pluck('total', 'civil_status')
                ->toArray(),
            'registered_today' => Resident::whereDate('created_at', today())->count(),
            'registered_this_month' => Resident::whereMonth('created_at', now()->month)->count(),
        ];

        // Certificate statistics
        $certificateStats = [
            'total' => Certificate::count(),
            'pending' => Certificate::where('status', 'Pending')->count(),
            'processing' => Certificate::where('status', 'Processing')->count(),
            'approved' => Certificate::where('status', 'Approved')->count(),
            'released' => Certificate::where('status', 'Released')->count(),
            'rejected' => Certificate::where('status', 'Rejected')->count(),
            'today' => Certificate::whereDate('created_at', today())->count(),
            'this_month' => Certificate::whereMonth('created_at', now()->month)->count(),
            'by_type' => Certificate::select('certificate_type', DB::raw('count(*) as total'))
                ->groupBy('certificate_type')
                ->get()
                ->pluck('total', 'certificate_type')
                ->toArray(),
        ];

        // Monthly trends for chart
        $monthlyTrends = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyTrends[$i] = [
                'month' => date('F', mktime(0, 0, 0, $i, 1)),
                'certificates' => Certificate::whereMonth('created_at', $i)
                    ->whereYear('created_at', date('Y'))
                    ->count(),
                'residents' => Resident::whereMonth('created_at', $i)
                    ->whereYear('created_at', date('Y'))
                    ->count(),
            ];
        }

        return view('clerk.reports.summary', compact('residentStats', 'certificateStats', 'monthlyTrends'));
    }
}
