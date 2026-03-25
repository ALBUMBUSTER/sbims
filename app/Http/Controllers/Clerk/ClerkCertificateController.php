<?php

namespace App\Http\Controllers\Clerk;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Resident;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
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
        $this->isClerk();

        $query = Certificate::with('resident');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('certificate_id', 'like', "%{$search}%")
                  ->orWhereHas('resident', function($r) use ($search) {
                      $r->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('certificate_type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $certificates = $query->latest()->paginate(15)->withQueryString();

        $certificateTypes = Certificate::distinct('certificate_type')->pluck('certificate_type');
        $statuses = ['Pending', 'Approved', 'Released', 'Rejected'];

        return view('clerk.certificates.index', compact('certificates', 'certificateTypes', 'statuses'));
    }

    /**
     * Show form for creating a new certificate
     */
    public function create()
    {
        $this->isClerk();

        $residents = Resident::orderBy('last_name')->get();
        $certificateTypes = [
            'Clearance',
            'Indigency',
            'Residency'
        ];

        return view('clerk.certificates.create', compact('residents', 'certificateTypes'));
    }

    /**
     * Store a newly created certificate
     */
    public function store(Request $request)
    {
        $this->isClerk();

        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'certificate_type' => 'required|string',
            'purpose' => 'required|string',
            'transaction_fee' => 'nullable|numeric|min:0|max:999999.99'
        ]);

        try {
            DB::beginTransaction();

            $year = date('Y');
            $month = date('m');
            $prefix = 'CERT';

            $lastCertificate = Certificate::where('certificate_id', 'like', "{$prefix}-{$year}{$month}-%")
                ->orderBy('certificate_id', 'desc')
                ->first();

            if ($lastCertificate) {
                $lastNumber = intval(substr($lastCertificate->certificate_id, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            $certificateNumber = "{$prefix}-{$year}{$month}-{$newNumber}";

            $resident = Resident::find($request->resident_id);
            $residentName = $resident ? $resident->first_name . ' ' . $resident->last_name : 'Unknown';

            $certificate = Certificate::create([
                'resident_id' => $request->resident_id,
                'certificate_id' => $certificateNumber,
                'certificate_type' => $request->certificate_type,
                'purpose' => $request->purpose,
                'transaction_fee' => $request->transaction_fee,
                'status' => 'Pending',
                'issued_by' => Auth::id(),
            ]);

            // ========== REAL-TIME NOTIFICATIONS ==========
            $currentUserId = Auth::id();

            // Notify captains (exclude current user)
            NotificationHelper::toCaptainsExceptCurrent(
                $currentUserId,
                'New Certificate Request',
                $certificate->certificate_type . ' certificate requested for ' . $residentName,
                'info',
                route('captain.certificates.show', $certificate->id)
            );

            // Notify secretaries (exclude current user)
            NotificationHelper::toSecretariesExceptCurrent(
                $currentUserId,
                'New Certificate Request',
                'Certificate #' . $certificate->certificate_id . ' requested for ' . $residentName,
                'info',
                route('secretary.certificates.show', $certificate->id)
            );

            // Notify admins (exclude current user)
            NotificationHelper::toAdminsExceptCurrent(
                $currentUserId,
                'New Certificate Request',
                $certificate->certificate_type . ' certificate requested by ' . Auth::user()->name,
                'info',
                route('secretary.certificates.show', $certificate->id)
            );
            // ========== END NOTIFICATIONS ==========

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_CERTIFICATE',
                'description' => "Created certificate request #{$certificateNumber} for resident: {$residentName}",
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
        $this->isClerk();

        $certificate->load('resident');
        return view('clerk.certificates.show', compact('certificate'));
    }

    /**
     * Print certificate
     */
    public function print(Certificate $certificate)
    {
        $this->isClerk();

        $certificate->load('resident');
        return view('clerk.certificates.print', compact('certificate'));
    }

    /**
     * Update certificate status to Released
     */
    public function release(Request $request, Certificate $certificate)
    {
        $this->isClerk();

        $oldStatus = $certificate->status;
        $resident = $certificate->resident;
        $residentName = $resident ? $resident->first_name . ' ' . $resident->last_name : 'Unknown';

        $certificate->update([
            'status' => 'Released',
            'released_at' => now(),
            'issued_date' => now(),
            'issued_by' => Auth::id()
        ]);

        // ========== REAL-TIME NOTIFICATIONS ==========
        $currentUserId = Auth::id();

        // Notify captains (exclude current user)
        NotificationHelper::toCaptainsExceptCurrent(
            $currentUserId,
            'Certificate Released',
            'Certificate #' . $certificate->certificate_id . ' for ' . $residentName . ' has been released',
            'success',
            route('captain.certificates.show', $certificate->id)
        );

        // Notify secretaries (exclude current user)
        NotificationHelper::toSecretariesExceptCurrent(
            $currentUserId,
            'Certificate Released',
            'Certificate #' . $certificate->certificate_id . ' for ' . $residentName . ' has been released',
            'success',
            route('secretary.certificates.show', $certificate->id)
        );

        // Notify admins (exclude current user)
        NotificationHelper::toAdminsExceptCurrent(
            $currentUserId,
            'Certificate Released',
            'Certificate #' . $certificate->certificate_id . ' for ' . $residentName . ' has been released',
            'success',
            route('secretary.certificates.show', $certificate->id)
        );
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'RELEASE_CERTIFICATE',
            'description' => 'Released certificate ' . $certificate->certificate_id . ' for ' . $residentName,
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('clerk.certificates.index')
            ->with('success', 'Certificate released successfully.');
    }
}
