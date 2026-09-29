<?php

namespace App\Providers\Filament;

use App\Filament\Huashu\Resources\CategoryResource;
use App\Filament\Huashu\Resources\OrderResource;
use App\Filament\Huashu\Resources\ProductResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Http\Middleware\EnsureRole;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\Huashu\Widgets\HuashuOrderStatsOverview;
use App\Filament\Huashu\Widgets\HuashuOrdersByStatusChart;
use App\Filament\Huashu\Widgets\HuashuRecentOrdersTable;

class HuashuPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('huashu')
            ->path('huashu-admin')
            ->authGuard('huashu')
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->brandName('OZ Wholesale - 2 - Huashu International')
            ->brandLogo(fn() => view('filament.brand-logo-huashu'))
            ->darkMode()
            ->brandLogoHeight('2rem')
            ->navigationGroups([
                NavigationGroup::make('Orders'),
                NavigationGroup::make('Catalogue'),
            ])
            ->resources([
                OrderResource::class,
                CategoryResource::class,
                ProductResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                HuashuOrderStatsOverview::class,
                HuashuOrdersByStatusChart::class,
                HuashuRecentOrdersTable::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
                EnsureRole::class . ':huashu,oz_admin',
            ]);
    }
}
