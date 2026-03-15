<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Blotter;
use App\Models\Resident;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CaptainController extends Controller
{
    /**
     * Display captain dashboard
     */
    public function dashboard()
    {
        // Get current year for monthly stats
        $currentYear = Carbon::now()->year;

        // Statistics Cards
        $totalResidents = Resident::count();
        $totalBlotters = Blotter::count();
        $pendingBlotters = Blotter::where('status', 'Pending')->count();
        $settledBlotters = Blotter::where('status', 'Settled')->count();
        $totalCertificates = Certificate::count();
        $pendingCertificates = Certificate::where('status', 'Pending')->count();
        $activeBlotters = Blotter::whereIn('status', ['Investigating', 'Hearings'])->count();
        $releasedCertificates = Certificate::where('status', 'Released')->count();

        // Monthly Statistics for Chart
        $monthlyStats = [
            'blotters' => [],
            'certificates' => []
        ];

        // Get monthly blotter counts
        for ($i = 1; $i <= 12; $i++) {
            $monthlyStats['blotters'][$i] = Blotter::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $i)
                ->count();

            $monthlyStats['certificates'][$i] = Certificate::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $i)
                ->count();
        }

        // Convert to indexed array for JSON (remove keys)
        $monthlyStats['blotters'] = array_values($monthlyStats['blotters']);
        $monthlyStats['certificates'] = array_values($monthlyStats['certificates']);

        // Pending Approvals (certificates only for now)
        $pendingApprovals = Certificate::with('resident')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($cert) {
                return (object)[
                    'id' => $cert->id,
                    'first_name' => $cert->resident->first_name ?? 'N/A',
                    'last_name' => $cert->resident->last_name ?? '',
                    'certificate_type' => $cert->certificate_type,
                    'type' => 'certificate'
                ];
            });

        // Recent Blotter Cases
        $recentBlotters = Blotter::with('complainant')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('captain.dashboard', compact(
            'totalResidents',
            'totalBlotters',
            'pendingBlotters',
            'settledBlotters',
            'totalCertificates',
            'pendingCertificates',
            'activeBlotters',
            'releasedCertificates',
            'monthlyStats',
            'pendingApprovals',
            'recentBlotters'
        ));
    }

    /**
     * Display approvals dashboard
     */
    public function approvals()
    {
        // Get pending certificate approvals
        $pendingCertificates = Certificate::with('resident')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get pending blotter cases that need captain's attention
        $pendingBlotters = Blotter::with('complainant')
            ->whereIn('status', ['Pending', 'Investigating'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('captain.approvals.index', compact('pendingCertificates', 'pendingBlotters'));
    }

    /**
 * Approve a certificate
 */
public function approveCertificate(Request $request, Certificate $certificate)
{
    $request->validate([
        'remarks' => 'nullable|string|max:500'
    ]);

    // Update certificate with existing columns only
    $certificate->update([
        'status' => 'Approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
        'issued_by' => Auth::id(),
        'issued_date' => now(),
    ]);

    // ========== FIXED NOTIFICATIONS ==========
    // Notify the secretary who created it (if exists and not the current user)
    if ($certificate->created_by && $certificate->created_by != Auth::id()) {
        NotificationHelper::toUser(
            $certificate->created_by,
            'Certificate Approved',
            'Certificate #' . $certificate->certificate_number . ' has been approved',
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // Notify all secretaries (EXCLUDING current user)
    $secretaries = User::where('role_id', 3)
        ->where('id', '!=', Auth::id())
        ->get();
    foreach ($secretaries as $secretary) {
        NotificationHelper::toUser(
            $secretary->id,
            'Certificate Approved',
            'Certificate #' . $certificate->certificate_number . ' has been approved by Captain',
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // Notify all clerks (EXCLUDING current user)
    $clerks = User::where('role_id', 4)
        ->where('id', '!=', Auth::id())
        ->get();
    foreach ($clerks as $clerk) {
        NotificationHelper::toUser(
            $clerk->id,
            'Certificate Ready',
            'Certificate #' . $certificate->certificate_number . ' is approved and ready for release',
            'success',
            route('clerk.certificates.show', $certificate->id)
        );
    }

    // Notify all admins (EXCLUDING current user)
    $admins = User::where('role_id', 1)
        ->where('id', '!=', Auth::id())
        ->get();
    foreach ($admins as $admin) {
        NotificationHelper::toUser(
            $admin->id,
            'Certificate Approved',
            'Certificate #' . $certificate->certificate_number . ' was approved by Captain ' . Auth::user()->name,
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
    }
    // ========== END FIXED NOTIFICATIONS ==========

    // Store the remarks in activity log instead
    ActivityLog::create([
        'user_id' => Auth::id(),
        'action' => 'APPROVE_CERTIFICATE',
        'description' => 'Approved certificate #' . $certificate->certificate_number . ' for ' . ($certificate->resident->full_name ?? 'Unknown') . '. Remarks: ' . ($request->remarks ?? 'No remarks'),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent()
    ]);

    return redirect()->back()->with('success', 'Certificate approved successfully.');
}

    /**
     * Reject a certificate
     */
    public function rejectCertificate(Request $request, Certificate $certificate)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        // Update certificate with existing columns only
        $certificate->update([
            'status' => 'Rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // ========== NOTIFICATIONS ==========
        // Notify the secretary who created it
        if ($certificate->created_by) {
            NotificationHelper::toUser(
                $certificate->created_by,
                'Certificate Rejected',
                'Certificate #' . $certificate->certificate_number . ' was rejected. Reason: ' . $request->rejection_reason,
                'danger',
                route('secretary.certificates.show', $certificate->id)
            );
        }

        // Notify all secretaries
        NotificationHelper::toSecretaries(
            'Certificate Rejected',
            'Certificate #' . $certificate->certificate_number . ' was rejected by Captain',
            'danger',
            route('secretary.certificates.show', $certificate->id)
        );

        // Notify all admins
        NotificationHelper::toAdmins(
            'Certificate Rejected',
            'Certificate #' . $certificate->certificate_number . ' was rejected by Captain ' . Auth::user()->name,
            'danger',
            route('secretary.certificates.show', $certificate->id)
        );
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'REJECT_CERTIFICATE',
            'description' => 'Rejected certificate #' . $certificate->certificate_number . ' for ' . ($certificate->resident->full_name ?? 'Unknown') . '. Reason: ' . $request->rejection_reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Certificate rejected successfully.');
    }

    /**
     * Mark certificate as released
     */
    public function releaseCertificate(Request $request, Certificate $certificate)
    {
        $certificate->update([
            'status' => 'Released',
            'released_at' => now(),
            'released_by' => Auth::id()
        ]);

        // ========== NOTIFICATIONS ==========
        // Notify the secretary who created it
        if ($certificate->created_by) {
            NotificationHelper::toUser(
                $certificate->created_by,
                'Certificate Released',
                'Certificate #' . $certificate->certificate_number . ' has been released',
                'success',
                route('secretary.certificates.show', $certificate->id)
            );
        }

        // Notify all secretaries
        NotificationHelper::toSecretaries(
            'Certificate Released',
            'Certificate #' . $certificate->certificate_number . ' has been released',
            'success',
            route('secretary.certificates.show', $certificate->id)
        );

        // Notify all admins
        NotificationHelper::toAdmins(
            'Certificate Released',
            'Certificate #' . $certificate->certificate_number . ' was released',
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'RELEASE_CERTIFICATE',
            'description' => 'Released certificate #' . $certificate->certificate_number . ' to ' . ($certificate->resident->full_name ?? 'Unknown'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Certificate marked as released.');
    }

    /**
     * View all residents (read-only for captain)
     */
    public function residents(Request $request)
    {
        $query = Resident::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('resident_id', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('purok')) {
            $query->where('purok', $request->purok);
        }

        $residents = $query->orderBy('last_name')->paginate(15)->withQueryString();
        $puroks = Resident::distinct()->orderBy('purok')->pluck('purok');

        return view('captain.residents.index', compact('residents', 'puroks'));
    }

    /**
     * View single resident details
     */
    public function showResident(Resident $resident)
    {
        return view('captain.residents.show', compact('resident'));
    }

    /**
     * View all blotter records
     */
    public function blotters(Request $request)
    {
        $query = Blotter::with('complainant');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('blotter_number', 'like', "%{$search}%")
                  ->orWhere('respondent_name', 'like', "%{$search}%")
                  ->orWhere('incident_type', 'like', "%{$search}%")
                  ->orWhere('incident_location', 'like', "%{$search}%")
                  ->orWhereHas('complainant', function($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('incident_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('incident_date', '<=', $request->date_to);
        }

        $blotters = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Get status counts for summary
        $statusCounts = [
            'pending' => Blotter::where('status', 'Pending')->count(),
            'investigating' => Blotter::where('status', 'Investigating')->count(),
            'hearings' => Blotter::where('status', 'Hearings')->count(),
            'settled' => Blotter::where('status', 'Settled')->count(),
        ];

        return view('captain.blotters.index', compact('blotters', 'statusCounts'));
    }

    /**
     * View single blotter details
     */
    public function showBlotter(Blotter $blotter)
    {
        $blotter->load('complainant');
        return view('captain.blotters.show', compact('blotter'));
    }

    /**
     * Update blotter status (captain can update status)
     */
    public function updateBlotterStatus(Request $request, Blotter $blotter)
    {
        $request->validate([
            'status' => 'required|in:Pending,Investigating,Hearings,Settled,Unsolved,Dismissed',
            'resolution' => 'nullable|required_if:status,Settled|string|max:2000',
            'settlement_date' => 'nullable|required_if:status,Settled|date',
        ]);

        $oldStatus = $blotter->status;

        $updateData = [
            'status' => $request->status,
        ];

        if ($request->status === 'Settled') {
            $updateData['resolution'] = $request->resolution;
            $updateData['settlement_date'] = $request->settlement_date;
            $updateData['settled_by'] = Auth::id();
        }

        $blotter->update($updateData);

        // ========== NOTIFICATIONS ==========
        $statusMessages = [
            'Investigating' => 'is now under investigation',
            'Hearings' => 'is scheduled for hearing',
            'Settled' => 'has been settled',
            'Unsolved' => 'has been marked as unsolved',
            'Dismissed' => 'has been dismissed',
        ];

        $message = $statusMessages[$request->status] ?? 'status changed to ' . $request->status;

        // Notify secretaries
        NotificationHelper::toSecretaries(
            'Blotter Case Updated',
            'Case #' . $blotter->case_id . ' ' . $message,
            $request->status == 'Settled' ? 'success' : 'info',
            route('secretary.blotter.show', $blotter->id)
        );

        // Notify admins
        NotificationHelper::toAdmins(
            'Blotter Case ' . $request->status,
            'Case #' . $blotter->case_id . ' ' . $message . ' by Captain',
            $request->status == 'Settled' ? 'success' : 'info',
            route('secretary.blotter.show', $blotter->id)
        );
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_BLOTTER_STATUS',
            'description' => 'Updated blotter case #' . $blotter->blotter_number . ' status from ' . $oldStatus . ' to ' . $request->status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Blotter status updated successfully.');
    }

    /**
     * Generate report (for the dashboard button)
     */
    public function generateReport(Request $request)
    {
        // This is a placeholder for report generation
        // You can implement this based on your reporting needs

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'GENERATE_REPORT',
            'description' => 'Generated system report',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Report generation started. You will be notified when it\'s ready.');
    }
}
