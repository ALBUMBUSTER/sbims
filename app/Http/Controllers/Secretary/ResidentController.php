<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;


class ResidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ==================== HELPER METHODS ==================== */
    /**
     * Check if current user is a clerk (role_id = 4)
     */
    private function isClerk()
    {
        return Auth::user()->role_id == 4;
    }

    /**
     * Check if current user is a secretary or admin (allowed full access)
     */
    private function isSecretaryOrAdmin()
    {
        $role = Auth::user()->role_id;
        return $role == 1 || $role == 3; // 1 = Admin, 3 = Secretary
    }

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
        } elseif (in_array($gender, ['o', 'other', 'prefer not to say'])) {
            return 'Other';
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
        // Clerks cannot update residents
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
        // Clerks cannot delete residents
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.index')
                ->with('error', 'You do not have permission to delete residents.');
        }

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

    /* ==================== SMART IMPORT FUNCTIONS ==================== */

    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('secretary.residents.import');
    }

    /**
     * Upload and parse CSV file for smart import
     */
    public function uploadImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240'
        ]);

        $file = $request->file('import_file');
        $path = $file->getRealPath();

        // Parse CSV file
        $data = [];
        $headers = [];

        if (($handle = fopen($path, 'r')) !== FALSE) {
            // Get headers (first row)
            $headers = fgetcsv($handle, 1000, ',');

            // Clean headers (remove BOM, trim, lowercase)
            $headers = array_map(function($header) {
                // Remove UTF-8 BOM and trim
                $header = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header);
                $header = trim($header);
                return $header;
            }, $headers);

            // Get sample data (next 5 rows for preview)
            $sampleData = [];
            $rowCount = 0;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE && $rowCount < 5) {
                // Ensure row has same number of columns as headers
                if (count($row) >= count($headers)) {
                    $sampleData[] = array_slice($row, 0, count($headers));
                } else {
                    // Pad with empty values
                    $row = array_pad($row, count($headers), '');
                    $sampleData[] = $row;
                }
                $rowCount++;
            }
            fclose($handle);
        }

        // Detect potential column matches
        $suggestedMapping = $this->detectColumnMapping($headers);

        // Store file path in session for later processing
        $filePath = $file->store('temp_imports');

        return view('secretary.residents.import-map', compact(
            'headers',
            'sampleData',
            'suggestedMapping',
            'filePath'
        ));
    }

    /**
     * Detect column mapping based on header names
     */
    private function detectColumnMapping($headers)
    {
        $mapping = [];

        // Define possible field names and their variations
        $fieldPatterns = [
            'first_name' => ['first name', 'firstname', 'given name', 'fname', 'first', 'given'],
            'last_name' => ['last name', 'lastname', 'surname', 'family name', 'lname', 'last'],
            'middle_name' => ['middle name', 'middlename', 'middle initial', 'mname', 'middle'],
            'suffix' => ['suffix', 'name suffix', 'extension', 'ext'],
            'birthdate' => ['birthdate', 'birth date', 'date of birth', 'dob', 'birthday', 'birth'],
            'gender' => ['gender', 'sex','male','female','other','m','f','o'],
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
                    // Check if pattern is in header
                    if (strpos($headerLower, $pattern) !== false) {
                        $score = strlen($pattern); // Longer matches score higher
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestMatch = $field;
                        }
                    }

                    // Check for exact match
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

    /**
     * Process column mapping from user input
     */
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

        // Parse file and preview data with mapping
        $previewData = $this->parseFileWithMapping($filePath, $mapping, $hasHeader);

        return view('secretary.residents.import-preview', [
            'file_path' => $request->file_path,
            'mapping' => $mapping,
            'has_header' => $hasHeader,
            'preview' => $previewData,
            'stats' => $this->calculateImportStats($previewData)
        ]);
    }

    /**
     * Parse file with given mapping and return preview data
     */
    private function parseFileWithMapping($filePath, $mapping, $hasHeader)
    {
        $data = [
            'headers' => [],
            'rows' => [],
            'errors' => []
        ];

        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            $rowNumber = 0;

            // Skip header if exists
            if ($hasHeader) {
                $data['headers'] = fgetcsv($handle, 1000, ',');
                $rowNumber++;
            }

            // Process next 10 rows for preview
            $previewRows = 0;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE && $previewRows < 10) {
                $mappedRow = [];
                $errors = [];

                foreach ($mapping as $index => $field) {
                    if ($field !== 'skip' && isset($row[$index])) {
                        $value = trim($row[$index]);

                        // Transform value based on field type
                        $transformedValue = $this->transformImportValue($field, $value);

                        // Validate based on field type
                        $validation = $this->validateImportField($field, $transformedValue, $rowNumber);
                        if ($validation !== true) {
                            $errors[$field] = $validation;
                        }

                        $mappedRow[$field] = $transformedValue;
                    }
                }

                // Check for duplicates
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

    /**
     * Transform import value based on field type
     */
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
                return in_array($value, ['yes', 'y', '1', 'true', 'on', 'voter', 'senior', 'pwd', '4ps']) ? 1 : 0;

            default:
                return $value;
        }
    }

    /**
     * Validate import field
     */
    private function validateImportField($field, $value, $rowNumber)
    {
        if (empty($value)) {
            // Required fields check
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

    /**
     * Check for duplicate resident
     */
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

    /**
     * Calculate import statistics
     */
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

    /**
     * Confirm and process import
     */
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
                // Skip header if exists
                if ($hasHeader) {
                    fgetcsv($handle, 1000, ',');
                }

                $rowNumber = $hasHeader ? 2 : 1;

                while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $data = [];

                    // Map columns based on user mapping
                    foreach ($mapping as $index => $field) {
                        if ($field !== 'skip' && isset($row[$index])) {
                            $value = trim($row[$index]);
                            $data[$field] = $this->transformImportValue($field, $value);
                        }
                    }

                    // Validate required fields
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
                        // Check for duplicate
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
                            // Generate resident_id if not provided
                            if (empty($data['resident_id'])) {
                                $data['resident_id'] = $this->generateResidentId();
                            }

                            // Set default values for missing fields
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

            // Log activity
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

            // Clean up temp file
            @unlink($filePath);

            // Prepare response message
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

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'first_name', 'last_name', 'middle_name', 'suffix',
            'birthdate', 'gender', 'civil_status', 'purok',
            'contact_number', 'email', 'address', 'household_number',
            'is_voter', 'is_senior', 'is_pwd', 'is_4ps'
        ];

        $filename = "resident_import_template.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add headers
        fputcsv($handle, $headers);

        // Add sample row
        fputcsv($handle, [
            'Juan', 'Dela Cruz', 'Santos', '',
            '1990-01-15', 'Male', 'Married', '1',
            '09123456789', 'juan@email.com', 'Purok 1, Barangay Libertad', '101',
            'Yes', 'No', 'No', 'No'
        ]);

        fclose($handle);
        exit;
    }

    /* ==================== ARCHIVE FUNCTIONS ==================== */

    /**
     * Archive the specified resident (soft delete)
     */
    public function archive(Request $request, Resident $resident)
    {
        // Clerks cannot archive residents
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.index')
                ->with('error', 'You do not have permission to archive residents.');
        }
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
        // Clerks cannot restore residents
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

    /**
     * Permanently delete an archived resident
     */
    public function forceDelete(Request $request, $id)
    {
        // Clerks cannot permanently delete residents
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.archived')
                ->with('error', 'You do not have permission to permanently delete residents.');
        }
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
        // Clerks cannot view archived residents
        if ($this->isClerk()) {
            return redirect()->route('secretary.residents.index')
                ->with('error', 'You do not have permission to view archived residents.');
        }
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
