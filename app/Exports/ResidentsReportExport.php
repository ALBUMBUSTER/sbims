<?php

namespace App\Exports;

use App\Models\Resident;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ResidentsReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $filters;
    protected $statistics;
    protected $distributionData;
    protected $residents;

    public function __construct($residents = null, $statistics = [], $filters = [])
    {
        $this->residents = $residents;
        $this->filters = $filters;
        $this->statistics = $statistics;
        $this->distributionData = $this->getDistributionData();
    }

    /**
     * Apply category filter to query
     */
    private function applyCategoryFilter($query, $category = null)
    {
        $category = $category ?? ($this->filters['category'] ?? 'all');

        switch ($category) {
            case 'senior':
                return $query->where('is_senior', true);
            case 'pwd':
                return $query->where('is_pwd', true);
            case 'voter':
                return $query->where('is_voter', true);
            case '4ps':
                return $query->where('is_4ps', true);
            case 'male':
                return $query->where('gender', 'Male');
            case 'female':
                return $query->where('gender', 'Female');
            case 'children':
                return $query->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) <= 17');
            case 'adult':
                return $query->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 59');
            case 'all':
            default:
                return $query;
        }
    }

    /**
     * Get distribution data for the tables (using the filtered residents)
     */
    protected function getDistributionData()
    {
        // Use the already filtered residents that were passed in
        $residents = $this->residents;

        // If no residents were passed, build a fresh query with filters
        if (!$residents || $residents->isEmpty()) {
            $query = Resident::query();
            $query = $this->applyCategoryFilter($query);

            if (!empty($this->filters['date_from'])) {
                $query->whereDate('created_at', '>=', $this->filters['date_from']);
            }
            if (!empty($this->filters['date_to'])) {
                $query->whereDate('created_at', '<=', $this->filters['date_to']);
            }
            $residents = $query->get();
        }

        $total = $residents->count() ?: 1;

        // Distribution by Purok (using filtered residents)
        $byPurok = $residents->groupBy('purok')
            ->map(function($group, $purok) use ($total) {
                return [
                    'purok' => $purok ? 'Purok ' . $purok : 'Not Specified',
                    'count' => $group->count(),
                    'percentage' => round(($group->count() / $total) * 100, 1) . '%'
                ];
            })
            ->sortBy('purok')
            ->values()
            ->toArray();

        // Civil Status Distribution (using filtered residents)
        $byCivilStatus = $residents->whereNotNull('civil_status')
            ->groupBy('civil_status')
            ->map(function($group, $status) use ($total) {
                return [
                    'status' => $status ?: 'Not Specified',
                    'count' => $group->count(),
                    'percentage' => round(($group->count() / $total) * 100, 1) . '%'
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();

        // Age Distribution (using filtered residents)
        $ageDistribution = ['0-17' => 0, '18-30' => 0, '31-45' => 0, '46-60' => 0, '60+' => 0];

        foreach ($residents as $resident) {
            if ($resident->birthdate) {
                $age = Carbon::parse($resident->birthdate)->age;
                if ($age <= 17) $ageDistribution['0-17']++;
                elseif ($age <= 30) $ageDistribution['18-30']++;
                elseif ($age <= 45) $ageDistribution['31-45']++;
                elseif ($age <= 60) $ageDistribution['46-60']++;
                else $ageDistribution['60+']++;
            }
        }

        $byAge = [];
        foreach ($ageDistribution as $range => $count) {
            $byAge[] = [
                'range' => $range,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1) . '%'
            ];
        }

        // Special Categories (using filtered residents)
        $specialCategories = [
            'Senior Citizens' => $residents->where('is_senior', true)->count(),
            'PWD' => $residents->where('is_pwd', true)->count(),
            '4Ps Members' => $residents->where('is_4ps', true)->count(),
        ];

        $bySpecial = [];
        foreach ($specialCategories as $category => $count) {
            $bySpecial[] = [
                'category' => $category,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1) . '%'
            ];
        }

        return [
            'byPurok' => $byPurok,
            'byCivilStatus' => $byCivilStatus,
            'byAge' => $byAge,
            'bySpecial' => $bySpecial,
            'total' => $total
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // If residents were passed directly, use them (already filtered)
        if ($this->residents && $this->residents->isNotEmpty()) {
            return $this->residents;
        }

        // Otherwise build query with filters
        $query = Resident::query();
        $query = $this->applyCategoryFilter($query);

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $categoryDisplay = $this->getCategoryDisplayName();

        $headings = [
            ['RESIDENTS REPORT'],
            ['Generated on: ' . Carbon::now()->format('F d, Y h:i A')],
            ['Filters: ' . $this->getFilterDescription()],
            ['Category: ' . $categoryDisplay],
            [],
            ['DISTRIBUTION BY PUROK'],
            ['Purok', 'Count', 'Percentage'],
        ];

        foreach ($this->distributionData['byPurok'] as $row) {
            $headings[] = [$row['purok'], $row['count'], $row['percentage']];
        }

        $headings[] = [];
        $headings[] = ['CIVIL STATUS'];
        $headings[] = ['Status', 'Count', 'Percentage'];

        foreach ($this->distributionData['byCivilStatus'] as $row) {
            $headings[] = [$row['status'], $row['count'], $row['percentage']];
        }

        $headings[] = [];
        $headings[] = ['AGE DISTRIBUTION'];
        $headings[] = ['Age Range', 'Count', 'Percentage'];

        foreach ($this->distributionData['byAge'] as $row) {
            $headings[] = [$row['range'], $row['count'], $row['percentage']];
        }

        $headings[] = [];
        $headings[] = ['SPECIAL CATEGORIES'];
        $headings[] = ['Category', 'Count', 'Percentage'];

        foreach ($this->distributionData['bySpecial'] as $row) {
            $headings[] = [$row['category'], $row['count'], $row['percentage']];
        }

        $headings[] = [];
        $headings[] = ['TOTAL RESIDENTS:', $this->distributionData['total'], '100%'];
        $headings[] = [];
        $headings[] = [];
        $headings[] = ['RESIDENTS LIST'];
        $headings[] = [
            'Resident ID', 'Full Name', 'Gender', 'Birthdate', 'Age', 'Civil Status', 'Purok',
            'Contact Number', 'Email', 'Registered Voter', 'Senior Citizen', 'PWD', '4Ps Member', 'Created Date'
        ];

        return $headings;
    }

    /**
     * @param mixed $resident
     * @return array
     */
    public function map($resident): array
    {
        return [
            $resident->resident_id ?? 'N/A',
            trim($resident->first_name . ' ' . ($resident->middle_name ? $resident->middle_name . ' ' : '') . $resident->last_name . ($resident->suffix ? ' ' . $resident->suffix : '')),
            $resident->gender ?? 'N/A',
            $resident->birthdate ? Carbon::parse($resident->birthdate)->format('M d, Y') : 'N/A',
            $resident->birthdate ? Carbon::parse($resident->birthdate)->age : 'N/A',
            $resident->civil_status ?? 'N/A',
            $resident->purok ? 'Purok ' . $resident->purok : 'N/A',
            $resident->contact_number ?? 'N/A',
            $resident->email ?? 'N/A',
            $resident->is_voter ? 'Yes' : 'No',
            $resident->is_senior ? 'Yes' : 'No',
            $resident->is_pwd ? 'Yes' : 'No',
            $resident->is_4ps ? 'Yes' : 'No',
            $resident->created_at ? $resident->created_at->format('M d, Y') : 'N/A'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $category = $this->filters['category'] ?? 'all';
        $names = [
            'all' => 'Residents Report',
            'senior' => 'Senior Citizens Report',
            'pwd' => 'PWD Report',
            'voter' => 'Voters Report',
            '4ps' => '4Ps Members Report',
            'male' => 'Male Residents Report',
            'female' => 'Female Residents Report',
            'children' => 'Children Report',
            'adult' => 'Adults Report',
        ];
        return $names[$category] ?? 'Residents Report';
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A1:N1');

        // Style generation date
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A2:N2');

        // Style filters
        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A3:N3');

        // Style category
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A4:N4');
        $sheet->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6F3FF');

        // Style table headers
        $sheet->getStyle('A6:C6')->getFont()->setBold(true);
        $sheet->getStyle('A6:C6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A6:C6')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Style civil status headers
        $civilStatusStart = 6 + count($this->distributionData['byPurok']) + 3;
        $sheet->getStyle('A' . $civilStatusStart . ':C' . $civilStatusStart)->getFont()->setBold(true);
        $sheet->getStyle('A' . $civilStatusStart . ':C' . $civilStatusStart)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A' . $civilStatusStart . ':C' . $civilStatusStart)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Style age distribution headers
        $ageStart = $civilStatusStart + count($this->distributionData['byCivilStatus']) + 3;
        $sheet->getStyle('A' . $ageStart . ':C' . $ageStart)->getFont()->setBold(true);
        $sheet->getStyle('A' . $ageStart . ':C' . $ageStart)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A' . $ageStart . ':C' . $ageStart)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Style special categories headers
        $specialStart = $ageStart + count($this->distributionData['byAge']) + 3;
        $sheet->getStyle('A' . $specialStart . ':C' . $specialStart)->getFont()->setBold(true);
        $sheet->getStyle('A' . $specialStart . ':C' . $specialStart)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A' . $specialStart . ':C' . $specialStart)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Style residents list header
        $headersRow = $specialStart + count($this->distributionData['bySpecial']) + 6;
        $sheet->getStyle('A' . $headersRow . ':N' . $headersRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headersRow . ':N' . $headersRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A' . $headersRow . ':N' . $headersRow)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Style total row
        $totalRow = $specialStart + count($this->distributionData['bySpecial']) + 3;
        $sheet->getStyle('A' . $totalRow . ':C' . $totalRow)->getFont()->setBold(true);

        // Add borders to data
        $lastRow = $headersRow + $this->distributionData['total'] + 1;
        $sheet->getStyle('A' . $headersRow . ':N' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * Get filter description
     */
    private function getFilterDescription()
    {
        $parts = [];
        if (!empty($this->filters['date_from'])) {
            $parts[] = 'From: ' . Carbon::parse($this->filters['date_from'])->format('M d, Y');
        }
        if (!empty($this->filters['date_to'])) {
            $parts[] = 'To: ' . Carbon::parse($this->filters['date_to'])->format('M d, Y');
        }
        return empty($parts) ? 'No date filters applied' : implode(' | ', $parts);
    }

    /**
     * Get category display name
     */
    private function getCategoryDisplayName()
    {
        $category = $this->filters['category'] ?? 'all';
        $names = [
            'all' => 'All Residents',
            'senior' => 'Senior Citizens (60 years and above)',
            'pwd' => 'Persons with Disability (PWD)',
            'voter' => 'Registered Voters',
            '4ps' => '4Ps Members',
            'male' => 'Male Residents',
            'female' => 'Female Residents',
            'children' => 'Children (0-17 years old)',
            'adult' => 'Adults (18-59 years old)',
        ];
        return $names[$category] ?? ucfirst($category);
    }
}
