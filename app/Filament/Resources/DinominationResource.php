<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DinominationResource\Pages;
use App\Filament\Resources\DinominationResource\RelationManagers;
use App\Models\Dinomination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;

class DinominationResource extends Resource
{
    protected static ?string $model = Dinomination::class;

    protected static ?string $navigationIcon = 'fas-hands-praying';

    protected static ?string $navigationGroup = 'Church Structure';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Dinomination');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),

                RichEditor::make('description')
                    ->disableToolbarButtons([
                        'codeBlock',
                        'strike',
                        'link',
                        'attachFiles'
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('description')
                    ->limit(50)
                    ->html(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update Dinomination'))
                ->visible(auth()->user()->checkPermissionTo('update Dinomination')),
                Tables\Actions\DeleteAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('delete Dinomination'))
                ->visible(auth()->user()->checkPermissionTo('delete Dinomination')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete Dinomination'))
                    // ->visible(auth()->user()->checkPermissionTo('delete Dinomination')),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('id', auth()->user()->dinomination_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDinominations::route('/'),
        ];
    }
}
