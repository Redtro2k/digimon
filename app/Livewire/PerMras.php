<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PerMras extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'perMras';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Per MRAS';
    protected static ?string $subheading = "Overview of customer call activities handled by MRAS representatives for service reminders and booking follow-ups.";
    use InteractsWithPageFilters;

    protected static bool $deferLoading = true;

    public static function canView(): bool
    {
        return auth()->user()->can('per_m_r_a_s');
    }
    protected function getOptions(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $user = User::role('mras')->withCount(['serviceReminders' => function ($q) use ($startDate, $endDate) {
            if ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            }
        }])->get();

        $result = $user->mapWithKeys(fn($user) => [
            $user->gender->title(). $user->name => $user->service_reminders_count,
        ]);

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $result->values()->all(),
            'labels' => $result->keys()->all(),
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
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
                    const value = opts.w.config.series[opts.seriesIndex];
                    return value + ' (' + val.toFixed(1) + '%)';
                },
                style: {
                    fontSize: '12px',
                    colors: ['#000']
                },
                background: {
                enabled: true,
                foreColor: '#fff',
                borderRadius: 4,
                opacity: 0.5,
                },
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val;
                    }
                }
            }
        }
        JS);
    }

}
