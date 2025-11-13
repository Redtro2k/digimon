<?php

namespace App\Livewire;

use App\Models\Reminder;
use Filament\Support\RawJs;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class UnsuccessfulCall extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $chartId = 'unsuccessfulCall';
    protected static ?string $heading = 'Reason for Unsuccessful Call';
    protected static ?string $subheading = 'Displays the different reasons why calls were unsuccessful, such as busy lines, unreachable numbers, or no response.';

    public static function canView(): bool
    {
        return auth()->user()->can('reason_for_unsuccessful_booking');
    }

    protected function getOptions(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $reminder = Reminder::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->where('sub_result', 'unsuccessful')
            ->when(auth()->user()->hasRole('mras'), fn($query) => $query->where('assigned_to', auth()->id()))
            ->get();
        $data = $reminder->groupBy('category_title')->map->count();

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $data->values()->all(),
            'labels' => $data->keys()->all(),
            'legend' => [
                'position' => 'bottom',
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
