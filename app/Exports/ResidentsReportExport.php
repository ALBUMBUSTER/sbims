<?php

namespace App\Exports;

use App\Models\Resident;
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

class ResidentsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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

        $this->totalRecords = $query->count();

        return $query->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            ['RESIDENTS REPORT'],
            ['Generated on: ' . Carbon::now()->format('F d, Y h:i A')],
            ['Filters: ' . $this->getFilterDescription()],
            [], // Empty row for spacing
            [
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
            ]
        ];
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
        return 'Residents';
    }

    /**
    * @param Worksheet $sheet
    */
    public function styles(Worksheet $sheet)
    {
        // Style for the main title
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for generation date
        $sheet->mergeCells('A2:N2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for filters
        $sheet->mergeCells('A3:N3');
        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for headers (row 5)
        $sheet->getStyle('A5:N5')->getFont()->setBold(true);
        $sheet->getStyle('A5:N5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle('A5:N5')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A5:N5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for all data cells
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A6:N' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Add borders to all cells
        $sheet->getStyle('A5:N' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Add conditional formatting for Yes/No columns
        $yesNoColumns = ['J', 'K', 'L', 'M']; // Columns for is_voter, is_senior, is_pwd, is_4ps
        foreach ($yesNoColumns as $column) {
            for ($row = 6; $row <= $lastRow; $row++) {
                $value = $sheet->getCell($column . $row)->getValue();
                if ($value === 'Yes') {
                    $sheet->getStyle($column . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFD1FAE5'); // Light green
                }
            }
        }

        // Add summary section at the bottom
        $summaryRow = $lastRow + 2;
        $sheet->setCellValue('A' . $summaryRow, 'SUMMARY STATISTICS');
        $sheet->mergeCells('A' . $summaryRow . ':N' . $summaryRow);
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Statistics rows
        $stats = [
            ['Total Residents', $this->statistics['total'] ?? 0],
            ['Male', $this->statistics['by_gender']['male'] ?? 0],
            ['Female', $this->statistics['by_gender']['female'] ?? 0],
            ['Registered Voters', $this->statistics['by_status']['voters'] ?? 0],
            ['Senior Citizens', $this->statistics['by_status']['seniors'] ?? 0],
            ['PWD', $this->statistics['by_status']['pwd'] ?? 0],
            ['4Ps Members', $this->statistics['by_status']['4ps'] ?? 0],
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

        return empty($parts) ? 'No filters applied' : implode(' | ', $parts);
    }
}
