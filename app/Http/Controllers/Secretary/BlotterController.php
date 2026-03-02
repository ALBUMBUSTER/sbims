<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\Resident;
use App\Models\ActivityLog;
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
        // Generate a temporary case ID for display
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
            'description' => 'required|string', // Changed from complaint_details to description
        ]);

        $validated['case_id'] = $this->generateCaseId(); // Changed from blotter_number to case_id
        $validated['status'] = 'Pending';
        $validated['handled_by'] = Auth::id(); // Add the current user as handler

        $blotter = Blotter::create($validated);

        $complainant = Resident::find($request->complainant_id);
        $complainantName = $complainant ? $complainant->first_name . ' ' . $complainant->last_name : 'Unknown';

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
            'description' => 'required|string', // Changed from complaint_details to description
            'status' => 'required|in:Pending,Ongoing,Settled,Referred',
            'resolution' => 'nullable|required_if:status,Settled|string',
            'resolved_date' => 'nullable|required_if:status,Settled|date',
        ]);

        $oldStatus = $blotter->status;
        $oldIncidentType = $blotter->incident_type;

        $blotter->update($validated);

        $complainant = Resident::find($request->complainant_id);
        $complainantName = $complainant ? $complainant->first_name . ' ' . $complainant->last_name : 'Unknown';

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

    public function destroy(Request $request, Blotter $blotter)
    {
        $caseId = $blotter->case_id;
        $incidentType = $blotter->incident_type;
        $complainant = $blotter->complainant;
        $complainantName = $complainant ? $complainant->first_name . ' ' . $complainant->last_name : 'Unknown';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_BLOTTER',
            'description' => 'Deleted blotter case ' . $caseId .
                            ' - ' . $incidentType .
                            ' (Complainant: ' . $complainantName .
                            ', Respondent: ' . $blotter->respondent_name . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $blotter->delete();

        return redirect()->route('secretary.blotter.index')
            ->with('success', 'Blotter case deleted successfully.');
    }

    private function generateCaseId()
    {
        $year = date('Y');
        $prefix = 'BLT';

        // Use case_id column instead of blotter_number
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
