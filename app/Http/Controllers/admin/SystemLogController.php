<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemLogController extends Controller
{
    /**
     * Display system activity logs
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'user_id' => $request->get('user_id', 'all'),
            'action' => $request->get('action', 'all'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ];

        // Start query
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        $query->filterByUser($filters['user_id'])
              ->filterByAction($filters['action'])
              ->dateRange($filters['from_date'], $filters['to_date']);

        // Get paginated results
        $logs = $query->paginate(20)->appends($filters);

        // Get statistics
        $stats = $this->getStatistics($filters);

        // Get filter options
        $users = User::orderBy('username')->get();
        $actions = ActivityLog::distinct()->pluck('action');

        return view('admin.logs.index', compact('logs', 'stats', 'filters', 'users', 'actions'));
    }

    /**
     * Get statistics for the dashboard
     */
    private function getStatistics($filters)
    {
        $query = ActivityLog::query();

        // Apply same filters to statistics
        $query->filterByUser($filters['user_id'])
              ->filterByAction($filters['action'])
              ->dateRange($filters['from_date'], $filters['to_date']);

        $totalLogs = $query->count();
        $logsToday = ActivityLog::whereDate('created_at', today())->count();
        $uniqueUsers = ActivityLog::distinct('user_id')->count('user_id');

        // Get most active user
        $mostActiveUser = ActivityLog::select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->first();

        $mostActiveUserName = 'None';
        if ($mostActiveUser && $mostActiveUser->user) {
            $mostActiveUserName = $mostActiveUser->user->username;
        }

        return [
            'total_logs' => $totalLogs,
            'logs_today' => $logsToday,
            'unique_users' => $uniqueUsers,
            'most_active_user' => $mostActiveUserName,
        ];
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $filters = [
            'user_id' => $request->get('user_id', 'all'),
            'action' => $request->get('action', 'all'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ];

        $logs = ActivityLog::with('user')
            ->filterByUser($filters['user_id'])
            ->filterByAction($filters['action'])
            ->dateRange($filters['from_date'], $filters['to_date'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'system-logs-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Timestamp',
                'User',
                'Role',
                'Action',
                'Description',
                'IP Address',
                'User Agent'
            ]);

            // Add data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->username ?? 'System',
                    $log->user->role ?? 'System',
                    $log->action,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
