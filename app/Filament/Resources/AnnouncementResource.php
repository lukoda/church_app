<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers;
use App\Models\Announcement;
use App\Models\Church;
use App\Models\Jumuiya;
use App\Models\ChurchDistrict;
use App\Models\Diocese;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use phpDocumentor\Reflection\Types\Null_;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid as ViewGrid;
use Filament\Infolists\Components\Section as ViewSection;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Fieldset;
use Filament\Tables\Actions\Action;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'fas-bullhorn';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Announcement');
    }

    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //         Tabs::make('Announcement Info')
    //             ->tabs([
    //                 Tabs\Tab::make('Announcement Details')
    //                     ->schema([
    //                         ViewGrid::make(5)
    //                         ->schema([
    //                             TextEntry::make('begin_date')
    //                                 ->label('Announcement Date')
    //                                 ->date(),
                
    //                             TextEntry::make('duration')
    //                                 ->label('Active For')
    //                                 ->state(function(Model $record){
    //                                     return Carbon::parse($record->begin_date)->diffInDays($record->end_date). ' Days';
    //                                 })
    //                                 ->numeric(),
                
    //                             TextEntry::make('end_date')
    //                                 ->label('Deactivation Date')
    //                                 ->date(),
        
    //                             TextEntry::make('level')
    //                                 ->label('Announcement Scope'),
        
    //                             TextEntry::make('status')
    //                                 ->badge()
    //                                 ->color(fn (string $state): string => match ($state) {
    //                                     'active' => 'success',
    //                                     'inactive' => 'warning',
    //                                 })
    //                             ]),
        
    //                     ViewGrid::make(5)
    //                         ->schema([
    //                             TextEntry::make('all_dioceses')
    //                                 ->state(function(Model $record) : string {
    //                                     if($record->all_dioceses == true){
    //                                         return 'All Dioceses Of KKKT';
    //                                     }
    //                                 })
    //                                 ->visible(function(Model $record){
    //                                     if($record->all_dioceses == true){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 }),
        
    //                             TextEntry::make('diocese')
    //                                 ->listWithLineBreaks()
    //                                 ->limitList(3)
    //                                 ->expandableLimitedList()
    //                                 ->visible(function(Model $record){
    //                                     if($record->diocese != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 })
    //                                 ->state(function(Model $record) : array {
    //                                     return Diocese::whereIn('id', $record->diocese)->pluck('name')->toArray();
    //                                 }),
        
    //                             TextEntry::make('all_church_districts')
    //                                 ->state(function(Model $record): string {
    //                                     if($record->all_church_districts == true){
    //                                         return 'All Church Districts Of Indicated Diocese';
    //                                     }
    //                                 })
    //                                 ->visible(function(Model $record){
    //                                     if($record->all_church_districts == true){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 }),
        
    //                             TextEntry::make('church_districts')
    //                                 ->listWithLineBreaks()
    //                                 ->limitList(3)
    //                                 ->expandableLimitedList()
    //                                 ->visible(function(Model $record){
    //                                     if($record->church_districts != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 })
    //                                 ->state(function(Model $record): array {
    //                                     return ChurchDistrict::whereIn('id', $record->church_districts)->pluck('name')->toArray();
    //                                 }),
        
    //                             TextEntry::make('all_churches')
    //                                 ->state(function(Model $record): string{
    //                                     if($record->all_churches == true){
    //                                         return 'All Churches Of Indicated Church Districts';
    //                                     }
    //                                 })
    //                                 ->visible(function(Model $record){
    //                                     if($record->all_churches != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 }),
        
    //                             TextEntry::make('church')
    //                                 ->listWithLineBreaks()
    //                                 ->limitList(3)
    //                                 ->expandableLimitedList()
    //                                 ->visible(function(Model $record){
    //                                     if($record->church != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 })
    //                                 ->state(function(Model $record){
    //                                     return Church::whereIn('id', $record->church)->pluck('name')->toArray();
    //                                 }),
        
    //                             TextEntry::make('all_sub_parishes')
    //                                 ->state(function(Model $record): string {
    //                                     if($record->all_sub_parishes == true){
    //                                         return 'All Sub-Parishes Of Indicated Church';
    //                                     }
    //                                 })
    //                                 ->visible(function(Model $record){
    //                                     if($record->all_sub_parishes != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 }),
        
    //                             TextEntry::make('sub_parish')
    //                                 ->listWithLineBreaks()
    //                                 ->limitList(3)
    //                                 ->expandableLimitedList()
    //                                 ->visible(function(Model $record){
    //                                     if($record->sub_parish != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 })
    //                                 ->state(function(Model $record){
    //                                     return Church::whereIn('id', $record->church)->pluck('name')->toArray();
    //                                 }),
        
    //                             TextEntry::make('all_jumuiyas')
    //                                 ->state(function(Model $record): string {
    //                                     if($record->all_jumuiyas == true){
    //                                         if($record->sub_parish != Null){
    //                                             return 'All Jumuiyas Of Indicated Sub-parish';
    //                                         }else{
    //                                             return 'All Jumuiyas Of Indicated Church';
    //                                         }
    //                                     }
    //                                 })
    //                                 ->visible(function(Model $record){
    //                                     if($record->all_jumuiyas != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 }),
        
    //                             TextEntry::make('jumuiya')
    //                                 ->listWithLineBreaks()
    //                                 ->limitList(3)
    //                                 ->expandableLimitedList()
    //                                 ->visible(function(Model $record){
    //                                     if($record->jumuiya != Null){
    //                                         return true;
    //                                     }else{
    //                                         return false;
    //                                     }
    //                                 })
    //                                 ->state(function(Model $record){
    //                                     return Church::whereIn('id', $record->church)->pluck('name')->toArray();
    //                                 }),
        
    //                             ]),
    //                         ]),

    //                         Tabs\Tab::make('Message')
    //                             ->schema([
    //                                 ViewSection::make('Announcement')
    //                                     ->schema([
    //                                         TextEntry::make('message')
    //                                         ->markdown()
    //                                         ->columnSpan('full')
    //                                     ])
    //                         ]),

    //                         Tabs\Tab::make('Documents')
    //                             ->schema([
    //                                 //in progess
    //                             ])

    //                     ])
    //                     ->columnSpan('full'),
                
    //         ]);
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->user()->id),

                Hidden::make('dinomination_id')
                    ->default(auth()->user()->church->churchDistrict->diocese->dinomination_id),

                Grid::make(4)
                    ->schema([
                        DatePicker::make('begin_date')
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->required(),

                        TextInput::make('duration')
                            ->live(onBlur: true)
                            ->prefix('Days')
                            ->numeric()
                            ->afterstateUpdated(function(Get $get, int $state, Set $set){
                                    $set('end_date', Carbon::parse($get('begin_date'))->addDays($state)->toDateString());
                            }),

                        DatePicker::make('end_date')
                            ->live(onBlur: true)
                            ->required()
                            ->afterStateUpdated(function(Get $get, $state, Set $set){
                                $date =  Carbon::parse($state);
                                $set('duration', $date->diffIndays(Carbon::parse($get('begin_date'))));
                            }),    
                        
                        Select::make('level')
                            ->options(function(){
                                if(auth()->user()->hasRole([['Dinomination Bishop', 'Super Admin']])){
                                    return [
                                        'diocese' => 'Diocese',
                                    ];
                                }else if(auth()->user()->hasRole('Diocese Bishop')){
                                    return [
                                        'jimbo' => 'Jimbo',
                                    ];
                                }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
                                    return  [
                                        'church' => 'Church',
                                    ];
                                }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor', 'Church Secretary']) && auth()->user()->church->where('church_type', 'parish')){
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
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(function(Set $set){
                                $set('all_dioceses', true);
                                $set('diocese', []);
                                $set('all_church_districts', true);
                                $set('church_districts', []);
                                $set('all_churches', true);
                                $set('church', []);
                                $set('all_sub_parishes', true);
                                $set('sub_parish', []);
                                $set('all_jumuiyas', true);
                                $set('jumuiya', []);
                            }),

                        Checkbox::make('all_dioceses')
                            ->default(true)
                            ->inline(false)
                            ->reactive()
                            ->visible(function(Get $get, string $context, $record){
                                    if(blank($get('level'))){
                                        return false;
                                    }else{
                                        if($get('level') == 'diocese'){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }                               

                            })
                            ->afterStateUpdated(function(Set $set){
                                $set('diocese', []);
                                $set('all_church_districts', true);
                                $set('church_districts', []);
                                $set('all_churches', true);
                                $set('church', []);
                                $set('all_sub_parishes', true);
                                $set('sub_parish', []);
                                $set('all_jumuiyas', true);
                                $set('jumuiya', []);
                            }),

                        Select::make('diocese')
                            ->reactive()
                            ->multiple()
                            ->options(function(){
                                return Diocese::all()->pluck('name','id');
                            })
                            ->visible(function(Get $get, string $context, $record){
                                    if($get('all_dioceses')){
                                        return false;
                                    }else{
                                        if(blank($get('level'))){
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                            })
                            ->disabled(function(string $context, Get $get){
                                if($context == true && $get('level') == 'jimbo'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->afterStateUpdated(function(Set $set){
                                $set('all_church_districts', true);
                                $set('church_districts', []);
                                $set('all_churches', true);
                                $set('church', []);
                                $set('all_sub_parishes', true);
                                $set('sub_parish', []);
                                $set('all_jumuiyas', true);
                                $set('jumuiya', []);
                            }),
                        
                        Checkbox::make('all_church_districts')
                                ->default(true)
                                ->inline(false)
                                ->reactive()
                                ->visible(function(Get $get, string $context, $record){
                                        if(blank($get('level'))){
                                            return false;
                                        }else{
                                            if($get('diocese') || $get('level') == 'jimbo'){
                                               return true;
                                            }else{
                                               return false;
                                            }
                                        }

                                })
                                ->afterStateUpdated(function(Set $set){
                                    $set('church_districts', []);
                                    $set('all_churches', true);
                                    $set('church', []);
                                    $set('all_sub_parishes', true);
                                    $set('sub_parish', []);
                                    $set('all_jumuiyas', true);
                                    $set('jumuiya', []);
                                }),

                        Select::make('church_districts')
                                ->reactive()
                                ->multiple()
                                ->options(function(Get $get){
                                    if($get('diocese')){
                                        $diocese = $get('diocese');
                                        return ChurchDistrict::whereIn('diocese_id', $diocese)->pluck('name', 'id');
                                    }else{
                                        $church = Church::whereId(auth()->user()->church_id)->first();
                                        $diocese_id = $church->churchDistrict->diocese_id;
                                        return ChurchDistrict::where('diocese_id', $diocese_id)->pluck('name','id');
                                    }
                                })
                                ->visible(function(Get $get, string $context, $record){
                                        if($get('level') == 'jimbo'){
                                            if($get('all_church_districts')){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }else{
                                            if($get('all_church_districts')){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }

                                })
                                ->afterStateUpdated(function(Set $set){
                                    $set('all_churches', true);
                                    $set('church', []);
                                    $set('all_sub_parishes', true);
                                    $set('sub_parish', []);
                                    $set('all_jumuiyas', true);
                                    $set('jumuiya', []);
                                }),

                        Checkbox::make('all_churches')
                                ->reactive()
                                ->default('true')
                                ->inline(false)
                                ->visible(function(Get $get,string $context, $record){
                                        if(blank($get('level'))){
                                            return false;
                                        }else{
                                            if($get('church_districts') || $get('level') == 'church'){
                                                return true;
                                            }else{
                                                return false;
                                            }
                                        }
                                })
                                ->afterStateUpdated(function(Set $set){
                                    $set('church', []);
                                    $set('all_sub_parishes', true);
                                    $set('sub_parish', []);
                                    $set('all_jumuiyas', true);
                                    $set('jumuiya', []);
                                }),

                        Select::make('church')
                            ->reactive()
                            ->multiple()
                            ->options(function(Get $get){
                                if($get('church_districts')){
                                    $church_districts = $get('church_districts');
                                    return Church::whereIn('church_district_id', $church_districts)->pluck('name','id');
                                }else {
                                    if($get('all_church_districts') && $get('level') == 'jimbo'){
                                        $church = Church::whereId(auth()->user()->church_id)->first();
                                        $diocese_id = $church->churchDistrict->diocese_id;
                                        return Church::where('church_district_id', ChurchDistrict::where('diocese_id', $diocese_id)->pluck('id'))->pluck('name','id');
                                    }else{
                                        return Church::whereId(auth()->user()->church_id)->pluck('name','id');
                                    }
                                }
                            })
                            ->visible(function(Get $get, string $context, $record){
                                    if($get('level') == 'church'){
                                        if($get('all_churches')){
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }else{
                                        if($get('all_churches')){
                                            return false;
                                        }else{
                                            return true;
                                        }
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

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive'
                            ])
                            ->required(),
                    ]),

                RichEditor::make('message')
                    ->disableToolbarButtons([
                        'codeBlock',
                        'strike',
                        'link',
                        'attachFiles'
                    ])
                    ->columnSpan('full'),

                FileUpload::make('documents')
                    ->multiple()
                    ->label('Upload Announcement Documents')
                    ->downloadable()
                    ->disk('announcementDocuments')
                    ->columnSpan('full'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(false)
            ->columns([
                TextColumn::make('level'),
                TextColumn::make('begin_date')
                    ->date()
                    ->description(fn(Model $record, $state) : string => 'Remaining '.Carbon::parse($state)->diffInDays($record->end_date).' Days'),
                TextColumn::make('end_date')
                    ->date(),
                TextColumn::make('diocese')
                    ->listWithLineBreaks()
                    ->limitList(fn(Model $record) => $record->all_dioceses == true ? 1 : 3)
                    ->formatstateUsing(fn(Model $record, $state) => $record->diocese != Null && $record->all_dioceses != true ? Diocese::whereIn('id', $state)->pluck('name')[0] : 'All Diocese')
                    ->default(function(Model $record, $state){
                        if($record->all_dioceses == true){
                            return 'All Dioceses';
                        }
                    }),
                TextColumn::make('church_districts')
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('church')
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('jumuiya')
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'active' => 'success',
                        'inactive' => 'danger'
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(fn(Model $record) => $record->user_id == auth()->user()->id ? true : false)
                ->visible(auth()->user()->checkPermissionTo('update Announcement')),
                Action::make('view_announcement')
                ->url(function(Model $record){
                   return route('filament.admin.pages.view-church-announcements', ['record' => $record->id]);
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if(auth()->user()->hasRole('ArchBishop')){
            return parent::getEloquentQuery()->where('status', 'active')->where('dinomination_id', auth()->user()->dinomination_id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('Bishop')){
            return parent::getEloquentQuery()->where('status', 'active')->where('dinomination_id', auth()->user()->dinomination_id)->where('published_level', 'diocese')->whereJsonContains('diocese', strval(auth()->user()->church->churchDistrict->diocese->id))->orWhereJsonContains('diocese', auth()->user()->church->churchDistrict->diocese->id)->where('user_id', auth()->user()->id)
                    ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('ChurchDistrict Pastor')){
            return parent::getEloquentQuery()->where('status', 'active')->where('dinomination_id', auth()->user()->dinomination_id)->where('published_level', 'jimbo')->whereJsonContains('church_districts', strval(auth()->user()->church->churchDistrict->id))->orWhereJsonContains('church_districts', auth()->user()->church->churchDistrict->id)
                    ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor'])){
            return parent::getEloquentQuery()->where('status', 'active')->where('dinomination_id', auth()->user()->dinomination_id)->where('published_level', 'church')->whereJsonContains('church', strval(auth()->user()->church_id))->orWhereJsonContains('church', auth()->user()->church_id)
                    ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('SubParish Pastor')){
            return parent::getEloquentQuery()->where('status', 'active')->where('dinomination_id', auth()->user()->dinomination_id)->where('published_level', 'sub_parish')->orWhereHas('published_announcement', function(Builder $query){
                $query->where('church_id', auth()->user()->church_id)->where('level', 'sub_parish');
            })->whereJsonContains('sub_parish', strval(auth()->user()->church_id))->orWhereJsonContains('sub_parish', auth()->user()->church_id)
            ->where('user_id', auth()->user()->id)->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('Jumuiya Chairperson')){
            if(auth()->user()->churchMember){
                if(auth()->user()->churchMember->whereNotNull('jumuiya_id')){
                    return parent::getEloquentQuery()->where('status', 'active')->where('dinomination_id', auth()->user()->dinomination_id)->where('published_level', 'jumuiya')->orWhereHas('published_announcement', function(Builder $query){
                        $query->where('church_id', auth()->user()->church_id)->where('level', 'jumuiya');
                    })->whereJsonContains('jumuiya', strval(auth()->user()->churchMember->jumuiya_id))->orWhereJsonContains('jumuiya', auth()->user()->churchMember->jumuiya_id)
                    ->whereDate('begin_date','<=', now()->toDateString())->where('created_at', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY))->where('created_at','<=', Carbon::now()->endOfWeek(Carbon::SUNDAY))->orderBy('created_at', 'desc');
                }else{
                    return parent::getEloquentQuery()->whereId(0);
                }
            }else{
                return parent::getEloquentQuery()->whereId(0);
            }

        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
