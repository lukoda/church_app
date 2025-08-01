<?php

namespace App\Filament\Administration\Resources;

use App\Filament\Administration\Resources\PastorResource\Pages;
use App\Filament\Administration\Resources\PastorResource\RelationManagers;
use App\Models\Pastor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use App\Models\Church;
use App\Models\ChurchMember;
use App\Models\Ward;
use App\Models\ChurchDistrict;
use App\Models\Diocese;
use App\Models\Region;
use App\Models\District;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

class PastorResource extends Resource
{
    protected static ?string $model = Pastor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function getModelLabel(): string
    // {
    //     if(Auth::guard('admin')->user()->hasRole('Dinomination Admin')){
    //         return "Archbishop";
    //     }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')){
    //         return 'Bishop';
    //     }else{
    //         return 'Pastor';
    //     }
    // }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('admin')->user()->checkPermissionTo('view-any Pastor');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        TextInput::make('first_name')
                        ->required(),

                        TextInput::make('middle_name')
                            ->required(),

                        TextInput::make('surname')
                            ->required(),

                        TextInput::make('email')
                            ->nullable()
                            ->email(),

                        Select::make('gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female'
                            ])
                            ->required(),

                        TextInput::make('phone')
                            ->tel()
                            ->unique(table: User::class, column: 'phone', ignoreRecord: true)
                            ->helperText('0789*********')
                            ->maxLength(10)
                            ->required(),

                        // Select::make('church_level')
                        // ->reactive()
                        // ->options([
                        //     'Bishop' => 'Diocese',
                        //     'ArchBishop' => 'Dinomination'
                        // ])
                        // ->required()
                        // ->visible(auth()->user()->hasRole('Dinomination Admin')),

                        Hidden::make('church_level')
                        ->default(function (){
                            if(auth()->user()->hasRole('Dinomination Admin')){
                                return 'ArchBishop';
                            }else if(auth()->user()->hasRole('Diocese Admin')){
                                return 'Bishop';
                            }
                        }),

                        Select::make('diocese')
                        ->reactive()
                        ->options(Diocese::all()->pluck('name', 'id'))
                        ->required()
                        ->visible(auth()->user()->hasRole('Dinomination Admin')),

                        Select::make('church_district')
                        ->live(onBlur: true)
                        ->options(function (Get $get) {
                            if(auth()->user()->hasRole('Dinomination Admin')){
                                if(blank($get('diocese'))){
                                    return [];
                                }else{
                                    return ChurchDistrict::where('diocese_id', $get('diocese'))->pluck('name', 'id');
                                }
                            }else if(auth()->user()->hasRole('Diocese Admin')){

                                    return ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('name','id');
                            }

                        })
                        ->required()
                        ->visible(auth()->user()->hasRole(['Dinomination Admin', 'Diocese Admin']))
                        ->createOptionForm([
                            Grid::make(3)
                            ->schema([
                                Select::make('diocese_id')
                                ->label('Diocese')
                                ->reactive()
                                ->searchable()
                                ->options(Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('name', 'id'))
                                ->default(fn(Get $get) => blank($get('diocese')) ? '' : $get('diocese'))
                                ->required()
                                ->visible(auth()->user()->hasRole('Dinomination Admin')),

                                TextInput::make('name')
                                ->required()
                                ->unique(modifyRuleUsing: function(Unique $rule, Get $get, $state){
                                    if(auth()->user()->hasRole('Dinomination Admin')){
                                        return $rule->where('diocese_id', $get('diocese_id'))
                                        ->where('name', $state);
                                    }else if(auth()->user()->hasRole('Diocese Admin')){
                                        return $rule->where('diocese_id', auth()->user()->diocese_id)
                                        ->where('name', $state);
                                    }

                                },ignoreRecord: true),

                                Select::make('status')
                                ->options([
                                    'Active' => 'Active',
                                    'Inactive' => 'Inactive'
                                ])
                                ->required(),

                                ]),
                                Repeater::make('church district details')
                                ->columns(2)
                                ->columnSpanFull()
                                ->schema([
            
                                    Select::make('regions')
                                        ->options(function(Get $get){
                                            if(auth()->user()->hasRole('Dinomination Admin')){
                                                if(blank($get('../../diocese_id'))){
                                                    return [];
                                                }else{
                                                    return Region::whereIn('name', Diocese::whereId($get('../../diocese_id'))->pluck('regions')->collapse())->pluck('name', 'name')->toArray();
                                                }
                                            }else if(auth()->user()->hasRole('Diocese Admin')){
                                                    return Region::whereIn('name', Diocese::whereId(auth()->user()->diocese_id)->pluck('regions')->collapse())->pluck('name', 'name')->toArray();
                                            }

                                        })
                                        ->distinct()
                                        ->reactive()
                                        ->required()
                                        ->afterStateUpdated(function(Set $set){
                                            $set('districts', []);
                                        }),
            
                                    Select::make('districts')
                                        ->multiple()
                                        ->reactive()
                                        ->options(function(Get $get){
                                            if(auth()->user()->hasRole('Dinomination Admin')){
                                                if(blank($get('regions'))){
                                                    return [];
                                                }else{
                                                    $churchdistrict = Diocese::whereId($get('../../diocese_id'))->pluck('districts');
        
                                                    return District::whereIn('id', $churchdistrict->flatten())->where('region_id', Region::whereName($get('regions'))->pluck('id'))->pluck('name', 'id');
                                                    // return District::whereIn('id', Diocese::whereId($get('../../diocese_id'))->pluck('districts'))->pluck('name', 'id');
                                                    // return $churchdistrict->keys();
                                                }
                                            }else if(auth()->user()->hasRole('Diocese Admin')){
                                                if(blank($get('regions'))){
                                                    return [];
                                                }else{
                                                    $churchdistrict = Diocese::whereId(auth()->user()->diocese_id)->pluck('districts');
        
                                                    return District::whereIn('id', $churchdistrict->flatten())->where('region_id', Region::whereName($get('regions'))->pluck('id'))->pluck('name', 'id');
                                                }
                                            }

                                        })
                                        ->visible(function(Get $get){
                                            if($get('all_districts')){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }),
                                    
                                    ])
                                ->collapsible()
                                ->addActionLabel('Add Church District Details'),
                        ])
                        ->createOptionUsing(function (array $data) : int {
                            $regions = []; $districts = []; $wards = []; $all_districts = []; $all_wards = [];

                            foreach($data['church district details'] as $key => $churchdistrict){
                                $regions[] = $churchdistrict['regions'];
                                $districts[] =[
                                        $regions[$key] => $churchdistrict['districts']
                                    ];
                                $wards[] =[
                                        $regions[$key] => Ward::whereIn('district_id', $churchdistrict['districts'])->pluck('id'),
                                    ];
                            }

                            if(ChurchDistrict::where('name', $data['name'])->where('diocese_id', $data['diocese_id'] ?? auth()->user()->diocese_id)->exists()){
                                Notification::make()
                                ->title('ChurchDistrict has already been created')
                                ->body('You can not create duplicate church districts since it already exists.')
                                ->warning()
                                ->send();
                                return ChurchDistrict::where('name', $data['name'])->where('diocese_id', $data['diocese_id'] ?? auth()->user()->diocese_id)->pluck('id')[0];
                            }else{
                                $churchdistrict = new ChurchDistrict;
                                $churchdistrict->name = $data['name'];
                                $churchdistrict->diocese_id = $data['diocese_id'] ?? auth()->user()->diocese_id;
                                $churchdistrict->status = $data['status'];
                                $churchdistrict->regions = $regions;
                                $churchdistrict->districts = $districts;
                                $churchdistrict->all_wards = true;
                                $churchdistrict->wards = $wards;
                                $churchdistrict->save();

                                return $churchdistrict->id;
                            }
                        }),

                        Select::make('church_assigned_id')
                            ->label('Assigned Church')
                            ->required()
                            ->options(function(Get $get){
                                if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                    return Church::where('church_district_id', auth()->user()->church_district_id)->pluck('name', 'id');
                                }else if(auth()->user()->hasRole('Dinomination Admin')){
                                    if($get('diocese') && $get('church_district')){
                                        return Church::where('church_district_id', $get('church_district'))->pluck('name', 'id');
                                    }else{
                                        return [];
                                    }
                                }else if(auth()->user()->hasRole('Parish Admin')){
                                    return Church::where('parent_church', auth()->user()->church_id)->where('church_type', 'sub_parish')->pluck('name', 'id');
                                }else if(auth()->user()->hasRole('Diocese Admin')){
                                    return Church::where('church_district_id', $get('church_district'))->pluck('name', 'id');
                                }
                            })
                            ->createOptionForm([
                                Grid::make(3)
                                ->schema([
                                    Select::make('diocese_id')
                                    ->label('Diocese')
                                    ->reactive()
                                    ->searchable()
                                    ->options(Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('name', 'id'))
                                    ->default(fn(Get $get) => blank($get('diocese')) ? '' : $get('diocese'))
                                    ->required()
                                    ->visible(auth()->user()->hasRole('Dinomination Admin')),
    
                                    Select::make('church_district')
                                    ->options(function(Get $get){
                                        if(blank('diocese_id')){
                                            return [];
                                        }else{
                                            if(auth()->user()->hasRole('Dinomination Admin')){
                                                return ChurchDistrict::where('diocese_id', $get('diocese_id'))->pluck('name','id');
                                            }else{
                                                return ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('name','id');
                                            }
                                        }
                                    })
                                    ->default(fn(Get $get) => blank($get('church_district')) ? '' : $get('church_district'))
                                    ->required()
                                    ->visible(auth()->user()->hasRole(['Diocese Admin', 'Dinomination Admin'])),
    
                                    TextInput::make('name')
                                    ->required()
                                    ->unique(modifyRuleUsing: function(Unique $rule, $state, Get $get) {
                                        return $rule->where('name', $state)
                                                    ->where('church_district_id', $get('church_district_id'));
                                    }, ignoreRecord:true),
    
                                    Hidden::make('church_type')
                                    ->default(function () {
                                       if((auth()->user()->hasRole('Parish Admin'))){
                                           return 'sub_parish';
                                       }else{
                                           if(auth()->user()->checkPermissionTo('create Church') && auth()->user()->hasRole('ChurchDistrict Admin')){
                                               return 'parish';
                                           }else if(auth()->user()->checkPermissionTo('create Church') && auth()->user()->hasRole('Diocese Admin')){
                                               return 'diocese';
                                           }
                                       }
                                    }),
    
                                    Select::make('region_id')
                                    ->preload()
                                    ->reactive()
                                    ->searchable()
                                    ->label('Region')
                                    ->options(function(){
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->pluck('regions');
                                            $diocese = $diocese->flatten();
                                            return Region::whereIn('name', $diocese)->pluck('name', 'id');
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $churchdistricts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('regions');
                                            $churchdistricts = $churchdistricts->flatten();
                                            return Region::whereIn('name', $churchdistricts)->pluck('name', 'id');
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            $churches = Church::whereId(auth()->user()->church_id)->first();
                                            return Region::whereId($churches->region_id)->pluck('name', 'id');
                                        }else if(auth()->user()->hasRole('Dinomination Admin')){
                                            return Region::all()->pluck('name', 'id');
                                        }
                                    })
                                    ->visible(function(){
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->pluck('regions');
                                            $diocese = $diocese->flatten();
                                            if($diocese->count() == 1){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $churchdistricts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('regions');
                                            $churchdistricts = $churchdistricts->flatten();
                                            if($churchdistricts->count() == 1){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            return false;
                                        }else if(auth()->user()->hasRole('Dinomination Admin')){
                                            return true;
                                        }
                                    })
                                    ->default(function(){
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->pluck('regions');
                                            $diocese = $diocese->flatten();
                                            if($diocese->count() == 1){
                                                return Region::whereIn('name', $diocese)->pluck('id');
                                            }
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $churchdistricts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('regions');
                                            $churchdistricts = $churchdistricts->flatten();
                                            if($churchdistricts->count() == 1){
                                                return Region::whereIn('name', $churchdistricts)->pluck('id');
                                            }
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            $churches = Church::whereId(auth()->user()->church_id)->first();
                                            return $churches->region_id;
                                        }
                
                                    })
                                    ->required()
                                    ->afterStateUpdated(function(Set $set){
                                        $set('district_id', []);
                                    }),
                
                                Hidden::make('region')
                                    ->default(function(){
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->pluck('regions');
                                            $diocese = $diocese->flatten();
                                            if($diocese->count() == 1){
                                                return Region::whereIn('name', $diocese)->pluck('id')[0];
                                            }
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $churchdistricts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('regions');
                                            $churchdistricts = $churchdistricts->flatten();
                                            if($churchdistricts->count() == 1){
                                                return Region::whereIn('name', $churchdistricts)->pluck('id')[0];
                                            }
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            $churches = Church::whereId(auth()->user()->church_id)->first();
                                            return $churches->region_id;
                                        }
                                    }),
    
                                    
                                    Select::make('district_id')
                                    ->preload()
                                    ->searchable()
                                    ->label('District')
                                    ->options(function (Get $get) {
                                        if (blank($get('region_id'))) {
                                            return [];
                                        }
                
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->first();
                                            $regions = []; $districts = [];
                                            foreach($diocese->districts as $key => $district){
                                                $regions[] = $district[$diocese->regions[$key]];
                                                $districts = array_merge($districts, District::whereIn('id', $district[$diocese->regions[$key]])->pluck('id' )->toArray());
                                            }
                
                                            return District::whereIn('id', $districts)->where('region_id', $get('region_id'))->pluck('name', 'id');
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $districts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('districts');
                                            $districts = $districts->flatten();
                    
                                            return District::whereIn('id', $districts)->where('region_id', $get('region_id'))->pluck('name', 'id');
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            $churches = Church::whereId(auth()->user()->church_id)->first();
                                            return $churches->district_id;
                                        }else if(auth()->user()->hasRole('Dinomination Admin')){
                                            return District::where('region_id', $get('region_id') ?? $get('region')->pluck('name', 'id'));
                                        }
                
                                    })
                                    ->visible(function(){
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->first();
                                            $regions = []; $districts = [];
                                            foreach($diocese->districts as $key => $district){
                                                $regions[] = $district[$diocese->regions[$key]];
                                                $districts = array_merge($districts, District::whereIn('id', $district[$diocese->regions[$key]])->pluck('id' )->toArray());
                                            }
                
                                            if(count($districts) == 1){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $districts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('districts');
                                            $districts = $districts->flatten();
                    
                                            if($districts->count() == 1){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            return false;
                                        }else if(auth()->user()->hasRole('Dinomination Admin')){
                                            return true;
                                        }
                
                                    })
                                    ->default(function(){
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->first();
                                            $regions = []; $districts = [];
                                            foreach($diocese->districts as $key => $district){
                                                $regions[] = $district[$diocese->regions[$key]];
                                                $districts = array_merge($districts, District::whereIn('id', $district[$diocese->regions[$key]])->pluck('id' )->toArray());
                                            }
                                             
                                            if(count($districts) == 1){
                                                return District::whereIn('id', $districts)->pluck('id');
                                            }
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $districts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('districts');
                                            $districts = $districts->flatten();
                    
                                            if($districts->count() == 1){
                                                return District::whereIn('id', $districts)->pluck('id');
                                            }
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            $churches = Church::whereId(auth()->user()->church_id)->first();
                                            return $churches->district_id;
                                        }
                
                                    })
                                    ->reactive()
                                    ->required()
                                    ->afterStateUpdated(function(Set $set){
                                        $set('ward_id', []);
                                    }),
                
                                Hidden::make('district')
                                    ->default(function(){
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->first();
                                            $regions = []; $districts = [];
                                            foreach($diocese->districts as $key => $district){
                                                $regions[] = $district[$diocese->regions[$key]];
                                                $districts = array_merge($districts, District::whereIn('id', $district[$diocese->regions[$key]])->pluck('id' )->toArray());
                                            }
                                             
                                            if(count($districts) == 1){
                                                return District::whereIn('id', $districts)->pluck('id')[0];
                                            }
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $districts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('districts');
                                            $districts = $districts->flatten();
                    
                                            if($districts->count() == 1){
                                                return District::whereIn('id', $districts)->pluck('id')[0];
                                            }
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            $churches = Church::whereId(auth()->user()->church_id)->first();
                                            return $churches->district_id;
                                        }
                                    }),
    
                                    Select::make('ward_id')
                                    ->preload()
                                    ->searchable()
                                    ->label('Ward')
                                    ->options(function (Get $get) {
                                        if (blank($get('district_id'))) {
                                            return [];
                                        }
    
                                        if(auth()->user()->hasRole('Diocese Admin')){
                                            $diocese = Diocese::whereId(auth()->user()->diocese_id)->first();
                                            $regions = []; $districts = [];
                                            foreach($diocese->districts as $key => $district){
                                                $regions[] = $district[$diocese->regions[$key]];
                                                $districts = array_merge($districts, District::whereIn('id', $district[$diocese->regions[$key]])->pluck('id' )->toArray());
                                            }
                
                                            if(count($districts) == 1){
                                                return Ward::whereIn('district_id', $districts)->pluck('name', 'id');
                                            }else{
                                                return Ward::all()->where('district_id', $get('district_id'))->pluck('name', 'id');
                                            }
                                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                            $districts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('districts');
                                            $districts = $districts->flatten();
                                            if($districts->count() == 1){
                                                return Ward::whereIn('district_id', $districts)->pluck('name', 'id');
                                            }else{
                                                return Ward::all()->where('district_id', $get('district_id'))->pluck('name', 'id');
                                            }
                                        }else if(auth()->user()->hasRole('Parish Admin')){
                                            $churches = Church::whereId(auth()->user()->church_id)->first();
                                            return Ward::all()->where('district_id', $get('district_id') ?? $get('district'))->pluck('name', 'id');
                                        }else if(auth()->user()->hasRole('Dinomination Admin')){
                                            return Ward::where('district_id', $get('district_id'))->pluck('name', 'id');
                                        }
                
                                    })
                                    ->required(),
                
                                Toggle::make('church_location_status')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->helperText('Please turn on this feature when in church grounds for accurate location')
                                    ->inline(false)
                                    ->default(false),
                
                                FileUpload::make('pictures')
                                    ->label('Church Pictures')
                                    ->maxFiles(5)
                                    ->minFiles(0)
                                    ->openable()
                                    ->multiple()
                                    ->downloadable()
                                    ->previewable()
                                    ->columnSpan('full')
                                    ->disk('churchImages'),
                                ]),
                            ])
                            ->createOptionUsing(function (array $data) : int {
                                if(Church::where('name', $data['name'])->where('church_district_id', $data['church_district'] ?? auth()->user()->church_district_id)->exists()){
                                    Notification::make()
                                    ->title('Church has already been created')
                                    ->body('You can not create duplicate churches since it already exists.')
                                    ->warning()
                                    ->send();
                                    return Church::where('church_district_id', $data['church_district'] ?? auth()->user()->church_district_id)->pluck('id')[0];
                                }else if(auth()->user()->hasRole('Diocese Admin') && Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->exists()){
                                    Notification::make()
                                    ->title('Can not create more than one Diocese Church')
                                    ->body('Each Diocese can have only one Diocese Church.')
                                    ->danger()
                                    ->send();
                                    return Church::where('church_district_id', $data['church_district'] ?? auth()->user()->church_district_id)->pluck('id')[0];
                                }else{
                                    $church = new Church;
                                    $church->name = $data['name'];
                                    $church->pictures = $data['pictures'] ?? Null;
                                    $church->parent_church = array_key_exists('parent_church', $data) ? $data['parent_church'] : Null;
                                    $church->church_type = $data['church_type'];
                                    $church->church_district_id = $data['church_district'] ?? auth()->user()->church_district_id;
                                    $church->region_id = $data['region'] == Null ? $data['region_id'] : $data['region'];
                                    $church->district_id = $data['district'] == Null ? $data['district_id'] : $data['district'];
                                    $church->ward_id = $data['ward_id'];
                                    $church->save();

                                    return $church->id;
                                }

                            }),

                        Select::make('title')
                        ->required()
                        ->options(function(Get $get){
                            if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                $church_district_churches = Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id');
                                if(Pastor::whereIn('church_assigned_id', $church_district_churches)->where('title', 'ChurchDistrict Pastor')->where('status', 'active')->exists()){
                                    return [
                                        'Senior Pastor' => 'Senior Pastor',
                                        'Pastor' => 'Pastor'
                                    ];
                                }else{
                                    return [
                                        'ChurchDistrict Pastor' => 'ChurchDistrict Pastor',
                                        'Senior Pastor' => 'Senior Pastor',
                                        'Pastor' => 'Pastor'
                                    ];
                                }
                            }else if(auth()->user()->hasRole('Dinomination Admin')){
                                if($get('church_level') == 'Dinomination' || auth()->user()->hasRole('Dinomination Admin')){
                                    if(Pastor::whereIn('church_assigned_id', Church::all()->pluck('id'))->where('title', 'ArchBishop')->exists()){
                                        return [];
                                    }else{
                                        return [
                                            'ArchBishop' => 'ArchBishop'
                                        ];
                                    }
                                }
                            }else if(auth()->user()->hasRole('Parish Admin')){
                                return [
                                    'SubParish Pastor' => 'SubParish Pastor'
                                ];
                            }else if(auth()->user()->hasRole('Diocese Admin')){
                                $diocese_church_districts = ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id');
                                $diocese_churches = Church::whereIn('church_district_id', $diocese_church_districts)->pluck('id');
                                if(Pastor::whereIn('church_assigned_id', $diocese_churches)->where('title', 'Bishop')->exists()){
                                    return [];
                                }else{
                                    return [
                                        'Bishop' => 'Bishop'
                                    ];
                                }
                            }
                        })

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_registered')
                ->date(),
                TextColumn::make('church_assigned_id')
                ->label('Church')
                ->formatStateUsing(fn($state) => Church::whereId($state)->pluck('name')[0]),
                TextColumn::make('title')
                ->label('Title'),
                TextColumn::make('churchMember.full_name')
                ->label('Names')
                ->wrap(),
                TextColumn::make('churchMember.phone')
                ->label('Phone')
                ->wrap(),
                TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'deceased' => 'warning',
                    'retired' => 'gray',
                })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update Pastor'))
                ->visible(auth()->user()->checkPermissionTo('update Pastor')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete Pastor'))
                    // ->visible(auth()->user()->checkPermissionTo('delete Pastor')),
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
        if(auth()->user()->hasRole('Dinomination Admin')){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::whereIn('diocese_id', Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('id'))->pluck('id'))->pluck('id'))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('Diocese Admin')){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->pluck('id'))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('id'))->pluck('id'))->whereNotIn('title', ['Bishop', 'ArchBishop'])->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('Parish Admin')){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::where('parent_church', auth()->user()->church_id)->whereNotNull('parent_church')->pluck('id'))->orderBy('created_at', 'desc');
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPastors::route('/'),
            'create' => Pages\CreatePastor::route('/create'),
            'edit' => Pages\EditPastor::route('/{record}/edit'),
        ];
    }
}
