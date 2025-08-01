<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionResource\Pages;
use App\Filament\Resources\RegionResource\RelationManagers;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static ?string $navigationIcon = 'fas-city';

    protected static ?string $navigationGroup = 'Settings';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Region');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                Toggle::make('status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger',
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update Region'))
                ->visible(auth()->user()->checkPermissionTo('update Region')),
                Tables\Actions\DeleteAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('delete Region'))
                ->visible(auth()->user()->checkPermissionTo('delete Region')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete Region'))
                    // ->visible(auth()->user()->checkPermissionTo('delete Region')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRegions::route('/'),
        ];
    }
}
