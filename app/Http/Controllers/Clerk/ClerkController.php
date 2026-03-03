<?php

namespace App\Http\Controllers\Clerk;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Certificate;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClerkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display clerk dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_residents' => Resident::count(),
            'total_certificates' => Certificate::count(),
            'pending_certificates' => Certificate::where('status', 'Pending')->count(),
            'released_certificates' => Certificate::where('status', 'Released')->count(),
            'recent_certificates' => Certificate::with('resident')
                ->latest()
                ->take(5)
                ->get(),
            'recent_residents' => Resident::latest()
                ->take(5)
                ->get(),
        ];

        return view('clerk.dashboard', compact('stats'));
        
    }
}
