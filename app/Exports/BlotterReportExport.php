<?php

namespace App\Exports;

use App\Models\Blotter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BlotterReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $filters;
    protected $statistics;
    protected $totalRecords;
    protected $summaryData;

    public function __construct($filters = [], $statistics = [])
    {
        $this->filters = $filters;
        $this->statistics = $statistics;
        $this->summaryData = $this->getSummaryData();
    }

    /**
     * Get summary data for the tables
     */
    protected function getSummaryData()
    {
        $query = Blotter::with('complainant');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('incident_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('incident_date', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        $total = $query->count() ?: 1;

        // Status Distribution
        $byStatus = [
            'Pending' => (clone $query)->where('status', 'Pending')->count(),
            'Ongoing' => (clone $query)->whereIn('status', ['Investigating', 'Hearings', 'Ongoing'])->count(),
            'Settled' => (clone $query)->where('status', 'Settled')->count(),
            'Referred' => (clone $query)->where('status', 'Referred')->count(),
        ];

        $statusData = [];
        foreach ($byStatus as $status => $count) {
            if ($count > 0) {
                $statusData[] = [
                    $status,
                    $count,
                    round(($count / $total) * 100, 1) . '%',
                ];
            }
        }

        // Incident Type Distribution
        $byType = (clone $query)
            ->select('incident_type', DB::raw('count(*) as count'))
            ->groupBy('incident_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function($item) use ($total) {
                return [
                    $item->incident_type ?? 'Not Specified',
                    $item->count,
                    round(($item->count / $total) * 100, 1) . '%',
                ];
            })->toArray();

        // Monthly Trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = (clone $query)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyTrend[] = [
                $month->format('M Y'),
                $count,
                $total > 0 ? round(($count / $total) * 100, 1) . '%' : '0%',
            ];
        }

        return [
            'byStatus' => $statusData,
            'byType' => $byType,
            'monthlyTrend' => $monthlyTrend,
            'total' => $total,
            'resolutionRate' => $total > 0 ? round(($byStatus['Settled'] / $total) * 100, 1) . '%' : '0%',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Blotter::with('complainant');

        // Apply date filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('incident_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('incident_date', '<=', $this->filters['date_to']);
        }

        // Apply status filter
        if (!empty($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        // Order by latest
        $query->orderBy('created_at', 'desc');

        $this->totalRecords = $query->count();

        return $query->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        $headings = [
            ['BLOTTER CASES REPORT'],
            ['Generated on: ' . Carbon::now()->format('F d, Y h:i A')],
            ['Filters: ' . $this->getFilterDescription()],
            [],
        ];

        // Status Distribution Table
        $headings[] = ['STATUS DISTRIBUTION'];
        $headings[] = ['Status', 'Count', 'Percentage'];
        foreach ($this->summaryData['byStatus'] as $row) {
            $headings[] = $row;
        }
        $headings[] = [];

        // Incident Type Distribution Table
        $headings[] = ['INCIDENT TYPE DISTRIBUTION'];
        $headings[] = ['Incident Type', 'Count', 'Percentage'];
        foreach ($this->summaryData['byType'] as $row) {
            $headings[] = $row;
        }
        $headings[] = [];

        // Monthly Trend Table
        $headings[] = ['MONTHLY TREND (Last 6 Months)'];
        $headings[] = ['Month', 'Count', 'Percentage'];
        foreach ($this->summaryData['monthlyTrend'] as $row) {
            $headings[] = $row;
        }
        $headings[] = [];

        // Summary Statistics
        $headings[] = ['SUMMARY STATISTICS'];
        $headings[] = ['Total Cases', $this->summaryData['total']];
        $headings[] = ['Resolution Rate', $this->summaryData['resolutionRate']];
        $headings[] = [];
        $headings[] = [];

        // Blotter Cases List Header
        $headings[] = ['BLOTTER CASES LIST'];
        $headings[] = [
            'Case #',
            'Complainant',
            'Respondent',
            'Respondent Address',
            'Incident Type',
            'Incident Date',
            'Incident Location',
            'Status',
            'Filed Date',
            'Remarks'
        ];

        return $headings;
    }

    /**
    * @param mixed $blotter
    * @return array
    */
    public function map($blotter): array
    {
        return [
            $blotter->case_id ?? 'N/A',
            $blotter->complainant ? $blotter->complainant->first_name . ' ' . $blotter->complainant->last_name : 'N/A',
            $blotter->respondent_name ?? 'N/A',
            $blotter->respondent_address ?? 'N/A',
            $blotter->incident_type ?? 'N/A',
            $blotter->incident_date ? Carbon::parse($blotter->incident_date)->format('M d, Y') : 'N/A',
            $blotter->incident_location ?? 'N/A',
            $blotter->status ?? 'N/A',
            $blotter->created_at ? $blotter->created_at->format('M d, Y') : 'N/A',
            $blotter->remarks ?? 'N/A'
        ];
    }

    /**
    * @return string
    */
    public function title(): string
    {
        return 'Blotter Cases';
    }

    /**
    * @param Worksheet $sheet
    */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Style for the main title (row 1)
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for generation date (row 2)
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for filters (row 3)
        $sheet->mergeCells('A3:J3');
        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Calculate positions
        $statusCount = count($this->summaryData['byStatus']);
        $typeCount = count($this->summaryData['byType']);
        $trendCount = count($this->summaryData['monthlyTrend']);

        // Style for Status Distribution table headers (row 5)
        $this->styleTableHeader($sheet, 5, 'A', 'C');

        // Style for Incident Type Distribution table headers
        $typeHeaderRow = 5 + $statusCount + 3;
        $this->styleTableHeader($sheet, $typeHeaderRow, 'A', 'C');

        // Style for Monthly Trend table headers
        $trendHeaderRow = $typeHeaderRow + $typeCount + 3;
        $this->styleTableHeader($sheet, $trendHeaderRow, 'A', 'C');

        // Style for Blotter Cases List header
        $listHeaderRow = $trendHeaderRow + $trendCount + 8;
        $this->styleTableHeader($sheet, $listHeaderRow, 'A', 'J');

        // Add borders to all cells
        $sheet->getStyle('A1:J' . $lastRow)
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Style for status column based on status
        $statusColumn = 'H'; // Status is in column H
        for ($row = $listHeaderRow + 1; $row <= $lastRow; $row++) {
            $status = $sheet->getCell($statusColumn . $row)->getValue();
            $color = $this->getStatusColor($status);
            if ($color) {
                $sheet->getStyle($statusColumn . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($color);
            }
        }

        // Add summary section at the bottom
        $summaryRow = $lastRow + 2;
        $sheet->setCellValue('A' . $summaryRow, 'SUMMARY STATISTICS');
        $sheet->mergeCells('A' . $summaryRow . ':J' . $summaryRow);
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Statistics rows
        $stats = [
            ['Total Cases', $this->statistics['total'] ?? 0],
            ['Pending', $this->statistics['by_status']['pending'] ?? 0],
            ['Ongoing', $this->statistics['by_status']['ongoing'] ?? 0],
            ['Settled', $this->statistics['by_status']['settled'] ?? 0],
            ['Referred', $this->statistics['by_status']['referred'] ?? 0],
            ['Resolution Rate', $this->statistics['resolution_rate'] ?? '0%'],
            ['Total Records Exported', $this->totalRecords]
        ];

        $statStartRow = $summaryRow + 1;
        foreach ($stats as $index => $stat) {
            $sheet->setCellValue('A' . ($statStartRow + $index), $stat[0]);
            $sheet->setCellValue('B' . ($statStartRow + $index), $stat[1]);
            $sheet->getStyle('A' . ($statStartRow + $index))->getFont()->setBold(true);
        }
    }

    /**
     * Style table headers with red background
     */
    private function styleTableHeader($sheet, $row, $startColumn, $endColumn)
    {
        $range = $startColumn . $row . ':' . $endColumn . $row;
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDC2626'); // Red color
        $sheet->getStyle($range)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
        if (!empty($this->filters['status']) && $this->filters['status'] !== '') {
            $parts[] = 'Status: ' . $this->filters['status'];
        }

        return empty($parts) ? 'No filters applied' : implode(' | ', $parts);
    }

    private function getStatusColor($status)
    {
        return match (strtolower($status)) {
            'pending' => 'FFFEF3C7', // Light yellow
            'investigating', 'ongoing' => 'FFDBEAFE', // Light blue
            'hearings' => 'FFEDE9FE', // Light purple
            'settled' => 'FFD1FAE5', // Light green
            'referred' => 'FFFEE2E2', // Light red
            default => null
        };
    }
}
