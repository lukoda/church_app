<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AuctionItem;

class PledgeStatOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total_pledges = AuctionItem::where('card_no', auth()->user()->churchMember->card_no)->count();

        $partialPayedpledges = AuctionItem::where('card_no', auth()->user()->churchMember->card_no)->where('status', 'Pending Auction')->count();

        $payedPledges = AuctionItem::where('card_no', auth()->user()->churchMember->card_no)->where('status', 'Auction Complete')->count();

        return [
            Stat::make('Total Pledges', number_format($total_pledges))
                ->description('Total items pledged')
                ->color('secondary'),
            Stat::make('Total Partial Payed Pledges', number_format($partialPayedpledges))
                ->description('Pledges still pending not completed')
                ->color('warning'),
            Stat::make('Total Payed Pledges', number_format($payedPledges))
                ->description('Pledges completed payment.')
                ->color('success'),
        ];
    }
}
