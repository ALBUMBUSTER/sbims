<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\Resident;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlotterController extends Controller
{
    public function index(Request $request)
    {
        $query = Blotter::with('complainant');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('case_id', 'like', "%{$search}%")
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

        $blotters = $query->orderBy('created_at', 'desc')->paginate(10);

        $statusCounts = [
            'pending' => Blotter::where('status', 'Pending')->count(),
            'ongoing' => Blotter::where('status', 'Ongoing')->count(),
            'settled' => Blotter::where('status', 'Settled')->count(),
            'referred' => Blotter::where('status', 'Referred')->count(),
        ];

        return view('secretary.blotter.index', compact('blotters', 'statusCounts'));
    }

    public function create()
    {
        $residents = Resident::orderBy('first_name')->get();
        $case_id = $this->generateCaseId();

        return view('secretary.blotter.create', compact('residents', 'case_id'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'complainant_id' => 'required|exists:residents,id',
            'respondent_name' => 'required|string|max:255',
            'respondent_address' => 'required|string',
            'incident_type' => 'required|string|max:255',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_location' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $validated['case_id'] = $this->generateCaseId();
        $validated['status'] = 'Pending';
        $validated['handled_by'] = Auth::id();

        $blotter = Blotter::create($validated);

        $complainant = Resident::find($request->complainant_id);
        $complainantName = $complainant ? $complainant->first_name . ' ' . $complainant->last_name : 'Unknown';

        // ========== NOTIFICATIONS ==========
        // Notify all captains
        NotificationHelper::toCaptains(
            'New Blotter Case',
            'A new blotter case has been filed: ' . $blotter->case_id . ' - ' . $request->incident_type,
            'warning',
            route('captain.blotters.show', $blotter->id)
        );

        // Notify all secretaries (if current user is not a secretary)
        if (Auth::user()->role_id != 3) {
            NotificationHelper::toSecretaries(
                'New Blotter Case',
                'Case #' . $blotter->case_id . ' has been filed by ' . $complainantName,
                'info',
                route('secretary.blotter.show', $blotter->id)
            );
        }

        // Notify all admins
        NotificationHelper::toAdmins(
            'New Blotter Case',
            'Case #' . $blotter->case_id . ' was filed by ' . Auth::user()->name,
            'info',
            route('secretary.blotter.show', $blotter->id)
        );
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_BLOTTER',
            'description' => 'Filed new blotter case: ' . $request->incident_type .
                            ' - Complainant: ' . $complainantName .
                            ', Respondent: ' . $request->respondent_name .
                            ' (Case ID: ' . $blotter->case_id . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.blotter.index')
            ->with('success', 'Blotter case created successfully.');
    }

    public function show(Blotter $blotter)
    {
        $blotter->load('complainant');
        return view('secretary.blotter.show', compact('blotter'));
    }

    public function edit(Blotter $blotter)
    {
        $residents = Resident::orderBy('first_name')->get();
        return view('secretary.blotter.edit', compact('blotter', 'residents'));
    }

    public function update(Request $request, Blotter $blotter)
    {
        $validated = $request->validate([
            'complainant_id' => 'required|exists:residents,id',
            'respondent_name' => 'required|string|max:255',
            'respondent_address' => 'required|string',
            'incident_type' => 'required|string|max:255',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_location' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:Pending,Ongoing,Settled,Referred',
            'resolution' => 'nullable|required_if:status,Settled|string',
            'resolved_date' => 'nullable|required_if:status,Settled|date',
        ]);

        $oldStatus = $blotter->status;
        $oldIncidentType = $blotter->incident_type;

        $blotter->update($validated);

        $complainant = Resident::find($request->complainant_id);
        $complainantName = $complainant ? $complainant->first_name . ' ' . $complainant->last_name : 'Unknown';

        // ========== NOTIFICATIONS FOR STATUS CHANGE ==========
        if ($oldStatus !== $validated['status']) {
            $statusMessages = [
                'Ongoing' => 'is now ongoing',
                'Settled' => 'has been settled',
                'Referred' => 'has been referred',
            ];

            $message = $statusMessages[$validated['status']] ?? 'status changed to ' . $validated['status'];

            // Notify captains
            NotificationHelper::toCaptains(
                'Blotter Case Updated',
                'Case #' . $blotter->case_id . ' ' . $message,
                $validated['status'] == 'Settled' ? 'success' : 'info',
                route('captain.blotters.show', $blotter->id)
            );

            // Notify secretaries
            NotificationHelper::toSecretaries(
                'Blotter Case Updated',
                'Case #' . $blotter->case_id . ' ' . $message,
                $validated['status'] == 'Settled' ? 'success' : 'info',
                route('secretary.blotter.show', $blotter->id)
            );

            // Notify admins
            NotificationHelper::toAdmins(
                'Blotter Case ' . $validated['status'],
                'Case #' . $blotter->case_id . ' ' . $message,
                $validated['status'] == 'Settled' ? 'success' : 'info',
                route('secretary.blotter.show', $blotter->id)
            );
        }
        // ========== END NOTIFICATIONS ==========

        $description = 'Updated blotter case ' . $blotter->case_id;

        if ($oldStatus !== $validated['status']) {
            $description .= ' - Status changed from ' . $oldStatus . ' to ' . $validated['status'];
        }

        if ($oldIncidentType !== $validated['incident_type']) {
            $description .= ' - Incident type changed from ' . $oldIncidentType . ' to ' . $validated['incident_type'];
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_BLOTTER',
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.blotter.show', $blotter)
            ->with('success', 'Blotter case updated successfully.');
    }

    public function updateStatus(Request $request, Blotter $blotter)
    {
        $request->validate([
            'status' => 'required|in:Pending,Ongoing,Settled,Referred',
            'resolution' => 'nullable|required_if:status,Settled|string',
            'resolved_date' => 'nullable|required_if:status,Settled|date',
        ]);

        $oldStatus = $blotter->status;

        $blotter->update([
            'status' => $request->status,
            'resolution' => $request->resolution,
            'resolved_date' => $request->resolved_date,
        ]);

        $complainant = $blotter->complainant;
        $complainantName = $complainant ? $complainant->first_name . ' ' . $complainant->last_name : 'Unknown';

        // ========== NOTIFICATIONS ==========
        $statusMessages = [
            'Ongoing' => 'is now ongoing',
            'Settled' => 'has been settled',
            'Referred' => 'has been referred',
        ];

        $message = $statusMessages[$request->status] ?? 'status changed to ' . $request->status;

        // Notify captains
        NotificationHelper::toCaptains(
            'Blotter Case Status Updated',
            'Case #' . $blotter->case_id . ' ' . $message,
            $request->status == 'Settled' ? 'success' : 'info',
            route('captain.blotters.show', $blotter->id)
        );

        // Notify secretaries
        NotificationHelper::toSecretaries(
            'Blotter Case Status Updated',
            'Case #' . $blotter->case_id . ' ' . $message,
            $request->status == 'Settled' ? 'success' : 'info',
            route('secretary.blotter.show', $blotter->id)
        );

        // Notify admins
        NotificationHelper::toAdmins(
            'Blotter Case ' . $request->status,
            'Case #' . $blotter->case_id . ' ' . $message,
            $request->status == 'Settled' ? 'success' : 'info',
            route('secretary.blotter.show', $blotter->id)
        );
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_BLOTTER_STATUS',
            'description' => 'Blotter case ' . $blotter->case_id .
                            ' status changed from ' . $oldStatus . ' to ' . $request->status .
                            ' (Case: ' . $blotter->incident_type .
                            ', Complainant: ' . $complainantName . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.blotter.show', $blotter)
            ->with('success', 'Blotter case status updated successfully.');
    }

    /**
     * Archive (Soft Delete) the specified blotter case
     */
    public function archive(Request $request, Blotter $blotter)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in.');
        }

        // Clerks (role_id = 4) cannot archive
        if (Auth::user()->role_id == 4) {
            return redirect()->route('secretary.blotter.index')
                ->with('error', 'You do not have permission to archive blotter cases.');
        }

        $caseId = $blotter->case_id;
        $incidentType = $blotter->incident_type;
        $complainant = $blotter->complainant;
        $complainantName = $complainant ? $complainant->first_name . ' ' . $complainant->last_name : 'Unknown';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'ARCHIVE_BLOTTER',
            'description' => 'Archived blotter case ' . $caseId .
                            ' - ' . $incidentType .
                            ' (Complainant: ' . $complainantName .
                            ', Respondent: ' . $blotter->respondent_name . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $blotter->delete(); // Soft delete

        return redirect()->route('secretary.blotter.index')
            ->with('success', 'Blotter case archived successfully.');
    }

    /**
     * Display archived blotter cases
     */
    public function archived(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in.');
        }

        // Clerks (role_id = 4) cannot view archive
        if (Auth::user()->role_id == 4) {
            return redirect()->route('secretary.blotter.index')
                ->with('error', 'You do not have permission to view archived cases.');
        }

        $query = Blotter::onlyTrashed()->with('complainant');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('case_id', 'like', "%{$search}%")
                  ->orWhere('respondent_name', 'like', "%{$search}%")
                  ->orWhere('incident_type', 'like', "%{$search}%");
            });
        }

        $archived = $query->orderBy('deleted_at', 'desc')->paginate(15);

        return view('secretary.blotter.archived', compact('archived'));
    }

    /**
     * Restore archived blotter case
     */
    public function restore(Request $request, $id)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in.');
        }

        // Clerks (role_id = 4) cannot restore
        if (Auth::user()->role_id == 4) {
            return redirect()->route('secretary.blotter.index')
                ->with('error', 'You do not have permission to restore cases.');
        }

        $blotter = Blotter::onlyTrashed()->findOrFail($id);
        $blotter->restore();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'RESTORE_BLOTTER',
            'description' => 'Restored blotter case ' . $blotter->case_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.blotter.archived')
            ->with('success', 'Blotter case restored successfully.');
    }

    /**
     * Permanently delete blotter case (Force Delete)
     */
    public function forceDelete(Request $request, $id)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in.');
        }

        $userRole = Auth::user()->role_id;

        // Allow only Admin (1) to permanently delete
        if (!in_array($userRole, [1])) { // Only Admin can permanently delete
            return redirect()->route('secretary.blotter.archived')
                ->with('error', 'You do not have permission to permanently delete cases.');
        }

        $blotter = Blotter::onlyTrashed()->findOrFail($id);
        $caseId = $blotter->case_id;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'FORCE_DELETE_BLOTTER',
            'description' => 'Permanently deleted blotter case ' . $caseId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $blotter->forceDelete();

        return redirect()->route('secretary.blotter.archived')
            ->with('success', 'Blotter case permanently deleted.');
    }

    /**
     * Legacy destroy method - consider removing or redirecting to archive
     */
    public function destroy(Request $request, Blotter $blotter)
    {
        // Redirect to archive method instead
        return $this->archive($request, $blotter);
    }

    private function generateCaseId()
    {
        $year = date('Y');
        $prefix = 'BLT';

        $lastBlotter = Blotter::where('case_id', 'like', "{$prefix}-{$year}-%")
            ->orderBy('case_id', 'desc')
            ->first();

        if ($lastBlotter) {
            $lastNumber = intval(substr($lastBlotter->case_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}-{$newNumber}";
    }
}
