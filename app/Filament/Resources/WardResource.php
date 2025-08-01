<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WardResource\Pages;
use App\Filament\Resources\WardResource\RelationManagers;
use App\Models\Ward;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;

class WardResource extends Resource
{
    protected static ?string $model = Ward::class;

    protected static ?string $navigationIcon = 'fas-road';

    protected static ?string $navigationGroup = 'Settings';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Ward');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('region')
                    ->preload()
                    ->options(Region::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),

                Select::make('district_id')
                    ->preload()
                    ->searchable()
                    ->label('District')
                    ->options(function (Get $get) {
                        if (blank($get('region'))) {
                            return [];
                        }

                        $region = Region::whereId($get('region'))->first();

                        return $region->districts()->pluck('name', 'id')->toArray();
                    })
                    ->reactive()
                    ->required(),

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
                TextColumn::make('district.name'),
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
                ->disabled(! auth()->user()->checkPermissionTo('update Ward'))
                ->visible(auth()->user()->checkPermissionTo('update Ward')),
                Tables\Actions\DeleteAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('udelete Ward'))
                ->visible(auth()->user()->checkPermissionTo('delete Ward')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->disabled(! auth()->user()->checkPermissionTo('udelete Ward'))
                    ->visible(auth()->user()->checkPermissionTo('delete Ward')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWards::route('/'),
        ];
    }
}
