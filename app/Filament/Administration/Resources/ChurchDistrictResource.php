<?php

namespace App\Filament\Administration\Resources;

use App\Filament\Administration\Resources\ChurchDistrictResource\Pages;
use App\Filament\Administration\Resources\ChurchDistrictResource\RelationManagers;
use App\Models\ChurchDistrict;
use App\Models\Church;
use App\Models\Diocese;
use App\Models\District;
use App\Models\Ward;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;

class ChurchDistrictResource extends Resource
{
    protected static ?string $model = ChurchDistrict::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Church Structure';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('admin')->user()->checkPermissionTo('view-any ChurchDistrict');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        // Select::make('diocese_id')
                        // ->reactive()
                        // ->searchable()
                        // ->options(Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('name', 'id'))
                        // ->required(),

                        Hidden::make('diocese_id')
                        ->default(auth()->user()->diocese_id),

                        TextInput::make('name')
                            ->required()
                            ->unique(modifyRuleUsing: function(Unique $rule, Get $get, $state){
                                return $rule->where('diocese_id', $get('diocese_id'))
                                            ->where('name', $state);
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
                                        if(blank($get('../../diocese_id'))){
                                            return [];
                                        }else{
                                            return Region::whereIn('name', Diocese::whereId($get('../../diocese_id'))->pluck('regions')->collapse())->pluck('name', 'name')->toArray();
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
                                        if(blank($get('regions'))){
                                            return [];
                                        }else{
                                            $churchdistrict = Diocese::whereId($get('../../diocese_id'))->pluck('districts');

                                            return District::whereIn('id', $churchdistrict->flatten())->where('region_id', Region::whereName($get('regions'))->pluck('id'))->pluck('name', 'id');
                                            // return District::whereIn('id', Diocese::whereId($get('../../diocese_id'))->pluck('districts'))->pluck('name', 'id');
                                            // return $churchdistrict->keys();
                                        }
                                    })
                                    ->visible(function(Get $get){
                                        if($get('all_districts')){
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }),

                                // Checkbox::make('all_wards')
                                //     ->inline(false)
                                //     ->default(true)
                                //     ->reactive()
                                //     ->visible(function(Get $get){
                                //             if($get('districts')){
                                //                 return true;
                                //             }else{
                                //                 return false;
                                //             }
                                //     }),

                                // Select::make('wards')
                                //     ->multiple()
                                //     ->options(function(Get $get){
                                //         if(blank($get('districts'))){
                                //             return [];
                                //         }else{
                                //             return Ward::whereIn('district_id', $get('districts'))->pluck('name', 'id');
                                //         }
                                //     })
                                //     ->visible(function(Get $get){
                                //         if($get('all_wards')){
                                //             return false;
                                //         }else{
                                //             return true;
                                //         }
                                //     }),
                                
                                ])
                            ->collapsible()
                            ->addActionLabel('Add Church District Details'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('diocese.name')
                //     ->label('Diocese Name')
                //     ->searchable(),

                TextColumn::make('name')
                ->label('ChurchDistrict Name')
                ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'Active' => 'success',
                        'Inactive' => 'danger'
                    }),
                TextColumn::make('regions')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->searchable(),

                TextColumn::make('districts')
                    ->listWithLineBreaks()
                    ->limitList(4)
                    ->formatStateUsing(function($state){
                        return District::where('id', $state)->pluck('name')[0];
                        
                    }),

                // TextColumn::make('wards')
                //     ->listWithLineBreaks()
                //     ->limitList(3)
                //     ->formatStateUsing(function(array $state): array{
                //         return Ward::whereIn('id', $state)->pluck('name');
                //     }),


            ])
            ->filters([
                SelectFilter::make('status')
                ->options([
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->hidden(! auth()->user()->checkPermissionTo('update ChurchDistrict')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->hidden(! auth()->user()->checkPermissionTo('delete ChurchDistrict')),
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
        if(auth()->user()->hasRole('Diocese Admin')){
            return parent::getEloquentQuery()->where('diocese_id', auth()->user()->diocese_id);
        }else if(auth()->user()->hasRole('Dinomination Admin')){
            return parent::getEloquentQuery()->whereIn('diocese_id', Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('id'));
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChurchDistricts::route('/'),
            'create' => Pages\CreateChurchDistrict::route('/create'),
            'edit' => Pages\EditChurchDistrict::route('/{record}/edit'),
        ];
    }
}
