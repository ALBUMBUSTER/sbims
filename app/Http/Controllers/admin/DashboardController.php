<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Resident; // Add this line
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        // Get user statistics
        $userStats = [
            'admin' => User::where('role', 'admin')->where('is_active', true)->count(),
            'captain' => User::where('role', 'captain')->where('is_active', true)->count(),
            'secretary' => User::where('role', 'secretary')->where('is_active', true)->count(),
            // This now counts actual residents from residents table
            'resident' => Resident::count(),
        ];

        // Get system statistics including resident count
        $systemStats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_certificates' => \App\Models\Certificate::count() ?? 0,
            'pending_certificates' => \App\Models\Certificate::where('status', 'pending')->count() ?? 0,
            'total_blotters' => \App\Models\Blotter::count() ?? 0,
            'active_blotters' => \App\Models\Blotter::where('status', 'active')->count() ?? 0,
            'total_residents' => Resident::count(), // Add this for overview cards
        ];

        // Get activity statistics
        $activityStats = [
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Get activities by role
        $activitiesByRole = [
            'admin' => ActivityLog::whereHas('user', function($query) {
                $query->where('role', 'admin');
            })->count(),
            'captain' => ActivityLog::whereHas('user', function($query) {
                $query->where('role', 'captain');
            })->count(),
            'secretary' => ActivityLog::whereHas('user', function($query) {
                $query->where('role', 'secretary');
            })->count(),
        ];

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Pass all variables to the view
        return view('admin', compact(
            'userStats',
            'systemStats',
            'activityStats',
            'activitiesByRole',
            'recentActivities'
        ));
    }
}
