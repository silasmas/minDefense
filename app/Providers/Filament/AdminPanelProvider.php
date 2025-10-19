<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\StateAssetsMap;
use App\Filament\Widgets\AssetsMapWidget;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Widgets\AssetsStatsOverview;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')              // ← utilise le guard web
            ->login()                       // ← active la page de login Filament
            // ->authMiddleware([FilamentAuthenticate::class]) // ← protège le panel
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->globalSearch(true)
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                //  Widgets\FilamentInfoWidget::class,
                  AssetsStatsOverview::class,       // ✅ classe
                 AssetsMapWidget::class,           // ✅ classe
                StateAssetsMap::class,           // ✅ classe
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
           ->renderHook('panels::head.start', fn () => <<<HTML
            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css">
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css">
            <style>
              .leaflet-container { z-index: 0; }
              .map-toolbar { position:absolute; top:12px; right:12px; z-index: 500; }
              .map-legend { position:absolute; left:12px; bottom:12px; z-index:500; background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:8px 10px; box-shadow:0 4px 10px rgba(0,0,0,.06);}
              .map-legend h4{margin:0 0 6px 0; font-size:13px}
              .map-legend .row{display:flex; align-items:center; gap:6px; font-size:12px; margin:4px 0}
              .chip{display:inline-block; width:12px; height:12px; border-radius:2px; border:1px solid #999;}
              .chip-imm { background:#10b981; border-color:#059669; }
              .chip-mat { background:#38bdf8; border-color:#0284c7; border-radius:50%;}
            </style>
        HTML)
        ->renderHook('panels::body.end', fn () => <<<HTML
            <script defer src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
            <script defer src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
        HTML);
    }
}
