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
    try {
        $totalResidents = DB::table('residents')->count();
        $totalCertificates = DB::table('certificates')->count();
        $totalBlotters = DB::table('blotters')->count();
        $pendingBlotters = DB::table('blotters')->where('status', 'Pending')->count();
        $activeBlotters = DB::table('blotters')->whereIn('status', ['Pending', 'Ongoing'])->count();
        $settledBlotters = DB::table('blotters')->where('status', 'Settled')->count();
        $pendingCertificates = DB::table('certificates')->where('status', 'Pending')->count();
        $releasedCertificates = DB::table('certificates')->where('status', 'Released')->count();

        // Pending approvals
        $pendingApprovals = DB::table('certificates as c')
            ->join('residents as r', 'c.resident_id', '=', 'r.id')
            ->where('c.status', 'Pending')
            ->select('c.*', 'r.first_name', 'r.last_name')
            ->orderBy('c.created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent blotters
        $recentBlotters = DB::table('blotters')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly statistics
        $certificateStats = DB::table('certificates')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $residentStats = DB::table('residents')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $blotterStats = DB::table('blotters')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

    } catch (\Exception $e) {
        // Set default values if tables don't exist
        $totalResidents = $totalCertificates = $totalBlotters = $pendingBlotters = $activeBlotters = $settledBlotters = $pendingCertificates = $releasedCertificates = 0;
        $pendingApprovals = collect([]);
        $recentBlotters = collect([]);
        $certificateStats = $residentStats = $blotterStats = collect([]);
    }

    return view('captain.dashboard', compact(
        'totalResidents',
        'totalCertificates',
        'totalBlotters',
        'pendingBlotters',
        'activeBlotters',
        'settledBlotters',
        'pendingCertificates',
        'releasedCertificates',
        'pendingApprovals',
        'recentBlotters',
        'certificateStats',
        'residentStats',
        'blotterStats'
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

    $currentUserId = Auth::id();
    $currentUserName = Auth::user()->name;
    $certificateNumber = $certificate->certificate_number ?? $certificate->certificate_id;
    $residentName = $certificate->resident->full_name ?? 'Unknown';

    // ========== FIXED NOTIFICATIONS - EXCLUDE CURRENT USER ==========

    // 1. Notify the secretary who created it (if exists and NOT the current user)
    if ($certificate->created_by && $certificate->created_by != $currentUserId) {
        NotificationHelper::toUser(
            $certificate->created_by,
            'Certificate Approved',
            'Certificate #' . $certificateNumber . ' for ' . $residentName . ' has been approved',
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // 2. Notify all OTHER secretaries (EXCLUDE current user)
    $otherSecretaries = User::where('role_id', 3)
        ->where('id', '!=', $currentUserId)
        ->get();
    foreach ($otherSecretaries as $secretary) {
        NotificationHelper::toUser(
            $secretary->id,
            'Certificate Approved',
            'Certificate #' . $certificateNumber . ' for ' . $residentName . ' has been approved by Captain ' . $currentUserName,
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // 3. Notify all clerks (EXCLUDE current user - though captain is not a clerk, this is safe)
    $clerks = User::where('role_id', 4)
        ->where('id', '!=', $currentUserId)
        ->get();
    foreach ($clerks as $clerk) {
        NotificationHelper::toUser(
            $clerk->id,
            'Certificate Ready',
            'Certificate #' . $certificateNumber . ' for ' . $residentName . ' is approved and ready for release',
            'success',
            route('clerk.certificates.show', $certificate->id)
        );
    }

    // 4. Notify all OTHER admins (EXCLUDE current user)
    $otherAdmins = User::where('role_id', 1)
        ->where('id', '!=', $currentUserId)
        ->get();
    foreach ($otherAdmins as $admin) {
        NotificationHelper::toUser(
            $admin->id,
            'Certificate Approved',
            'Certificate #' . $certificateNumber . ' was approved by Captain ' . $currentUserName,
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // 5. IMPORTANT: DO NOT notify the captain who performed the approval
    // (No notification is sent to the current user/captain)
    // ========== END FIXED NOTIFICATIONS ==========

    // Store the remarks in activity log instead
    ActivityLog::create([
        'user_id' => Auth::id(),
        'action' => 'APPROVE_CERTIFICATE',
        'description' => 'Approved certificate #' . $certificateNumber . ' for ' . $residentName . '. Remarks: ' . ($request->remarks ?? 'No remarks'),
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

    $currentUserId = Auth::id();
    $currentUserName = Auth::user()->name;
    $certificateNumber = $certificate->certificate_number ?? $certificate->certificate_id;
    $residentName = $certificate->resident->full_name ?? 'Unknown';

    // ========== NOTIFICATIONS - EXCLUDE CURRENT USER ==========

    // 1. Notify the secretary who created it (if exists and NOT the current user)
    if ($certificate->created_by && $certificate->created_by != $currentUserId) {
        NotificationHelper::toUser(
            $certificate->created_by,
            'Certificate Rejected',
            'Certificate #' . $certificateNumber . ' for ' . $residentName . ' was rejected. Reason: ' . $request->rejection_reason,
            'danger',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // 2. Notify all OTHER secretaries (EXCLUDE current user)
    $otherSecretaries = User::where('role_id', 3)
        ->where('id', '!=', $currentUserId)
        ->get();
    foreach ($otherSecretaries as $secretary) {
        NotificationHelper::toUser(
            $secretary->id,
            'Certificate Rejected',
            'Certificate #' . $certificateNumber . ' for ' . $residentName . ' was rejected by Captain ' . $currentUserName,
            'danger',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // 3. Notify all OTHER admins (EXCLUDE current user)
    $otherAdmins = User::where('role_id', 1)
        ->where('id', '!=', $currentUserId)
        ->get();
    foreach ($otherAdmins as $admin) {
        NotificationHelper::toUser(
            $admin->id,
            'Certificate Rejected',
            'Certificate #' . $certificateNumber . ' was rejected by Captain ' . $currentUserName,
            'danger',
            route('secretary.certificates.show', $certificate->id)
        );
    }

    // 4. IMPORTANT: DO NOT notify the captain who performed the rejection
    // (No notification is sent to the current user/captain)
    // ========== END NOTIFICATIONS ==========

    ActivityLog::create([
        'user_id' => Auth::id(),
        'action' => 'REJECT_CERTIFICATE',
        'description' => 'Rejected certificate #' . $certificateNumber . ' for ' . $residentName . '. Reason: ' . $request->rejection_reason,
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
