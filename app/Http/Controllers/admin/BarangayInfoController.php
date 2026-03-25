<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangayInfo;
use App\Models\Resident;
use App\Models\Blotter;
use App\Models\Certificate;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BarangayInfoController extends Controller
{
    /**
     * Display barangay information with statistics
     */
    public function index()
    {
        // Get or create barangay info record
        $barangayInfo = BarangayInfo::first();

        // If no record exists, create a default one
        if (!$barangayInfo) {
            $barangayInfo = BarangayInfo::create([
                'barangay_name' => 'Libertad',
                'address' => 'Libertad, Isabel, Leyte'
            ]);
        }

        // Get statistics
        $statistics = $this->getBarangayStatistics();

        return view('admin.barangay.index', compact('barangayInfo', 'statistics'));
    }

    /**
     * Get comprehensive barangay statistics
     */
    private function getBarangayStatistics()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        // Check if we have manual overrides in session (for testing/editing)
        $manual = session('manual_stats', []);

        // Get officials data from session
        $officialsData = session('officials_data', []);

        // Resident Statistics
        $totalResidents = Resident::count();
        $maleResidents = Resident::where('gender', 'Male')->count();
        $femaleResidents = Resident::where('gender', 'Female')->count();
        $otherResidents = Resident::where('gender', 'Other')->count();
        $seniorCitizens = Resident::where('is_senior', true)->count();
        $pwd = Resident::where('is_pwd', true)->count();
        $fourPs = Resident::where('is_4ps', true)->count();
        $registeredVoters = Resident::where('is_voter', true)->count();

        // New residents this month
        $newResidentsMonth = Resident::where('created_at', '>=', $startOfMonth)->count();

        // Purok distribution
        $purokDistribution = Resident::select('purok', DB::raw('count(*) as total'))
            ->whereNotNull('purok')
            ->groupBy('purok')
            ->orderBy('purok')
            ->pluck('total', 'purok')
            ->toArray();

        // Household statistics
        $totalHouseholds = Resident::distinct('household_number')
            ->whereNotNull('household_number')
            ->count('household_number');

        $avgHouseholdSize = $totalHouseholds > 0
            ? round($totalResidents / $totalHouseholds, 1)
            : 0;

        // Blotter Statistics
        $totalBlotters = Blotter::count();
        $pendingBlotters = Blotter::where('status', 'Pending')->count();
        $ongoingBlotters = Blotter::where('status', 'Ongoing')->count();
        $referredBlotters = Blotter::where('status', 'Referred')->count();
        $settledBlotters = Blotter::where('status', 'Settled')->count();
        $monthlyBlotters = Blotter::where('created_at', '>=', $startOfMonth)->count();

        // Certificate Statistics
        $totalCertificates = Certificate::count();
        $clearanceCertificates = Certificate::where('certificate_type', 'Clearance')->count();
        $indigencyCertificates = Certificate::where('certificate_type', 'Indigency')->count();
        $residencyCertificates = Certificate::where('certificate_type', 'Residency')->count();
        $otherCertificates = Certificate::whereNotIn('certificate_type', ['Clearance', 'Indigency', 'Residency',])->count();

        $pendingCertificates = Certificate::where('status', 'Pending')->count();
        $releasedCertificates = Certificate::where('status', 'Released')->count();
        $rejectedCertificates = Certificate::where('status', 'Rejected')->count();
        $monthlyCertificates = Certificate::where('created_at', '>=', $startOfMonth)->count();

        // Officials Statistics
        $totalOfficials = User::whereIn('role_id', [2, 3, 4])->count(); // Captain, Secretary, etc.
        $activeOfficials = User::whereIn('role_id', [2, 3, 4])->where('is_active', true)->count();

        // Monthly Transactions
        $monthlyTransactions = $monthlyCertificates + $monthlyBlotters + $newResidentsMonth;
        $monthlySettled = Blotter::where('status', 'Settled')
            ->where('updated_at', '>=', $startOfMonth)
            ->count();

        // Build statistics array
        $statistics = [
            // Resident stats
            'total_residents' => $manual['total_residents'] ?? $totalResidents,
            'male_residents' => $maleResidents,
            'female_residents' => $femaleResidents,
            'other_residents' => $otherResidents,
            'senior_citizens' => $seniorCitizens,
            'pwd' => $pwd,
            'four_ps' => $fourPs,
            'registered_voters' => $registeredVoters,
            'new_residents_month' => $newResidentsMonth,
            'purok_distribution' => $purokDistribution,

            // Household stats
            'total_households' => $manual['total_households'] ?? $totalHouseholds,
            'avg_household_size' => $avgHouseholdSize,

            // Blotter stats
            'total_blotters' => $manual['total_blotters'] ?? $totalBlotters,
            'pending_blotters' => $pendingBlotters,
            'ongoing_blotters' => $ongoingBlotters,
            'referred_blotters' => $referredBlotters,
            'settled_blotters' => $settledBlotters,
            'monthly_blotters' => $monthlyBlotters,

            // Certificate stats
            'total_certificates' => $manual['total_certificates'] ?? $totalCertificates,
            'clearance_certificates' => $clearanceCertificates,
            'indigency_certificates' => $indigencyCertificates,
            'residency_certificates' => $residencyCertificates,
            'other_certificates' => $otherCertificates,
            'pending_certificates' => $pendingCertificates,
            'released_certificates' => $releasedCertificates,
            'rejected_certificates' => $rejectedCertificates,
            'certificates_month' => $monthlyCertificates,

            // Officials stats
            'total_officials' => $totalOfficials,
            'active_officials' => $activeOfficials,
            'barangay_treasurer' => $officialsData['barangay_treasurer'] ?? 'Not set',
            'sk_chairman' => $officialsData['sk_chairman'] ?? 'Not set',
            'kagawads_count' => $officialsData['kagawads_count'] ?? 'Not set',
            'tanods_count' => $officialsData['tanods_count'] ?? 'Not set',

            // Monthly stats
            'monthly_transactions' => $monthlyTransactions,
            'monthly_certificates' => $monthlyCertificates,
            'monthly_settled' => $monthlySettled,
        ];

        return $statistics;
    }

    /**
     * Update barangay information
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'barangay_name' => 'required|string|max:100',
            'barangay_captain' => 'nullable|string|max:100',
            'barangay_secretary' => 'nullable|string|max:100',
            'address' => 'required|string',
            'contact_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the barangay info record
        $barangayInfo = BarangayInfo::first();

        if (!$barangayInfo) {
            $barangayInfo = new BarangayInfo();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($barangayInfo->logo_path && Storage::exists($barangayInfo->logo_path)) {
                Storage::delete($barangayInfo->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('barangay-logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Update the record
        $barangayInfo->fill($validated);
        $barangayInfo->save();

        // ========== NOTIFICATIONS ==========
        NotificationHelper::toAdmins(
            'Barangay Info Updated',
            'Barangay information was updated by ' . Auth::user()->name,
            'info',
            route('admin.barangay.index')
        );
        // ========== END NOTIFICATIONS ==========

        // Log the action
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated barangay information",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.barangay.index')
            ->with('success', 'Barangay information updated successfully.');
    }

    /**
     * Update barangay officials
     */
    public function updateOfficials(Request $request)
    {
        $validated = $request->validate([
            'barangay_captain' => 'nullable|string|max:100',
            'barangay_secretary' => 'nullable|string|max:100',
            'barangay_treasurer' => 'nullable|string|max:100',
            'kagawads_count' => 'nullable|integer|min:0|max:20',
            'sk_chairman' => 'nullable|string|max:100',
            'tanods_count' => 'nullable|integer|min:0|max:50',
        ]);

        // Get the barangay info record
        $barangayInfo = BarangayInfo::first();

        if (!$barangayInfo) {
            $barangayInfo = new BarangayInfo();
        }

        // Update captain and secretary in the main table
        $barangayInfo->barangay_captain = $validated['barangay_captain'] ?? $barangayInfo->barangay_captain;
        $barangayInfo->barangay_secretary = $validated['barangay_secretary'] ?? $barangayInfo->barangay_secretary;
        $barangayInfo->save();

        // Store the other values in session
        $officialsData = [
            'barangay_treasurer' => $validated['barangay_treasurer'] ?? null,
            'kagawads_count' => $validated['kagawads_count'] ?? null,
            'sk_chairman' => $validated['sk_chairman'] ?? null,
            'tanods_count' => $validated['tanods_count'] ?? null,
        ];

        session(['officials_data' => $officialsData]);

        // Log the action
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated barangay officials information",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.barangay.index')
            ->with('success', 'Barangay officials updated successfully.');
    }

    /**
     * Update statistics manually (for testing/overrides)
     */
    public function updateStats(Request $request)
    {
        $request->validate([
            'manual_total_residents' => 'nullable|integer|min:0',
            'manual_total_blotters' => 'nullable|integer|min:0',
            'manual_total_certificates' => 'nullable|integer|min:0',
            'manual_total_households' => 'nullable|integer|min:0',
        ]);

        $manualStats = [
            'total_residents' => $request->manual_total_residents,
            'total_blotters' => $request->manual_total_blotters,
            'total_certificates' => $request->manual_total_certificates,
            'total_households' => $request->manual_total_households,
        ];

        // Remove null values
        $manualStats = array_filter($manualStats, function($value) {
            return !is_null($value) && $value !== '';
        });

        // Store in session
        session(['manual_stats' => $manualStats]);

        // ========== NOTIFICATIONS ==========
        NotificationHelper::toAdmins(
            'Statistics Updated',
            'Barangay statistics were manually updated by ' . Auth::user()->name,
            'info',
            route('admin.barangay.index')
        );
        // ========== END NOTIFICATIONS ==========

        // Log the action
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Manually updated barangay statistics",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.barangay.index')
            ->with('success', 'Barangay statistics updated successfully.');
    }
}
