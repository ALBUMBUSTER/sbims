<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Resident;
use App\Models\Certificate;
use App\Models\Blotter;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        // Get user statistics by role_id
        $userStats = [
            'admin' => User::where('role_id', 1)->where('is_active', true)->count(),
            'captain' => User::where('role_id', 2)->where('is_active', true)->count(),
            'secretary' => User::where('role_id', 3)->where('is_active', true)->count(),
            'clerk' => User::where('role_id', 4)->where('is_active', true)->count(),
            'resident' => Resident::count(),
        ];

        // Get system statistics
        $systemStats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_certificates' => Certificate::count() ?? 0,
            'pending_certificates' => Certificate::where('status', 'Pending')->count() ?? 0,
            'total_blotters' => Blotter::count() ?? 0,
            'active_blotters' => Blotter::whereIn('status', ['Pending', 'Investigating', 'Hearings'])->count() ?? 0,
            'total_residents' => Resident::count(),
        ];

        // Get activity statistics
        $activityStats = [
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Get activities by role (last 7 days)
        $activitiesByRole = [
            'admin' => ActivityLog::whereHas('user', function($query) {
                $query->where('role_id', 1);
            })->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'captain' => ActivityLog::whereHas('user', function($query) {
                $query->where('role_id', 2);
            })->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'secretary' => ActivityLog::whereHas('user', function($query) {
                $query->where('role_id', 3);
            })->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'clerk' => ActivityLog::whereHas('user', function($query) {
                $query->where('role_id', 4);
            })->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'userStats',
            'systemStats',
            'activityStats',
            'activitiesByRole',
            'recentActivities'
        ));
    }
}
