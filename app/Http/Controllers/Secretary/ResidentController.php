<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\FamilyRelationship;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ==================== HELPER METHODS ==================== */

    private function isClerk()
    {
        return Auth::user()->role_id == 4;
    }

    private function isSecretaryOrAdmin()
    {
        $role = Auth::user()->role_id;
        return $role == 1 || $role == 3;
    }

    private function generateResidentId()
    {
        $year = date('Y');
        $month = date('m');

        $latest = Resident::where('resident_id', 'LIKE', "RES-{$year}{$month}-%")
                          ->orderBy('resident_id', 'desc')
                          ->first();

        if ($latest) {
            $parts = explode('-', $latest->resident_id);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }

        return 'RES-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function generatePwdId()
    {
        $year = date('Y');

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

        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    private function formatGender($gender)
    {
        $gender = strtolower(trim($gender));

        if (in_array($gender, ['m', 'male', 'lalaki'])) {
            return 'Male';
        } elseif (in_array($gender, ['f', 'female', 'babae'])) {
            return 'Female';
        } elseif (in_array($gender, ['o', 'other', 'prefer not to say'])) {
            return 'Other';
        }

        return ucfirst($gender);
    }

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

    public function index(Request $request)
    {
        $query = Resident::active();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query = Resident::query();
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('middle_name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%")
                  ->orWhere('resident_id', 'LIKE', "%{$search}%");
            });
        }

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

        if ($request->has('civil_filter') && !empty($request->civil_filter) && $request->civil_filter != 'all') {
            $civilStatus = ucfirst(strtolower($request->civil_filter));
            $query->where('civil_status', $civilStatus);
        }

        $residents = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('secretary.residents.index', compact('residents'));
    }

    public function create()
    {
        $generatedId = $this->generateResidentId();
        $pwdResponse = $this->generatePwdId();
        $generatedPwdId = $pwdResponse->getData()->id;

        return view('secretary.residents.create', compact('generatedId', 'generatedPwdId'));
    }

    public function generateId()
    {
        return response()->json(['id' => $this->generateResidentId()]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'resident_id' => 'required|string|max:20|unique:residents',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'birthdate' => 'required|date',
                'gender' => 'required|in:Male,Female,Other',
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
                'pwd_id' => 'nullable|required_if:is_pwd,true|string|max:50',
                'disability_type' => 'nullable|required_if:is_pwd,true|string|max:100',
                'spouse_name' => 'nullable|string|max:255',
                'children' => 'nullable|array',
                'children.*.name' => 'nullable|string|max:255',
                'children.*.birthdate' => 'nullable|date',
                'children.*.gender' => 'nullable|in:Male,Female',
            ]);
            // CHECK FOR DUPLICATE RESIDENT
            $duplicate = Resident::checkDuplicate($validated);

            if ($duplicate) {
            $duplicateName = $duplicate->full_name;
            $duplicateId = $duplicate->resident_id;
            $duplicateAge = $duplicate->age;

            $errorMessage = "A resident with the same name and birthdate already exists.\n\n";
            $errorMessage .= "Existing Resident:\n";
            $errorMessage .= "• Name: {$duplicateName}\n";
            $errorMessage .= "• Resident ID: {$duplicateId}\n";
            $errorMessage .= "• Age: {$duplicateAge} years old\n";
            $errorMessage .= "• Purok: {$duplicate->purok}\n\n";
            $errorMessage .= "Please check if this is a duplicate entry or use a different name.";

            return redirect()->back()
                ->withInput()
                ->with('duplicate_error', $errorMessage)
                ->with('duplicate_resident', $duplicate)
                ->with('error', 'Duplicate resident found!');
            }

            $validated['is_voter'] = $request->has('is_voter') ? 1 : 0;
            $validated['is_4ps'] = $request->has('is_4ps') ? 1 : 0;
            $validated['is_senior'] = $request->has('is_senior') ? 1 : 0;
            $validated['is_pwd'] = $request->has('is_pwd') ? 1 : 0;

            if (!$validated['is_pwd']) {
            $validated['pwd_id'] = null;
            $validated['disability_type'] = null;
            }

        $spouseName = $request->spouse_name ?? null;
        $children = $request->children ?? [];

        $resident = Resident::create($validated);

            // Save spouse relationship
            if ($validated['civil_status'] === 'Married' && !empty($spouseName)) {
                $resident->familyRelationships()->create([
                    'relationship_type' => 'spouse',
                    'full_name' => $spouseName,
                    'birthdate' => null,
                    'gender' => null,
                ]);
            }

            // Save children relationships
            foreach ($children as $child) {
                if (!empty($child['name'])) {
                    $resident->familyRelationships()->create([
                        'relationship_type' => 'child',
                        'full_name' => $child['name'],
                        'birthdate' => !empty($child['birthdate']) ? $child['birthdate'] : null,
                        'gender' => !empty($child['gender']) ? $child['gender'] : null,
                    ]);
                }
            }

            $statuses = [];
            if ($validated['is_voter']) $statuses[] = 'Voter';
            if ($validated['is_senior']) $statuses[] = 'Senior';
            if ($validated['is_4ps']) $statuses[] = '4Ps';
            if ($validated['is_pwd']) $statuses[] = 'PWD';
            $statusText = !empty($statuses) ? ' (' . implode(', ', $statuses) . ')' : '';

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

    public function show(Resident $resident)
    {
        $resident->load(['spouse', 'children']);
        return view('secretary.residents.show', compact('resident'));
    }

    public function edit(Resident $resident)
    {
        $resident->load(['spouse', 'children']);
        $spouse = $resident->spouse;
        $children = $resident->children;

        return view('secretary.residents.edit', compact('resident', 'spouse', 'children'));
    }

    public function update(Request $request, Resident $resident)
    {
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.show', $resident)
                ->with('error', 'You do not have permission to update residents.');
        }

        try {
            $validated = $request->validate([
                'resident_id' => 'required|string|max:20|unique:residents,resident_id,' . $resident->id,
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'birthdate' => 'required|date',
                'gender' => 'required|in:Male,Female,Other',
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
                'spouse_name' => 'nullable|string|max:255',
                'children' => 'nullable|array',
                'children.*.name' => 'nullable|string|max:255',
                'children.*.birthdate' => 'nullable|date',
                'children.*.gender' => 'nullable|in:Male,Female',
            ]);

            $validated['is_voter'] = $request->has('is_voter') ? 1 : 0;
            $validated['is_4ps'] = $request->has('is_4ps') ? 1 : 0;
            $validated['is_senior'] = $request->has('is_senior') ? 1 : 0;
            $validated['is_pwd'] = $request->has('is_pwd') ? 1 : 0;

            if (!$validated['is_pwd']) {
                $validated['pwd_id'] = null;
                $validated['disability_type'] = null;
            }

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

            // Update spouse relationship
            if ($validated['civil_status'] === 'Married' && $request->has('spouse_name') && !empty($request->spouse_name)) {
                $spouse = $resident->spouse;
                if ($spouse) {
                    $spouse->update(['full_name' => $request->spouse_name]);
                } else {
                    $resident->familyRelationships()->create([
                        'relationship_type' => 'spouse',
                        'full_name' => $request->spouse_name,
                    ]);
                }
            } else {
                if ($resident->spouse) {
                    $resident->spouse->delete();
                }
            }

            // Update children relationships
            $resident->children()->delete();

            if ($request->has('children') && is_array($request->children)) {
                foreach ($request->children as $child) {
                    if (!empty($child['name'])) {
                        $resident->familyRelationships()->create([
                            'relationship_type' => 'child',
                            'full_name' => $child['name'],
                            'birthdate' => !empty($child['birthdate']) ? $child['birthdate'] : null,
                            'gender' => !empty($child['gender']) ? $child['gender'] : null,
                        ]);
                    }
                }
            }

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

    public function destroy(Request $request, Resident $resident)
    {
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.index')
                ->with('error', 'You do not have permission to delete residents.');
        }

        try {
            $residentName = $resident->first_name . ' ' . $resident->last_name;
            $residentId = $resident->resident_id;
            $purok = $resident->purok;

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

    public function archive(Request $request, Resident $resident)
    {
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.index')
                ->with('error', 'You do not have permission to archive residents.');
        }
        try {
            $residentName = $resident->first_name . ' ' . $resident->last_name;
            $residentId = $resident->resident_id;

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

    public function restore(Request $request, $id)
    {
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.archived')
                ->with('error', 'You do not have permission to restore residents.');
        }
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

    public function forceDelete(Request $request, $id)
    {
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.archived')
                ->with('error', 'You do not have permission to permanently delete residents.');
        }
        try {
            $resident = Resident::withTrashed()->findOrFail($id);
            $residentName = $resident->first_name . ' ' . $resident->last_name;

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

    public function archived(Request $request)
    {
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.index')
                ->with('error', 'You do not have permission to view archived residents.');
        }
        $query = Resident::onlyTrashed();

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

    // Import methods (keep your existing import methods here)
    public function showImportForm()
    {
        return view('secretary.residents.import');
    }

    public function uploadImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240'
        ]);

        $file = $request->file('import_file');
        $path = $file->getRealPath();

        $data = [];
        $headers = [];

        if (($handle = fopen($path, 'r')) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ',');

            $headers = array_map(function($header) {
                $header = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header);
                $header = trim($header);
                return $header;
            }, $headers);

            $sampleData = [];
            $rowCount = 0;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE && $rowCount < 5) {
                if (count($row) >= count($headers)) {
                    $sampleData[] = array_slice($row, 0, count($headers));
                } else {
                    $row = array_pad($row, count($headers), '');
                    $sampleData[] = $row;
                }
                $rowCount++;
            }
            fclose($handle);
        }

        $suggestedMapping = $this->detectColumnMapping($headers);
        $filePath = $file->store('temp_imports');

        return view('secretary.residents.import-map', compact(
            'headers',
            'sampleData',
            'suggestedMapping',
            'filePath'
        ));
    }

    private function detectColumnMapping($headers)
    {
        $mapping = [];

        $fieldPatterns = [
            'first_name' => ['first name', 'firstname', 'given name', 'fname', 'first', 'given'],
            'last_name' => ['last name', 'lastname', 'surname', 'family name', 'lname', 'last'],
            'middle_name' => ['middle name', 'middlename', 'middle initial', 'mname', 'middle'],
            'birthdate' => ['birthdate', 'birth date', 'date of birth', 'dob', 'birthday', 'birth'],
            'gender' => ['gender', 'sex'],
            'civil_status' => ['civil status', 'marital status', 'status', 'civil'],
            'purok' => ['purok', 'zone', 'area', 'sitio'],
            'contact_number' => ['contact number', 'contact', 'phone', 'mobile', 'telephone', 'cellphone', 'cp number'],
            'email' => ['email', 'email address', 'e-mail'],
            'address' => ['address', 'street address', 'home address', 'street'],
            'household_number' => ['household number', 'household', 'house no', 'household no'],
            'is_voter' => ['voter', 'registered voter', 'voter status', 'is voter'],
            'is_senior' => ['senior', 'senior citizen', 'is senior'],
            'is_pwd' => ['pwd', 'person with disability', 'disability', 'is pwd'],
            'is_4ps' => ['4ps', 'four ps', 'pantawid', 'is 4ps', '4p\'s'],
        ];

        foreach ($headers as $index => $header) {
            $headerLower = strtolower($header);
            $bestMatch = null;
            $bestScore = 0;

            foreach ($fieldPatterns as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if (strpos($headerLower, $pattern) !== false) {
                        $score = strlen($pattern);
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestMatch = $field;
                        }
                    }

                    if ($headerLower === $pattern) {
                        $bestScore = 100;
                        $bestMatch = $field;
                        break 2;
                    }
                }
            }

            $mapping[$index] = $bestMatch ?: 'skip';
        }

        return $mapping;
    }

    public function processMapping(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
            'has_header' => 'boolean',
        ]);

        $filePath = storage_path('app/' . $request->file_path);
        $mapping = $request->mapping;
        $hasHeader = $request->boolean('has_header', true);

        $previewData = $this->parseFileWithMapping($filePath, $mapping, $hasHeader);

        return view('secretary.residents.import-preview', [
            'file_path' => $request->file_path,
            'mapping' => $mapping,
            'has_header' => $hasHeader,
            'preview' => $previewData,
            'stats' => $this->calculateImportStats($previewData)
        ]);
    }

    private function parseFileWithMapping($filePath, $mapping, $hasHeader)
    {
        $data = [
            'headers' => [],
            'rows' => [],
            'errors' => []
        ];

        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            $rowNumber = 0;

            if ($hasHeader) {
                $data['headers'] = fgetcsv($handle, 1000, ',');
                $rowNumber++;
            }

            $previewRows = 0;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE && $previewRows < 10) {
                $mappedRow = [];
                $errors = [];

                foreach ($mapping as $index => $field) {
                    if ($field !== 'skip' && isset($row[$index])) {
                        $value = trim($row[$index]);
                        $transformedValue = $this->transformImportValue($field, $value);
                        $validation = $this->validateImportField($field, $transformedValue, $rowNumber);
                        if ($validation !== true) {
                            $errors[$field] = $validation;
                        }
                        $mappedRow[$field] = $transformedValue;
                    }
                }

                $duplicateCheck = $this->checkForDuplicate($mappedRow);
                if ($duplicateCheck) {
                    $errors['duplicate'] = $duplicateCheck;
                }

                $data['rows'][] = [
                    'data' => $mappedRow,
                    'errors' => $errors,
                    'is_valid' => empty($errors),
                    'row_number' => $rowNumber + 1
                ];

                $previewRows++;
                $rowNumber++;
            }
            fclose($handle);
        }

        return $data;
    }

    private function transformImportValue($field, $value)
    {
        if (empty($value)) {
            return null;
        }

        switch ($field) {
            case 'gender':
                return $this->formatGender($value);
            case 'civil_status':
                return $this->formatCivilStatus($value);
            case 'birthdate':
                return $this->formatDateForDB($value);
            case 'is_voter':
            case 'is_senior':
            case 'is_pwd':
            case 'is_4ps':
                $value = strtolower(trim($value));
                return in_array($value, ['yes', 'y', '1', 'true', 'on']) ? 1 : 0;
            default:
                return $value;
        }
    }

    private function validateImportField($field, $value, $rowNumber)
    {
        if (empty($value)) {
            $requiredFields = ['first_name', 'last_name', 'birthdate', 'gender'];
            if (in_array($field, $requiredFields)) {
                return ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
            return true;
        }

        switch ($field) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Invalid email format';
                }
                break;
            case 'birthdate':
                if (!$this->formatDateForDB($value)) {
                    return 'Invalid date format. Use YYYY-MM-DD, MM/DD/YYYY, or DD-MM-YYYY';
                }
                break;
            case 'gender':
                if (!in_array($value, ['Male', 'Female', 'Other'])) {
                    return 'Gender must be Male, Female, or Other';
                }
                break;
            case 'civil_status':
                if (!in_array($value, ['Single', 'Married', 'Widowed', 'Divorced'])) {
                    return 'Civil status must be Single, Married, Widowed, or Divorced';
                }
                break;
            case 'contact_number':
                if (!preg_match('/^[0-9+\-\s]{10,15}$/', $value)) {
                    return 'Invalid contact number format';
                }
                break;
        }

        return true;
    }

    private function checkForDuplicate($data)
    {
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['birthdate'])) {
            return null;
        }

        $existing = Resident::where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'])
            ->where('birthdate', $data['birthdate'])
            ->first();

        if ($existing) {
            return 'Duplicate record (already exists as ID: ' . $existing->resident_id . ')';
        }

        return null;
    }

    private function calculateImportStats($previewData)
    {
        $total = count($previewData['rows']);
        $valid = count(array_filter($previewData['rows'], function($row) {
            return $row['is_valid'];
        }));

        return [
            'total' => $total,
            'valid' => $valid,
            'invalid' => $total - $valid,
            'has_errors' => $total - $valid > 0
        ];
    }

    public function confirmImport(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
            'has_header' => 'boolean',
        ]);

        $filePath = storage_path('app/' . $request->file_path);
        $mapping = $request->mapping;
        $hasHeader = $request->boolean('has_header', true);

        DB::beginTransaction();

        try {
            $imported = 0;
            $skipped = 0;
            $duplicateCount = 0;
            $failedRows = [];
            $duplicateRows = [];
            $importedResidents = [];

            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                if ($hasHeader) {
                    fgetcsv($handle, 1000, ',');
                }

                $rowNumber = $hasHeader ? 2 : 1;

                while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $data = [];

                    foreach ($mapping as $index => $field) {
                        if ($field !== 'skip' && isset($row[$index])) {
                            $value = trim($row[$index]);
                            $data[$field] = $this->transformImportValue($field, $value);
                        }
                    }

                    $validator = Validator::make($data, [
                        'first_name' => 'required|string|max:50',
                        'last_name' => 'required|string|max:50',
                        'birthdate' => 'required|date',
                        'gender' => 'required|in:Male,Female,Other',
                        'email' => 'nullable|email|unique:residents,email',
                    ]);

                    if ($validator->fails()) {
                        $skipped++;
                        $failedRows[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    } else {
                        $existing = Resident::where('first_name', $data['first_name'])
                            ->where('last_name', $data['last_name'])
                            ->where('birthdate', $data['birthdate'])
                            ->first();

                        if ($existing) {
                            $skipped++;
                            $duplicateCount++;
                            $duplicateRows[] = "Row {$rowNumber}: " . $data['first_name'] . ' ' . $data['last_name'] .
                                              ' (Already exists as ID: ' . $existing->resident_id . ')';
                        } else {
                            if (empty($data['resident_id'])) {
                                $data['resident_id'] = $this->generateResidentId();
                            }

                            $data = array_merge([
                                'middle_name' => null,
                                'civil_status' => 'Single',
                                'address' => null,
                                'purok' => null,
                                'household_number' => null,
                                'contact_number' => null,
                                'email' => null,
                                'is_voter' => 0,
                                'is_senior' => 0,
                                'is_pwd' => 0,
                                'is_4ps' => 0,
                            ], $data);

                            Resident::create($data);
                            $imported++;
                            $importedResidents[] = $data['first_name'] . ' ' . $data['last_name'];
                        }
                    }

                    $rowNumber++;
                }
                fclose($handle);
            }

            if ($imported > 0) {
                $importedList = implode(', ', array_slice($importedResidents, 0, 5));
                $moreText = $imported > 5 ? " and " . ($imported - 5) . " more" : "";

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'IMPORT_RESIDENTS',
                    'description' => "Imported $imported resident records via smart import" .
                                    ($imported > 0 ? " (e.g., " . $importedList . $moreText . ")" : ""),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            DB::commit();

            @unlink($filePath);

            $message = "Successfully imported $imported residents.";
            if ($duplicateCount > 0) {
                $message .= " Skipped $duplicateCount duplicate records.";
            }
            if ($skipped - $duplicateCount > 0) {
                $message .= " Skipped " . ($skipped - $duplicateCount) . " invalid records.";
            }

            return redirect()->route('secretary.residents.index')
                ->with('import_success', true)
                ->with('import_count', $imported)
                ->with('skipped_count', $skipped)
                ->with('duplicate_count', $duplicateCount)
                ->with('failed_rows', $failedRows)
                ->with('duplicate_rows', $duplicateRows)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import exception: ' . $e->getMessage());
            return redirect()->route('secretary.residents.import')
                ->with('error', 'Error processing import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'first_name', 'last_name', 'middle_name',
            'birthdate', 'gender', 'civil_status', 'purok',
            'contact_number', 'email', 'address', 'household_number',
            'is_voter', 'is_senior', 'is_pwd', 'is_4ps'
        ];

        $filename = "resident_import_template.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, $headers);
        fputcsv($handle, [
            'Juan', 'Dela Cruz', 'Santos',
            '1990-01-15', 'Male', 'Married', '1',
            '09123456789', 'juan@email.com', 'Purok 1, Barangay Libertad', '101',
            'Yes', 'No', 'No', 'No'
        ]);

        fclose($handle);
        exit;
    }
    public function checkDuplicate(Request $request)
{
    $request->validate([
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'birthdate' => 'required|date',
        'middle_name' => 'nullable|string',
    ]);

    $duplicate = Resident::checkDuplicate($request->all());

    if ($duplicate) {
        return response()->json([
            'exists' => true,
            'resident' => [
                'full_name' => $duplicate->full_name,
                'resident_id' => $duplicate->resident_id,
                'age' => $duplicate->age,
                'purok' => $duplicate->purok,
                'address' => $duplicate->address,
            ]
        ]);
    }

    return response()->json(['exists' => false]);
}
}
