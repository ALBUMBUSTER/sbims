<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function admin()
    {
        // Get user statistics
        $userStats = [
            'admin' => \App\Models\User::where('role_id', 1)->where('is_active', true)->count(),
            'captain' => \App\Models\User::where('role_id', 2)->where('is_active', true)->count(),
            'secretary' => \App\Models\User::where('role_id', 3)->where('is_active', true)->count(),
            'clerk' => \App\Models\User::where('role_id', 4)->where('is_active', true)->count(), // FIXED: Changed from 5 to 4
        ];

        // System-wide statistics
        try {
            $systemStats = [
                'total_residents' => DB::table('residents')->count(),
                'total_certificates' => DB::table('certificates')->count(),
                'pending_certificates' => DB::table('certificates')->where('status', 'Pending')->count(),
                'total_blotters' => DB::table('blotters')->count(),
                'active_blotters' => DB::table('blotters')->whereIn('status', ['Pending', 'Investigating', 'Hearings'])->count(),
                'total_users' => \App\Models\User::count(),
                'active_users' => \App\Models\User::where('is_active', true)->count(),
            ];
        } catch (\Exception $e) {
            $systemStats = [
                'total_residents' => 0,
                'total_certificates' => 0,
                'pending_certificates' => 0,
                'total_blotters' => 0,
                'active_blotters' => 0,
                'total_users' => \App\Models\User::count(),
                'active_users' => \App\Models\User::where('is_active', true)->count(),
            ];
        }

        // Get recent activities from ALL users
        try {
            $recentActivities = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get()
                ->map(function ($activity) {
                    // Add action type for styling
                    $activity->action_type = $this->getActionType($activity->action);
                    return $activity;
                });

            // Activity statistics
            $activityStats = [
                'today' => ActivityLog::whereDate('created_at', today())->count(),
                'yesterday' => ActivityLog::whereDate('created_at', today()->subDay())->count(),
                'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => ActivityLog::whereMonth('created_at', now()->month)->count(),
            ];

            // Activities by role
            $activitiesByRole = [
                'admin' => ActivityLog::whereHas('user', function($q) { $q->where('role_id', 1); })->count(),
                'captain' => ActivityLog::whereHas('user', function($q) { $q->where('role_id', 2); })->count(),
                'secretary' => ActivityLog::whereHas('user', function($q) { $q->where('role_id', 3); })->count(),
                'clerk' => ActivityLog::whereHas('user', function($q) { $q->where('role_id', 4); })->count(), // FIXED: Added clerk
            ];

        } catch (\Exception $e) {
            $recentActivities = collect([]);
            $activityStats = ['today' => 0, 'yesterday' => 0, 'this_week' => 0, 'this_month' => 0];
            $activitiesByRole = ['admin' => 0, 'captain' => 0, 'secretary' => 0, 'clerk' => 0]; // FIXED: Added clerk
        }

        return view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'userStats' => $userStats,
            'systemStats' => $systemStats,
            'recentActivities' => $recentActivities,
            'activityStats' => $activityStats,
            'activitiesByRole' => $activitiesByRole,
            'user' => Auth::user()
        ]);
    }

    // Helper method to get action type
    private function getActionType($action)
    {
        $action = strtolower($action);

        if (str_contains($action, 'create') || str_contains($action, 'added')) return 'create';
        if (str_contains($action, 'update') || str_contains($action, 'updated')) return 'update';
        if (str_contains($action, 'delete') || str_contains($action, 'deleted')) return 'delete';
        if (str_contains($action, 'login')) return 'login';
        if (str_contains($action, 'logout')) return 'logout';
        if (str_contains($action, 'process')) return 'process';
        if (str_contains($action, 'generate')) return 'generate';
        if (str_contains($action, 'export')) return 'export';
        if (str_contains($action, 'certificate')) return 'certificate';
        if (str_contains($action, 'blotter')) return 'blotter';
        if (str_contains($action, 'resident')) return 'resident';

        return 'other';
    }

    public function captain()
    {
        // Delegate to CaptainController's dashboard method
        return app(\App\Http\Controllers\Captain\CaptainController::class)->dashboard();
    }

    public function secretary()
{
    try {
        $totalResidents = DB::table('residents')->count();
        $totalCertificates = DB::table('certificates')->count();
        $totalBlotters = DB::table('blotters')->count();
        $activeBlotterCases = DB::table('blotters')->whereIn('status', ['Pending', 'Ongoing'])->count();
        $pendingCertificates = DB::table('certificates')->where('status', 'Pending')->count();
        $certificatesToday = DB::table('certificates')
            ->whereDate('created_at', today())
            ->count();

        $recentCertificates = DB::table('certificates as c')
            ->join('residents as r', 'c.resident_id', '=', 'r.id')
            ->where('c.status', '!=', 'Archived')
            ->select('c.*', 'r.first_name', 'r.last_name')
            ->orderBy('c.created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly certificate stats
        $certificateStats = DB::table('certificates')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('MONTHNAME(created_at) as month_name')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get();

        if ($certificateStats->isEmpty()) {
            $certificateStats = collect([
                (object) ['month' => 1, 'month_name' => 'January', 'total' => 0],
                (object) ['month' => 2, 'month_name' => 'February', 'total' => 0],
                (object) ['month' => 3, 'month_name' => 'March', 'total' => 0],
                (object) ['month' => 4, 'month_name' => 'April', 'total' => 0],
                (object) ['month' => 5, 'month_name' => 'May', 'total' => 0],
                (object) ['month' => 6, 'month_name' => 'June', 'total' => 0],
                (object) ['month' => 7, 'month_name' => 'July', 'total' => 0],
                (object) ['month' => 8, 'month_name' => 'August', 'total' => 0],
                (object) ['month' => 9, 'month_name' => 'September', 'total' => 0],
                (object) ['month' => 10, 'month_name' => 'October', 'total' => 0],
                (object) ['month' => 11, 'month_name' => 'November', 'total' => 0],
                (object) ['month' => 12, 'month_name' => 'December', 'total' => 0],
            ]);
        }

        // Resident monthly statistics (new residents per month)
        $residentStats = DB::table('residents')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('MONTHNAME(created_at) as month_name')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get();

        if ($residentStats->isEmpty()) {
            $residentStats = collect([
                (object) ['month' => 1, 'month_name' => 'January', 'total' => 0],
                (object) ['month' => 2, 'month_name' => 'February', 'total' => 0],
                (object) ['month' => 3, 'month_name' => 'March', 'total' => 0],
                (object) ['month' => 4, 'month_name' => 'April', 'total' => 0],
                (object) ['month' => 5, 'month_name' => 'May', 'total' => 0],
                (object) ['month' => 6, 'month_name' => 'June', 'total' => 0],
                (object) ['month' => 7, 'month_name' => 'July', 'total' => 0],
                (object) ['month' => 8, 'month_name' => 'August', 'total' => 0],
                (object) ['month' => 9, 'month_name' => 'September', 'total' => 0],
                (object) ['month' => 10, 'month_name' => 'October', 'total' => 0],
                (object) ['month' => 11, 'month_name' => 'November', 'total' => 0],
                (object) ['month' => 12, 'month_name' => 'December', 'total' => 0],
            ]);
        }

        // Blotter monthly statistics
        $blotterStats = DB::table('blotters')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('MONTHNAME(created_at) as month_name')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get();

        if ($blotterStats->isEmpty()) {
            $blotterStats = collect([
                (object) ['month' => 1, 'month_name' => 'January', 'total' => 0],
                (object) ['month' => 2, 'month_name' => 'February', 'total' => 0],
                (object) ['month' => 3, 'month_name' => 'March', 'total' => 0],
                (object) ['month' => 4, 'month_name' => 'April', 'total' => 0],
                (object) ['month' => 5, 'month_name' => 'May', 'total' => 0],
                (object) ['month' => 6, 'month_name' => 'June', 'total' => 0],
                (object) ['month' => 7, 'month_name' => 'July', 'total' => 0],
                (object) ['month' => 8, 'month_name' => 'August', 'total' => 0],
                (object) ['month' => 9, 'month_name' => 'September', 'total' => 0],
                (object) ['month' => 10, 'month_name' => 'October', 'total' => 0],
                (object) ['month' => 11, 'month_name' => 'November', 'total' => 0],
                (object) ['month' => 12, 'month_name' => 'December', 'total' => 0],
            ]);
        }

    } catch (\Exception $e) {
        $totalResidents = 0;
        $totalCertificates = 0;
        $totalBlotters = 0;
        $activeBlotterCases = 0;
        $pendingCertificates = 0;
        $certificatesToday = 0;
        $recentCertificates = [];
        $recentActivities = collect([]);
        $certificateStats = collect([]);
        $residentStats = collect([]);
        $blotterStats = collect([]);
    }

    return view('secretary.dashboard', [
        'title' => 'Secretary Dashboard',
        'totalResidents' => $totalResidents,
        'totalCertificates' => $totalCertificates,
        'totalBlotters' => $totalBlotters,
        'activeBlotterCases' => $activeBlotterCases,
        'pendingCertificates' => $pendingCertificates,
        'certificatesToday' => $certificatesToday,
        'recentCertificates' => $recentCertificates,
        'recentActivities' => $recentActivities,
        'certificateStats' => $certificateStats,
        'residentStats' => $residentStats,
        'blotterStats' => $blotterStats,
        'user' => Auth::user()
    ]);
}

    public function redirectToRoleDashboard()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'captain':
                return redirect()->route('captain.dashboard');
            case 'secretary':
                return redirect()->route('secretary.dashboard');
            case 'clerk':
                return redirect()->route('clerk.dashboard');
            default:
                return redirect()->route('login');
        }
    }

    public function clerk()
    {
        return view('clerk.dashboard');
    }

    public function activities(Request $request)
    {
        $query = ActivityLog::with('user');

        // Apply filters based on your ActivityLog structure
        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('secretary.activities', compact('activities'));
    }
}
