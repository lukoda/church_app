<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers;
use App\Models\Card;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\ColorColumn;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'fas-envelopes-bulk';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Card');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Select::make('church_id')
                            ->label('Church')
                            ->options(Church::where('id', auth()->user()->church_id)->pluck('name', 'id'))
                            ->default(Church::where('id', auth()->user()->church_id)->pluck('id')[0])
                            ->searchable(),

                        TextInput::make('card_name')
                            ->unique(
                                modifyRuleUsing: function(Unique $rule, Get $get, $state){
                                    return $rule->where('card_name', $state)
                                                ->where('church_id', $get('church_id'));
                                },
                                ignoreRecord: true
                            )
                            ->required(),

                        // TextInput::make('card_duration')
                        //     ->numeric()
                        //     ->required()
                        //     ->prefix('Months'),

                        ColorPicker::make('card_color'),

                        TextInput::make('card_target')
                            ->numeric()
                            ->reactive()
                            ->default(0),

                        TextInput::make('minimum_target')
                            ->numeric()
                            ->helperText('Provide minimum amount for each church member')
                            ->visible(function(Get $get){
                                if($get('card_target')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        Select::make('card_status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive'
                            ])
                            ->required()
                            ->default('inactive'),
                            ]),


                // RichEditor::make('verse_for_card')
                //     ->disableToolbarButtons([
                //         'codeBlock',
                //         'strike',
                //         'link',
                //         'attachFiles'
                //     ]),

                RichEditor::make('card_description')
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
                ColorColumn::make('card_color')
                    ->copyable()
                    ->copyMessage('Color code copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('card_name')
                ->searchable(),
                // TextColumn::make('card_duration')
                //     ->description('In Months'),
                // TextColumn::make('verse_for_card')
                //     ->limit(50)
                //     ->html(),
                TextColumn::make('card_target')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->description(fn($record) => 'Minimum Amount '.$record->minimum_target),
                TextColumn::make('card_description')
                    ->limit(50)
                    ->html(),
                TextColumn::make('card_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'active' => 'success',
                        'inactive' => 'danger'
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible((auth()->user()->hasRole('Church Secretary')) && auth()->user()->checkPermissionTo('update Card')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->visible(auth()->user()->hasRole(['Church Secretary', 'Senior Pastor', 'Pastor'])  && auth()->user()->checkPermissionTo('delete Card')),
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
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
