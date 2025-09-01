<?php

namespace App\Filament\Widgets;

use App\Models\Veteran;
use App\Models\VeteranPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class VeteransOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null; // rafraîchi à la demande

    protected function getCards(): array
    {
        $total = Veteran::count();
        $recognized30 = Veteran::where('status', 'recognized')
            ->where('updated_at', '>=', now()->subDays(30))->count();
        $sum30 = (float) VeteranPayment::where('status', 'paid')
            ->where('paid_at', '>=', now()->subDays(30))
            ->sum('amount');

        return [
            Card::make('Vétérans (total)', number_format($total)),
            Card::make('Reconnu — 30 j', number_format($recognized30))
                ->description('Mises à jour récentes'),
            Card::make('Pensions payées — 30 j', number_format($sum30, 0, ' ', ' '))
                ->description('CDF'),
        ];
    }
}
