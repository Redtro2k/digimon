<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AssigneCustomerJob implements ShouldQueue
{
    use Queueable;

    protected Collection $services;
    protected $notifyUser;

    public function __construct($services, ?User $notifyUser = null)
    {
        $this->services = $services;
        $this->notifyUser = $notifyUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->services->load('assignedMras');

        // Check if any service is already assigned
        if ($this->services->some(fn($service) => $service->assigned_mras_id !== null)) {
            if ($this->notifyUser) {
                Notification::make()
                    ->title('Assignment Failed')
                    ->body('Some selected services are already assigned to an MRAS user.')
                    ->danger()
                    ->sendToDatabase($this->notifyUser, isEventDispatched: true);
            }
            return;
        }

        $shuffled = $this->services->shuffle();
        $mras = User::role('mras')->get();

        $chunks = $shuffled->split($mras->count());
        foreach($mras as $mra => $user)
        {
            $userServices = collect($chunks[$mra] ?? []);
            $userServices->chunk(50)->each(function ($subChunk) use ($user) {
                foreach ($subChunk as $service) {
                    try {
                        $service->update(['assigned_mras_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::warning("Failed to assign service ID {$service->id} to user {$user->id}: {$e->getMessage()}");
                    }
                }
            });
            // Optional: send a summary notification for this sub-chunk
            if ($userServices->isNotEmpty()) {
                Notification::make()
                    ->title('New Services Assigned')
                    ->body("You have " . $userServices->count() . " new service(s) assigned.")
                    ->info()
                    ->sendToDatabase($user, isEventDispatched: true)
                    ->broadcast($user);
            }

            Notification::make()
                ->title('Assigned Successfully')
                ->body('Your Selected services have been assigned.')
                ->info()
                ->sendToDatabase($this->notifyUser, isEventDispatched: true);
        }
    }
}
