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
            'resident' => \App\Models\User::where('role_id', 4)->where('is_active', true)->count(),
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
            ];

        } catch (\Exception $e) {
            $recentActivities = collect([]);
            $activityStats = ['today' => 0, 'yesterday' => 0, 'this_week' => 0, 'this_month' => 0];
            $activitiesByRole = ['admin' => 0, 'captain' => 0, 'secretary' => 0];
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
        // Try to get data if tables exist
        try {
            $totalResidents = DB::table('residents')->count();
            $activeBlotterCases = DB::table('blotters')->whereIn('status', ['Pending', 'Ongoing'])->count();
            $pendingCertificates = DB::table('certificates')->where('status', 'Pending')->count();
            $certificatesToday = DB::table('certificates')
                ->whereDate('created_at', today())
                ->count();

            // Get recent certificates
            $recentCertificates = DB::table('certificates as c')
                ->join('residents as r', 'c.resident_id', '=', 'r.id')
                ->where('c.status', '!=', 'Archived')
                ->select('c.*', 'r.first_name', 'r.last_name')
                ->orderBy('c.created_at', 'desc')
                ->limit(5)
                ->get()
                ->toArray();

            // Get recent activities
            $recentActivities = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            // Tables don't exist yet
            $totalResidents = 0;
            $activeBlotterCases = 0;
            $pendingCertificates = 0;
            $certificatesToday = 0;
            $recentCertificates = [];
            $recentActivities = collect([]);
        }

        return view('secretary.dashboard', [
            'title' => 'Secretary Dashboard',
            'totalResidents' => $totalResidents,
            'activeBlotterCases' => $activeBlotterCases,
            'pendingCertificates' => $pendingCertificates,
            'certificatesToday' => $certificatesToday,
            'recentCertificates' => $recentCertificates,
            'recentActivities' => $recentActivities,
            'user' => Auth::user()
        ]);
    }

    public function resident()
    {
        // Create resident dashboard view if it doesn't exist
        return view('resident.dashboard', [
            'title' => 'Resident Dashboard',
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
            case 'resident':
                return redirect()->route('resident.dashboard');
            default:
                return redirect()->route('login');
        }
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
