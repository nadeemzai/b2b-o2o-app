<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\BannerResource;
use App\Filament\Admin\Resources\ConversationResource;
use App\Filament\Admin\Resources\OrderAuditResource;
use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\RetailerResource;
use App\Filament\Admin\Resources\TownshipStoreResource;
use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Pages\CommissionDashboard;
use App\Filament\Admin\Pages\HomepageSettings;
use App\Filament\Admin\Pages\ReviewSettings;
use App\Filament\Admin\Pages\AiSettings;
use App\Filament\Admin\Resources\ProductResource;
use App\Filament\Admin\Widgets\OrdersByStatusChart;
use App\Filament\Admin\Widgets\OrderStatsOverview;
use App\Filament\Admin\Widgets\RecentOrdersTable;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('OZ Wholesale B2B')
            ->brandLogo(fn() => view('filament.brand-logo'))
            ->darkMode()
            ->brandLogoHeight('2rem')
            ->navigationGroups([
                NavigationGroup::make('Content'),
                NavigationGroup::make('Catalogue'),
                NavigationGroup::make('KYC & Retailers'),
                NavigationGroup::make('Retailer Relations'),
                NavigationGroup::make('Operations'),
                NavigationGroup::make('Settings'),
                NavigationGroup::make('System')
                    ->collapsed(),
            ])
            ->resources([
                BannerResource::class,
                ConversationResource::class,
                ProductResource::class,
                RetailerResource::class,
                TownshipStoreResource::class,
                OrderResource::class,
                OrderAuditResource::class,
                UserResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
                CommissionDashboard::class,
                HomepageSettings::class,
                ReviewSettings::class,
                AiSettings::class,
            ])
            ->widgets([
                OrderStatsOverview::class,
                OrdersByStatusChart::class,
                RecentOrdersTable::class,
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
            ]);
    }
}
