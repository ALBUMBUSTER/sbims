<?php

namespace App\Exports;

use App\Models\Certificate;
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

class CertificatesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
        $query = Certificate::with('resident');

        // Apply date filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
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
            ['CERTIFICATES REPORT'],
            ['Generated on: ' . Carbon::now()->format('F d, Y h:i A')],
            ['Filters: ' . $this->getFilterDescription()],
            [], // Empty row for spacing
            [
                'Certificate #',
                'Resident Name',
                'Certificate Type',
                'Purpose',
                'Status',
                'Request Date',
                'Release Date',
                'OR Number',
                'Amount',
                'Remarks'
            ]
        ];
    }

    /**
    * @param mixed $certificate
    * @return array
    */
    public function map($certificate): array
    {
        return [
            $certificate->certificate_id ?? 'N/A',
            $certificate->resident ? $certificate->resident->first_name . ' ' . $certificate->resident->last_name : 'N/A',
            $certificate->certificate_type ?? 'N/A',
            $certificate->purpose ?? 'N/A',
            $certificate->status ?? 'N/A',
            $certificate->created_at ? $certificate->created_at->format('M d, Y') : 'N/A',
            $certificate->released_at ? Carbon::parse($certificate->released_at)->format('M d, Y') : 'Not Released',
            $certificate->or_number ?? 'N/A',
            $certificate->amount ? '₱' . number_format($certificate->amount, 2) : 'N/A',
            $certificate->remarks ?? 'N/A'
        ];
    }

    /**
    * @return string
    */
    public function title(): string
    {
        return 'Certificates';
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
        $statusColumn = 'E'; // Status is in column E
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
            ['Total Certificates', $this->statistics['total'] ?? 0],
            ['Pending', $this->statistics['by_status']['pending'] ?? 0],
            ['Approved', $this->statistics['by_status']['approved'] ?? 0],
            ['Released', $this->statistics['by_status']['released'] ?? 0],
            ['Rejected', $this->statistics['by_status']['rejected'] ?? 0],
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
            'approved' => 'FFDBEAFE', // Light blue
            'released' => 'FFD1FAE5', // Light green
            'rejected' => 'FFFEE2E2', // Light red
            default => null
        };
    }
}
