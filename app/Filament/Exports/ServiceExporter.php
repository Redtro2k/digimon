<?php

namespace App\Filament\Exports;

use App\Models\Service;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Number;

class ServiceExporter extends Exporter
{
    protected static ?string $model = Service::class;
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer.source')
            ->label('Source'),
            ExportColumn::make('customer.customer_name')
            ->label('Customer Name'),
            ExportColumn::make('customer.mobile_number')
            ->label('Mobile Number'),
            ExportColumn::make('customer.address')
            ->label('Address'),
            ExportColumn::make('vehicle.cs_number')
            ->label('CS Number'),
            ExportColumn::make('vehicle.plate')
            ->label('Plate'),
            ExportColumn::make('vehicle.model')
            ->label('Model'),
            ExportColumn::make('last_service_availed'),
            ExportColumn::make('recommended_pm_service'),
            ExportColumn::make('forecast_status')
                ->formatStateUsing(fn($state) => $state ? 'Open' : 'Close'),
            ExportColumn::make('forecast_date')
            ->formatStateUsing(fn($state) => Carbon::parse($state)->format('Y-m-d')),
            ExportColumn::make('personal_email'),
            ExportColumn::make('personal_mobile'),
            ExportColumn::make('company_email_address'),
            ExportColumn::make('company_mobile'),
            ExportColumn::make('has_fpm')
                ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('first_reminder.category.name')
                ->label('first Category Attempt'),
            ExportColumn::make('first_reminder.sub_result')
                ->label('first Sub Result Attempt'),
            ExportColumn::make('first_reminder.call_back')
                ->label('first Call Back Attempt'),
            ExportColumn::make('second_reminder.category.name')
                ->label('Second Category Attempt'),
            ExportColumn::make('second_reminder.sub_result')
                ->label('Second Sub Result Attempt'),
            ExportColumn::make('second_reminder.call_back')
                ->label('Second Call Back Attempt'),
            ExportColumn::make('third_reminder.category.name')
                ->label('Third Category Attempt'),
            ExportColumn::make('third_reminder.sub_result')
                ->label('Third Sub Result Attempt'),
            ExportColumn::make('third_reminder.call_back')
                ->label('Third Call Back Attempt'),
        ];
    }
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your service export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public static function columnMap(): array
    {
        return [
            'customer.source' => 'Source',
            'customer.customer_name' => 'Customer Name',
            'customer.mobile_number' => 'Mobile Number',
            'customer.address' => 'Address',
            'vehicle.cs_number' => 'CS Number',
            'vehicle.plate' => 'Plate',
            'vehicle.model' => 'Model',
            'last_service_availed' => 'Last Service Availed',
            'recommended_pm_service' => 'Recommended PM Service',
            'forecast_status' => 'Forecast Status',
            'forecast_date' => 'Forecast Date',
            'personal_email' => 'Personal Email',
            'personal_mobile' => 'Personal Mobile',
            'company_email_address' => 'Company Email Address',
            'company_mobile' => 'Company Mobile',
            'has_fpm' => 'Has FPM',
        ];
    }
}
