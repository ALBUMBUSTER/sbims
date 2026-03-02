<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Resident;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::with('resident');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('certificate_id', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('resident', function($rq) use ($search) {
                      $rq->where('first_name', 'like', "%{$search}%")
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

        $certificates = $query->orderBy('created_at', 'desc')->paginate(15);

        $counts = [
            'pending' => Certificate::where('status', 'Pending')->count(),
            'approved' => Certificate::where('status', 'Approved')->count(),
            'released' => Certificate::where('status', 'Released')->count(),
            'rejected' => Certificate::where('status', 'Rejected')->count(),
            'today' => Certificate::whereDate('created_at', today())->count(),
        ];

        return view('secretary.certificates.index', compact('certificates', 'counts'));
    }

    public function create()
    {
        $residents = Resident::orderBy('first_name')->get();
        return view('secretary.certificates.create', compact('residents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'certificate_type' => 'required|in:Clearance,Indigency,Residency',
            'purpose' => 'required|string',
        ]);

        $validated['certificate_id'] = $this->generateCertificateNumber();
        $validated['status'] = 'Pending';

        $certificate = Certificate::create($validated);

        $resident = Resident::find($request->resident_id);
        $residentName = $resident ? $resident->first_name . ' ' . $resident->last_name : 'Unknown';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_CERTIFICATE',
            'description' => 'Created ' . $request->certificate_type . ' certificate for ' . $residentName .
                            ' (Certificate #: ' . $certificate->certificate_id . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.certificates.index')
            ->with('success', 'Certificate created successfully.');
    }

    public function show(Certificate $certificate)
    {
        $certificate->load('resident', 'issuer', 'approver');
        return view('secretary.certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        $residents = Resident::orderBy('first_name')->get();
        return view('secretary.certificates.edit', compact('certificate', 'residents'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'certificate_type' => 'required|in:Clearance,Indigency,Residency',
            'purpose' => 'required|string',
            'status' => 'required|in:Pending,Approved,Released,Rejected',
            'rejection_reason' => 'nullable|required_if:status,Rejected|string',
        ]);

        $oldStatus = $certificate->status;

        // Handle status-specific fields
        if ($validated['status'] === 'Approved' && $oldStatus !== 'Approved') {
            $validated['approved_at'] = now();
            $validated['approved_by'] = Auth::id();
        }

        if ($validated['status'] === 'Released' && $oldStatus !== 'Released') {
            $validated['released_at'] = now();
            $validated['issued_date'] = now();
            $validated['issued_by'] = Auth::id();
        }

        if ($validated['status'] === 'Rejected') {
            $validated['rejected_at'] = now();
        }

        $certificate->update($validated);

        $resident = $certificate->resident;
        $residentName = $resident ? $resident->first_name . ' ' . $resident->last_name : 'Unknown';

        $description = 'Updated certificate ' . $certificate->certificate_id . ' for ' . $residentName;
        if ($oldStatus !== $validated['status']) {
            $description .= ' - Status changed from ' . $oldStatus . ' to ' . $validated['status'];
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_CERTIFICATE',
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.certificates.show', $certificate)
            ->with('success', 'Certificate updated successfully.');
    }

    public function process(Request $request, Certificate $certificate)
    {
        $request->validate([
            'status' => 'required|in:Approved,Released,Rejected',
            'rejection_reason' => 'nullable|required_if:status,Rejected|string',
        ]);

        $oldStatus = $certificate->status;
        $updateData = ['status' => $request->status];

        if ($request->status === 'Approved') {
            $updateData['approved_at'] = now();
            $updateData['approved_by'] = Auth::id();
        } elseif ($request->status === 'Released') {
            $updateData['released_at'] = now();
            $updateData['issued_date'] = now();
            $updateData['issued_by'] = Auth::id();
        } elseif ($request->status === 'Rejected') {
            $updateData['rejected_at'] = now();
            $updateData['rejection_reason'] = $request->rejection_reason;
        }

        $certificate->update($updateData);

        $resident = $certificate->resident;
        $residentName = $resident ? $resident->first_name . ' ' . $resident->last_name : 'Unknown';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'PROCESS_CERTIFICATE',
            'description' => 'Certificate ' . $certificate->certificate_id . ' for ' . $residentName .
                            ' status changed from ' . $oldStatus . ' to ' . $request->status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('secretary.certificates.show', $certificate)
            ->with('success', 'Certificate processed successfully.');
    }

    public function destroy(Request $request, Certificate $certificate)
    {
        $certificateId = $certificate->certificate_id;
        $resident = $certificate->resident;
        $residentName = $resident ? $resident->first_name . ' ' . $resident->last_name : 'Unknown';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_CERTIFICATE',
            'description' => 'Deleted certificate ' . $certificateId . ' for ' . $residentName,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $certificate->delete();

        return redirect()->route('secretary.certificates.index')
            ->with('success', 'Certificate deleted successfully.');
    }

    public function print(Certificate $certificate)
    {
        $certificate->load('resident');
        return view('secretary.certificates.print', compact('certificate'));
    }

    private function generateCertificateNumber()
    {
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

        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }
}
