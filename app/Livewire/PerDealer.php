<?php

namespace App\Livewire;

use App\Models\Dealer;
use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PerDealer extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'perDealer';

    protected static ?string $heading = 'Per Dealer';

    protected static ?string $subheading = "Customers assigned to each dealer";
    protected static bool $deferLoading = true;

    use InteractsWithPageFilters;

    protected function getOptions(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $dealers = Dealer::with(['users' => function ($query) use ($startDate, $endDate) {
            $query->role('mras')->withCount(['serviceReminders' => function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                }
            }]);
        }])->get();

        $result = $dealers->mapWithKeys(function ($dealer) {
            return [$dealer->acronym => $dealer->users->sum('service_reminders_count')];
        });
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Total Service Reminders',
                    'data' => array_values($result->toArray()),
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($result->toArray()),
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
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'borderRadius' => 5,
                    'borderRadiusApplication' => 'end',
                ],
            ],
            'stroke' => [
                'show' => true,
            ],
            'colors' => ['#f59e0b'],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('per_dealer');
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    // Get total per category for stacked bars
                    const total = opts.w.globals.stackedSeriesTotals[opts.dataPointIndex];
                    const percentage = total ? ((val / total) * 100).toFixed(1) : 0;
                    // Show actual value + percentage
                    return val + ' (' + percentage + '%)';
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
                        // Same format as data label
                        const total = opts.w.globals.stackedSeriesTotals[opts.dataPointIndex];
                        const percentage = total ? ((val / total) * 100).toFixed(1) : 0;
                        return val + ' (' + percentage + '%)';
                    }
                }
            }
        }
        JS);
    }
}
