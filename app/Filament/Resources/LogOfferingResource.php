<?php

namespace App\Filament\Resources;

use App\Models\ChurchMember;
use DB;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LogOffering;
use App\Models\AdhocOffering;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LogOfferingResource\Pages;
use App\Filament\Resources\LogOfferingResource\RelationManagers;
use App\Models\Church;
use App\Models\ChurchMass;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use Illuminate\Validation\Rules\Unique;
use App\Filament\Resources\LogOfferingResource\Widgets\LogOfferingOverview;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\Section as InfolistSection;
use App\Filament\Resources\LogOfferingResource\RelationManagers\ChurchAuctionAuctionItemsRelationManager;
use App\Filament\Resources\LogOfferingResource\RelationManagers\ChurchAuctionAuctionItemPledgesRelationManager;
use App\Filament\Resources\LogOfferingResource\RelationManagers\ChurchAuctionAuctionItemPaymentsRelationManager;

class LogOfferingResource extends Resource
{
    protected static ?string $model = LogOffering::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Church Offerings';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any LogOffering');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistGrid::make(4)
                    ->schema([
                        TextEntry::make('adhoc_offering_id')
                            ->label('Adhoc Offering')
                            ->formatStateUsing(fn($state) => AdhocOffering::find($state)->title),
                        TextEntry::make('church_mass_id')
                            ->label('Church Mass')
                            ->formatStateUsing(fn($state) => ChurchMass::find($state)->title),
                        TextEntry::make('amount_committee')
                            ->label('amount')
                            ->visible(auth()->user()->hasRole('Committee Member')),
                        TextEntry::make('date')
                            ->date(),
                        TextEntry::make('amount_accountant')
                            ->label('amount')
                            ->visible(auth()->user()->hasRole('Church Accountant')),
                    ]),
                InfolistSection::make('Auction Details')
                    ->description('This auction relates to the adhoc offering.')
                    ->schema([
                        TextEntry::make('type')
                            ->default(fn(LogOffering $record) => AdhocOffering::find($record->church_auction->type)->title),
                        TextEntry::make('status')
                            ->default(fn(LogOffering $record) => $record->church_auction->status)
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Pending Auction' => 'warning',
                                'Auction Complete' => 'success',
                            }),
                        TextEntry::make('auction_description')
                            ->default(fn(LogOffering $record) => $record->church_auction->auction_description)
                            ->markdown()
                            ->columnSpanFull()
                    ])
                    ->columns(3)
                    ->visible(fn(LogOffering $record) => $record->has_auction)
                
            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Select::make('adhoc_offering_id')
                            ->label('Adhoc Offering')
                            ->options(DB::table('adhoc_offerings')->where('status', 'Active')->select('title','id')->pluck('title', 'id'))
                            ->required()
                            ->reactive(),
    
                        Select::make('church_mass_id')
                            ->label('church_mass')
                            ->options(ChurchMass::all()->pluck('title', 'id'))
                            ->reactive()
                            ->unique(modifyRuleUsing: function (Unique $rule, $state) {
                                $log_date = LogOffering::whereDate('date', Carbon::now()->startOfWeek()->subDay()->addDays(ChurchMass::whereId($state)->pluck('day')[0]))->first();
                                if($log_date){
                                    return $rule->where('adhoc_offering_id', $state)
                                    ->where('date', $log_date->date);
                                }

                            },ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'The offering has already been registered.',
                            ])
                            ->required(),
        
                        TextInput::make('amount_committee')
                            ->label('amount')
                            ->required(),

                        TextInput::make('amount_accountant')
                            ->label('amount')
                            ->required()
                            ->visible(false),
                    ]),

                Hidden::make('user_id')
                    ->default(auth()->user()->id),

                Hidden::make('church_id')
                ->default(auth()->user()->church_id),

                Checkbox::make('has_auction')
                    ->reactive()
                    ->inline('false')
                    ->default(false),

                Section::make('Auction Details')
                    ->description('General details about the auction')
                    ->schema([
                        Select::make('type')
                            ->label('auction_type')
                            ->options(DB::table('adhoc_offerings')->where('status', 'Active')->select('title','id')->pluck('title', 'id')),

                        RichEditor::make('auction_description')
                            ->disableToolbarButtons([
                                'codeBlock',
                                'strike',
                                'link',
                                'attachFiles'
                            ])
                            ->columnSpanFull(),

                        Hidden::make('status')
                            ->default('Pending Auction')
                        ])
                        ->visible(function(Get $get){
                            if($get('has_auction')){
                                return true;
                            }else{
                                return false;
                            }
                        }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('adhoc_offering_id')
                    ->label('Adhoc Offers')
                    ->formatStateUsing(function($state){
                        return AdhocOffering::where('id', $state)->pluck('title')[0];
                    })
                    ->searchable(),
                TextColumn::make('amount_committee')
                ->numeric(
                    decimalPlaces: 0,
                    decimalSeparator: '.',
                    thousandsSeparator: ',',
                )
                ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                // TextColumn::make('user_id')
                // ->label('Commiitee')
                // ->formatStateUsing(function($state){
                //     return ChurchMember::where('user_id', $state)->pluck('full_name')[0];
                // }),
                TextColumn::make('church_id')->label('Church')
                ->formatStateUsing(function ($state){
                    return Church::where('id', $state)->pluck('name')[0];
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(! auth()->user()->checkPermissionTo('update LogOffering'))
                    ->visible(auth()->user()->checkPermissionTo('update LogOffering')),
                Tables\Actions\ViewAction::make()
                    ->disabled(!(auth()->user()->checkPermissionTo('view LogOffering')))
                    ->visible(auth()->user()->checkPermissionTo('view LogOffering')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    //     ->disabled(! auth()->user()->checkPermissionTo('delete LogOffering'))
                    //     ->visible(auth()->user()->checkPermissionTo('delete LogOffering')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ChurchAuctionAuctionItemsRelationManager::class,
            ChurchAuctionAuctionItemPledgesRelationManager::class,
            ChurchAuctionAuctionItemPaymentsRelationManager::class
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LogOfferingOverview::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id)->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogOfferings::route('/'),
            'create' => Pages\CreateLogOffering::route('/create'),
            'edit' => Pages\EditLogOffering::route('/{record}/edit'),
            'view' => Pages\ViewLogOffering::route('/{record}'),
        ];
    }
}
