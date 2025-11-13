<?php

namespace App\Livewire;

use App\Models\Service;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\RawJs;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
class MrasPerCustomerCalled extends ApexChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $chartId = 'mrasPerCustomerCalled';

    protected static ?string $heading = 'Down Down Irregularity Chart';
    protected static ?string $subheading = 'Tracks frequency and duration of irregularities or unexpected drops in performance.';
    protected static bool $deferLoading = true;

    public static function canView(): bool
    {
        return auth()->user()->can('irregularity_chart');
    }

    protected int | string | array $columnSpan = 3;
    protected function getOptions(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;


        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'stacked' => true,
            ],
            'series' => [
                [
                    'name' => 'DLR SAP (TMP GENERATED) ('.$tmp = $this->generatedForecast('1. DLR SAP (TMP GENERATED)', $startDate, $endDate).')',
                    'data' => [$tmp],
                ],
                [
                    'name' => 'Dealer Internal Forecast',
                    'data' => [$internal_forecast = $this->generatedForecast('Dealer Internal Forecast', $startDate, $endDate)],
                ],
                [
                    'name' => 'MRS Target',
                    'data' => [0, $mrs_target = (int)$tmp + (int)$internal_forecast],
                ],
                [
                    'name' => 'Called Attempt ('.$call_attempt = $this->callAttempt($startDate, $endDate).')',
                    'data' => [0, 0, $call_attempt]
                ],
                [
                    'name' => 'Target Remaining ('. $target_remaining = (int)$mrs_target - (int)$call_attempt.')',
                    'data' => [0, 0, $target_remaining]
                ],
                [
                    'name' => 'Customer Successfully Called ('.$this->serviceSubResult('successful', $startDate, $endDate).')',
                    'data' => [0, 0, 0, $this->serviceSubResult('successful', $startDate, $endDate)]
                ],
                [
                    'name' => 'Customer Unsuccessful Called ('.$this->serviceSubResult('unsuccessful', $startDate, $endDate).')',
                    'data' => [0, 0, 0, $this->serviceSubResult('unsuccessful', $startDate, $endDate)]
                ],
                [
                    'name' => 'Booked for Appointment',
                    'data' => [0,0,0,0, $this->bookForAppointment(true,  $startDate, $endDate)]
                ],
                [
                    'name' => 'Unsuccessful Appointment',
                    'data' => [0,0,0,0, $this->bookForAppointment(false, $startDate, $endDate)]
                ],
                [
                    'name' => 'MRS Show-up',
                    'data' => [0, 0, 0, 0, 0, $this->customerShowUp($startDate, $endDate)]
                ]

            ],
            'xaxis' => [
                'categories' => ['General Forecast', 'MRS Target', 'Call Attempt', 'Successful Calls', 'MRS Booked', 'MRS Show-Up'],
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
                'opacity' => 0.7, // 0 = fully transparent, 1 = solid
                'type' => 'solid', // keep consistent fill style
            ],
            'legend' => [
                'position' => 'right',
                'offsetX' => 0,
                'offsetY' => 50,
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
    public function generatedForecast($source, $startDate, $endDate)
    {
        return Service::query()->whereHas('customer', function ($query) use($source, $startDate, $endDate) {
            $query->where('source', $source)
                ->when($startDate, fn (Builder $query) => $query->whereDate('forecast_date', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('forecast_date', '<=', $endDate));
        })
        ->count();
    }
    public function callAttempt($startDate, $endDate){
        return Service::query()
            ->when(auth()->user()->hasRole('mras'), function(Builder $query){
                $query->where('assigned_mras_id', auth()->user()->id);
            })
            ->when($startDate, fn (Builder $query) => $query->whereDate('forecast_date', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('forecast_date', '<=', $endDate))
            ->whereHas('latestReminder', function($q) use($startDate, $endDate){
               $q->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                   ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate));
            })->count();
        }
    public function serviceSubResult($status, $startDate, $endDate){
        return Service::query()
            ->when(auth()->user()->hasRole('mras'), function(Builder $query){
                $query->where('assigned_mras_id', auth()->user()->id);
            })
            ->when($startDate, fn (Builder $query) => $query->whereDate('forecast_date', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('forecast_date', '<=', $endDate))
            ->whereHas('latestReminder', function ($query) use($status, $startDate, $endDate) {
            $query->where('sub_result', $status)
                ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate));
        })
        ->count();
    }
    public function bookForAppointment($isUnsuccessful = false, $startDate, $endDate){
        return Service::query()
            ->when(auth()->user()->hasRole('mras'), function(Builder $query){
                $query->where('assigned_mras_id', auth()->user()->id);
            })
            ->whereHas('latestReminder', function ($query) use($isUnsuccessful, $startDate, $endDate) {
                $query->where('sub_result', 'successful')
                    ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                    ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate));
                    if(!$isUnsuccessful){
                        $query->whereNot('category_id', 1);
                    }
                    else{
                        $query->where('category_id', 1);
                    }
            })
            ->count();
    }
    public function customerShowUp($startDate, $endDate){
       return Service::query()
            ->where('has_completed', 1)
           ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
           ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when(auth()->user()->hasRole('mras'), function(Builder $query){
                $query->where('assigned_mras_id', auth()->user()->id);
            })
            ->count();
    }
}
