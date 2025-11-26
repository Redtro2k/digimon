<?php

namespace App\Classes;

use App\Exports\ReminderAllSummaryExport;
use App\Filament\Exports\ServiceExporter;
use App\Imports\ForecastListImport;
use App\Models\Dealer;
use App\Models\User;
use Carbon\Carbon;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ServiceAction extends Resource
{
   public static function make(): Action
   {
       return Action::make('upload-forecast')
           ->visible(fn() => auth()->user()->can('upload_customer'))
           ->label('Upload Forecast')
           ->color(Color::Indigo)
           ->icon(LucideIcon::CloudUpload)
           ->modalIcon(LucideIcon::CloudUpload)
           ->modal()
           ->modalWidth('2xl') // Upgraded from 'lg' for better space
           ->modalHeading('Upload Forecast Data')
           ->modalSubmitActionLabel('Upload & Process')
           ->modalCancelActionLabel('Cancel')
           ->closeModalByClickingAway(false)
           ->modalDescription('Upload your forecast files to import customer, vehicle, and service data into the system. The system will automatically process and validate the data before importing.')
           ->schema([
               FileUpload::make('file_upload')
                   ->directory('forecast')
                   ->loadingIndicatorPosition('left')
                   ->label('Upload Forecast Data')
                   ->disk('public')
                   ->required()
                   ->acceptedFileTypes([
                       'text/csv',
                       'application/csv',
                       'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                       'application/vnd.ms-excel' // .xls
                   ])
                   ->maxSize(10240)
                   ->helperText('the Rows without mobile numbers will be automatically skipped during processing.')
                   ->hint('Supported format: CSV files up to 10MB')
                   ->downloadable()
                   ->previewable(false)
                   ->deletable()
           ])
           ->action(function($data){
               $filePath = storage_path('app/public/' . $data['file_upload']);
               (new ForecastListImport(auth()->user()))->queue($filePath);

           });
   }

   public static function exports(): ExportAction
   {
       return ExportAction::make()
           ->exporter(ServiceExporter::class)
           ->hidden(fn() => auth()->user()->cannot('export_forecast_service'))
           ->chunkSize(250)
           ->schema([
               Select::make('forecast_status')
                   ->options([
                       'Open' => 'Open',
                       'Close (Already serviced within dealer)' => 'Close (Already serviced within dealer)',
                       'Cancel (Serviced in other dealer)' => 'Cancel (Serviced in other dealer)',
                       'Invalid Contact Number' => 'Invalid Contact Number',
                       'No Contact Number' => 'No Contact Number',
                       'all' => 'All'
                   ])
                   ->default('all')
                   ->required(),
               Select::make('has_fpm')
                   ->options([
                       'yes' => 'Yes',
                       'no' => 'No',
                       'all' => 'All',
                   ])
                   ->default('all')
                   ->required(),
               Select::make('assigned_mras_id')
                   ->helperText('this field are not available for the MRAS user')
                   ->label('Select MRAS')
                   ->multiple()
                   ->options(fn() => User::role('mras')->pluck('name', 'id'))
                   ->hidden(auth()->user()->hasRole('mras'))
                   ->required(!auth()->user()->hasRole('mras')),
               Fieldset::make('Forecast / Date Range')
                   ->schema([
                       Select::make('preset')
                           ->label('Quick Range (Forecast Date)')
                           ->options([
                               'today' => 'Today',
                               'this_week' => 'This Week',
                               'this_month' => 'This Month',
                               'this_year' => 'This Year',
                               'custom' => 'Custom Range',
                           ])
                           ->live()
                           ->afterStateUpdated(function ($state, callable $set) {
                               match ($state) {
                                   'today' => [
                                       $set('startDate', Carbon::today()),
                                       $set('endDate', Carbon::today()),
                                   ],
                                   'this_week' => [
                                       $set('startDate', Carbon::now()->startOfWeek()),
                                       $set('endDate', Carbon::now()->endOfWeek()),
                                   ],
                                   'this_month' => [
                                       $set('startDate', Carbon::now()->startOfMonth()),
                                       $set('endDate', Carbon::now()->endOfMonth()),
                                   ],
                                   'this_year' => [
                                       $set('startDate', Carbon::now()->startOfYear()),
                                       $set('endDate', Carbon::now()->endOfYear()),
                                   ],
                                   default => [
                                       $set('startDate', null),
                                       $set('endDate', null),
                                   ]
                               };
                           })
                           ->columnSpanFull(),
                       DatePicker::make('startDate')->native(false)->maxDate(fn ($get) => $get('endDate')),
                       DatePicker::make('endDate')->native(false)->minDate(fn ($get) => $get('startDate')),
                   ]),
           ])
           ->columnMapping(false)
           ->modifyQueryUsing(function(Builder $query, array $options){

               if($options['has_fpm'] !== 'all'){
                   $query->where('has_fpm', $options['has_fpm']); // filter per FPM
               }

               if($options['forecast_status'] !== 'all'){
                   $query->where('forecast_status', $options['forecast_status']); //filter per status
               }

               $query->whereIn('assigned_mras_id', $options['assigned_mras_id']); // filter mras

               $query->whereBetween('forecast_date', [$options['startDate'], $options['endDate']]);

               return $query;
           });
   }

   public static function resultSummary(): Action
   {
        return Action::make('call_all_result_summary')
            ->hidden(auth()->user()->cannot('call_result_summary'))
            ->label('ALL - Call Result Summary')
            ->icon(LucideIcon::Download)
            ->tooltip('extract the all call result base this month.')
            ->color(Color::Cyan)
            ->schema([
                Select::make('dealer')
                    ->options(fn() => Dealer::query()->pluck('acronym', 'id')->prepend('All Dealer', 'all'))
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('assigned', null)),

                Select::make("assigned")
                    ->multiple()
                    ->options(fn(Get $get) => User::role('mras')
                        ->when($get('dealer') != 'all', fn($q) => $q->join('users_dealers', 'users.id', '=', 'users_dealers.user_id')
                            ->where('users_dealers.dealer_id', $get('dealer'))
                            ->select('users.name', 'users.id')
                            ->distinct()
                        )
                        ->pluck('name','id')),
                Fieldset::make('Filter by Date')
                    ->schema([
                        Select::make('month')
                            ->native(false)
                            ->default(Carbon::now()->month - 1)
                            ->options(collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('F'))->toArray()),
                        Select::make('year')
                            ->native(false)
                            ->default(Carbon::now()->year)
                            ->options(array_combine($r = range(2020, now()->year), $r))
                    ])
            ])
            ->action(function (Action $action, $data) {
                Notification::make()
                    ->title('Successfully Extracted')
                    ->body('Represents completed extractions from all call data.')
                    ->success()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->send();

                return Excel::download(
                    new ReminderAllSummaryExport($data['year'], $data['month'] + 1, $data['assigned']),
                    'reminder_summary.xlsx'
                );
            });
   }
}
