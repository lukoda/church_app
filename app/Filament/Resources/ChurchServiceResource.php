<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChurchServiceResource\Pages;
use App\Filament\Resources\ChurchServiceResource\RelationManagers;
use App\Models\ChurchService;
use App\Models\Church;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class ChurchServiceResource extends Resource
{
    protected static ?string $model = ChurchService::class;

    protected static ?string $navigationIcon = 'fas-handshake-simple';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any ChurchService');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),

                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactve' => 'Inactive'
                    ])
                    ->required()
                    ->default('inactive'),

                RichEditor::make('description')
                    ->disableToolbarButtons([
                        'codeBlock',
                        'strike',
                        'link',
                        'attachFiles'
                    ])
                    ->columnSpanFull(),

                Hidden::make('church_id')
                    ->default(auth()->user()->church_id),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('status')
                    ->label('Service Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'warning',
                        'active' => 'success',
                    }),
                TextColumn::make('description')
                    ->html()
                    ->limit(50),
                TextColumn::make('church_id')
                    ->label('church')
                    ->formatStateUsing(function($state){
                        return Church::whereId($state)->pluck('name')[0];
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(!(auth()->user()->checkPermissionTo('update ChurchService')))
                ->visible(auth()->user()->checkPermissionTo('update ChurchService')),
                Tables\Actions\DeleteAction::make()
                ->disabled(!(auth()->user()->checkPermissionTo('delete ChurchService')))
                ->visible(auth()->user()->checkPermissionTo('delete ChurchService')),
                Action::make('deactivate')
                    ->label(fn(ChurchService $record) => $record->status == 'active' ? 'Deactivate' : 'Activate')
                    ->action(function($record){
                        if($record->status == 'inactive'){
                            $record->update([
                                'status' => 'active'
                            ]);
                        }else{
                            if($record->status == 'active'){
                                $record->update([
                                    'status' => 'inactive'
                                ]);
                            }
                        }

                        Notification::make()
                            ->title('Church service status updated successfully')
                            ->success()
                            ->send();
                    })
                    ->disabled(! auth()->user()->checkPermissionTo('deactivate ChurchService'))
                    ->visible(auth()->user()->checkPermissionTo('deactivate ChurchService'))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(!(auth()->user()->checkPermissionTo('delete ChurchService')))
                    // ->visible(auth()->user()->checkPermissionTo('delete ChurchService')),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageChurchServices::route('/'),
        ];
    }
}
