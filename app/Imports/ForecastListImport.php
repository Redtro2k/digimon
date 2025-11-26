<?php

namespace App\Imports;

use App\Classes\MobileNumber;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ForecastListImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents
{
    use Importable;

    protected $user;

    public function __construct(User $user)
    {
        return $this->user = $user;
    }

    public function model(array $row)
    {
        $row = collect($row)
            ->reject(fn($value, $key) => is_numeric($key) || empty($key))
            ->mapWithKeys(fn($value, $key) => [Str::snake(trim($key)) => $value])
            ->toArray();

        $row = collect($row)->toArray();

        if (empty($row['mobile_number'])) {

            Notification::make()
                ->title('Row Import Failed')
                ->body("the row customer: {$row['customer_name']} doesnt have phone number")
                ->danger()
                ->sendToDatabase($this->user);

            logger()->warning('Empty mobile number');
            return null;
        }
        try {
            $customer = Customer::firstOrCreate(
                ['mobile_number' => $row['mobile_number']],
                [
                    'source'        => $row['source'] ?? null,
                    'customer_name' => $row['customer_name'] ?? null,
                    'address'       => $row['address'] ?? null,
                ]
            );

            $vehicle = $customer->vehicles()->updateOrCreate(
                [
                    'cs_number' => $row['cs_number'] ?? null,
                    'plate' => $row['plate'] ?? null,
                ],
                [
                    'model' => $row['model'] ?? null,
                ]
            );

            return $vehicle->services()->create([
                'dealer_id' => $this->user->dealer()->first()->id,
                'last_service_availed' => $row['last_service_availed'] ?? null,
                'recommended_pm_service' => $row['recommended_pm_service'] ?? null,
                'forecast_status'        => $row['forecast_status'] ?? null,
                'forecast_date'          => $row['forecast_date'] ? Carbon::instance(Date::excelToDateTimeObject($row['forecast_date']))->format('Y-m-d') : null,
                'personal_email'         => $row['personal_email'] ?? null,
                'personal_mobile'        => $row['personal_mobile'] ?? null,
                'company_email_address'  => $row['company_email_address'] ?? null,
                'company_mobile'         => $row['company_mobile'] ?? null,
                'has_fpm' => match(true) {
                    !isset($row['has_fpm']) || trim($row['has_fpm']) === '' => null,
                    strtoupper(trim($row['has_fpm'])) === 'FPM' => 1,
                    default => 0,
                },
            ]);

        }catch (\Exception $exception){
            Log::warning("Invalid date format in row: ",  [
                'row' => $row,
            ]);
            return null;
        }
    }
    public function chunkSize(): int
    {
        return 1000;
    }
    public function headingRow(): int
    {
        return 2;
    }
    public function registerEvents(): array
    {

        return [
            AfterImport::class => function () {
                Notification::make()
                    ->title('Forecast Import Complete')
                    ->body('All forecast data has been imported successfully.')
                    ->success()
                    ->sendToDatabase($this->user);
            },
            ImportFailed::class => function ($event) {
                Notification::make()
                    ->title('Forecast Import Failed')
                    ->body('There was an error importing the forecast file.')
                    ->danger()
                    ->sendToDatabase($this->user);
            },
        ];
    }
}
