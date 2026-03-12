<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class SummaryReportExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected $statistics;
    protected $year;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
        $this->year = $statistics['year'] ?? date('Y');
    }

    public function array(): array
    {
        $data = [];

        // Header section
        $data[] = ['SUMMARY REPORT FOR YEAR ' . $this->year];
        $data[] = ['Generated on: ' . Carbon::now()->format('F d, Y h:i A')];
        $data[] = [];

        // Residents Summary
        $data[] = ['RESIDENTS SUMMARY'];
        $data[] = ['Total Residents', $this->statistics['residents']['total'] ?? 0];
        $data[] = ['New Residents This Year', $this->statistics['residents']['new_this_year'] ?? 0];
        $data[] = ['Male', $this->statistics['residents']['by_gender']['male'] ?? 0];
        $data[] = ['Female', $this->statistics['residents']['by_gender']['female'] ?? 0];
        $data[] = [];

        // Residents Monthly Trend - as a table
        $data[] = ['RESIDENTS MONTHLY TREND'];
        $data[] = ['Month', 'Count'];
        foreach ($this->statistics['residents']['monthly'] as $month => $count) {
            $data[] = [$month, $count];
        }
        $data[] = [];

        // Certificates Summary
        $data[] = ['CERTIFICATES SUMMARY'];
        $data[] = ['Total Certificates', $this->statistics['certificates']['total'] ?? 0];
        $data[] = ['Issued This Year', $this->statistics['certificates']['issued_this_year'] ?? 0];
        $data[] = ['Pending', $this->statistics['certificates']['by_status']['pending'] ?? 0];
        $data[] = ['Released', $this->statistics['certificates']['by_status']['released'] ?? 0];
        $data[] = [];

        // Certificates Monthly Trend - as a table
        $data[] = ['CERTIFICATES MONTHLY TREND'];
        $data[] = ['Month', 'Count'];
        foreach ($this->statistics['certificates']['monthly'] as $month => $count) {
            $data[] = [$month, $count];
        }
        $data[] = [];

        // Blotters Summary
        $data[] = ['BLOTTER CASES SUMMARY'];
        $data[] = ['Total Cases', $this->statistics['blotters']['total'] ?? 0];
        $data[] = ['Filed This Year', $this->statistics['blotters']['filed_this_year'] ?? 0];
        $data[] = ['Active Cases', $this->statistics['blotters']['by_status']['active'] ?? 0];
        $data[] = ['Settled Cases', $this->statistics['blotters']['by_status']['settled'] ?? 0];
        $data[] = [];

        // Blotters Monthly Trend - as a table
        $data[] = ['BLOTTER CASES MONTHLY TREND'];
        $data[] = ['Month', 'Count'];
        foreach ($this->statistics['blotters']['monthly'] as $month => $count) {
            $data[] = [$month, $count];
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Summary ' . $this->year;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Remove any default coloring from all cells
        $sheet->getStyle('A1:B' . $lastRow)
            ->getFill()
            ->setFillType(Fill::FILL_NONE);

        $row = 1;

        // Style for main title (row 1)
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // Style for generation date (row 2)
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // Process remaining rows
        while ($row <= $lastRow) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();

            if (empty($cellValue)) {
                $row++;
                continue;
            }

            // Check if this is a section header (SUMMARY or TREND)
            if (str_contains($cellValue, 'SUMMARY') || str_contains($cellValue, 'TREND')) {
                // Section header styling - light gray background
                $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A' . $row . ':B' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0'); // Light gray
                $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->getColor()->setARGB(Color::COLOR_BLACK);
                $sheet->mergeCells('A' . $row . ':B' . $row);
            }
            // Check if this is a table header (Month, Count)
            elseif ($cellValue === 'Month' || $cellValue === 'Count') {
                // Table header styling - light gray background
                $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':B' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0'); // Light gray
                $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            }
            // Regular data rows
            else {
                // Add borders to data cells
                $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Make category labels bold (like "Total Residents", "Male", etc.)
                if (!is_numeric($cellValue) && !str_contains($cellValue, 'SUMMARY') && !str_contains($cellValue, 'TREND') && $cellValue !== 'Month' && $cellValue !== 'Count') {
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                }
            }

            $row++;
        }

        // Auto-size columns A and B
        foreach (range('A', 'B') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
