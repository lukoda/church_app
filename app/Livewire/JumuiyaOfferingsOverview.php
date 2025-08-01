<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\JumuiyaRevenue;
use Carbon\Carbon;

class JumuiyaOfferingsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $thisweekoffering = auth()->user()->churchMember ? JumuiyaRevenue::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('approval_status', 'Approved')->whereDate('created_at','>', now()->startOfWeek(Carbon::SATURDAY)->subDays(1))->whereDate('created_at', '<=', now()->startOfWeek(Carbon::SATURDAY)->addDays(1))->sum('amount') : 0;

        $thismonthoffering = auth()->user()->churchMember ? JumuiyaRevenue::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('approval_status', 'Approved')->whereMonth('created_at', now()->month)->sum('amount') : 0;

        $thisyearoffering = auth()->user()->churchMember ? JumuiyaRevenue::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('approval_status', 'Approved')->whereYear('created_at', now()->year)->sum('amount') : 0;
        return [
            Stat::make('This Week Offerings', number_format($thisweekoffering))
                ->description('This week offerings.')
                ->color('success'),
            Stat::make('This Month Offerings', number_format($thismonthoffering))
                ->description('This month total offerings.')
                ->color('warning'),
            Stat::make('This Year offerings', number_format($thisyearoffering))
                ->description('This year total offerings.')
                ->color('secondary')
        ];
    }
}
