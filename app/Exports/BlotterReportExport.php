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
use Carbon\Carbon;

class BlotterReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $filters;
    protected $statistics;
    protected $totalRecords;

    public function __construct($filters = [], $statistics = [])
    {
        $this->filters = $filters;
        $this->statistics = $statistics;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Blotter::with(['complainant']);

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
        return [
            ['BLOTTER CASES REPORT'],
            ['Generated on: ' . Carbon::now()->format('F d, Y h:i A')],
            ['Filters: ' . $this->getFilterDescription()],
            [], // Empty row for spacing
            [
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
            ]
        ];
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
        // Style for the main title
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for generation date
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for filters
        $sheet->mergeCells('A3:J3');
        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for headers (row 5)
        $sheet->getStyle('A5:J5')->getFont()->setBold(true);
        $sheet->getStyle('A5:J5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A5:J5')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A5:J5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for all data cells
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A6:J' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Add borders to all cells
        $sheet->getStyle('A5:J' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Style for status column based on status
        $statusColumn = 'H'; // Status is in column H (8th column)
        for ($row = 6; $row <= $lastRow; $row++) {
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
            ['Pending Cases', $this->statistics['by_status']['pending'] ?? 0],
            ['Ongoing Cases', $this->statistics['by_status']['ongoing'] ?? 0],
            ['Settled Cases', $this->statistics['by_status']['settled'] ?? 0],
            ['Referred Cases', $this->statistics['by_status']['referred'] ?? 0],
            ['Resolution Rate', ($this->statistics['resolution_rate'] ?? 0) . '%'],
            ['Total Records Exported', $this->totalRecords]
        ];

        $statStartRow = $summaryRow + 1;
        foreach ($stats as $index => $stat) {
            $sheet->setCellValue('A' . ($statStartRow + $index), $stat[0]);
            $sheet->setCellValue('B' . ($statStartRow + $index), $stat[1]);
            $sheet->getStyle('A' . ($statStartRow + $index))->getFont()->setBold(true);
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
