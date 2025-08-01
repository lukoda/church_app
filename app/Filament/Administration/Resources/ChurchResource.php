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
                ->options(ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('name','id'))
                ->required()
                ->visible(auth()->user()->hasRole('Diocese Admin')),

                TextInput::make('name')
                    ->required()
                    ->unique(modifyRuleUsing: function(Unique $rule, $state, Get $get) {
                        return $rule->where('name', $state)
                                    ->where('church_district_id', $get('church_district_id'));
                    }, ignoreRecord:true),

                // Select::make('church_type')
                //     ->options(function () {
                //         if((auth()->user()->hasRole('Parish Admin'))){
                //             return [
                //                 'sub_parish' => 'sub parish'
                //             ];
                //         }else{
                //             if(auth()->user()->checkPermissionTo('create Church') || auth()->user()->hasRole('ChurchDistrict Admin')){
                //                 return [
                //                     'parish' => 'parish',
                //                     ];
                //             }
                //         }
                //      })
                //     ->reactive()
                //     ->required(),
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

                Hidden::make('parent_church')
                ->default(auth()->user()->church_id)
                ->visible(function(Get $get){
                        if($get('church_type') == 'sub_parish'){
                            return true;
                        }else{
                            return false;
                        }
                    }),

                // Select::make('parent_church')
                //     ->options(fn(Get $get) => Church::all()->where('church_district_id', $get('church_district_id'))->pluck('name', 'id'))
                //     ->default(fn() => auth()->user()->church_id)
                //     ->searchable()
                //     ->dehydrateStateUsing(fn($state) => Church::whereid($state)->pluck('name'))
                //     ->visible(function(Get $get){
                //         if($get('church_type') == 'sub_parish'){
                //             return true;
                //         }else{
                //             return false;
                //         }
                //     }),

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

                        // $wards = ChurchDistrict::whereId(Church::whereId(auth()->user()->church_id)->pluck('church_district_id'))->pluck('wards');
                        // $wards = $wards->flatten();

                        // return Ward::whereIn('id', $wards)->where('district_id', $get('district_id'))->pluck('name', 'id');
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
                        }

                    })
                    // ->visible(function(){
                    //     $wards = ChurchDistrict::whereId(Church::whereId(auth()->user()->church_id)->pluck('church_district_id'))->pluck('wards');
                    //     $wards = $wards->flatten();

                    //     if($wards->count() == 1){
                    //         return true;
                    //     }else{
                    //         return true;
                    //     }
                    // })
                    // ->default(function(){
                    //     $wards = ChurchDistrict::whereId(Church::whereId(auth()->user()->church_id)->pluck('church_district_id'))->pluck('wards');
                    //     $wards = $wards->flatten();

                    //     if($wards->count() == 1){
                    //         return Ward::whereIn('id', $wards)->pluck('id');
                    //     }
                    // })
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
                ->visible( auth()->user()->checkPermissionTo('update Church')),
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
                    ->visible(auth()->user()->checkPermissionTo('assign Pastor')),
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
