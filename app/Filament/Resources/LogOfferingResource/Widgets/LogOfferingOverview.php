<?php

namespace App\Filament\Resources\LogOfferingResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\LogOffering;
use App\Models\AdhocOffering;
use Carbon\Carbon;

class LogOfferingOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $adhocOfferings = AdhocOffering::where('church_id', auth()->user()->church_id)->get();
        $logOfferings = LogOffering::where('church_id', auth()->user()->church_id)->whereDate('date', '>=', Carbon::now()->startOfWeek(Carbon::SUNDAY))->orWhereDate('date', '<', Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays(7))->get();
        $widgets = [];
        foreach($adhocOfferings as $adhocOffering){
            $widgets[] = Stat::make($adhocOffering->title, number_format($logOfferings->where('adhoc_offering_id', $adhocOffering->id)->sum('amount_committee')))
                            ->description('Total Offerings this week for '.$adhocOffering->title);
        }

        return $widgets;

    }
}
