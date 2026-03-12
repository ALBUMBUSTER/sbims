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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResidentsReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $filters;
    protected $statistics;
    protected $distributionData;

    public function __construct($filters = [], $statistics = [])
    {
        $this->filters = $filters;
        $this->statistics = $statistics;
        $this->distributionData = $this->getDistributionData();
    }

    /**
     * Get distribution data for the tables
     */
    protected function getDistributionData()
    {
        $query = Resident::query();

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        $total = $query->count() ?: 1;
        $residents = $this->collection();

        // Distribution by Purok
        $byPurok = (clone $query)
            ->select('purok', DB::raw('count(*) as count'))
            ->groupBy('purok')
            ->orderBy('purok')
            ->get()
            ->map(function($item) use ($total) {
                return [
                    'purok' => 'Purok ' . ($item->purok ?? 'Not Specified'),
                    'count' => $item->count,
                    'percentage' => round(($item->count / $total) * 100, 1) . '%',
                ];
            })->toArray();

        // Civil Status Distribution
        $byCivilStatus = (clone $query)
            ->select('civil_status', DB::raw('count(*) as count'))
            ->whereNotNull('civil_status')
            ->groupBy('civil_status')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function($item) use ($total) {
                return [
                    'status' => $item->civil_status ?? 'Not Specified',
                    'count' => $item->count,
                    'percentage' => round(($item->count / $total) * 100, 1) . '%',
                ];
            })->toArray();

        // Age Distribution
        $ageDistribution = [
            '0-17' => 0,
            '18-30' => 0,
            '31-45' => 0,
            '46-60' => 0,
            '60+' => 0,
        ];

        foreach ($residents as $resident) {
            if ($resident->birthdate) {
                $age = $resident->birthdate->age;
                if ($age <= 17) {
                    $ageDistribution['0-17']++;
                } elseif ($age <= 30) {
                    $ageDistribution['18-30']++;
                } elseif ($age <= 45) {
                    $ageDistribution['31-45']++;
                } elseif ($age <= 60) {
                    $ageDistribution['46-60']++;
                } else {
                    $ageDistribution['60+']++;
                }
            }
        }

        $byAge = [];
        foreach ($ageDistribution as $range => $count) {
            $byAge[] = [
                'range' => $range,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1) . '%',
            ];
        }

        // Special Categories
        $specialCategories = [
            'Senior Citizens' => (clone $query)->where('is_senior', true)->count(),
            'PWD' => (clone $query)->where('is_pwd', true)->count(),
            '4Ps Members' => (clone $query)->where('is_4ps', true)->count(),
        ];

        $bySpecial = [];
        foreach ($specialCategories as $category => $count) {
            $bySpecial[] = [
                'category' => $category,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1) . '%',
            ];
        }

        return [
            'byPurok' => $byPurok,
            'byCivilStatus' => $byCivilStatus,
            'byAge' => $byAge,
            'bySpecial' => $bySpecial,
            'total' => $total,
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Resident::query();

        // Apply date filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        // Order by latest
        $query->orderBy('created_at', 'desc');

        return $query->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        $headings = [
            ['RESIDENTS REPORT'],
            ['Generated on: ' . Carbon::now()->format('F d, Y h:i A')],
            ['Filters: ' . $this->getFilterDescription()],
            [],
        ];

        // Add Distribution by Purok table
        $headings[] = ['DISTRIBUTION BY PUROK'];
        $headings[] = ['Purok', 'Count', 'Percentage'];
        foreach ($this->distributionData['byPurok'] as $row) {
            $headings[] = [$row['purok'], $row['count'], $row['percentage']];
        }
        $headings[] = [];

        // Add Civil Status table
        $headings[] = ['CIVIL STATUS'];
        $headings[] = ['Status', 'Count', 'Percentage'];
        foreach ($this->distributionData['byCivilStatus'] as $row) {
            $headings[] = [$row['status'], $row['count'], $row['percentage']];
        }
        $headings[] = [];

        // Add Age Distribution table
        $headings[] = ['AGE DISTRIBUTION'];
        $headings[] = ['Age Range', 'Count', 'Percentage'];
        foreach ($this->distributionData['byAge'] as $row) {
            $headings[] = [$row['range'], $row['count'], $row['percentage']];
        }
        $headings[] = [];

        // Add Special Categories table
        $headings[] = ['SPECIAL CATEGORIES'];
        $headings[] = ['Category', 'Count', 'Percentage'];
        foreach ($this->distributionData['bySpecial'] as $row) {
            $headings[] = [$row['category'], $row['count'], $row['percentage']];
        }
        $headings[] = [];

        // Add Total Residents
        $headings[] = ['TOTAL RESIDENTS:', $this->distributionData['total'], '100%'];
        $headings[] = [];
        $headings[] = [];

        // Add Residents List header
        $headings[] = ['RESIDENTS LIST'];
        $headings[] = [
            'Resident ID',
            'Full Name',
            'Gender',
            'Birthdate',
            'Age',
            'Civil Status',
            'Purok',
            'Contact Number',
            'Email',
            'Registered Voter',
            'Senior Citizen',
            'PWD',
            '4Ps Member',
            'Created Date'
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
            $resident->first_name . ' ' . $resident->last_name . ($resident->middle_name ? ' ' . $resident->middle_name : ''),
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
        return 'Residents Report';
    }

    /**
    * @param Worksheet $sheet
    */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A1:N1');

        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A2:N2');

        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A3:N3');

        // Find the starting row for residents list headers
        $row = 5;
        $tablesCount = count($this->distributionData['byPurok']) + count($this->distributionData['byCivilStatus']) +
                      count($this->distributionData['byAge']) + count($this->distributionData['bySpecial']);
        $headersRow = 5 + ($tablesCount * 2) + 24; // Approximate calculation

        // Style the residents list headers
        $sheet->getStyle('A' . $headersRow . ':N' . $headersRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headersRow . ':N' . $headersRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A' . $headersRow . ':N' . $headersRow)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A' . $headersRow . ':N' . $headersRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style the total row
        $totalRow = $headersRow - 3;
        $sheet->getStyle('A' . $totalRow . ':C' . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $totalRow . ':C' . $totalRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF3F4F6');

        // Style table headers
        $tableHeaderRows = [5, 5 + count($this->distributionData['byPurok']) + 3,
                           5 + count($this->distributionData['byPurok']) + count($this->distributionData['byCivilStatus']) + 7,
                           5 + count($this->distributionData['byPurok']) + count($this->distributionData['byCivilStatus']) + count($this->distributionData['byAge']) + 11];

        foreach ($tableHeaderRows as $tableHeaderRow) {
            $sheet->getStyle('A' . $tableHeaderRow . ':C' . $tableHeaderRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $tableHeaderRow . ':C' . $tableHeaderRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF667EEA');
            $sheet->getStyle('A' . $tableHeaderRow . ':C' . $tableHeaderRow)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $sheet->getStyle('A' . $tableHeaderRow . ':C' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }

    private function getFilterDescription()
    {
        $parts = [];
        if (!empty($this->filters['date_from'])) {
            $parts[] = 'From: ' . Carbon::parse($this->filters['date_from'])->format('M d, Y');
        }
        if (!empty($this->filters['date_to'])) {
            $parts[] = 'To: ' . Carbon::parse($this->filters['date_to'])->format('M d, Y');
        }

        return empty($parts) ? 'No filters applied' : implode(' | ', $parts);
    }
}
