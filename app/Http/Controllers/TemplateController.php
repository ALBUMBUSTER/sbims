<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Certificate;
use App\Models\Resident;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use App\Models\BarangayInfo;  // ADD THIS LINE


class TemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Generate and download certificate as DOCX
     */
    public function generateCertificate(Certificate $certificate)
    {
        // Only allow if certificate is Released
        if ($certificate->status !== 'Released') {
            return redirect()->back()->with('error', 'Certificate must be released first.');
        }

        $resident = $certificate->resident;

        // Check if template exists
        $templatePath = $this->getTemplatePath($certificate->certificate_type);

        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Template file not found for ' . $certificate->certificate_type);
        }

        try {
            // Load the template
            $template = new TemplateProcessor($templatePath);

            // Prepare data for template
            $data = $this->prepareTemplateData($certificate, $resident);

            // Replace all placeholders
            foreach ($data as $key => $value) {
                $template->setValue($key, $value);
            }

            // Generate file name
            $fileName = $this->generateFileName($certificate);
            $outputPath = storage_path('app/temp/' . $fileName);

            // Create temp directory if not exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            // Save the document
            $template->saveAs($outputPath);

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'GENERATE_CERTIFICATE',
                'description' => 'Generated ' . $certificate->certificate_type . ' certificate ' . $certificate->certificate_id . ' for ' . $resident->full_name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Return the file as download
            return response()->download($outputPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating certificate: ' . $e->getMessage());
        }
    }

    /**
 * Show certificate as HTML for printing
 */
public function printCertificate(Certificate $certificate)
{
    // Only allow if certificate is Released
    if ($certificate->status !== 'Released') {
        return redirect()->back()->with('error', 'Certificate must be released first.');
    }

    $resident = $certificate->resident;

    return view('secretary.certificates.print-preview', compact('certificate', 'resident'));
}
    /**
     * Get template path based on certificate type
     */
    private function getTemplatePath($type)
    {
        $templates = [
            'Clearance' => 'cert_clearance.docx',
            'Indigency' => 'cert_indigency.docx',
            'Residency' => 'cert_residency.docx',
        ];

        $filename = $templates[$type] ?? 'barangay_clearance.docx';
        return storage_path('app/templates/' . $filename);
    }

/**
 * Prepare data for template placeholders
 */
private function prepareTemplateData($certificate, $resident)
{
    // Get barangay info from database
    $barangayInfo = BarangayInfo::first();

    // Format full name with middle initial
    $fullName = $resident->first_name . ' ' . $resident->last_name;
    if ($resident->middle_name) {
        $fullName = $resident->first_name . ' ' . $resident->middle_name[0] . '. ' . $resident->last_name;
    }

    // Get age from birthdate
    $age = $resident->birthdate ? $resident->birthdate->age : '___';

    // Gender prefix for certificate
    $genderPrefix = $resident->gender === 'Male' ? 'Filipino' : 'Filipina';
    $title = $resident->gender === 'Male' ? 'Mr.' : 'Ms.';

    // Format date
    $today = now();

    // Get captain's name from database - NO FALLBACKS, MUST BE SET IN DATABASE
    $captainName = $barangayInfo && $barangayInfo->barangay_captain
        ? strtoupper($barangayInfo->barangay_captain)
        : 'BARANGAY CAPTAIN NAME NOT SET'; // This will alert if not set

    // Get barangay name from database
    $barangayName = $barangayInfo && $barangayInfo->barangay_name
        ? $barangayInfo->barangay_name
        : 'Libertad';

    // Get address/contact info from database
    $barangayAddress = $barangayInfo && $barangayInfo->address
        ? $barangayInfo->address
        : '';

    // Build full address
    $barangayFull = "Barangay {$barangayName}, Isabel, Leyte";
    if ($barangayAddress) {
        $barangayFull = $barangayAddress;
    }

    return [
        'full_name' => $fullName,
        'first_name' => $resident->first_name,
        'last_name' => $resident->last_name,
        'middle_name' => $resident->middle_name ?? '',
        'age' => $age,
        'civil_status' => $resident->civil_status ?? 'Single',
        'gender' => $genderPrefix,
        'gender_title' => $title,
        'purok' => $resident->purok ?? '___',
        'address' => $resident->address ?? '',
        'purpose' => $certificate->purpose,
        'certificate_no' => $certificate->certificate_id,
        'certificate_id' => $certificate->certificate_id,
        'day' => $today->format('jS'),
        'month' => $today->format('F'),
        'year' => $today->year,
        'issued_date' => $today->format('F d, Y'),
        'captain_name' => 'HON. ' . $captainName,  // This will be used in template
        'captain_full_name' => 'HON. ' . $captainName,  // For compatibility
        'barangay' => $barangayName,
        'municipality' => 'Isabel, Leyte',
        'barangay_full' => $barangayFull,
    ];
}
    /**
     * Generate filename for certificate
     */
    private function generateFileName($certificate)
    {
        $type = strtolower($certificate->certificate_type);
        $number = $certificate->certificate_id;
        $date = now()->format('Y-m-d');

        return "{$type}_{$number}_{$date}.docx";
    }
}
