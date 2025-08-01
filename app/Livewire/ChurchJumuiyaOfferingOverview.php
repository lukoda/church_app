<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\JumuiyaRevenue;
use App\Models\Jumuiya;
use Carbon\Carbon;

class ChurchJumuiyaOfferingOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total_jumuiya_offerings = JumuiyaRevenue::whereIn('jumuiya_id', Jumuiya::where('church_id', auth()->user()->church_id)->pluck('id'))->whereDate('date_recorded', now()->endOfWeek(Carbon::SATURDAY))->where('approval_status', 'Verified')->sum('amount');
        $total_unverified_jumuiya_offerings = JumuiyaRevenue::whereIn('jumuiya_id', Jumuiya::where('church_id', auth()->user()->church_id)->pluck('id'))->whereDate('date_recorded', now()->endOfWeek(Carbon::SATURDAY))->where('approval_status', 'Unverified')->sum('amount');
        $total_jumuiya_attendance = JumuiyaRevenue::whereIn('jumuiya_id', Jumuiya::where('church_id', auth()->user()->church_id)->pluck('id'))->whereDate('date_recorded', now()->endOfWeek(Carbon::SATURDAY))->where('approval_status', 'Verified')->sum('jumuiya_attendance');

        return [
            Stat::make('Total Verified Offerings', number_format($total_jumuiya_offerings))
            ->description('This week total verified jumuiya offerings')
            ->color('success'),

            Stat::make('Total Unverified Offerings', $total_unverified_jumuiya_offerings)
            ->description('This week total unverified jumuiya offerings')
            ->color('danger'),

            Stat::make('Total Jumuiya Attendance', number_format($total_jumuiya_attendance))
            ->description('This week total attendance in jumuiyas')
            ->color('success'),

        ];
    }
}
