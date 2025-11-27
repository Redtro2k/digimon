<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PerMrasCalledStatistics extends ApexChartWidget
{

    use InteractsWithPageFilters;
    protected static ?string $chartId = 'perMrasCalledStatistics';
    protected int | string | array $columnSpan = 2;

    protected static ?string $heading = 'Per MRAS Called Statistics & Assigned';
    protected static ?string $subheading = 'Displays the number of calls made per MRAS and the total customers assigned to each.';

    public static function canView(): bool
    {
        return auth()->user()->can('per_m_r_a_s_called_statistics');
    }

    protected function getOptions(): array
    {
        $users = User::role('mras')->with(['serviceReminders', 'assignedService'])->get();

        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $userNames = $users->pluck('name')->toArray();

        // Calculate data and percentages for each user
        $calledCustomerData = [];
        $assignedServiceData = [];
        $percentages = [];

        foreach ($users as $user) {
            $called = $user->serviceReminders()
                ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->count();
            $assigned = $user->assignedService()
                ->when($startDate, fn (Builder $query) => $query->whereDate('assigned_date', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('assigned_date', '<=', $endDate))
                ->count();
            $percentage = $assigned > 0 ? (($called / $assigned) * 100) : 0;

            $calledCustomerData[] = $called;
            $assignedServiceData[] = $assigned;
            $percentages[] = round($percentage, 1);
        }

        $series = [
            [
                'name' => 'Called Customer',
                'data' => $calledCustomerData,
            ],
            [
                'name' => 'Assigned',
                'data' => $assignedServiceData,
            ],
        ];

        // Create categories with percentages
        $categories = array_map(function($name, $percentage) {
            return $name . ' (' . $percentage . '%)';
        }, $userNames, $percentages);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'stacked' => true,
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['oklch(60.6% 0.25 292.717)', 'oklch(69.6% 0.17 162.48)', 'oklch(55.5% 0.163 48.998)', 'oklch(54.6% 0.245 262.881)', 'oklch(74.6% 0.16 232.661)', 'oklch(55.1% 0.027 264.364)', 'oklch(65.6% 0.241 354.308)',
                'oklch(60% 0.118 184.704)', 'oklch(76.8% 0.233 130.85)', 'skyblue'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 2,
                    'borderRadiusApplication' => 'end',
                    'horizontal' => false,
                    'columnWidth' => '35%',
                    'dataLabels' => [
                        'position' => 'center',
                    ],
                ],
            ],
            'fill' => [
                'opacity' => 0.7,
                'type' => 'solid',
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
    {
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                // Get the series index (0 = Called Customer, 1 = Assigned Service)
                const seriesIndex = opts.seriesIndex;
                const dataPointIndex = opts.dataPointIndex;

                // Get values from both series
                const calledCustomer = opts.w.config.series[0].data[dataPointIndex];
                const assignedService = opts.w.config.series[1].data[dataPointIndex];

                // Only calculate percentage for Called Customer (series 0)
                if (seriesIndex === 0) {
                    const percentage = assignedService > 0 ? ((calledCustomer / assignedService) * 100).toFixed(1) : 0;
                    return calledCustomer + ' (' + percentage + '%)';
                }

                // For Assigned Service, just show the value
                return assignedService;
            },
            style: {
                colors: ['#fff'],
                fontSize: '12px',
                fontWeight: 'bold',
            },
            background: {
                enabled: true,
                foreColor: '#000',
                borderRadius: 4,
                opacity: 0.6,
            },
        },
        tooltip: {
            y: {
                formatter: function (val, opts) {
                    const seriesIndex = opts.seriesIndex;
                    const dataPointIndex = opts.dataPointIndex;

                    const calledCustomer = opts.w.config.series[0].data[dataPointIndex];
                    const assignedService = opts.w.config.series[1].data[dataPointIndex];

                    if (seriesIndex === 0) {
                        const percentage = assignedService > 0 ? ((calledCustomer / assignedService) * 100).toFixed(1) : 0;
                        return calledCustomer + ' (' + percentage + '%)';
                    }

                    return assignedService;
                }
            }
        }
    }
    JS);
    }
}
