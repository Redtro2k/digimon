<?php

namespace App\Classes;

use App\Imports\ForecastListImport;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Maatwebsite\Excel\Excel;

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
               (new ForecastListImport)->queue($filePath);

//               Notification::make()
//                   ->title('Successfully Inserted')
//                   ->body('CSV file successfully uploaded.')
//                   ->success()
//                   ->send();

           });
   }
}
