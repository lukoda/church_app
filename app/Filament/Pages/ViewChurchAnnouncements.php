<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use App\Models\Announcement;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Section as ViewSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use LaraZeus\Accordion\Infolists\Accordion;
use LaraZeus\Accordion\Infolists\Accordions;
use Filament\Infolists\Components\ViewEntry;
use Filament\Support\Enums\MaxWidth;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Checkbox;
use App\Models\Diocese;
use App\Models\ChurchDistrict;
use App\Models\Church;
use App\Models\Jumuiya;
use Filament\Forms\Components\Grid;
use App\Models\AnnouncementPublications;
use Filament\Support\Enums\ActionSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewChurchAnnouncements extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.view-church-announcements';

    public int $record = 0;

    private int $passed_record_param = 0;

    public ?array $announcements;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->checkPermissionTo('view Announcement')){
            return true;
        }else {
            return false;
        }
    }

    public function setRecordParam()
    {
        $id = $_REQUEST['record'];
        if(is_numeric($id)){
            $this->record = $id;
        }else{
            Notification::make()
            ->title('Page Not Found')
            ->body('Sorry, the requested page does not exist.')
            ->danger()
            ->send();
            redirect()->to('/admin/church-announcements'); 
            // redirect()->to('admin/church-announcements');
            // Notification::make()
            //     ->danger()
            //     ->title('The record doesn\'t exist')
            //     ->body('Please select valid record in table.')
            //     ->send();
        }
    }

    // public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null): string
    // {
    //     if (blank($panel) || Filament::getPanel($panel)->hasTenancy()) {
    //         $parameters['tenant'] ??= ($tenant ?? Filament::getTenant());
    //     }
    //     if(is_numeric($parameters['record'])){
    //         $this->passed_record_param = $parameters['record'];
    //     }
    //     return route(static::getRouteName($panel), $parameters, $isAbsolute);
    // }

    public function mountCanAuthorizeAccess(): void
    {
        $this->setRecordParam();
        if(static::canAccess()){
            if(Auth::guard('web')->user()->hasRole('ArchBishop')){
                if(Announcement::whereId($this->passed_record_param)->where('status', 'active')->where('user_id', Auth::guard('web')->user()->id)->whereDate('begin_date','<=', now()->toDateString())->exists()){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Page Not Found')
                    ->body('Sorry, the requested page does not exist.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin/church-announcements');
                }
             }else if(Auth::guard('web')->user()->hasRole('Bishop')){
                if(Announcement::whereId($this->passed_record_param)->where('status', 'active')->where('published_level', 'diocese')->whereJsonContains('diocese', strval(Auth::guard('web')->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', Auth::guard('web')->user()->church->churchDistrict->diocese->id)->orWhere('user_id', Auth::guard('web')->user()->id)->whereDate('begin_date','<=', now()->toDateString())->exists()){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Page Not Found')
                    ->body('Sorry, the requested page does not exist.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin/church-announcements');
                }
             }else if(Auth::guard('web')->user()->hasRole('ChurchDistrict Pastor')){
                if(Announcement::whereId($this->passed_record_param)->where('status', 'active')->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(Auth::guard('web')->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', Auth::guard('web')->user()->church->churchDistrict->id)->orWhere('user_id', Auth::guard('web')->user()->id)->whereDate('begin_date','<=', now()->toDateString())->exists()){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Page Not Found')
                    ->body('Sorry, the requested page does not exist.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin/church-announcements');
                }
             }else if(Auth::guard('web')->user()->hasRole(['Senior Pastor', 'Pastor'])){
                if(Announcement::whereId($this->passed_record_param)->where('status', 'active')->where('published_level', 'church')->whereJsonContains('church', strval(Auth::guard('web')->user()->church_id))->orWhereJsonContains('church', Auth::guard('web')->user()->church_id)->orWhere('user_id', Auth::guard('web')->user()->id)->whereDate('begin_date','<=', now()->toDateString())->exists()){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Page Not Found')
                    ->body('Sorry, the requested page does not exist.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin/church-announcements'); 
                }
             }else if(Auth::guard('web')->user()->hasRole('SubParish Pastor')){
                if(Announcement::whereId($this->passed_record_param)->where('status', 'active')->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                    $query->where('church_id', Auth::guard('web')->user()->church_id)->where('level', 'sub_parish');
                })->whereJsonContains('sub_parish', strval(Auth::guard('web')->user()->church_id))->orWhereJsonContains('sub_parish', Auth::guard('web')->user()->church_id)->orWhere('user_id', Auth::guard('web')->user()->id)->whereDate('begin_date','<=', now()->toDateString())->exists()){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Page Not Found')
                    ->body('Sorry, the requested page does not exist.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin/church-announcements');  
                }
             }else if(Auth::guard('web')->user()->hasRole('Jumuiya Chairperson')){
                if(Announcement::whereId($this->passed_record_param)->where('status', 'active')->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                    $query->where('church_id', Auth::guard('web')->user()->church_id)->where('level', 'jumuiya');
                })->whereJsonContains('jumuiya', strval(Auth::guard('web')->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', Auth::guard('web')->user()->churchMember->jumuiya_id)->whereDate('begin_date','<=', now()->toDateString())->exists()){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Page Not Found')
                    ->body('Sorry, the requested page does not exist.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin/church-announcements');  
                }
             }else if(Auth::guard('web')->user()->hasRole(['Church Member', 'Jumuiya Accountant', 'Committee Member', 'Church Secretary']) || Auth::guard('web')->user()->hasRole('Guest')){
                if(Announcement::whereId($this->passed_record_param)->where('status', 'active')->where('published_level', 'church_members')->orWhereHas('published_announcement', function(Builder $query){
                    $query->where('church_id', Auth::guard('web')->user()->church_id)->where('level', 'church_members');
                })->whereJsonContains('church', strval(Auth::guard('web')->user()->church_id))->orWhereJsonContains('church', Auth::guard('web')->user()->church_id)->whereDate('begin_date','<=', now()->toDateString())->exists()){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Page Not Found')
                    ->body('Sorry, the requested page does not exist.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin/church-announcements'); 
                }
             }
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your Administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        if(auth()->user()->hasRole(['Senior Pastor', 'Pastor', 'SubParish Pastor'])){
            return [
                Action::make('publish_announcement')
                ->form([
                    Grid::make(3)
                    ->schema([
                        Select::make('level')
                        ->reactive()
                        ->options(function() {
                            if(auth()->user()->hasRole(['Senior Pastor', 'Pastor']) && auth()->user()->church->where('church_type', 'parish')){
                                return [
                                    'sub_parish' => 'sub parish',
                                    'jumuiya' => 'Jumuiya',
                                    'church_members' => 'All Church Members'
                                ];
                            }else if(auth()->user()->hasRole('SubParish Pastor') && auth()->user()->church->where('church_type', 'sub_parish')){
                                return [
                                    'jumuiya' => 'Jumuiya',
                                    'church_members' => 'All Church Members'
                                ];
                            }
                        })
                        ->required()
                        ->visible(function(){
                            $announcement = Announcement::whereId($this->record)->first();
                            if($announcement->published_level == 'singular'){
                                return true;
                            }else{
                                return false;
                            }
                        })
                        ->afterStateUpdated(function(Set $set){
                            $set('all_sub_parishes', true);
                            $set('sub_parish', []);
                            $set('all_jumuiyas', true);
                            $set('jumuiya', []);
                        }),
            
                        Checkbox::make('all_sub_parishes')
                            ->reactive()
                            ->required()
                            ->default('true')
                            ->inline(false)
                            ->visible(function(Get $get){
                                    if($get('level') == 'sub_parish'){
                                            return true;
                                    }else{
                                        return false;
                                    }
                            })
                            ->afterStateUpdated(function(Set $set){
                                $set('sub_parish', []);
                                $set('all_jumuiyas', true);
                                $set('jumuiya', []);
                            }),
            
                        Select::make('sub_parish')
                            ->reactive()
                            ->multiple()
                            ->options(Church::where('parent_church', auth()->user()->church_id)->pluck('name', 'id'))
                            ->visible(function(Get $get, string $context){
                                if($get('all_sub_parishes')){
                                    return false;
                                }else{
                                    return true;
                                }
                            })
                            ->afterStateUpdated(function(Set $set){
                                $set('all_jumuiyas', true);
                                $set('jumuiya', []);
                            }),
            
                        Checkbox::make('all_jumuiyas')
                            ->reactive()
                            ->required()
                            ->default('true')
                            ->inline(false)
                            ->visible(function(Get $get){
                                        if($get('level') == 'jumuiya'){
                                            return true;
                                        }else{
                                            return false;
                                        }
                            })
                            ->afterStateUpdated(function(Set $set){
                                $set('jumuiya', []);
                            }),
            
                        Select::make('jumuiya')
                            ->reactive()
                            ->options(Jumuiya::all()->where('church_id', auth()->user()->church_id)->pluck('name', 'id'))
                            ->multiple()
                            ->visible(function(Get $get){
                                if($get('level') == 'jumuiya'){
                                    if($get('all_jumuiyas')){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                }else{                                    
                                    return false;
                                }
                            }),
                    ])
    
                ])
                ->action(function(array $data): void {
                    $announcement = Announcement::whereId($this->record)->first();
                    if(array_key_exists('level', $data)){
                        if(AnnouncementPublications::where('announcement_id', $announcement->id)->where('church_id', auth()->user()->church_id)->exists()){
                            $publishedAnnouncement = AnnouncementPublications::where('announcement_id', $announcement->id)->where('church_id', auth()->user()->church_id)->first();
                            $publishedAnnouncement->level = $data['level'];
                            $publishedAnnouncement->user_id = auth()->user()->id;
                            $publishedAnnouncement->sub_parish = $data['sub_parish'] ?? Null;
                            $publishedAnnouncement->jumuiya = $data['jumuiya'] ?? Null;
                            $publishedAnnouncement->church_members = $data['level'] == 'church_members' ? true : false;
                            $publishedAnnouncement->save();
                        }else{
                            $announcement_publish = new AnnouncementPublications;
                            $announcement_publish->level = $data['level'];
                            $announcement_publish->church_id = auth()->user()->church_id;
                            $announcement_publish->user_id = auth()->user()->id;
                            $announcement_publish->announcement_id = $announcement->id;
                            $announcement_publish->sub_parish = $data['sub_parish'] ?? Null;
                            $announcement_publish->jumuiya = $data['jumuiya'] ?? Null;
                            $announcement_publish->church_members = $data['level'] == 'church_members' ? true : false;
                            $announcement_publish->save();
                        }
                    }else{
                        if($announcement->level == 'diocese'){
                            $announcement->update([
                                'published_level' => 'jimbo'
                            ]);
                        }else if($announcement->level == 'jimbo'){
                            $announcement->update([
                                'published_level' => 'church'
                            ]);
                        }else if($announcement->level == 'church'){
                            $announcement->update([
                                'published_level' => 'singular'
                            ]);
                        }
                    }
    
                    Notification::make()
                    ->title('Published Successfully')
                    ->body('Announcement has been published successfully.')
                    ->success()
                    ->send();
                })
                ->visible(fn() => auth()->user()->hasRole(['Pastor', 'Senior Pastor', 'SubParish Pastor']) && auth()->user()->checkPermissionTo('create AnnouncementPublications')),
            ];
        }else if(auth()->user()->hasRole(['Bishop','ChurchDistrict Pastor'])){
            return [
                Action::make('publish_announcement')
                ->action(function(array $data): void {
                    $announcement = Announcement::whereId($this->record)->first();
                        if($announcement->published_level == 'diocese'){
                            $announcement->update([
                                'published_level' => 'jimbo',
                                'church_districts' => $announcement->church_districts == Null ? ChurchDistrict::whereId(auth()->user()->church->churchDistrict->id)->pluck('id') : array_merge($announcement->church_districts, ChurchDistrict::whereId(auth()->user()->church->churchDistrict->id)->pluck('id')->toArray())
                            ]);
                        }else if($announcement->published_level == 'jimbo'){
                            $announcement->update([
                                'published_level' => 'singular',
                                'church' => $announcement->church == Null ? Church::whereId(auth()->user()->church_id)->pluck('id') : array_merge($announcement->church, Church::whereId(auth()->user()->church_id)->pluck('id')->toArray())
                            ]);
                        }
                        // else if($announcement->published_level == 'church'){
                        //     $announcement->update([
                        //         'published_level' => 'singular',
                        //     ]);
                        // }
    
                    Notification::make()
                    ->title('Published Successfully')
                    ->body('Announcement has been published successfully.')
                    ->success()
                    ->send();
                })
                ->visible(fn() => auth()->user()->hasRole(['Bishop', 'ChurchDistrict Pastor'])),
            ];
        }else{
            return [];
        }

    }
    public function setRecord($id)
    {
        if(is_numeric($id)){
            $this->record = $id;
        }else{
            Notification::make()
            ->title('Page Not Found')
            ->body('Sorry, the requested page does not exist.')
            ->danger()
            ->send();
            redirect()->to('/admin/church-announcements'); 
            // redirect()->to('admin/church-announcements');
            // Notification::make()
            //     ->danger()
            //     ->title('The record doesn\'t exist')
            //     ->body('Please select valid record in table.')
            //     ->send();
        }
    }

    public function setAnnouncementDocuments($id)
    {
        $announcement_docs = Announcement::whereId($this->record)->first();
        $docs = [];
        foreach($announcement_docs as $doc){
            $docs = [
                'document' => $doc
            ];
        }
        $this->announcements[] = $docs;
    }

    public function generateAccordions(): array
    {
        $accordions = [];
        $announcements = Announcement::whereId($this->record)->first();
        foreach($announcements->documents as $key => $announcement){
            $accordions[] =  Accordion::make('documents')
                                ->columns()
                                ->schema([
                                    ViewEntry::make('document')
                                        ->view('infolists.components.pdf-viewer', ['document' => 'announcementDocuments/'.$announcement, 'key' => 'document-'.$key])
                                ]);
        }
        return $accordions;
    }

    protected function makeInfolist(): Infolist
    {
        return Infolist::make($this)
                ->record(Announcement::whereId($this->record)->first())
                ->state([
                    'Announcement Documents' => $this->announcements[0]
                 ])
                ->schema([
                    Tabs::make('View Announcement Details')
                        ->schema([
                            Tabs\Tab::make('Message')
                                ->schema([
                                    Section::make('Content')
                                        ->schema([
                                            TextEntry::make('message')
                                                ->default(fn(Announcement $record) => $record->message)
                                                ->markdown()
                                                ->columnSpan('full'),
                                        ])
                                    ]),
                            Tabs\Tab::make('Documents')
                                ->schema([
                                    Accordions::make()
                                    ->activeAccordion(2)
                                    ->isolated()                                
                                    ->accordions($this->generateAccordions())
                                ])
                        ])
                ]);
    }

}
