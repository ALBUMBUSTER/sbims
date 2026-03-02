<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangayInfo;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BarangayInfoController extends Controller
{
    /**
     * Display barangay information
     */
    public function index()
    {
        // Get or create barangay info record
        $barangayInfo = BarangayInfo::first();

        // If no record exists, create a default one
        if (!$barangayInfo) {
            $barangayInfo = BarangayInfo::create([
                'barangay_name' => 'Libertad',
                'address' => 'Libertad, Isabel, Leyte'
            ]);
        }

        return view('admin.barangay.index', compact('barangayInfo'));
    }

    /**
     * Update barangay information
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'barangay_name' => 'required|string|max:100',
            'barangay_captain' => 'nullable|string|max:100',
            'barangay_secretary' => 'nullable|string|max:100',
            'address' => 'required|string',
            'contact_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the barangay info record
        $barangayInfo = BarangayInfo::first();

        if (!$barangayInfo) {
            $barangayInfo = new BarangayInfo();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($barangayInfo->logo_path && Storage::exists($barangayInfo->logo_path)) {
                Storage::delete($barangayInfo->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('barangay-logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Update the record
        $barangayInfo->update($validated);

        // Log the action
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated barangay information",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.barangay.index')
            ->with('success', 'Barangay information updated successfully.');
    }
}
