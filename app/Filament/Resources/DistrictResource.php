<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictResource\Pages;
use App\Filament\Resources\DistrictResource\RelationManagers;
use App\Models\District;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationIcon = 'fas-building-circle-arrow-right';

    protected static ?string $navigationGroup = 'Settings';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any District');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('region_id')
                    ->options(Region::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('region.name')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update District'))
                ->visible(auth()->user()->checkPermissionTo('update District')),
                Tables\Actions\DeleteAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('delete District'))
                ->visible(auth()->user()->checkPermissionTo('delete District')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete District'))
                    // ->visible(auth()->user()->checkPermissionTo('delete District')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDistricts::route('/'),
        ];
    }
}
