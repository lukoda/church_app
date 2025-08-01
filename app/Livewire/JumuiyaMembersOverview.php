<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\ChurchMember;
use App\Models\JumuiyaMember;
use Carbon\Carbon;
use DB;

class JumuiyaMembersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total_members = ChurchMember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->count();

        $spouses = ChurchMember::where('marital_status', 'Married')->whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status','active')->pluck('spouse_id')->toArray();

        if(ChurchMember::whereIn('id', $spouses)->where('status','active')->exists()){
            $total_families = ChurchMember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status','active')->where('marital_status', 'Married')->whereNotIn('id', $spouses)->count();
        }else{
            $total_families = ChurchMember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status','active')->where('marital_status', 'Married')->count();
        }


        $total_dependants = DB::table('church_members')->join('dependants', 'church_members.id', '=', 'dependants.church_member_id')->where('status', 'active')->whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->count();

        return [
            Stat::make('Total Members', number_format($total_members))
                ->description('Total verified members')
                ->color('success'),
            Stat::make('Total Families', number_format($total_families))
                ->description('Total families in jumuiya')
                ->color('warning'),
            Stat::make('Total Dependants', number_format($total_dependants))
                ->description('Total dependants in jumuiya')
                ->color('warning')
        ];
    }
}
