<?php

namespace App\Events;

use App\Enums\LogActivities;
use Illuminate\Auth\Events\Logout;

class UserLogLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            activity()
                ->causedBy($user)
                ->event(LogActivities::SIGN_OUT->value)
                ->withProperties([
                    'last_logged_in_at' => $user->logged_dt,
                    'signed_out' => now()->toDateTimeString(),
                ])
                ->useLog('system')
                ->log(LogActivities::SIGN_OUT->getDescription());

            $user->update([
                'logout_at' => now()->toDateTimeString(),
            ]);
        }
    }
}
