<?php

namespace App\Filament\Administration\Resources;

use App\Filament\Administration\Resources\DioceseResource\Pages;
use App\Filament\Administration\Resources\DioceseResource\RelationManagers;
use App\Models\Dinomination;
use App\Models\Diocese;
use App\Models\Region;
use App\Models\District;
use Arr;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;

class DioceseResource extends Resource
{
    protected static ?string $model = Diocese::class;

    protected static ?string $navigationIcon = 'fas-place-of-worship';

    protected static ?string $navigationGroup = 'Church Structure';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('admin')->user()->checkPermissionTo('view-any Diocese');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Grid::make(3)
                ->schema([
                    Hidden::make('dinomination_id')
                        ->default(auth()->user()->dinomination_id),

                    TextInput::make('name')
                        ->required()
                        ->unique(ignoreRecord: true),

                    Select::make('status')
                        ->options([
                            'Active' => 'Active',
                            'Inactive' => 'Inactive'
                        ])
                        ->required(),
                        ]),

                Repeater::make('diocese details')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([

                        Select::make('regions')
                            ->searchable()
                            ->options(function(Get $get, string $context, $record){
                                if(Diocese::where('dinomination_id', $get('dinomination_id'))->count() > 0){
                                    $diocese = Diocese::where('dinomination_id', $get('dinomination_id'))->pluck('regions');
                                    return [];
                                }else{
                                    if($context == 'edit'){
                                        $diocese = Diocese::all()->where('id', '!=', $record->id)->pluck('regions');
                                        return Region::whereNotIn('name', $diocese->collapse())->pluck('name','name');
                                    }else{
                                        $diocese = Diocese::all()->pluck('regions');
                                        return Region::whereNotIn('name', $diocese->collapse())->pluck('name','name');
                                    }
                                }
                            })
                            ->distinct()
                            ->required()
                            ->reactive()
                            ->unique()
                            ->afterStateUpdated(function(Set $set){
                                $set('districts', []);
                                $set('all_districts', true);
                            }),

                        Checkbox::make('all_districts')
                            ->inline(false)
                            ->default(true)
                            ->reactive(),

                        Select::make('districts')
                            ->multiple()
                            ->options(function(Get $get, $context){
                                if(blank($get('regions')) && $context == 'create'){
                                    return [];
                                }else{
                                    return District::where('region_id', Region::where('name', $get('regions'))->pluck('id')[0])->pluck('name', 'id');
                                }
                            })
                            ->visible(function(Get $get){
                                if($get('all_districts')){
                                    return false;
                                }else{
                                    return true;
                                }
                            })
                        ])
                    ->collapsible()
                    ->addActionLabel('Add Diocese Details'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'Active' => 'success',
                        'Inactive' => 'danger'
                    }),
                TextColumn::make('regions')
                    ->formatStateUsing(function($state){
                        if(is_numeric($state)){
                            return Region::whereId($state)->pluck('name')[0];
                        }else{
                            return $state;
                        }
                    })
                    ->listWithLineBreaks(),
                TextColumn::make('dinomination.name'),
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
                ->disabled(! auth()->user()->checkPermissionTo('update Diocese'))
                ->visible(auth()->user()->checkPermissionTo('update Diocese')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete Diocese'))
                    // ->visible(auth()->user()->checkPermissionTo('delete Diocese'))
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
        return parent::getEloquentQuery()->where('dinomination_id', auth()->user()->dinomination_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDioceses::route('/'),
            'create' => Pages\CreateDiocese::route('/create'),
            'edit' => Pages\EditDiocese::route('/{record}/edit'),
        ];
    }
}
