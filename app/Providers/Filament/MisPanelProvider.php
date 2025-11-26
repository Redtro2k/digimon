<?php

namespace App\Providers\Filament;

use App\Filament\Pages\NewLogin;
use App\Filament\Pages\NewRegistration;
use App\Filament\Provider\AvatarProvider\AvatarPlaceholderProvider;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\CustomerTimerWidget;
use App\Http\Middleware\RolesRedirection;
use App\Livewire\Feeds;
use App\Livewire\MRAScalledToday;
use App\NavigationGroup;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Components\Livewire;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use lockscreen\FilamentLockscreen\Lockscreen;
use Muazzam\SlickScrollbar\SlickScrollbarPlugin;
use Spatie\Activitylog\Models\Activity;

class MisPanelProvider extends PanelProvider
{
    public string $name = 'Timer Widget';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->registration(NewRegistration::class)
            ->login(NewLogin::class)
            ->brandLogo(Storage::disk('public')->url('Logo/logo.png'))
            ->brandLogoHeight('7rem')
            ->default()
            ->spa()
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->id('digimon')
            ->path('digimon')
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Cyan,
            ])
            ->navigationGroups([
                'Dealer',
            ])
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
            ])
            ->plugins([
                FilamentApexChartsPlugin::make()
            ])
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->font('Manrope')
            ->topbar(false)
            ->plugins([
                BreezyCore::make(),
                Lockscreen::make(),
                SlickScrollbarPlugin::make(),
                EasyFooterPlugin::make()
                    ->withBorder(true)
                    ->withLogo(
                        Storage::disk('public')->url('Logo/logo.png'),
                        '/digimon/login',
                        null,
                        40)
                    ->withLinks([
                        ['title' => ' About', 'url' => 'https://example.com/about'],
                        ['title' => 'CGV', 'url' => 'https://example.com/cgv'],
                        ['title' => 'Privacy Policy', 'url' => 'https://example.com/privacy-policy']
                    ])
                    ->hiddenFromPagesEnabled(),
                FilamentShieldPlugin::make()
                    ->navigationGroup(LucideIcon::Shield),
                GlobalSearchModalPlugin::make()
                    ->modal(width:Width::Large,hasCloseButton: false)
                    ->highlightQueryStyles([
                        'background-color' => 'yellow',
                        'font-weight' => 'bold',
                    ])
                    ->localStorageMaxItemsAllowed(20)
                    ->RetainRecentIfFavorite(true)
                    ->associateItemsWithTheirGroups()
                    ->showGroupSearchCounts()
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/mis/theme.css')
            ->defaultAvatarProvider(AvatarPlaceholderProvider::class);
    }
}
