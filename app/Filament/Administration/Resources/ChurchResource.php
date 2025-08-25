<?php

namespace App\Filament\Administration\Resources;

use App\Filament\Administration\Resources\ChurchResource\Pages;
use App\Filament\Administration\Resources\ChurchResource\RelationManagers;
use App\Models\Church;
use App\Models\ChurchDistrict;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Diocese;
use App\Models\ChurchMember;
use App\Models\Pastor;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ChurchResource extends Resource
{
    protected static ?string $model = Church::class;

    protected static ?string $navigationIcon = 'fas-church';

    protected static ?string $navigationGroup = 'Church Structure';

    public static function getModelLabel(): string
    {
        if(Auth::guard('admin')->user()->hasRole('Parish Admin')){
            return "SubParish";
        }else if(Auth::guard('admin')->user()->hasRole(['ChurchDistrict Admin','Dinomination Admin', 'Diocese Admin'])){
            return 'Church';
        }
    }

    public static function getNavigationLabel(): string
    {
        if(Auth::guard('admin')->user()->hasRole('Parish Admin')){
            return "SubParishes";
        }else if(Auth::guard('admin')->user()->hasRole(['ChurchDistrict Admin','Dinomination Admin', 'Diocese Admin'])){
            return 'Churches';
        }
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('admin')->user()->checkPermissionTo('view-any Church');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('church_district_id')
                    ->default(auth()->user()->church_district_id)
                    ->visible(auth()->user()->hasRole(['ChurchDistrict Admin', 'Parish Admin'])),

                Select::make('church_district')
                ->options(function(){
                    if(auth()->user()->hasRole('Dinomination Admin')){
                        return ChurchDistrict::whereIn('diocese_id', Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('id'))->pluck('name','id');
                    }else if(auth()->user()->hasRole('Diocese Admin')){
                        return ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('name','id');
                    }
                })
                ->required()
                ->visible(auth()->user()->hasRole('Diocese Admin') || auth()->user()->hasRole('Dinomination Admin'))
                ->live(),

                TextInput::make('name')
                    ->required()
                    ->unique(modifyRuleUsing: function(Unique $rule, $state, Get $get) {
                        return $rule->where('name', $state);
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
                            }else if(auth()->user()->checkPermissionTo('create Church') && auth()->user()->hasRole('Dinomination Admin')){
                                return 'dinomination';
                            }
                        }
                     }),

                Hidden::make('parent_church')
                ->default(auth()->user()->church_id)
                ->visible(function(Get $get){
                        if($get('church_type') == 'sub_parish'){
                            return true;
                        }else{
                            return false;
                        }
                    }),

                Select::make('region_id')
                    ->preload()
                    ->reactive()
                    ->searchable()
                    ->label('Region')
                    ->options(function(Get $get){
                        if(auth()->user()->hasRole('Dinomination Admin') || auth()->user()->hasRole('Diocese Admin')){
                            if(blank($get("church_district"))){
                                return [];
                            }else{
                                $churchDistrict = ChurchDistrict::whereId($get("church_district"))->pluck('regions');
                                $churchDistrict = $churchDistrict->flatten();
                                return Region::whereIn('name',$churchDistrict)->pluck('name','id');
                            }
                        }else{
                            if(blank($get('church_district_id'))){
                                return [];
                            }
                            else if(auth()->user()->hasRole('Parish Admin')){
                                $churches = Church::whereId(auth()->user()->church_id)->first();
                                return Region::whereId($churches->region_id)->pluck('name', 'id');
                            }
                            else{
                                $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->pluck('regions');
                                $churchDistrict = $churchDistrict->flatten();
                                return Region::whereIn('name',$churchDistrict)->pluck('name','id');
                            }
                        }
                    })
                    ->visible(function(Get $get){
                        if(auth()->user()->hasRole('Parish Admin')){
                            return false;
                        }
                        else if(blank($get('church_district_id'))){
                            return true;
                        }
                        else{
                            $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->pluck('regions');
                            $churchDistrict = $churchDistrict->flatten();
                            
                            if($churchDistrict->count() == 1){
                                return false;
                            }else{
                                return true;
                            }
                        }
                    })
                    ->default(function(Get $get, Set $set){
                        if(blank($get('church_district_id'))){
                            return [];
                        }else if(auth()->user()->hasRole('Parish Admin')){
                            $churches = Church::whereId(auth()->user()->church_id)->first();
                            return $churches->region_id;
                        }
                        else{
                            $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->pluck('regions');
                            $churchDistrict = $churchDistrict->flatten();

                            if($churchDistrict->count() == 1){
                                return Region::whereIn('name', $churchDistrict)->pluck('id');
                            }
                        }
                    })
                    ->required()
                    ->afterStateUpdated(function(Set $set){
                        $set('district_id', []);
                    }),


                Hidden::make('region')
                    ->default(function(Get $get){
                        if(auth()->user()->hasRole('Dinomination Admin') || auth()->user()->hasRole('Diocese Admin')){
                            return null;
                        }else{
                            if(auth()->user()->hasRole('Parish Admin')){
                                $churches = Church::whereId(auth()->user()->church_id)->first();
                                return $churches->region_id;
                            }
                            else if(blank($get('church_district_id'))){
                                return null;
                            }
                            else{
                                $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->pluck('regions');
                                $churchDistrict = $churchDistrict->flatten();

                                if($churchDistrict->count() == 1){
                                    return Region::whereIn('name', $churchDistrict)->pluck('id')[0];
                                }
                            }
                        }
                    }),

                Select::make('district_id')
                    ->preload()
                    ->searchable()
                    ->label('District')
                    ->options(function (Get $get) {
                        if (blank($get('region_id')) && (! auth()->user()->hasRole('Parish Admin'))) {
                            return [];
                        }

                        if(auth()->user()->hasRole('Dinomination Admin') || auth()->user()->hasRole('Diocese Admin')){
                            if(blank($get("church_district"))){
                                return [];
                            }else{
                                $churchDistrict = ChurchDistrict::whereId($get("church_district"))->first();
                                $regions = []; $districts = [];
                                foreach($churchDistrict->districts as $key => $district){
                                    $regions[] = $district[$churchDistrict->regions[$key]];
                                    $districts = array_merge($districts, District::whereIn('id', $district[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                                }

                                if(blank($get('region_id'))){
                                    return District::whereIn('id', $districts)->where('region_id', $get('region'))->pluck('name', 'id');
                                }else{
                                    return District::whereIn('id', $districts)->where('region_id', $get('region_id'))->pluck('name', 'id');
                                }
                            }
                        }else{
                            if(auth()->user()->hasRole('Parish Admin')){
                                //get church district id
                                $church_district_id = Church::whereId(auth()->user()->church_id)->first();
                                $churchDistrict = ChurchDistrict::whereId($church_district_id->church_district_id)->first();
                                $regions = []; $districts = [];
                                foreach($churchDistrict->districts as $key => $district){
                                    $regions[] = $district[$churchDistrict->regions[$key]];
                                    $districts = array_merge($districts, District::whereIn('id', $district[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                                }

                                if(blank($get('region_id'))){
                                    return District::whereIn('id', $districts)->where('region_id', $get('region'))->pluck('name', 'id');
                                }else{
                                    return District::whereIn('id', $districts)->where('region_id', $get('region_id'))->pluck('name', 'id');
                                }
                            }
                            else if(blank($get('church_district_id'))){
                                return [];
                            }
                            else{
                                $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->first();
                                $regions = []; $districts = [];
                                foreach($churchDistrict->districts as $key => $district){
                                    $regions[] = $district[$churchDistrict->regions[$key]];
                                    $districts = array_merge($districts, District::whereIn('id', $district[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                                }

                                if(blank($get('region_id'))){
                                    return District::whereIn('id', $districts)->where('region_id', $get('region'))->pluck('name', 'id');
                                }else{
                                    return District::whereIn('id', $districts)->where('region_id', $get('region_id'))->pluck('name', 'id');
                                }
                            }
                        }
                    })
                    ->visible(function(Get $get){
                        if(blank($get('church_district_id')) && (! auth()->user()->hasRole('Parish Admin'))){
                            return true;
                        }
                        else if(auth()->user()->hasRole('Parish Admin')){
                            return true;
                        }
                        else{
                            $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->first();
                            $regions = []; $districts = [];
                            foreach($churchDistrict->districts as $key => $district){
                                $regions[] = $district[$churchDistrict->regions[$key]];
                                $districts = array_merge($districts, District::whereIn('id', $district[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                            }

                            if(count($districts) == 1){
                                return false;
                            }else{
                                return true;
                            }
                        }
                    })
                    ->default(function(Get $get){
                        if(auth()->user()->hasRole('Parish Admin')){
                            $churches = Church::whereId(auth()->user()->church_id)->first();
                            return $churches->district_id;
                        }
                        else if(blank($get('church_district_id'))){
                            return [];
                        }
                        else{
                            $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->first();
                            $regions = []; $districts = [];
                            foreach($churchDistrict->districts as $key => $district){
                                $regions[] = $district[$churchDistrict->regions[$key]];
                                $districts = array_merge($districts, District::whereIn('id', $district[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                            }

                            if(count($districts) == 1){
                                return District::whereIn('id', $districts)->where('region_id', $get('region_id'))->pluck('name', 'id');
                            }
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
                            return null;
                        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                            $districts = ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('districts');
                            $districts = $districts->flatten();
    
                            if($districts->count() == 1){
                                return District::whereIn('id', $districts)->pluck('id')[0];
                            }
                        }else if(auth()->user()->hasRole('Parish Admin')){
                            return null;
                        }
                    }),

                Select::make('ward_id')
                    ->preload()
                    ->searchable()
                    ->label('Ward')
                    ->options(function (Get $get) {
                        if (blank($get('district_id')) && (! auth()->user()->hasRole('Parish Admin'))) {
                            return [];
                        }

                        if(auth()->user()->hasRole('Dinomination Admin') || auth()->user()->hasRole('Diocese Admin')){
                            if(blank($get("church_district"))){
                                return [];
                            }else{
                                $churchDistrict = ChurchDistrict::whereId($get("church_district"))->first();
                                $regions = []; $wards = [];
                                foreach($churchDistrict->wards as $key => $ward){
                                    $regions[] = $ward[$churchDistrict->regions[$key]];
                                    $wards = array_merge($wards, Ward::whereIn('id', $ward[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                                }
                                if(blank($get('district_id'))){
                                    return Ward::whereIn('id', $wards)->where('district_id', $get('district'))->pluck('name', 'id');
                                }else{
                                    return Ward::whereIn('id', $wards)->where('district_id', $get('district_id'))->pluck('name', 'id');
                                }
                            }
                        }else{
                            if(auth()->user()->hasRole('Parish Admin')){
                                $church_district_id = Church::whereId(auth()->user()->church_id)->first();
                                $churchDistrict = ChurchDistrict::whereId($church_district_id->church_district_id)->first();

                                $regions = []; $wards = [];
                                foreach($churchDistrict->wards as $key => $ward){
                                    $regions[] = $ward[$churchDistrict->regions[$key]];
                                    $wards = array_merge($wards, Ward::whereIn('id', $ward[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                                }

                                if(blank($get('district_id'))){
                                    return Ward::whereIn('id', $wards)->where('district_id', $get('district'))->pluck('name', 'id');
                                }else{
                                    return Ward::whereIn('id', $wards)->where('district_id', $get('district_id'))->pluck('name', 'id');
                                }
                            }
                            else if(blank($get('church_district_id'))){
                                return [];
                            }else{
                                $churchDistrict = ChurchDistrict::whereId($get("church_district_id"))->first();
                                $regions = []; $wards = [];
                                foreach($churchDistrict->wards as $key => $ward){
                                    $regions[] = $ward[$churchDistrict->regions[$key]];
                                    $wards = array_merge($wards, Ward::whereIn('id', $ward[$churchDistrict->regions[$key]])->pluck('id' )->toArray());
                                }

                                if(blank($get('district_id'))){
                                    return Ward::whereIn('id', $wards)->where('district_id', $get('district'))->pluck('name', 'id');
                                }else{
                                    return Ward::whereIn('id', $wards)->where('district_id', $get('district_id'))->pluck('name', 'id');
                                }
                            }
                        }
                    })->required(),

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

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('pictures')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->disk('churchImages'),
                TextColumn::make('name')
                ->searchable(),
                TextColumn::make('churchDistrict.name')
                    ->description(fn($record): string => "Diocese of ". Diocese::whereId($record->churchDistrict->diocese_id)->pluck('name')[0])
                    ->searchable(),
                TextColumn::make('parent_church')
                ->formatStateUsing(fn($state) : string => Church::whereId($state)->pluck('name')[0] ?? '-')
                ->default('Null'),
                TextColumn::make('church_type')
                ->searchable(),
                TextColumn::make('region_id')
                    ->label('Address')
                    ->formatStateUsing(function(string $state, $record) {
                        $region = Region::whereId($state)->first();
                        $district = District::where('id', $record->district_id)->first();
                        $ward = Ward::where('id', $record->ward_id)->first();

                        return "{$ward->name}, {$district->name} {$region->name}";
                    }),
                TextColumn::make('pastors.churchMember.full_name')
                    ->label('Pastors')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->default('Not assigned a Pastor')
                    // location provide link to maps
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(function($record){
                    if(auth()->user()->hasRole('Dinomination Admin') && $record->church_type == 'dinomination' && auth()->user()->checkPermissionTo('update Church')){
                        return true;
                    }else if(auth()->user()->hasRole('Diocese Admin') && $record->church_type == 'diocese' && auth()->user()->checkPermissionTo('update Church')){
                        return true;
                    }else if(auth()->user()->hasRole('ChurchDistrict Admin') && $record->church_type == 'parish' && auth()->user()->checkPermissionTo('update Church')){
                        return true;
                    }else if(auth()->user()->hasRole('Parish Admin') && $record->church_type == 'sub_parish' && auth()->user()->checkPermissionTo('update Church')){
                        return true;
                    }else{
                        return false;
                    }
                }),
                Tables\Actions\Action::make('assign_pastor')
                    ->label(fn($record) => $record->pastors == Null ? 'Assign Pastor' : (count($record->pastors) == 0 ? 'Assign Pastor' : 'Pastor Assigned'))
                    ->form([
                        Select::make('pastors')
                            ->label('pastors')
                            ->reactive()
                            ->multiple()
                            ->searchable()
                            ->options(ChurchMember::all()->pluck('full_name', 'id'))
                            ->required(),

                        Select::make('senior_pastor')
                            ->required()
                            ->options(function(Get $get){
                                return ChurchMember::whereIn('id', $get('pastors'))->pluck('full_name', 'id')->toArray();
                            })
                            ->visible(function(Get $get){
                                if(! blank($get('pastors'))){
                                    if(count($get('pastors')) > 1){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }
                            }),
                        
                    ])
                    ->action(function(array $data, Church $record){
                        // dd($data['pastors']);
                        $record->update([
                            'pastors' => $data['pastors'],
                        ]);

                        if(count($data['pastors']) > 1){
                            foreach($data['pastors'] as $key => $pastor){
                                $pastor = new Pastor;
                                $pastor->church_member_id = $data['pastors'][$key];
                                $pastor->date_registered = now();
                                $pastor->status = 'Active';
                                $pastor->church_assigned_id = $record->id;
                                $pastor->title = $data['senior_pastor'] == Null ? 'pastor' : ($data['senior_pastor'] == $data['pastors'][$key] ? 'senior' : 'pastor');
                                $pastor->save();
                            }
                        }else{
                            $pastor = new Pastor;
                            $pastor->church_member_id = $pastor;
                            $pastor->date_registered = now();
                            $pastor->status = 'Active';
                            $pastor->church_assigned_id = $record->id;
                            $pastor->title = 'pastor';
                            $pastor->save();
                        }

                        Notification::make()
                            ->title('Assign pastor to church successfully')
                            ->success()
                            ->send();

                    })
                    ->visible(function($record){
                        if(auth()->user()->hasRole('Dinomination Admin') && $record->church_type == 'dinomination' && auth()->user()->checkPermissionTo('assign Pastor')){
                            return true;
                        }else if(auth()->user()->hasRole('Diocese Admin') && $record->church_type == 'diocese' && auth()->user()->checkPermissionTo('assign Pastor')){
                            return true;
                        }else if(auth()->user()->hasRole('ChurchDistrict Admin') && $record->church_type == 'parish' && auth()->user()->checkPermissionTo('assign Pastor')){
                            return true;
                        }else if(auth()->user()->hasRole('Parish Admin') && $record->church_type == 'sub_parish' && auth()->user()->checkPermissionTo('assign Pastor')){
                            return true;
                        }else {
                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->visible(auth()->user()->hasRole(['Church Secretary', 'Senior Pastor', 'Pastor']) && auth()->user()->checkPermissionTo('delete Church')),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->church_members->count() > 0 ? false : true,
            );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if(auth()->user()->hasRole('Parish Admin')){
            $church = Church::where('parent_church', auth()->user()->church_id)->whereNotNull('parent_church')->pluck('id');
            return parent::getEloquentQuery()->whereIn('id', $church);
        }else if(auth()->user()->hasRole('Diocese Admin')){
            return parent::getEloquentQuery()->whereIn('church_district_id', ChurchDistrict::whereIn('diocese_id', Diocese::whereId(auth()->user()->diocese_id)->pluck('id'))->pluck('id'));
        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
            $church = Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id');
            return parent::getEloquentQuery()->whereIn('id', $church);
        }else if(auth()->user()->hasRole('Dinomination Admin')){
            return parent::getEloquentQuery();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChurches::route('/'),
            'create' => Pages\CreateChurch::route('/create'),
            'edit' => Pages\EditChurch::route('/{record}/edit'),
        ];
    }
}
