<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReminderAllSummaryExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected ?Carbon $startDate;
    protected ?Carbon $endDate;
    protected int $daysInMonth;
    protected array $categoryRows = [];
    protected array $mrs = [];
    public function __construct($year = null, $month = null, $mrs = [])
    {
        $this->startDate = Carbon::create($year ?? now()->year, $month ?? now()->month, 1)->startOfMonth();
        $this->endDate = $this->startDate->copy()->endOfMonth();
        $this->daysInMonth = $this->startDate->daysInMonth; // Correct way to get days in month
        $this->mrs = $mrs;
    }

    public function array(): array
    {
        $dayColumns = [];
        $dayCasesForTotal = [];

        for($day = 1; $day <= $this->daysInMonth; $day++) {
            $date = $this->startDate->copy()->setDay($day)->format('Y-m-d');
            // Add COALESCE to convert NULL to 0
            $dayColumns[] = "COALESCE(COUNT(CASE WHEN DATE(reminders.created_at) = '$date' THEN 1 END), 0) as day_$day";
            $dayCasesForTotal[] = "COALESCE(COUNT(CASE WHEN DATE(reminders.created_at) = '$date' THEN 1 END), 0)";
        }

        $dayColumnsForTotal = implode(' + ', $dayCasesForTotal);

        $results = DB::table('categories')
            ->select('categories.id', 'categories.name', 'categories.status')
            ->selectRaw(implode(', ', $dayColumns))
            ->selectRaw("COALESCE(($dayColumnsForTotal), 0) as total_all_days")
            ->leftJoin('reminders', function($join) {
                $join->on('reminders.category_id', '=', 'categories.id')
                    ->whereIn('reminders.assigned_to', $this->mrs)
                    ->whereBetween(DB::raw('DATE(reminders.created_at)'), [
                        $this->startDate->format('Y-m-d'),
                        $this->endDate->format('Y-m-d')
                    ]);
            })
            ->where('categories.what_field', 'reminder_category')
            ->groupBy('categories.id', 'categories.name', 'categories.status')
            ->orderBy('categories.status')
            ->orderBy('categories.id')
            ->get();

        return $this->formatResults($results);
    }

    protected function formatResults($results): array
    {
        $formatted = [];
        $currentStatus = null;
        $rowIndex = 2;

        foreach ($results as $result) {
            // Add status header when status changes
            if ($currentStatus !== $result->status) {
                $formatted[] = array_merge([$result->status], array_fill(0, $this->daysInMonth + 1, ''));
                $this->categoryRows[] = $rowIndex++;
                $currentStatus = $result->status;
            }

            $dataRow = [$result->name];

            // Add daily counts - COALESCE ensures 0 from database
            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                $dayValue = $result->{"day_$day"} ?? 0;
                $dataRow[] = empty($dayValue) ? 0 : (int)$dayValue;
            }

            // Add total - COALESCE ensures 0 from database
            $totalValue = $result->total_all_days ?? 0;
            $dataRow[] = empty($totalValue) ? 0 : (int)$totalValue;

            $formatted[] = $dataRow;
            $rowIndex++;
        }

        return $formatted;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            // Header row styling
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
        ];

        // Style category rows (bold)
        foreach ($this->categoryRows as $rowNumber) {
            $styles[$rowNumber] = [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0']
                ]
            ];
        }

        return $styles;
    }
    public function headings(): array
    {
        $headings = ['Category'];

        // Add day headers
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $headings[] = "day_$day";
        }

        $headings[] = 'Total';

        return $headings;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $this->getColumnLetter($this->daysInMonth + 2); // +2 for Sub_result and Total

                // Merge and center category headers
                foreach ($this->categoryRows as $rowNumber) {
                    $range = "A{$rowNumber}:{$lastColumn}{$rowNumber}";
                    $sheet->mergeCells($range);

                    // Apply additional styling
                    $sheet->getStyle($range)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F0F0F0']
                        ]
                    ]);
                }
            }
        ];
    }
    protected function getColumnLetter($columnIndex): string
    {
        $letters = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $letters = chr($columnIndex % 26 + 65) . $letters;
            $columnIndex = intval($columnIndex / 26);
        }
        return $letters;
    }
}
