<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;

class ResidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ==================== HELPER METHODS ==================== */

    /**
     * Generate a unique resident ID
     * Format: RES-YYYYMM-XXXX (e.g., RES-202502-0001)
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

        return 'RES-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a unique PWD ID
     * Format: PWD-YYYY-XXXXX (e.g., PWD-2024-00001)
     */
    public function generatePwdId()
    {
        $year = date('Y');

        // Get the latest PWD ID for this year
        $latestPwd = Resident::where('pwd_id', 'LIKE', "PWD-{$year}-%")
                             ->where('is_pwd', true)
                             ->orderBy('pwd_id', 'desc')
                             ->first();

        if ($latestPwd && $latestPwd->pwd_id) {
            $parts = explode('-', $latestPwd->pwd_id);
            $lastNumber = isset($parts[2]) ? intval($parts[2]) : 0;
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        $pwdId = 'PWD-' . $year . '-' . $newNumber;
        return response()->json(['id' => $pwdId]);
    }

    /**
     * Format date for database (handles multiple input formats)
     */
    private function formatDateForDB($date)
    {
        if (empty($date)) return null;

        $date = trim($date);
        $formats = [
            'Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d', 'Y.m.d'
        ];

        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) == $date) {
                return $d->format('Y-m-d');
            }
        }

        // Try strtotime as last resort
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Format gender (handles variations like M, Male, Lalaki)
     */
    private function formatGender($gender)
    {
        $gender = strtolower(trim($gender));

        if (in_array($gender, ['m', 'male', 'lalaki'])) {
            return 'Male';
        } elseif (in_array($gender, ['f', 'female', 'babae'])) {
            return 'Female';
        }

        return ucfirst($gender);
    }

    /**
     * Format civil status (handles variations)
     */
    private function formatCivilStatus($status)
    {
        if (empty($status)) return 'Single';

        $status = strtolower(trim($status));
        $statusMap = [
            'single' => 'Single',
            'married' => 'Married',
            'widowed' => 'Widowed',
            'divorced' => 'Divorced',
            'separated' => 'Divorced',
        ];

        return $statusMap[$status] ?? 'Single';
    }

    /* ==================== CRUD OPERATIONS ==================== */

    /**
     * Display a listing of residents with search and filter functionality.
     */
    public function index(Request $request)
    {
        $query = Resident::active();

        // Search by multiple fields
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query = Resident::query(); // This automatically excludes soft-deleted records
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('middle_name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%")
                  ->orWhere('resident_id', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status (voter, senior, pwd, 4ps)
        if ($request->has('filter') && !empty($request->filter) && $request->filter != 'all') {
            switch ($request->filter) {
                case 'voter':
                    $query->where('is_voter', 1);
                    break;
                case 'senior':
                    $query->where('is_senior', 1);
                    break;
                case 'pwd':
                    $query->where('is_pwd', 1);
                    break;
                case '4ps':
                    $query->where('is_4ps', 1);
                    break;
            }
        }

        // Filter by civil status
        if ($request->has('civil_filter') && !empty($request->civil_filter) && $request->civil_filter != 'all') {
            $civilStatus = ucfirst(strtolower($request->civil_filter));
            $query->where('civil_status', $civilStatus);
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
        $pwdResponse = $this->generatePwdId();
        $generatedPwdId = $pwdResponse->getData()->id;

        return view('secretary.residents.create', compact('generatedId', 'generatedPwdId'));
    }

    /**
     * Generate ID for AJAX request (used in create form)
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
                'pwd_id' => 'nullable|required_if:is_pwd,true|string|max:50|unique:residents,pwd_id',
                'disability_type' => 'nullable|required_if:is_pwd,true|string|max:100',
            ]);

            // Convert checkbox values to integers
            $validated['is_voter'] = $request->has('is_voter') ? 1 : 0;
            $validated['is_4ps'] = $request->has('is_4ps') ? 1 : 0;
            $validated['is_senior'] = $request->has('is_senior') ? 1 : 0;
            $validated['is_pwd'] = $request->has('is_pwd') ? 1 : 0;

            // Clear PWD fields if not PWD
            if (!$validated['is_pwd']) {
                $validated['pwd_id'] = null;
                $validated['disability_type'] = null;
            }

            $resident = Resident::create($validated);

            // Build status description for activity log
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
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating resident: ' . $e->getMessage())->withInput();
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

            // Convert checkbox values to integers
            $validated['is_voter'] = $request->has('is_voter') ? 1 : 0;
            $validated['is_4ps'] = $request->has('is_4ps') ? 1 : 0;
            $validated['is_senior'] = $request->has('is_senior') ? 1 : 0;
            $validated['is_pwd'] = $request->has('is_pwd') ? 1 : 0;

            // Clear PWD fields if not PWD
            if (!$validated['is_pwd']) {
                $validated['pwd_id'] = null;
                $validated['disability_type'] = null;
            }

            // Store old values for comparison (for activity log)
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

            // Build description of what changed for activity log
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
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating resident: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resident from storage.
     */
    public function destroy(Request $request, Resident $resident)
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
            return redirect()->back()->with('error', 'Error deleting resident: ' . $e->getMessage());
        }
    }

    /* ==================== IMPORT FUNCTIONS ==================== */

    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('secretary.residents.import');
    }

    /**
     * Import residents from CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240' // max 10MB
        ]);

        $file = $request->file('csv_file');
        $importCount = 0;
        $skippedCount = 0;
        $duplicateCount = 0;
        $failedRows = [];
        $duplicateRows = [];

        try {
            $handle = fopen($file->getRealPath(), 'r');
            fgetcsv($handle); // Skip header row

            $rowNumber = 1;
            $importedResidents = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($data))) {
                    $skippedCount++;
                    $failedRows[] = "Row $rowNumber: Empty row";
                    continue;
                }

                // Prepare resident data
                $residentData = $this->prepareImportData($data, $rowNumber);

                // Validate the data
                $validator = $this->validateImportRow($residentData);
                if ($validator->fails()) {
                    $skippedCount++;
                    $failedRows[] = "Row $rowNumber: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Check for duplicate resident
                $existing = Resident::where('first_name', $residentData['first_name'])
                    ->where('last_name', $residentData['last_name'])
                    ->where('birthdate', $residentData['birthdate'])
                    ->first();

                if ($existing) {
                    $skippedCount++;
                    $duplicateCount++;
                    $duplicateRows[] = "Row $rowNumber: " . $residentData['first_name'] . ' ' . $residentData['last_name'] .
                                      ' (Already exists as ID: ' . $existing->resident_id . ')';
                    continue;
                }

                // Save to database
                try {
                    $resident = Resident::create($residentData);
                    $importCount++;
                    $importedResidents[] = $resident->first_name . ' ' . $resident->last_name;
                } catch (\Exception $e) {
                    $skippedCount++;
                    $failedRows[] = "Row $rowNumber: Database error - " . $e->getMessage();
                    Log::error('Import error: ' . $e->getMessage());
                }
            }

            fclose($handle);

            // Log the import activity if any records were imported
            if ($importCount > 0) {
                $importedList = implode(', ', array_slice($importedResidents, 0, 5));
                $moreText = $importCount > 5 ? " and " . ($importCount - 5) . " more" : "";

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'IMPORT_RESIDENTS',
                    'description' => "Imported $importCount resident records" .
                                    ($importCount > 0 ? " (e.g., " . $importedList . $moreText . ")" : ""),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                // Prepare success message
                $message = "Successfully imported $importCount residents.";
                if ($duplicateCount > 0) {
                    $message .= " Skipped $duplicateCount duplicate records.";
                }
                if ($skippedCount - $duplicateCount > 0) {
                    $message .= " Skipped " . ($skippedCount - $duplicateCount) . " invalid records.";
                }

                return redirect()->route('secretary.residents.import')
                    ->with('import_success', true)
                    ->with('import_count', $importCount)
                    ->with('skipped_count', $skippedCount)
                    ->with('duplicate_count', $duplicateCount)
                    ->with('failed_rows', $failedRows)
                    ->with('duplicate_rows', $duplicateRows)
                    ->with('success', $message);

            } else {
                // No records imported
                if ($duplicateCount > 0 && $skippedCount == $duplicateCount) {
                    return redirect()->route('secretary.residents.import')
                        ->with('error', 'No new records were imported. All ' . $duplicateCount . ' records were duplicates.')
                        ->with('duplicate_count', $duplicateCount)
                        ->with('duplicate_rows', $duplicateRows);
                } else {
                    return redirect()->route('secretary.residents.import')
                        ->with('error', 'No records were imported. Please check your CSV format.')
                        ->with('failed_rows', $failedRows);
                }
            }

        } catch (\Exception $e) {
            Log::error('Import exception: ' . $e->getMessage());
            return redirect()->route('secretary.residents.import')
                ->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    /**
     * Prepare resident data from CSV row
     */
    private function prepareImportData($data, $rowNumber)
    {
        return [
            'resident_id' => $this->generateResidentId(),
            'first_name' => trim($data[0] ?? ''),
            'last_name' => trim($data[1] ?? ''),
            'middle_name' => trim($data[2] ?? null) ?: null,
            'birthdate' => $this->formatDateForDB(trim($data[3] ?? '')),
            'gender' => $this->formatGender(trim($data[4] ?? '')),
            'contact_number' => trim($data[5] ?? '') ?: null,
            'email' => trim($data[6] ?? '') ?: null,
            'address' => trim($data[7] ?? '') ?: null,
            'purok' => trim($data[8] ?? '') ?: null,
            'household_number' => trim($data[9] ?? '') ?: null,
            'is_voter' => strtolower(trim($data[10] ?? 'no')) === 'yes' ? 1 : 0,
            'is_senior' => strtolower(trim($data[11] ?? 'no')) === 'yes' ? 1 : 0,
            'is_pwd' => strtolower(trim($data[12] ?? 'no')) === 'yes' ? 1 : 0,
            'is_4ps' => strtolower(trim($data[13] ?? 'no')) === 'yes' ? 1 : 0,
            'civil_status' => $this->formatCivilStatus(trim($data[14] ?? '')),
        ];
    }

    /**
     * Validate import row data
     */
    private function validateImportRow($data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'birthdate' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'civil_status' => 'nullable|in:Single,Married,Widowed,Divorced',
            'contact_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'purok' => 'nullable|string|max:50',
            'household_number' => 'nullable|string|max:20',
            'is_voter' => 'boolean',
            'is_senior' => 'boolean',
            'is_pwd' => 'boolean',
            'is_4ps' => 'boolean',
        ]);
    }
    /* ==================== ARCHIVE FUNCTIONS ==================== */

/**
 * Archive the specified resident (soft delete)
 */
public function archive(Request $request, Resident $resident)
{
    try {
        $residentName = $resident->first_name . ' ' . $resident->last_name;
        $residentId = $resident->resident_id;

        // Soft delete the resident
        $resident->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'ARCHIVE_RESIDENT',
            'description' => 'Archived resident: ' . $residentName . ' (ID: ' . $residentId . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.residents.index')
            ->with('success', 'Resident archived successfully!');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error archiving resident: ' . $e->getMessage());
    }
}

/**
 * Restore an archived resident
 */
public function restore(Request $request, $id)
{
    try {
        $resident = Resident::withTrashed()->findOrFail($id);
        $residentName = $resident->first_name . ' ' . $resident->last_name;

        $resident->restore();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'RESTORE_RESIDENT',
            'description' => 'Restored resident: ' . $residentName . ' (ID: ' . $resident->resident_id . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.residents.archived')
            ->with('success', 'Resident restored successfully!');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error restoring resident: ' . $e->getMessage());
    }
}

/**
 * Permanently delete an archived resident
 */
public function forceDelete(Request $request, $id)
{
    try {
        $resident = Resident::withTrashed()->findOrFail($id);
        $residentName = $resident->first_name . ' ' . $resident->last_name;

        // Force delete permanently
        $resident->forceDelete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'FORCE_DELETE_RESIDENT',
            'description' => 'Permanently deleted resident: ' . $residentName,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.residents.archived')
            ->with('success', 'Resident permanently deleted!');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error deleting resident: ' . $e->getMessage());
    }
}

/**
 * Display archived residents
 */
public function archived(Request $request)
{
    $query = Resident::onlyTrashed(); // Use onlyTrashed() instead of archived() scope

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

    $archivedResidents = $query->orderBy('deleted_at', 'desc')->paginate(15);

    return view('secretary.residents.archived', compact('archivedResidents'));
}
}
