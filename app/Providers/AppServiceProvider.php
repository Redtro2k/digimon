<?php

namespace App\Providers;

use App\Events\UserLogLogout;
use App\Http\Response\CustomLoginResponse;
use App\Http\Response\CustomLogoutResponse;
use Filament\Auth\Http\Responses\LoginResponse;
use Filament\Auth\Http\Responses\LogoutResponse;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(LoginResponse::class, CustomLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Model::preventLazyLoading();
        Event::listen(Logout::class, UserLogLogout::class);
    }
}
