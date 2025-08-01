<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\Announcement;
use App\Models\Jumuiya;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ChurchAnnouncements extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fas-bullhorn';

    protected static string $view = 'filament.pages.church-announcements';

    protected static ?string $navigationGroup = 'Announcements';

    public int $activeTab = 0;

    public int $thisWeekAnnouncements;

    public int $lastWeekAnnouncements;

    public int $thisMonthAnnouncements;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->checkPermissionTo('view Announcement') && Auth::guard('web')->user()->hasRole('Church Member')){
            return true;
        }else{
            return false;
        }
    }

    public function mountCanAuthorizeAccess(): void
    {
        if(static::canAccess()){
            abort_unless(static::canAccess(), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('web')->user()->checkPermissionTo('view Announcement') && auth()->user()->hasRole('Church Member');
    }
    
    public function getTabs(): array
    {
        return [
            'This Week',
            'Last Week',
            'This Month'
        ];
    }

    public function setThisWeekAnnouncements()
    {
        // $announcements = Announcement::where('status', 'active')->whereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)->orWhereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))
        // ->orWhereJsonContains('sub_parish', auth()->user()->church->id)->orWhereJsonContains('sub_parish', strval(auth()->user()->church->id))->orWhereJsonContains('church', strval(auth()->user()->church->id))->orWhereJsonContains('church', auth()->user()->church->id)->orWhereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->orWhereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
        // ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
        // $this->thisWeekAnnouncements = $announcements;
        if(auth()->user()->hasRole('ArchBishop')){
            $this->thisWeekAnnouncements = Announcement::where('status', 'active')->where('user_id', auth()->user()->id)
            ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
         }else if(auth()->user()->hasRole('Bishop')){
            $this->thisWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'diocese')->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
         }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
            $this->thisWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
         }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor'])){
            $this->thisWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'church')->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
         }else if(auth()->user()->hasRole('SubParish Pastor')){
            $this->thisWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                        $query->where('church_id', auth()->user()->church_id)->where('level', 'sub_parish');
                    })->whereJsonContains('sub_parish', strval(auth()->user()->church_id))->orWhereJsonContains('sub_parish', auth()->user()->church_id)
                    ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
         }else if(auth()->user()->hasRole('Jumuiya Chairperson')){
            $this->thisWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                        $query->where('church_id', auth()->user()->church_id)->where('level', 'jumuiya');
                    })->whereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)
                    ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
         }else if(auth()->user()->hasRole(['Church Member', 'Jumuiya Accountant', 'Committee Member', 'Guest', 'Church Secretary'])){
            $this->thisWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'church_members')->orWhereHas('published_announcement', function(Builder $query){
                    $query->where('church_id', auth()->user()->church_id)->where('level', 'church_members');
                })->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->count();
         }
        // if(count($announcements) == 0){
        //     $this->thisWeekAnnouncements = 0;
        // }else{
        //     $totalThisWeekAnnouncements = 0;
        //     $count = 0;
        //     foreach($announcements as $announcement){
        //         if($announcement->level == 'diocese'){
        //             if($announcement->all_dioceses == true){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->diocese != Null && $announcement->all_church_districts == true){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_church_districts == false && $announcement->all_churches == true){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }else if($announcement->level == 'jimbo'){
        //             if($announcement->all_church_districts == true){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->church_districts != Null && $announcement->all_churches == true){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }else if($announcement->level == 'church'){
        //             if($announcement->all_churches == true){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalThisWeekAnnouncements = $totalThisWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }
        //     }
        //     $this->thisWeekAnnouncements = $totalThisWeekAnnouncements;
        // } 
    }

    public function setLastWeekAnnouncements()
    {
        // $announcements = Announcement::where('status', 'active')->whereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)->orWhereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))
        // ->orWhereJsonContains('sub_parish', auth()->user()->church->id)->orWhereJsonContains('sub_parish', strval(auth()->user()->church->id))->orWhereJsonContains('church', strval(auth()->user()->church->id))->orWhereJsonContains('church', auth()->user()->church->id)->orWhereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->orWhereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
        // ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
        // $this->lastWeekAnnouncements = $announcements;
        if(auth()->user()->hasRole('ArchBishop')){
            $this->lastWeekAnnouncements =  Announcement::query()->where('status', 'active')->where('user_id', auth()->user()->id)
            ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
         }else if(auth()->user()->hasRole('Bishop')){
            $this->lastWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'diocese')->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
         }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
            $this->lastWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
         }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor'])){
            $this->lastWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'church')->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
         }else if(auth()->user()->hasRole('SubParish Pastor')){
            $this->lastWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                        $query->where('church_id', auth()->user()->church_id)->where('level', 'sub_parish');
                    })->whereJsonContains('sub_parish', strval(auth()->user()->church_id))->orWhereJsonContains('sub_parish', auth()->user()->church_id)
                    ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
         }else if(auth()->user()->hasRole('Jumuiya Chairperson')){
            $this->lastWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                        $query->where('church_id', auth()->user()->church_id)->where('level', 'jumuiya');
                    })->whereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)
                    ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
         }else if(auth()->user()->hasRole(['Church Member', 'Jumuiya Accountant', 'Committee Member', 'Guest', 'Church Secretary'])){
            $this->lastWeekAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'church_members')->orWhereHas('published_announcement', function(Builder $query){
                    $query->where('church_id', auth()->user()->church_id)->where('level', 'church_members');
                })->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->count();
         }
        // if(count($announcements) == 0){
        //     $this->lastWeekAnnouncements = 0;
        // }else{
        //     $totalLastWeekAnnouncements = 0;
        //     $count = 0;
        //     foreach($announcements as $announcement){
        //         if($announcement->level == 'diocese'){
        //             if($announcement->all_dioceses == true){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->diocese != Null && $announcement->all_church_districts == true){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_church_districts == false && $announcement->all_churches == true){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }else if($announcement->level == 'jimbo'){
        //             if($announcement->all_church_districts == true){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->church_districts != Null && $announcement->all_churches == true){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }else if($announcement->level == 'church'){
        //             if($announcement->all_churches == true){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalLastWeekAnnouncements = $totalLastWeekAnnouncements +  Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }
        //     }
        //     $this->lastWeekAnnouncements = $totalLastWeekAnnouncements;
        // }
    }

    public function setThisMonthAnnouncements()
    {
        // $announcements = Announcement::where('status', 'active')->whereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)->orWhereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))
        // ->orWhereJsonContains('sub_parish', auth()->user()->church->id)->orWhereJsonContains('sub_parish', strval(auth()->user()->church->id))->orWhereJsonContains('church', strval(auth()->user()->church->id))->orWhereJsonContains('church', auth()->user()->church->id)->orWhereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->orWhereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
        // ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
        // $this->thisMonthAnnouncements = $announcements;
        if(auth()->user()->hasRole('ArchBishop')){
            $this->thisMonthAnnouncements =  Announcement::query()->where('status', 'active')->where('user_id', auth()->user()->id)
            ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
         }else if(auth()->user()->hasRole('Bishop')){
            $this->thisMonthAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'diocese')->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
         }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
            $this->thisMonthAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
         }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor'])){
            $this->thisMonthAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'church')->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
         }else if(auth()->user()->hasRole('SubParish Pastor')){
            $this->thisMonthAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                        $query->where('church_id', auth()->user()->church_id)->where('level', 'sub_parish');
                    })->whereJsonContains('sub_parish', strval(auth()->user()->church_id))->orWhereJsonContains('sub_parish', auth()->user()->church_id)
                    ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
         }else if(auth()->user()->hasRole('Jumuiya Chairperson')){
            $this->thisMonthAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                        $query->where('church_id', auth()->user()->church_id)->where('level', 'jumuiya');
                    })->whereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)
                    ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
         }else if(auth()->user()->hasRole(['Church Member', 'Jumuiya Accountant', 'Committee Member', 'Guest', 'Church Secretary'])){
            $this->thisMonthAnnouncements = Announcement::query()->where('status', 'active')->where('published_level', 'church_members')->orWhereHas('published_announcement', function(Builder $query){
                    $query->where('church_id', auth()->user()->church_id)->where('level', 'church_members');
                })->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->count();
         }
        // if(count($announcements) == 0){
        //     $this->thisMonthAnnouncements = 0;
        // }else{
        //     $totalMonthAnnouncements = 0;
        //     $count = 0;
        //     foreach($announcements as $announcement){
        //         $count++;
        //         if($announcement->level == 'diocese'){
        //             if($announcement->all_dioceses == true){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements +  Announcement::whereId($announcement->id)->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->where('dinomination_id', strval(auth()->user()->church->churchDistrict->diocese->dinomination_id))->count();
        //             }else if($announcement->diocese != Null && $announcement->all_church_districts == true){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::whereId($announcement->id)->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_church_districts == false && $announcement->all_churches == true){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }else if($announcement->level == 'jimbo'){
        //             if($announcement->all_church_districts == true){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->church_districts != Null && $announcement->all_churches == true){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }else if($announcement->level == 'church'){
        //             if($announcement->all_churches == true){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }else if($announcement->all_churches == false && $announcement->church != Null){
        //                 $totalMonthAnnouncements = $totalMonthAnnouncements + Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church', strval(auth()->user()->church->id))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->count();
        //             }
        //         }
        //     }
        //     $this->thisMonthAnnouncements = $totalMonthAnnouncements;
        // }
    }

    public function table (Table $table): Table
    {
        return $table
                ->heading('Church Announcements')
                ->query(function(){
                    if($this->activeTab == 0){
                        $announcements = Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->get();
                        // return Announcement::query()->where('status', 'active')->whereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)->orWhereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))
                        //        ->orWhereJsonContains('sub_parish', auth()->user()->church->id)->orWhereJsonContains('sub_parish', strval(auth()->user()->church->id))->orWhereJsonContains('church', strval(auth()->user()->church->id))->orWhereJsonContains('church', auth()->user()->church->id)->orWhereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->orWhereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
                        //        ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                               if(auth()->user()->hasRole('ArchBishop')){
                                return Announcement::query()->where('status', 'active')->where('user_id', auth()->user()->id)
                                ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                             }else if(auth()->user()->hasRole('Bishop')){
                                return Announcement::query()->where('status', 'active')->where('published_level', 'diocese')->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
                                ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                             }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
                                return Announcement::query()->where('status', 'active')->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)
                                ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                             }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor'])){
                                return Announcement::query()->where('status', 'active')->where('published_level', 'church')->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                                ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                             }else if(auth()->user()->hasRole('SubParish Pastor')){
                                 return Announcement::query()->where('status', 'active')->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                                            $query->where('church_id', auth()->user()->church_id)->where('level', 'sub_parish');
                                        })->whereJsonContains('sub_parish', strval(auth()->user()->church_id))->orWhereJsonContains('sub_parish', auth()->user()->church_id)
                                        ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                             }else if(auth()->user()->hasRole('Jumuiya Chairperson')){
                                  return Announcement::query()->where('status', 'active')->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                                            $query->where('church_id', auth()->user()->church_id)->where('level', 'jumuiya');
                                        })->whereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)
                                        ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                             }else if(auth()->user()->hasRole(['Church Member', 'Jumuiya Accountant', 'Committee Member', 'Guest', 'Church Secretary'])){
                              return Announcement::query()->where('status', 'active')->where('published_level', 'church_members')->orWhereHas('published_announcement', function(Builder $query){
                                        $query->where('church_id', auth()->user()->church_id)->where('level', 'church_members');
                                    })->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                                    ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
                             }else{
                                return Announcement::query()->whereId(0);
                             }
                        // if(count($announcements) == 0){
                        //     return Announcement::query()->whereNull('created_at');
                        // }else{
                        //     foreach($announcements as $announcement){
                        //         if($announcement->level == 'diocese'){
                        //             if($announcement->all_dioceses == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->diocese != Null && $announcement->all_church_districts == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_church_districts == false && $announcement->all_churches == true){2024-02-21
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }else if($announcement->level == 'jimbo'){
                        //             if($announcement->all_church_districts == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->church_districts != Null && $announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }else if($announcement->level == 'church'){
                        //             if($announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }
                        //     }
                        // }

                    }else if($this->activeTab == 1){
                        $announcements = Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->get();
                        // return Announcement::query()->where('status', 'active')->whereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)->orWhereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))
                        // ->orWhereJsonContains('sub_parish', auth()->user()->church->id)->orWhereJsonContains('sub_parish', strval(auth()->user()->church->id))->orWhereJsonContains('church', strval(auth()->user()->church->id))->orWhereJsonContains('church', auth()->user()->church->id)->orWhereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->orWhereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
                        // ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                        if(auth()->user()->hasRole('ArchBishop')){
                            return Announcement::query()->where('status', 'active')->where('user_id', auth()->user()->id)
                            ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                         }else if(auth()->user()->hasRole('Bishop')){
                            return Announcement::query()->where('status', 'active')->where('published_level', 'diocese')->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
                            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                         }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
                            return Announcement::query()->where('status', 'active')->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)
                            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                         }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor'])){
                            return Announcement::query()->where('status', 'active')->where('published_level', 'church')->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                         }else if(auth()->user()->hasRole('SubParish Pastor')){
                             return Announcement::query()->where('status', 'active')->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                                        $query->where('church_id', auth()->user()->church_id)->where('level', 'sub_parish');
                                    })->whereJsonContains('sub_parish', strval(auth()->user()->church_id))->orWhereJsonContains('sub_parish', auth()->user()->church_id)
                                    ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                         }else if(auth()->user()->hasRole('Jumuiya Chairperson')){
                              return Announcement::query()->where('status', 'active')->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                                        $query->where('church_id', auth()->user()->church_id)->where('level', 'jumuiya');
                                    })->whereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)
                                    ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                         }else if(auth()->user()->hasRole(['Church Member', 'Jumuiya Accountant', 'Committee Member', 'Guest', 'Church Secretary'])){
                          return Announcement::query()->where('status', 'active')->where('published_level', 'church_members')->orWhereHas('published_announcement', function(Builder $query){
                                    $query->where('church_id', auth()->user()->church_id)->where('level', 'church_members');
                                })->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                                ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7));
                         }else{
                            return Announcement::query()->whereId(0);
                         }
                        // if(count($announcements) == 0){
                        //     return Announcement::query()->whereNull('created_at');
                        // }else{
                        //     foreach($announcements as $announcement){
                        //         if($announcement->level == 'diocese'){
                        //             if($announcement->all_dioceses == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->diocese != Null && $announcement->all_church_districts == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_church_districts == false && $announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }else if($announcement->level == 'jimbo'){
                        //             if($announcement->all_church_districts == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->church_districts != Null && $announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }else if($announcement->level == 'church'){
                        //             if($announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->subDays(7))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->subDays(7))->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }
                        //     }
                        // }
                    }else if($this->activeTab == 2){
                        $announcements = Announcement::where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->get();
                        // return Announcement::query()->where('status', 'active')->whereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)->orWhereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))
                        // ->orWhereJsonContains('sub_parish', auth()->user()->church->id)->orWhereJsonContains('sub_parish', strval(auth()->user()->church->id))->orWhereJsonContains('church', strval(auth()->user()->church->id))->orWhereJsonContains('church', auth()->user()->church->id)->orWhereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->orWhereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
                        // ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                        if(auth()->user()->hasRole('ArchBishop')){
                            return Announcement::query()->where('status', 'active')->where('user_id', auth()->user()->id)
                            ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                         }else if(auth()->user()->hasRole('Bishop')){
                            return Announcement::query()->where('status', 'active')->where('published_level', 'diocese')->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)
                            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                         }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
                            return Announcement::query()->where('status', 'active')->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)
                            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                         }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor'])){
                            return Announcement::query()->where('status', 'active')->where('published_level', 'church')->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                         }else if(auth()->user()->hasRole('SubParish Pastor')){
                             return Announcement::query()->where('status', 'active')->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                                        $query->where('church_id', auth()->user()->church_id)->where('level', 'sub_parish');
                                    })->whereJsonContains('sub_parish', strval(auth()->user()->church_id))->orWhereJsonContains('sub_parish', auth()->user()->church_id)
                                    ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                         }else if(auth()->user()->hasRole('Jumuiya Chairperson')){
                              return Announcement::query()->where('status', 'active')->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                                        $query->where('church_id', auth()->user()->church_id)->where('level', 'jumuiya');
                                    })->whereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)
                                    ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                         }else if(auth()->user()->hasRole(['Church Member', 'Jumuiya Accountant', 'Committee Member', 'Guest', 'Church Secretary'])){
                          return Announcement::query()->where('status', 'active')->where('published_level', 'church_members')->orWhereHas('published_announcement', function(Builder $query){
                                    $query->where('church_id', auth()->user()->church_id)->where('level', 'church_members');
                                })->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                                ->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month);
                         }else{
                            return Announcement::query()->whereId(0);
                         }
                        //     return Announcement::query()->whereNull('created_at');
                        // }else{
                        //     foreach($announcements as $announcement){
                        //         if($announcement->level == 'diocese'){
                        //             if($announcement->all_dioceses == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->diocese != Null && $announcement->all_church_districts == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_church_districts == false && $announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }else if($announcement->level == 'jimbo'){
                        //             if($announcement->all_church_districts == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->church_districts != Null && $announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }else if($announcement->level == 'church'){
                        //             if($announcement->all_churches == true){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }else if($announcement->all_churches == false && $announcement->church != Null){
                        //                 return Announcement::query()->where('status', 'active')->whereDate('begin_date','<=', now()->toDateString())->whereMonth('created_at', now()->month)->whereJsonContains('church', auth()->user()->church->id)->where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->orderBy('created_at', 'desc');
                        //             }
                        //         }
                        //     }
                        // }
                    }else{
                        return Announcement::query()->whereNull('created_at');
                    }
                })
                ->columns([
                    TextColumn::make('begin_date')
                        ->label('Published On')
                        ->date()
                        ->description(fn(Model $record, $state) : string => 'Remaining '.Carbon::parse($state)->diffInDays($record->end_date).' Days'),
                    TextColumn::make('end_date')
                        ->date(),
                    TextColumn::make('level'),
                    TextColumn::make('status')
                        ->badge()
                        ->color(fn(string $state): string => match ($state){
                            'active' => 'success',
                            'inactive' => 'danger'
                        })
                ])
                ->emptyStateIcon('fas-bullhorn')
                ->emptyStateHeading('No new Announcements')
                ->emptyStateDescription('Once you have new announcements will appear here.')
                ->actions([
                    Action::make('view_announcement')
                        ->url(function(Model $record){
                           return route('filament.admin.pages.view-church-announcements', ['record' => $record->id]);
                        }),
                    ]);
    }

}
