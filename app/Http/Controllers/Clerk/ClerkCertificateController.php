<?php
// app/Http/Controllers/Clerk/ClerkCertificateController.php

namespace App\Http\Controllers\Clerk;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Resident;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClerkCertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if user is clerk
     */
    private function isClerk()
    {
        if (Auth::user()->role_id != 4) {
            abort(403, 'Unauthorized access. This area is for Clerk only.');
        }
    }

    /**
     * Display a listing of certificates
     */
    public function index(Request $request)
    {
        $this->isClerk(); // Role check

        $query = Certificate::with('resident');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhereHas('resident', function($r) use ($search) {
                      $r->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by certificate type
        if ($request->filled('type')) {
            $query->where('certificate_type', $request->type);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $certificates = $query->latest()->paginate(15)->withQueryString();

        // Get unique certificate types for filter
        $certificateTypes = Certificate::distinct('certificate_type')->pluck('certificate_type');
        $statuses = ['Pending', 'Processing', 'Approved', 'Released', 'Rejected'];

        return view('clerk.certificates.index', compact('certificates', 'certificateTypes', 'statuses'));
    }

    /**
     * Show form for creating a new certificate
     */
    public function create()
    {
        $this->isClerk(); // Role check

        $residents = Resident::orderBy('last_name')->get();
        $certificateTypes = [
            'Barangay Clearance',
            'Certificate of Indigency',
            'Certificate of Residency',
            'Certificate of Good Moral Character',
            'Barangay Business Clearance',
            'Certificate of Cohabitation',
            'First Time Jobseeker Certificate'
        ];

        return view('clerk.certificates.create', compact('residents', 'certificateTypes'));
    }

    /**
     * Store a newly created certificate
     */
    public function store(Request $request)
    {
        $this->isClerk(); // Role check

        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'certificate_type' => 'required|string',
            'purpose' => 'required|string',
            'or_number' => 'nullable|string',
            'amount_paid' => 'nullable|numeric'
        ]);

        try {
            DB::beginTransaction();

            // Generate certificate number
            $year = date('Y');
            $month = date('m');
            $lastCertificate = Certificate::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $certificateNumber = sprintf('CERT-%s%s-%04d', $year, $month, $lastCertificate + 1);

            $certificate = Certificate::create([
                'resident_id' => $request->resident_id,
                'certificate_number' => $certificateNumber,
                'certificate_type' => $request->certificate_type,
                'purpose' => $request->purpose,
                'status' => 'Pending',
                'or_number' => $request->or_number,
                'amount_paid' => $request->amount_paid,
                'requested_by' => Auth::id(),
                'requested_at' => now()
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Create Certificate',
                'description' => "Created certificate request #{$certificateNumber} for resident ID: {$request->resident_id}",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return redirect()->route('clerk.certificates.index')
                ->with('success', 'Certificate request created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating certificate: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified certificate
     */
    public function show(Certificate $certificate)
    {
        $this->isClerk(); // Role check

        $certificate->load('resident');
        return view('clerk.certificates.show', compact('certificate'));
    }

    /**
     * Print certificate
     */
    public function print(Certificate $certificate)
    {
        $this->isClerk(); // Role check

        $certificate->load('resident');
        return view('clerk.certificates.print', compact('certificate'));
    }
}
