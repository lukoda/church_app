<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdhocOfferingResource\Pages;
use App\Filament\Resources\AdhocOfferingResource\RelationManagers;
use App\Models\AdhocOffering;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class AdhocOfferingResource extends Resource
{
    protected static ?string $model = AdhocOffering::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any AdhocOffering');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            TextInput::make('title'),
            RichEditor::make('description')->columnSpan(1),
            Hidden::make('church_id')
                ->default(auth()->user()->church_id),
            Hidden::make('status')
                ->default('Active'),
            ])->columns(1);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('description')
                    ->html()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'Active' => 'success',
                        'Inactive' => 'danger'
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->hidden(! (auth()->user()->checkPermissionTo('update AdhocOffering') && auth()->user()->hasRole('Church Secretary'))),
                Tables\Actions\Action::make('deactivate')
                    ->label(fn($record) => $record->status == 'Active' ? 'Deactivate' : 'Activate')
                    ->action(function($record){
                        if($record->status == 'Active'){
                            $record->update([
                                'status' => 'Inactive'
                            ]);
                        }else{
                            if($record->status == 'Inactive'){
                                $record->update([
                                    'status' => 'Active'
                                ]);
                            }
                        }

                        Notification::make()
                            ->title('Adhoc offering status updated successfully')
                            ->success()
                            ->send();
                    })
                    ->hidden(! auth()->user()->checkPermissionTo('deactivate AdhocOffering'))
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
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id)->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdhocOfferings::route('/'),
            'create' => Pages\CreateAdhocOffering::route('/create'),
            'edit' => Pages\EditAdhocOffering::route('/{record}/edit'),
        ];
    }

}
