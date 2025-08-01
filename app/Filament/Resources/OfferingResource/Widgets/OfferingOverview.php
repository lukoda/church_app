<?php

namespace App\Filament\Resources\OfferingResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Offering;
use App\Models\Card;
use Carbon\Carbon;

class OfferingOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $cards = Card::where('card_status', 'active')->get();

        $widgets = [];
        foreach($cards as $card){
            $card_offerings = Offering::where('card_type', $card->id)->whereYear('amount_registered_on', Carbon::now()->year)->sum('amount_offered');
            $widgets[] = Stat::make($card->card_name, number_format($card_offerings))
                            ->description('Total Offerings for '.$card->card_name);
        }

        return $widgets;
    }
}
