<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferingResource\Pages;
use App\Filament\Resources\OfferingResource\RelationManagers;
use App\Models\ChurchMember;
use App\Models\Offering;
use App\Models\Card;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\Section;
use App\Filament\Resources\OfferingResource\Widgets\OfferingOverview;
use Carbon\Carbon;

class OfferingResource extends Resource
{
    protected static ?string $model = Offering::class;

    protected static ?string $navigationIcon = 'fas-money-bill-transfer';

    protected static ?string $modelLabel = 'Card Offering';

    protected static ?string $navigationGroup = 'Church Offerings';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Offering');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('card_no')
                    ->options(ChurchMember::whereNotNull('card_no')->where('church_id', auth()->user()->church_id)->pluck('card_no', 'card_no')->toArray())
                    ->searchable()
                    ->required(),

                Repeater::make('cards')
                    ->schema([
                        Select::make('card_type')
                            ->options(Card::all()->pluck('card_name', 'id')->toArray())
                            ->searchable()
                            ->distinct()
                            ->required()
                            ->unique(modifyRuleUsing: function(Unique $rule, callable $get, $state){
                                return $rule->where('card_no', $get('../../card_no'))
                                            ->where('card_type', $state)
                                            ->where('amount_registered_on', $get('../../amount_registered_on'));
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'The offering for this card has already been registered.',
                            ]),

                        TextInput::make('amount_offered')
                            ->numeric()
                            ->required(),

                    ])
                    ->visible(function(string $context){
                        if($context == 'create'){
                            return true;
                        }else{
                            if($context == 'edit'){
                                return false;
                            }
                        }
                    })
                    ->columnSpan('full')
                    ->columns(2),

                Section::make('cards')
                    ->schema([
                        Select::make('card_type')
                            ->options(Card::all()->pluck('card_name', 'id')->toArray())
                            ->searchable()
                            ->distinct()
                            ->required()
                            ->unique(modifyRuleUsing: function(Unique $rule, callable $get, $state){
                                return $rule->where('card_no', $get('../../card_no'))
                                            ->where('card_type', $state)
                                            ->where('amount_registered_on', $get('../../amount_registered_on'));
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'The offering for this card has already been registered.',
                            ]),

                        TextInput::make('amount_offered')
                            ->numeric()
                            ->required(),
                    ])
                    ->visible(function(String $context){
                        if($context == 'create'){
                            return false;
                        }else{
                            if($context == 'edit'){
                                return true;
                            }
                        }
                    })
                    ->columnSpan('full')
                    ->columns(2),

                Hidden::make('created_by')
                    ->default(auth()->user()->id),

                Hidden::make('updated_by')
                    ->default(auth()->user()->id),

                Hidden::make('church_id')
                    ->default(auth()->user()->church_id)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_no')
                ->searchable(isIndividual: true),
                TextColumn::make('card_type')
                ->formatStateUsing(fn($state) => Card::whereId($state)->pluck('card_name')[0])
                ->searchable(isIndividual: true),
                TextColumn::make('amount_offered'),
                TextColumn::make('amount_registered_on')
                    ->date()
                    ->searchable(isIndividual: true),
                TextColumn::make('created_by')
                    ->formatStateUsing(function (string $state) {
                        $name = ChurchMember::where('user_id', $state)->get();
                        return "{$name[0]->surname}, {$name[0]->middle_name} {$name[0]->first_name}";
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(! auth()->user()->checkPermissionTo('update Offering'))
                    ->visible(auth()->user()->checkPermissionTo('update Offering'))
                    ->hidden(fn(Offering $record) => Carbon::parse($record->amount_registered_on)->equalTo(Carbon::now()->startOfWeek(Carbon::SUNDAY)->subDays(7))),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    //     ->disabled(! auth()->user()->checkPermissionTo('delete Offering'))
                    //     ->visible(auth()->user()->checkPermissionTo('delete Offering')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OfferingOverview::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id)->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferings::route('/'),
            'create' => Pages\CreateOffering::route('/create'),
            'edit' => Pages\EditOffering::route('/{record}/edit'),
        ];
    }
}
