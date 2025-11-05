<?php
namespace App\Http\Response;

use App\Enums\LogActivities;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;


class CustomLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $auth = auth()->user();

        $auth->update([
            'logged_dt' => now()->toDateTimeString(),
            'last_login_dt' => null,
        ]);

        activity()
            ->causedBy($auth)
            ->event(LogActivities::SIGN_IN->value)
            ->useLog('system')
            ->withProperties([
                'logged_at' => $auth->logged_dt,
            ])
            ->log(LogActivities::SIGN_IN->getDescription());

        return redirect()->to(Dashboard::getUrl(panel: 'digimon'));
    }
}
