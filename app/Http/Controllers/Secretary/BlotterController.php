<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\CaseParty;
use App\Models\Resident;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BlotterController extends Controller
{
    public function index(Request $request)
    {
        $query = Blotter::with(['complainants', 'respondents']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('case_id', 'like', "%{$search}%")
                  ->orWhere('incident_type', 'like', "%{$search}%")
                  ->orWhere('incident_location', 'like', "%{$search}%")
                  ->orWhereHas('complainants', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('respondents', function($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
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
        // Validate the request with multiple parties
        $validated = $request->validate([
            'incident_type' => 'required|string|max:255',
            'other_incident_type' => 'nullable|required_if:incident_type,Other|string',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_time' => 'required|date_format:H:i',
            'incident_location' => 'required|string',
            'description' => 'required|string',
            'complainants' => 'required|array|min:1',
            'complainants.*.name' => 'required|string',
            'complainants.*.address' => 'nullable|string',
            'complainants.*.contact' => 'nullable|string',
            'complainants.*.resident_id' => 'nullable|exists:residents,id',
            'respondents' => 'required|array|min:1',
            'respondents.*.name' => 'required|string',
            'respondents.*.address' => 'required|string',
            'respondents.*.resident_id' => 'nullable|exists:residents,id',
            'witnesses' => 'nullable|array',
            'witnesses.*.name' => 'required_with:witnesses|string',
            'witnesses.*.statement' => 'nullable|string',
        ]);

        // Determine final incident type
        $finalIncidentType = $validated['incident_type'];
        if ($finalIncidentType === 'Other' && isset($validated['other_incident_type'])) {
            $finalIncidentType = $validated['other_incident_type'];
        }

        // Combine date and time
        $incidentDateTime = $validated['incident_date'] . ' ' . $validated['incident_time'];

        // Add additional validation: check if combined datetime is not in future
        $incidentDateTimeObj = Carbon::parse($incidentDateTime);
        if ($incidentDateTimeObj->isFuture()) {
            return back()->withErrors(['incident_date' => 'Incident date and time cannot be in the future.'])->withInput();
        }

        DB::beginTransaction();

        try {
            // Create primary blotter record
            $blotter = Blotter::create([
                'case_id' => $this->generateCaseId(),
                'status' => 'Pending',
                'handled_by' => Auth::id(),
                'incident_type' => $finalIncidentType,
                'incident_date' => $incidentDateTime,
                'incident_location' => $validated['incident_location'],
                'description' => $validated['description'],
                'complainant_id' => null,
                'respondent_name' => null,
                'respondent_address' => null,
            ]);

            // ========== START MEDIATION PROCESS (15-15-30 RULE) ==========
            $blotter->startMediation();
            // ========== END MEDIATION PROCESS ==========

            // Save complainants
            foreach ($validated['complainants'] as $complainant) {
                $blotter->parties()->create([
                    'party_type' => 'complainant',
                    'name' => $complainant['name'],
                    'address' => $complainant['address'] ?? null,
                    'contact_number' => $complainant['contact'] ?? null,
                    'resident_id' => $complainant['resident_id'] ?? null,
                ]);
            }

            // Save respondents
            foreach ($validated['respondents'] as $respondent) {
                $blotter->parties()->create([
                    'party_type' => 'respondent',
                    'name' => $respondent['name'],
                    'address' => $respondent['address'],
                    'resident_id' => $respondent['resident_id'] ?? null,
                ]);
            }

            // Save witnesses (optional)
            if (isset($validated['witnesses'])) {
                foreach ($validated['witnesses'] as $witness) {
                    if (!empty($witness['name'])) {
                        $blotter->parties()->create([
                            'party_type' => 'witness',
                            'name' => $witness['name'],
                            'additional_info' => $witness['statement'] ?? null,
                        ]);
                    }
                }
            }

            // Get names for notification
            $complainantNames = $blotter->complainants->pluck('name')->implode(', ');
            $respondentNames = $blotter->respondents->pluck('name')->implode(', ');

            // ========== REAL-TIME NOTIFICATIONS ==========
            $currentUserId = Auth::id();

            NotificationHelper::toCaptainsExceptCurrent(
                $currentUserId,
                'New Blotter Case',
                'A new blotter case has been filed: ' . $blotter->case_id . ' - ' . $finalIncidentType,
                'warning',
                route('captain.blotters.show', $blotter->id)
            );

            NotificationHelper::toAdminsExceptCurrent(
                $currentUserId,
                'New Blotter Case',
                'Case #' . $blotter->case_id . ' was filed by ' . Auth::user()->name,
                'info',
                route('secretary.blotter.show', $blotter->id)
            );
            // ========== END NOTIFICATIONS ==========

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_BLOTTER',
                'description' => 'Filed new blotter case: ' . $finalIncidentType .
                                ' - Complainant(s): ' . $complainantNames .
                                ', Respondent(s): ' . $respondentNames .
                                ' (Case ID: ' . $blotter->case_id . ')',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('secretary.blotter.index')
                ->with('success', 'Blotter case created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating blotter case: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Blotter $blotter)
    {
        $blotter->load(['complainants', 'respondents', 'witnesses']);
        return view('secretary.blotter.show', compact('blotter'));
    }

    public function edit(Blotter $blotter)
    {
        $residents = Resident::orderBy('first_name')->get();
        $blotter->load(['parties']);
        return view('secretary.blotter.edit', compact('blotter', 'residents'));
    }

    public function update(Request $request, Blotter $blotter)
    {
        $validated = $request->validate([
            'incident_type' => 'required|string|max:255',
            'other_incident_type' => 'nullable|required_if:incident_type,Other|string',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_time' => 'required|date_format:H:i',
            'incident_location' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:Pending,Ongoing,Settled,Referred',
            'resolution' => 'nullable|required_if:status,Settled|string',
            'resolved_date' => 'nullable|required_if:status,Settled|date',
        ]);

        $finalIncidentType = $validated['incident_type'];
        if ($finalIncidentType === 'Other' && isset($validated['other_incident_type'])) {
            $finalIncidentType = $validated['other_incident_type'];
        }

        $incidentDateTime = $validated['incident_date'] . ' ' . $validated['incident_time'];
        $oldStatus = $blotter->status;

        $updateData = [
            'incident_type' => $finalIncidentType,
            'incident_date' => $incidentDateTime,
            'incident_location' => $validated['incident_location'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ];

        if ($validated['status'] == 'Settled') {
            $updateData['resolution'] = $validated['resolution'];
            $updateData['resolved_date'] = $validated['resolved_date'] ?? now();
        }

        $blotter->update($updateData);

        // ========== REAL-TIME NOTIFICATIONS FOR STATUS CHANGE ==========
        if ($oldStatus !== $validated['status']) {
            $currentUserId = Auth::id();
            $statusMessages = [
                'Ongoing' => 'is now ongoing',
                'Settled' => 'has been settled',
                'Referred' => 'has been referred',
            ];

            $message = $statusMessages[$validated['status']] ?? 'status changed to ' . $validated['status'];

            NotificationHelper::toCaptainsExceptCurrent(
                $currentUserId,
                'Blotter Case Updated',
                'Case #' . $blotter->case_id . ' ' . $message,
                $validated['status'] == 'Settled' ? 'success' : 'info',
                route('captain.blotters.show', $blotter->id)
            );

            NotificationHelper::toSecretariesExceptCurrent(
                $currentUserId,
                'Blotter Case Updated',
                'Case #' . $blotter->case_id . ' ' . $message,
                $validated['status'] == 'Settled' ? 'success' : 'info',
                route('secretary.blotter.show', $blotter->id)
            );

            NotificationHelper::toAdminsExceptCurrent(
                $currentUserId,
                'Blotter Case ' . $validated['status'],
                'Case #' . $blotter->case_id . ' ' . $message,
                $validated['status'] == 'Settled' ? 'success' : 'info',
                route('secretary.blotter.show', $blotter->id)
            );
        }
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_BLOTTER',
            'description' => 'Updated blotter case ' . $blotter->case_id . ' - Status changed from ' . $oldStatus . ' to ' . $validated['status'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.blotter.show', $blotter)
            ->with('success', 'Blotter case updated successfully.');
    }

    public function updateStatus(Request $request, Blotter $blotter)
    {
        if (Auth::user()->role_id == 4) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update blotter status.'
                ], 403);
            }
            return redirect()->route('secretary.blotter.show', $blotter)
                ->with('error', 'You do not have permission to update blotter status.');
        }

        $request->validate([
            'status' => 'required|in:Pending,Ongoing,Settled,Referred',
            'resolution' => 'nullable|required_if:status,Settled|string',
            'resolved_date' => 'nullable|date',
        ]);

        $oldStatus = $blotter->status;

        $updateData = [
            'status' => $request->status,
        ];

        if ($request->status == 'Settled') {
            $updateData['resolution'] = $request->resolution;
            $updateData['resolved_date'] = $request->resolved_date ?? now();
        } else {
            $updateData['resolution'] = null;
            $updateData['resolved_date'] = null;
        }

        $blotter->update($updateData);

        $complainantNames = $blotter->complainants->pluck('name')->implode(', ');

        $currentUserId = Auth::id();
        $statusMessages = [
            'Ongoing' => 'is now ongoing',
            'Settled' => 'has been settled',
            'Referred' => 'has been referred',
        ];

        $message = $statusMessages[$request->status] ?? 'status changed to ' . $request->status;

        NotificationHelper::toSecretariesExceptCurrent(
            $currentUserId,
            'Blotter Case Status Updated',
            'Case #' . $blotter->case_id . ' ' . $message,
            $request->status == 'Settled' ? 'success' : 'info',
            route('secretary.blotter.show', $blotter->id)
        );

        NotificationHelper::toCaptainsExceptCurrent(
            $currentUserId,
            'Blotter Case Status Updated',
            'Case #' . $blotter->case_id . ' ' . $message,
            $request->status == 'Settled' ? 'success' : 'info',
            route('captain.blotters.show', $blotter->id)
        );

        NotificationHelper::toAdminsExceptCurrent(
            $currentUserId,
            'Blotter Case ' . $request->status,
            'Case #' . $blotter->case_id . ' ' . $message . ' by ' . Auth::user()->name,
            $request->status == 'Settled' ? 'success' : 'info',
            route('secretary.blotter.show', $blotter->id)
        );

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_BLOTTER_STATUS',
            'description' => 'Blotter case ' . $blotter->case_id .
                            ' status changed from ' . $oldStatus . ' to ' . $request->status .
                            ' (Case: ' . $blotter->incident_type .
                            ', Complainant(s): ' . $complainantNames . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Case status updated successfully!',
                'status' => $request->status,
                'case_id' => $blotter->case_id
            ]);
        }

        return redirect()->route('secretary.blotter.show', $blotter)
            ->with('success', 'Blotter case status updated successfully.');
    }

    /**
     * Record hearing attendance
     */
    public function recordHearing(Request $request, Blotter $blotter)
    {
        $request->validate([
            'respondent_attended' => 'required|boolean',
            'complainant_attended' => 'required|boolean',
            'hearing_notes' => 'nullable|string',
        ]);

        $result = $blotter->recordAttendance(
            $request->respondent_attended,
            $request->complainant_attended
        );

        // ========== NOTIFICATIONS ==========
        $currentUserId = Auth::id();

        if (!$request->respondent_attended) {
            $missedCount = $blotter->hearing_count;

            if ($missedCount >= 3) {
                NotificationHelper::toCaptainsExceptCurrent(
                    $currentUserId,
                    'CFA Issued',
                    'Case #' . $blotter->case_id . ' - Respondent missed 3 hearings. Certificate to File Action issued.',
                    'warning',
                    route('captain.blotters.show', $blotter->id)
                );

                NotificationHelper::toSecretariesExceptCurrent(
                    $currentUserId,
                    'CFA Issued',
                    'Case #' . $blotter->case_id . ' - Certificate to File Action issued.',
                    'warning',
                    route('secretary.blotter.show', $blotter->id)
                );
            } else {
                $remaining = 3 - $missedCount;
                NotificationHelper::toCaptainsExceptCurrent(
                    $currentUserId,
                    'Missed Hearing',
                    'Case #' . $blotter->case_id . ' - Respondent missed hearing. ' . $remaining . ' more absence(s) before CFA.',
                    'warning',
                    route('captain.blotters.show', $blotter->id)
                );
            }
        } elseif ($result === true && $blotter->status == 'Settled') {
            // Case settled notification
            NotificationHelper::toCaptainsExceptCurrent(
                $currentUserId,
                'Case Settled',
                'Case #' . $blotter->case_id . ' has been settled through ' . ucfirst($blotter->hearing_stage) . '.',
                'success',
                route('captain.blotters.show', $blotter->id)
            );
        }
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'RECORD_HEARING',
            'description' => 'Recorded hearing for case #' . $blotter->case_id .
                             ' - Respondent attended: ' . ($request->respondent_attended ? 'Yes' : 'No') .
                             ', Complainant attended: ' . ($request->complainant_attended ? 'Yes' : 'No'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.blotter.show', $blotter)
            ->with('success', 'Hearing recorded successfully.');
    }

    /**
     * Schedule next hearing
     */
    public function scheduleHearing(Request $request, Blotter $blotter)
    {
        $request->validate([
            'hearing_date' => 'required|date|after:today',
            'hearing_notes' => 'nullable|string',
        ]);

        $daysFromNow = Carbon::now()->diffInDays($request->hearing_date);
        $blotter->scheduleNextHearing($daysFromNow);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'SCHEDULE_HEARING',
            'description' => 'Scheduled hearing for case #' . $blotter->case_id . ' on ' . $request->hearing_date,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.blotter.show', $blotter)
            ->with('success', 'Hearing scheduled successfully.');
    }

    /**
     * Extend conciliation deadline (15 more days)
     */
    public function extendDeadline(Request $request, Blotter $blotter)
    {
        if ($blotter->extendConciliation()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'EXTEND_DEADLINE',
                'description' => 'Extended deadline for case #' . $blotter->case_id . ' by 15 days',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->route('secretary.blotter.show', $blotter)
                ->with('success', 'Deadline extended by 15 days.');
        }

        return redirect()->route('secretary.blotter.show', $blotter)
            ->with('error', 'Cannot extend deadline for this case.');
    }

    public function archive(Request $request, Blotter $blotter)
    {
        if (Auth::user()->role_id == 4) {
            return redirect()->route('secretary.blotter.index')
                ->with('error', 'You do not have permission to archive blotter cases.');
        }

        $caseId = $blotter->case_id;
        $incidentType = $blotter->incident_type;
        $complainantNames = $blotter->complainants->pluck('name')->implode(', ');
        $respondentNames = $blotter->respondents->pluck('name')->implode(', ');

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'ARCHIVE_BLOTTER',
            'description' => 'Archived blotter case ' . $caseId .
                            ' - ' . $incidentType .
                            ' (Complainant(s): ' . $complainantNames .
                            ', Respondent(s): ' . $respondentNames . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $blotter->delete();

        return redirect()->route('secretary.blotter.index')
            ->with('success', 'Blotter case archived successfully.');
    }

    public function archived(Request $request)
    {
        if (Auth::user()->role_id == 4) {
            return redirect()->route('secretary.blotter.index')
                ->with('error', 'You do not have permission to view archived cases.');
        }

        $query = Blotter::onlyTrashed()->with('complainants', 'respondents');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('case_id', 'like', "%{$search}%")
                  ->orWhere('incident_type', 'like', "%{$search}%")
                  ->orWhereHas('complainants', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('respondents', function($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $archived = $query->orderBy('deleted_at', 'desc')->paginate(15);

        return view('secretary.blotter.archived', compact('archived'));
    }

    public function restore(Request $request, $id)
    {
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

    public function forceDelete(Request $request, $id)
    {
        $userRole = Auth::user()->role_id;

        if (!in_array($userRole, [1])) {
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

    public function destroy(Request $request, Blotter $blotter)
    {
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
