<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class ResidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Generate a unique resident ID
     */
    private function generateResidentId()
    {
        $year = date('Y');
        $month = date('m');

        // Get the latest resident ID for this year/month
        $latest = Resident::where('resident_id', 'LIKE', "RES-{$year}{$month}-%")
                          ->orderBy('resident_id', 'desc')
                          ->first();

        if ($latest) {
            // Extract the sequence number and increment
            $parts = explode('-', $latest->resident_id);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }

        // Format: RES-YYYYMM-XXXX (e.g., RES-202502-0001)
        return 'RES-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display a listing of residents.
     */
    public function index(Request $request)
    {
        $query = Resident::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('middle_name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%")
                  ->orWhere('resident_id', 'LIKE', "%{$search}%");
            });
        }

        $residents = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('secretary.residents.index', compact('residents'));
    }

    /**
     * Show the form for creating a new resident.
     */
    public function create()
    {
        $generatedId = $this->generateResidentId();
        return view('secretary.residents.create', compact('generatedId'));
    }

    /**
     * Generate ID for AJAX request
     */
    public function generateId()
    {
        return response()->json(['id' => $this->generateResidentId()]);
    }

    /**
     * Store a newly created resident in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'resident_id' => 'required|string|max:20|unique:residents',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'birthdate' => 'required|date',
                'gender' => 'required|in:Male,Female',
                'civil_status' => 'required|in:Single,Married,Widowed,Divorced',
                'contact_number' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:100',
                'address' => 'required|string',
                'purok' => 'required|string|max:50',
                'household_number' => 'nullable|string|max:20',
                'is_voter' => 'sometimes|boolean',
                'is_4ps' => 'sometimes|boolean',
                'is_senior' => 'sometimes|boolean',
                'is_pwd' => 'sometimes|boolean',
                'pwd_id' => 'nullable|string|max:50',
                'disability_type' => 'nullable|string|max:100',
            ]);

            // Set checkbox values
            $validated['is_voter'] = $request->has('is_voter') ? 1 : 0;
            $validated['is_4ps'] = $request->has('is_4ps') ? 1 : 0;
            $validated['is_senior'] = $request->has('is_senior') ? 1 : 0;
            $validated['is_pwd'] = $request->has('is_pwd') ? 1 : 0;

            // Remove PWD fields if not PWD
            if (!$validated['is_pwd']) {
                $validated['pwd_id'] = null;
                $validated['disability_type'] = null;
            }

            $resident = Resident::create($validated);

            // Build status description
            $statuses = [];
            if ($validated['is_voter']) $statuses[] = 'Voter';
            if ($validated['is_senior']) $statuses[] = 'Senior';
            if ($validated['is_4ps']) $statuses[] = '4Ps';
            if ($validated['is_pwd']) $statuses[] = 'PWD';

            $statusText = !empty($statuses) ? ' (' . implode(', ', $statuses) . ')' : '';

            // Log the activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_RESIDENT',
                'description' => 'Created new resident: ' . $resident->first_name . ' ' . $resident->last_name .
                                ' (ID: ' . $resident->resident_id . ', Purok: ' . $resident->purok . ')' . $statusText,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->route('secretary.residents.index')
                ->with('success', 'Resident created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating resident: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resident.
     */
    public function show(Resident $resident)
    {
        return view('secretary.residents.show', compact('resident'));
    }

    /**
     * Show the form for editing the specified resident.
     */
    public function edit(Resident $resident)
    {
        return view('secretary.residents.edit', compact('resident'));
    }

    /**
     * Update the specified resident in storage.
     */
    public function update(Request $request, Resident $resident)
    {
        try {
            $validated = $request->validate([
                'resident_id' => 'required|string|max:20|unique:residents,resident_id,' . $resident->id,
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'birthdate' => 'required|date',
                'gender' => 'required|in:Male,Female',
                'civil_status' => 'required|in:Single,Married,Widowed,Divorced',
                'contact_number' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:100',
                'address' => 'required|string',
                'purok' => 'required|string|max:50',
                'household_number' => 'nullable|string|max:20',
                'is_voter' => 'sometimes|boolean',
                'is_4ps' => 'sometimes|boolean',
                'is_senior' => 'sometimes|boolean',
                'is_pwd' => 'sometimes|boolean',
                'pwd_id' => 'nullable|string|max:50',
                'disability_type' => 'nullable|string|max:100',
            ]);

            // Set checkbox values
            $validated['is_voter'] = $request->has('is_voter') ? 1 : 0;
            $validated['is_4ps'] = $request->has('is_4ps') ? 1 : 0;
            $validated['is_senior'] = $request->has('is_senior') ? 1 : 0;
            $validated['is_pwd'] = $request->has('is_pwd') ? 1 : 0;

            // Remove PWD fields if not PWD
            if (!$validated['is_pwd']) {
                $validated['pwd_id'] = null;
                $validated['disability_type'] = null;
            }

            // Store old values for comparison
            $oldData = [
                'first_name' => $resident->first_name,
                'last_name' => $resident->last_name,
                'purok' => $resident->purok,
                'is_voter' => $resident->is_voter,
                'is_senior' => $resident->is_senior,
                'is_4ps' => $resident->is_4ps,
                'is_pwd' => $resident->is_pwd,
            ];

            $resident->update($validated);

            // Build description of what changed
            $changes = [];
            if ($oldData['first_name'] !== $validated['first_name'] || $oldData['last_name'] !== $validated['last_name']) {
                $changes[] = 'name updated';
            }
            if ($oldData['purok'] !== $validated['purok']) {
                $changes[] = 'purok changed from ' . $oldData['purok'] . ' to ' . $validated['purok'];
            }

            $statusChanges = [];
            if ($oldData['is_voter'] != $validated['is_voter']) {
                $statusChanges[] = $validated['is_voter'] ? 'added as voter' : 'removed as voter';
            }
            if ($oldData['is_senior'] != $validated['is_senior']) {
                $statusChanges[] = $validated['is_senior'] ? 'marked as senior' : 'unmarked as senior';
            }
            if ($oldData['is_4ps'] != $validated['is_4ps']) {
                $statusChanges[] = $validated['is_4ps'] ? 'marked as 4Ps' : 'unmarked as 4Ps';
            }
            if ($oldData['is_pwd'] != $validated['is_pwd']) {
                $statusChanges[] = $validated['is_pwd'] ? 'marked as PWD' : 'unmarked as PWD';
            }

            if (!empty($statusChanges)) {
                $changes[] = 'status: ' . implode(', ', $statusChanges);
            }

            $description = 'Updated resident: ' . $resident->first_name . ' ' . $resident->last_name .
                          ' (ID: ' . $resident->resident_id . ')';

            if (!empty($changes)) {
                $description .= ' - ' . implode('; ', $changes);
            }

            // Log the activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_RESIDENT',
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->route('secretary.residents.index')
                ->with('success', 'Resident updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating resident: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resident from storage.
     */
    public function destroy(Request $request, Resident $resident)  // Added Request parameter
    {
        try {
            $residentName = $resident->first_name . ' ' . $resident->last_name;
            $residentId = $resident->resident_id;
            $purok = $resident->purok;

            // Log before deletion
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_RESIDENT',
                'description' => 'Deleted resident: ' . $residentName .
                                ' (ID: ' . $residentId . ', Purok: ' . $purok . ')',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $resident->delete();

            return redirect()->route('secretary.residents.index')
                ->with('success', 'Resident deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting resident: ' . $e->getMessage());
        }
    }
}
