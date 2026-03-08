<?php
// app/Http/Controllers/Clerk/ClerkResidentController.php

namespace App\Http\Controllers\Clerk;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClerkResidentController extends Controller
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
     * Display a listing of residents (READ ONLY)
     */
    public function index(Request $request)
    {
        $this->isClerk(); // Role check

        $query = Resident::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by purok
        if ($request->filled('purok')) {
            $query->where('purok', $request->purok);
        }

        // Filter by civil status
        if ($request->filled('civil_status')) {
            $query->where('civil_status', $request->civil_status);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $residents = $query->paginate(15)->withQueryString();

        // Get unique values for filters
        $puroks = Resident::distinct('purok')->whereNotNull('purok')->pluck('purok');
        $civilStatuses = ['Single', 'Married', 'Divorced', 'Widowed', 'Separated'];

        return view('clerk.residents.index', compact('residents', 'puroks', 'civilStatuses'));
    }

    /**
     * Display the specified resident (READ ONLY)
     */
    public function show(Resident $resident)
    {
        $this->isClerk(); // Role check

        // Get resident's certificates
        $certificates = Certificate::where('resident_id', $resident->id)
            ->latest()
            ->take(10)
            ->get();

        return view('clerk.residents.show', compact('resident', 'certificates'));
    }
}
